<?php

declare(strict_types=1);

final class PortalAccessRequestServiceTest extends TransactionedTestCase
{
	public function testSubmitCreatesPendingRequestAndQueuesConfirmationEmail(): void
	{
		$result = PortalAccessRequestService::submit(
			'Person@example.com',
			true,
			'https://portal.example.test',
			'en-US',
			'Europe/Budapest',
		);

		$this->assertSame('created', $result['state']);
		$this->assertTrue($result['queued_email']);
		$this->assertStringContainsString('context=portalAccessRequest', $result['confirmation_url'] ?? '');

		$request = DbHelper::selectOne('portal_access_requests', ['email_normalized' => 'person@example.com']);
		$this->assertIsArray($request);
		$this->assertSame('Person@example.com', $request['email'] ?? null);
		$this->assertSame('en-US', $request['locale'] ?? null);
		$this->assertSame('Europe/Budapest', $request['timezone'] ?? null);
		$this->assertSame(1, (int) ($request['wants_updates'] ?? 0));
		$this->assertSame(PortalAccessRequestService::STATUS_PENDING_CONFIRMATION, $request['status'] ?? null);
		$this->assertNotSame('', trim((string) ($request['confirmation_token_hash'] ?? '')));

		$this->assertSame(1, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM email_outbox'));
		$this->assertSame(1, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM email_queue_transactional'));
	}

	public function testSubmitReissuesConfirmationForLivePendingRequestsAndPromotesUpdatesOptIn(): void
	{
		$first = PortalAccessRequestService::submit(
			'pending@example.com',
			false,
			'https://portal.example.test'
		);

		$result = PortalAccessRequestService::submit(
			'PENDING@example.com',
			true,
			'https://portal.example.test'
		);

		$this->assertSame('existing_pending', $result['state']);
		$this->assertTrue($result['queued_email']);
		$this->assertSame(1, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM portal_access_requests'));
		$this->assertSame(2, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM email_outbox'));

		$request = DbHelper::selectOne('portal_access_requests', ['email_normalized' => 'pending@example.com']);
		$this->assertIsArray($request);
		$this->assertSame(1, (int) ($request['wants_updates'] ?? 0));
		$this->assertNotSame(
			$this->extractTokenFromUrl((string) ($first['confirmation_url'] ?? '')),
			$this->extractTokenFromUrl((string) ($result['confirmation_url'] ?? ''))
		);
	}

	public function testSubmitRearmsExpiredPendingRequestWithNewTokenAndNewEmail(): void
	{
		$first = PortalAccessRequestService::submit(
			'expired@example.com',
			false,
			'https://portal.example.test'
		);

		$first_token = $this->extractTokenFromUrl((string) ($first['confirmation_url'] ?? ''));
		$request = DbHelper::selectOne('portal_access_requests', ['email_normalized' => 'expired@example.com']);
		$this->assertIsArray($request);

		EntityPortalAccessRequest::updateById((int) $request['request_id'], [
			'confirmation_expires_at' => date('Y-m-d H:i:s', time() - 60),
		]);

		$second = PortalAccessRequestService::submit(
			'expired@example.com',
			true,
			'https://portal.example.test'
		);

		$this->assertSame('rearmed', $second['state']);
		$this->assertTrue($second['queued_email']);
		$this->assertSame(1, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM portal_access_requests'));
		$this->assertSame(2, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM email_outbox'));

		$second_token = $this->extractTokenFromUrl((string) ($second['confirmation_url'] ?? ''));
		$this->assertNotSame($first_token, $second_token);

		$request = DbHelper::selectOne('portal_access_requests', ['email_normalized' => 'expired@example.com']);
		$this->assertIsArray($request);
		$this->assertSame(1, (int) ($request['wants_updates'] ?? 0));
	}

	public function testSubmitDoesNotDuplicateConfirmedRequests(): void
	{
		$first = PortalAccessRequestService::submit(
			'confirmed@example.com',
			false,
			'https://portal.example.test',
			'hu-HU',
			'Europe/Budapest',
		);

		$confirm_state = PortalAccessRequestService::confirm(
			$this->extractTokenFromUrl((string) ($first['confirmation_url'] ?? ''))
		);
		$this->assertSame(PortalAccessRequestService::UI_STATE_CONFIRMED, $confirm_state);

		$second = PortalAccessRequestService::submit(
			'confirmed@example.com',
			true,
			'https://portal.example.test',
			'de-DE',
			'Europe/Budapest',
		);

		$this->assertSame('existing_confirmed', $second['state']);
		$this->assertTrue($second['queued_email']);
		$this->assertSame(1, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM portal_access_requests'));
		$this->assertSame(2, (int) DbHelper::selectOneColumnFromQuery('SELECT COUNT(*) FROM email_outbox'));

		$request = DbHelper::selectOne('portal_access_requests', ['email_normalized' => 'confirmed@example.com']);
		$this->assertIsArray($request);
		$this->assertSame(1, (int) ($request['wants_updates'] ?? 0));
		$this->assertSame(PortalAccessRequestService::STATUS_CONFIRMED, $request['status'] ?? null);
		$this->assertSame('hu-HU', $request['locale'] ?? null);
		$this->assertSame('Europe/Budapest', $request['timezone'] ?? null);

		$statement = Db::instance()->query('SELECT subject, text_body FROM email_outbox ORDER BY outbox_id DESC LIMIT 1');
		$latest_outbox = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
		$this->assertCount(1, $latest_outbox);
		$this->assertSame('We already have your early access request', $latest_outbox[0]['subject'] ?? null);
		$this->assertStringContainsString('We already have a confirmed early-access request', (string) ($latest_outbox[0]['text_body'] ?? ''));
		$this->assertStringContainsString('Recorded at:', (string) ($latest_outbox[0]['text_body'] ?? ''));
		$this->assertStringNotContainsString('context=portalAccessRequest&event=confirm', (string) ($latest_outbox[0]['text_body'] ?? ''));
	}

	public function testConfirmMarksPendingRequestConfirmedAndClearsToken(): void
	{
		$result = PortalAccessRequestService::submit(
			'confirm@example.com',
			true,
			'https://portal.example.test'
		);

		$state = PortalAccessRequestService::confirm(
			$this->extractTokenFromUrl((string) ($result['confirmation_url'] ?? ''))
		);

		$this->assertSame(PortalAccessRequestService::UI_STATE_CONFIRMED, $state);

		$request = DbHelper::selectOne('portal_access_requests', ['email_normalized' => 'confirm@example.com']);
		$this->assertIsArray($request);
		$this->assertSame(PortalAccessRequestService::STATUS_CONFIRMED, $request['status'] ?? null);
		$this->assertNull($request['confirmation_token_hash'] ?? null);
		$this->assertNull($request['confirmation_expires_at'] ?? null);
		$this->assertNotNull($request['confirmed_at'] ?? null);
	}

	public function testConfirmReturnsExpiredForExpiredTokens(): void
	{
		$result = PortalAccessRequestService::submit(
			'expired-confirm@example.com',
			false,
			'https://portal.example.test'
		);

		$request = DbHelper::selectOne('portal_access_requests', ['email_normalized' => 'expired-confirm@example.com']);
		$this->assertIsArray($request);
		EntityPortalAccessRequest::updateById((int) $request['request_id'], [
			'confirmation_expires_at' => date('Y-m-d H:i:s', time() - 60),
		]);

		$state = PortalAccessRequestService::confirm(
			$this->extractTokenFromUrl((string) ($result['confirmation_url'] ?? ''))
		);

		$this->assertSame(PortalAccessRequestService::UI_STATE_EXPIRED, $state);
	}

	public function testConfirmReturnsInvalidForUnknownToken(): void
	{
		$this->assertSame(
			PortalAccessRequestService::UI_STATE_INVALID,
			PortalAccessRequestService::confirm('definitely-invalid-token')
		);
	}

	public function testFlashUiStateIsConsumedFromSessionWithoutQueryString(): void
	{
		PortalAccessRequestService::flashUiState(PortalAccessRequestService::UI_STATE_SUBMITTED);

		$this->assertSame(
			PortalAccessRequestService::UI_STATE_SUBMITTED,
			PortalAccessRequestService::consumeUiState()
		);
		$this->assertSame('', PortalAccessRequestService::consumeUiState());
		$this->assertSame('/request-access/', PortalAccessRequestService::buildRequestAccessUrl());
	}

	private function extractTokenFromUrl(string $url): string
	{
		$parts = parse_url($url);
		parse_str((string) ($parts['query'] ?? ''), $params);

		return (string) ($params['token'] ?? '');
	}
}
