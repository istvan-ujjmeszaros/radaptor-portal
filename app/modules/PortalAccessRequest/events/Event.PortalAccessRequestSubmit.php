<?php

declare(strict_types=1);

class EventPortalAccessRequestSubmit extends AbstractEvent
{
	public function authorize(PolicyContext $policyContext): PolicyDecision
	{
		return PolicyDecision::allow();
	}

	public function run(): void
	{
		if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
			header('Allow: POST');
			WebpageView::header('HTTP/1.1 405 Method Not Allowed');
			echo 'Method Not Allowed';

			return;
		}

		$email = trim((string) Request::_POST('email', ''));
		$wants_updates = Request::_POST('wants_updates') ? true : false;
		$locale = trim((string) Request::_POST('locale', ''));
		$timezone = trim((string) Request::_POST('timezone', ''));

		try {
			PortalAccessRequestService::submit(
				$email,
				$wants_updates,
				Url::getCurrentHost(false),
				$locale,
				$timezone,
			);
			PortalAccessRequestService::flashUiState(PortalAccessRequestService::UI_STATE_SUBMITTED);
			Url::redirect(PortalAccessRequestService::buildRequestAccessUrl());
		} catch (InvalidArgumentException) {
			PortalAccessRequestService::flashUiState(PortalAccessRequestService::UI_STATE_INVALID_EMAIL);
			Url::redirect(PortalAccessRequestService::buildRequestAccessUrl());
		} catch (Throwable) {
			PortalAccessRequestService::flashUiState(PortalAccessRequestService::UI_STATE_ERROR);
			Url::redirect(PortalAccessRequestService::buildRequestAccessUrl());
		}
	}
}
