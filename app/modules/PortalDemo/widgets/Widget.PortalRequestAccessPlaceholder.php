<?php

class WidgetPortalRequestAccessPlaceholder extends AbstractWidget
{
	public const string ID = 'portal_request_access_placeholder';
	public const array AUTHORING = [
		'insert_mode' => 'system',
		'reuse' => 'repeatable',
		'surfaces' => ['public'],
		'group' => 'content',
		'sort' => 30,
	];

	public static function getName(): string
	{
		return t('widget.' . self::ID . '.name');
	}

	public static function getDescription(): string
	{
		return t('widget.' . self::ID . '.description');
	}

	public static function getListVisibility(): bool
	{
		return Roles::hasRole(RoleList::ROLE_CONTENT_ADMIN);
	}

	protected function buildAuthorizedTree(iTreeBuildContext $tree_build_context, WidgetConnection $connection, array $build_context = []): array
	{
		return $this->createComponentTree('portalRequestAccessPlaceholder', [
			'submitUrl' => Url::getUrl('portalAccessRequest.submit'),
			'requestState' => PortalAccessRequestService::consumeUiState(),
		]);
	}

	public function canAccess(iTreeBuildContext $tree_build_context, WidgetConnection $connection): bool
	{
		return true;
	}

	public static function isWrapperStylingEnabled(): bool
	{
		return false;
	}
}
