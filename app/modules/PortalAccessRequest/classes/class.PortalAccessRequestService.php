<?php

declare(strict_types=1);

/**
 * @phpstan-type ShapePortalAccessRequestSubmitResult array{
 *   state: string,
 *   request_id: int,
 *   queued_email: bool,
 *   confirmation_url?: string
 * }
 */
class PortalAccessRequestService
{
	public const string STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
	public const string STATUS_CONFIRMED = 'confirmed';

	public const string UI_STATE_SUBMITTED = 'submitted';
	public const string UI_STATE_CONFIRMED = 'confirmed';
	public const string UI_STATE_EXPIRED = 'expired';
	public const string UI_STATE_INVALID = 'invalid';
	public const string UI_STATE_INVALID_EMAIL = 'invalid_email';
	public const string UI_STATE_ERROR = 'error';

	public const int CONFIRMATION_TTL_SECONDS = 86400;
	public const string REQUEST_ACCESS_PATH = '/request-access/';
	public const string SESSION_KEY_FLASH_UI_STATE = 'portalAccessRequestUiState';

	/**
	 * @return ShapePortalAccessRequestSubmitResult
	 */
	public static function submit(
		string $email,
		bool $wants_updates,
		string $base_host_url,
		?string $locale = null,
		?string $timezone = null,
	): array {
		$email = trim($email);
		$email_normalized = self::normalizeEmail($email);
		$locale = self::sanitizeLocale($locale);
		$timezone = self::sanitizeTimezone($timezone);
		$pdo = Db::instance();
		$owns_transaction = !$pdo->inTransaction();
		$confirmation_token = null;
		$state = 'created';

		if ($owns_transaction) {
			$pdo->beginTransaction();
		}

		try {
			$request = EntityPortalAccessRequest::findFirst(['email_normalized' => $email_normalized]);

			if (is_null($request)) {
				[$confirmation_token, $expires_at] = self::issueConfirmationToken();

				$request = EntityPortalAccessRequest::saveFromArray([
					'email' => $email,
					'email_normalized' => $email_normalized,
					'locale' => $locale,
					'timezone' => $timezone,
					'wants_updates' => $wants_updates ? 1 : 0,
					'status' => self::STATUS_PENDING_CONFIRMATION,
					'confirmation_token_hash' => self::hashToken($confirmation_token),
					'confirmation_expires_at' => $expires_at,
				]);
			} elseif ((string) ($request->dto()['status'] ?? '') === self::STATUS_CONFIRMED) {
				$request = self::mergeWantsUpdates($request, $wants_updates);
				$request = self::mergeClientContext($request, $locale, $timezone);
				$state = 'existing_confirmed';
			} else {
				$request = self::mergeWantsUpdates($request, $wants_updates);
				$request = self::mergeClientContext($request, $locale, $timezone);
				$request_data = $request->dto();

				if (self::isExpired($request_data)) {
					[$confirmation_token, $expires_at] = self::issueConfirmationToken();

					$request = EntityPortalAccessRequest::updateById((int) ($request_data['request_id'] ?? 0), [
						'email' => $email,
						'status' => self::STATUS_PENDING_CONFIRMATION,
						'confirmation_token_hash' => self::hashToken($confirmation_token),
						'confirmation_expires_at' => $expires_at,
						'confirmed_at' => null,
					]);
					$state = 'rearmed';
				} else {
					[$confirmation_token, $expires_at] = self::issueConfirmationToken();

					$request = EntityPortalAccessRequest::updateById((int) ($request_data['request_id'] ?? 0), [
						'confirmation_token_hash' => self::hashToken($confirmation_token),
						'confirmation_expires_at' => $expires_at,
					]);
					$state = 'existing_pending';
				}
			}

			$request_data = $request->dto();
			$result = [
				'state' => $state,
				'request_id' => (int) ($request_data['request_id'] ?? 0),
				'queued_email' => true,
			];

			if ($state === 'existing_confirmed') {
				self::enqueueAlreadyConfirmedEmail($request_data);
			} elseif ($confirmation_token !== null && $confirmation_token !== '') {
				$confirmation_url = self::buildConfirmationUrl($base_host_url, $confirmation_token);
				self::enqueueConfirmationEmail($request_data, $confirmation_url);
				$result['confirmation_url'] = $confirmation_url;
			}

			if ($owns_transaction && $pdo->inTransaction()) {
				$pdo->commit();
			}

			return $result;
		} catch (Throwable $e) {
			if ($owns_transaction && $pdo->inTransaction()) {
				$pdo->rollBack();
			}

			throw $e;
		}
	}

	public static function confirm(string $token): string
	{
		$token = trim($token);

		if ($token === '') {
			return self::UI_STATE_INVALID;
		}

		$request = EntityPortalAccessRequest::findFirst([
			'confirmation_token_hash' => self::hashToken($token),
		]);

		if (is_null($request)) {
			return self::UI_STATE_INVALID;
		}

		$request_data = $request->dto();

		if ((string) ($request_data['status'] ?? '') !== self::STATUS_PENDING_CONFIRMATION) {
			return self::UI_STATE_INVALID;
		}

		if (self::isExpired($request_data)) {
			return self::UI_STATE_EXPIRED;
		}

		EntityPortalAccessRequest::updateById((int) ($request_data['request_id'] ?? 0), [
			'status' => self::STATUS_CONFIRMED,
			'confirmation_token_hash' => null,
			'confirmation_expires_at' => null,
			'confirmed_at' => date('Y-m-d H:i:s'),
		]);

		return self::UI_STATE_CONFIRMED;
	}

	public static function buildRequestAccessUrl(): string
	{
		return self::REQUEST_ACCESS_PATH;
	}

	public static function sanitizeUiState(string $ui_state): string
	{
		$allowed = [
			self::UI_STATE_SUBMITTED,
			self::UI_STATE_CONFIRMED,
			self::UI_STATE_EXPIRED,
			self::UI_STATE_INVALID,
			self::UI_STATE_INVALID_EMAIL,
			self::UI_STATE_ERROR,
		];

		return in_array($ui_state, $allowed, true) ? $ui_state : '';
	}

	public static function flashUiState(string $ui_state): void
	{
		Request::saveSessionData([self::SESSION_KEY_FLASH_UI_STATE], self::sanitizeUiState($ui_state));
	}

	public static function consumeUiState(): string
	{
		$ui_state = self::sanitizeUiState((string) Request::_SESSION(self::SESSION_KEY_FLASH_UI_STATE, ''));
		Request::saveSessionData([self::SESSION_KEY_FLASH_UI_STATE], '');

		return $ui_state;
	}

	private static function normalizeEmail(string $email): string
	{
		$email = trim($email);

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new InvalidArgumentException('A valid email address is required.');
		}

		return mb_strtolower($email);
	}

	private static function mergeWantsUpdates(EntityPortalAccessRequest $request, bool $wants_updates): EntityPortalAccessRequest
	{
		$request_data = $request->dto();

		if (!$wants_updates || (bool) ($request_data['wants_updates'] ?? false)) {
			return $request;
		}

		return EntityPortalAccessRequest::updateById((int) ($request_data['request_id'] ?? 0), [
			'wants_updates' => 1,
		]);
	}

	private static function mergeClientContext(
		EntityPortalAccessRequest $request,
		?string $locale,
		?string $timezone,
	): EntityPortalAccessRequest {
		$request_data = $request->dto();
		$update = [];

		if (
			$locale !== null
			&& trim((string) ($request_data['locale'] ?? '')) === ''
		) {
			$update['locale'] = $locale;
		}

		if (
			$timezone !== null
			&& trim((string) ($request_data['timezone'] ?? '')) === ''
		) {
			$update['timezone'] = $timezone;
		}

		if ($update === []) {
			return $request;
		}

		return EntityPortalAccessRequest::updateById((int) ($request_data['request_id'] ?? 0), $update);
	}

	/**
	 * @param array<string, mixed> $request_data
	 */
	private static function isExpired(array $request_data): bool
	{
		$expires_at = trim((string) ($request_data['confirmation_expires_at'] ?? ''));

		if ($expires_at === '') {
			return true;
		}

		$expires_at_ts = strtotime($expires_at);

		return $expires_at_ts === false || $expires_at_ts < time();
	}

	/**
	 * @return array{0: string, 1: string}
	 */
	private static function issueConfirmationToken(): array
	{
		$token = bin2hex(random_bytes(24));

		return [
			$token,
			date('Y-m-d H:i:s', time() + self::CONFIRMATION_TTL_SECONDS),
		];
	}

	private static function hashToken(string $token): string
	{
		return hash('sha256', $token);
	}

	private static function sanitizeLocale(?string $locale): ?string
	{
		$locale = trim((string) $locale);

		if ($locale === '') {
			return null;
		}

		if (!preg_match('/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8}){0,2}$/', $locale)) {
			return null;
		}

		return substr(str_replace('_', '-', $locale), 0, 32);
	}

	private static function sanitizeTimezone(?string $timezone): ?string
	{
		$timezone = trim((string) $timezone);

		if ($timezone === '') {
			return null;
		}

		try {
			new DateTimeZone($timezone);

			return substr($timezone, 0, 64);
		} catch (Throwable) {
			return null;
		}
	}

	private static function buildConfirmationUrl(string $base_host_url, string $token): string
	{
		return rtrim($base_host_url, '/') . Url::getUrl('portalAccessRequest.confirm', [
			'token' => $token,
		]);
	}

	/**
	 * @param array<string, mixed> $request_data
	 */
	private static function enqueueConfirmationEmail(array $request_data, string $confirmation_url): void
	{
		$recipient_email = (string) ($request_data['email'] ?? '');
		$platform_name = 'Radaptor Platform';
		$subject = 'Confirm your early access request';
		$safe_url = htmlspecialchars($confirmation_url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$safe_platform_name = htmlspecialchars($platform_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$html_body = <<<HTML
			<p>Thanks for requesting early access to the {$safe_platform_name}.</p>
			<p>Please confirm your email address by clicking the link below:</p>
			<p><a href="{$safe_url}">Confirm my request</a></p>
			<p>If you did not submit this request, you can ignore this email.</p>
			HTML;
		$text_body = <<<TEXT
			Thanks for requesting early access to the {$platform_name}.

			Please confirm your email address by opening this link:
			{$confirmation_url}

			If you did not submit this request, you can ignore this email.
			TEXT;

		EmailOrchestrator::enqueueTransactionalSnapshotAsSystem(
			subject: $subject,
			htmlBody: $html_body,
			textBody: $text_body,
			recipients: [
				['email' => $recipient_email],
			]
		);
	}

	/**
	 * @param array<string, mixed> $request_data
	 */
	private static function enqueueAlreadyConfirmedEmail(array $request_data): void
	{
		$recipient_email = (string) ($request_data['email'] ?? '');
		$platform_name = 'Radaptor Platform';
		$recorded_at = self::formatRecordedAt((string) ($request_data['confirmed_at'] ?? ''), (string) ($request_data['created_at'] ?? ''), (string) ($request_data['timezone'] ?? ''));
		$safe_platform_name = htmlspecialchars($platform_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$safe_recorded_at = htmlspecialchars($recorded_at, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$subject = 'We already have your early access request';
		$html_body = <<<HTML
			<p>We already have a confirmed early-access request for this email address for the {$safe_platform_name}.</p>
			<p>Recorded at: <strong>{$safe_recorded_at}</strong></p>
			<p>You do not need to confirm this address again. We will contact you here when the next access step is ready.</p>
			HTML;
		$text_body = <<<TEXT
			We already have a confirmed early-access request for this email address for the {$platform_name}.

			Recorded at: {$recorded_at}

			You do not need to confirm this address again. We will contact you here when the next access step is ready.
			TEXT;

		EmailOrchestrator::enqueueTransactionalSnapshotAsSystem(
			subject: $subject,
			htmlBody: $html_body,
			textBody: $text_body,
			recipients: [
				['email' => $recipient_email],
			]
		);
	}

	private static function formatRecordedAt(string $preferred_timestamp, string $fallback_timestamp, string $timezone): string
	{
		$timestamp = trim($preferred_timestamp) !== '' ? trim($preferred_timestamp) : trim($fallback_timestamp);

		if ($timestamp === '') {
			return 'UTC time unavailable';
		}

		$utc = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $timestamp, new DateTimeZone('UTC'));

		if ($utc === false) {
			return $timestamp . ' UTC';
		}

		try {
			$target_timezone = trim($timezone) !== '' ? new DateTimeZone($timezone) : new DateTimeZone('UTC');
		} catch (Throwable) {
			$target_timezone = new DateTimeZone('UTC');
		}

		return $utc->setTimezone($target_timezone)->format('Y-m-d H:i:s T') . ' (' . $target_timezone->getName() . ')';
	}
}
