<?php

class WidgetPortalRequestAccessPlaceholder extends AbstractWidget
{
	public const string ID = 'portal_request_access_placeholder';

	public static function getName(): string
	{
		return 'Portal request access placeholder';
	}

	public static function getDescription(): string
	{
		return 'Placeholder access request form for the portal demo.';
	}

	public static function getListVisibility(): bool
	{
		return Roles::hasRole(RoleList::ROLE_CONTENT_ADMIN);
	}

	protected function buildAuthorizedTree(iTreeBuildContext $tree_build_context, WidgetConnection $connection, array $build_context = []): array
	{
		return $this->createComponentTree('portalRequestAccessPlaceholder', [
			'loginUrl' => '/login.html',
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
