<?php

class WidgetPortalHero extends AbstractWidget
{
	public const string ID = 'portal_hero';

	public static function getName(): string
	{
		return 'Portal hero';
	}

	public static function getDescription(): string
	{
		return 'Landing hero for the Radaptor Portal demo.';
	}

	public static function getListVisibility(): bool
	{
		return Roles::hasRole(RoleList::ROLE_CONTENT_ADMIN);
	}

	public static function getDefaultPathForCreation(): array
	{
		return [
			'path' => '/',
			'resource_name' => 'index.html',
			'layout' => LayoutTypePortalMarketing::ID,
		];
	}

	protected function buildAuthorizedTree(iTreeBuildContext $tree_build_context, WidgetConnection $connection, array $build_context = []): array
	{
		return $this->createComponentTree('portalHero', [
			'comparisonUrl' => '/comparison/',
			'requestAccessUrl' => '/request-access/',
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
