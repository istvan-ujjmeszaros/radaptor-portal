<?php

declare(strict_types=1);

class EventPortalAccessRequestConfirm extends AbstractEvent
{
	public function authorize(PolicyContext $policyContext): PolicyDecision
	{
		return PolicyDecision::allow();
	}

	public function run(): void
	{
		if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
			header('Allow: GET');
			WebpageView::header('HTTP/1.1 405 Method Not Allowed');
			echo 'Method Not Allowed';

			return;
		}

		$state = PortalAccessRequestService::confirm((string) Request::_GET('token', ''));
		$redirect_state = match ($state) {
			PortalAccessRequestService::UI_STATE_CONFIRMED => PortalAccessRequestService::UI_STATE_CONFIRMED,
			PortalAccessRequestService::UI_STATE_EXPIRED => PortalAccessRequestService::UI_STATE_EXPIRED,
			default => PortalAccessRequestService::UI_STATE_INVALID,
		};

		PortalAccessRequestService::flashUiState($redirect_state);
		Url::redirect(PortalAccessRequestService::buildRequestAccessUrl());
	}
}
