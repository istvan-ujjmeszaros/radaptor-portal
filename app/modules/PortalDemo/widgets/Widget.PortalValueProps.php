<?php

class WidgetPortalValueProps extends AbstractWidget
{
	public const string ID = 'portal_value_props';

	public static function getName(): string
	{
		return 'Portal value props';
	}

	public static function getDescription(): string
	{
		return 'Feature grid that explains the Radaptor positioning.';
	}

	public static function getListVisibility(): bool
	{
		return Roles::hasRole(RoleList::ROLE_CONTENT_ADMIN);
	}

	protected function buildAuthorizedTree(iTreeBuildContext $tree_build_context, WidgetConnection $connection, array $build_context = []): array
	{
		return $this->createComponentTree('portalValueProps', [
			'items' => [
				[
					'icon' => 'bi-lightning-charge',
					'title' => 'Explicit request flow',
					'description' => 'Every actionable request resolves to one concrete handler, so authorization and execution stay visible.',
				],
				[
					'icon' => 'bi-puzzle',
					'title' => 'Widget-owned contract',
					'description' => 'Widgets return explicit tree nodes. The CMS composes pages without taking over the widget internals.',
				],
				[
					'icon' => 'bi-diagram-3',
					'title' => 'SDUI built in',
					'description' => 'HTML is the current renderer, but the same widget tree is already shaped for server-driven UI output as well.',
				],
				[
					'icon' => 'bi-shield-check',
					'title' => 'Authorization stays local',
					'description' => 'Role checks live next to the use case instead of being scattered across middleware and helper layers.',
				],
				[
					'icon' => 'bi-eye',
					'title' => 'Less hidden control flow',
					'description' => 'You do not have to trace routing conventions, policy helpers, and controller gravity before finding the actual behavior.',
				],
				[
					'icon' => 'bi-feather',
					'title' => 'Lean operational surface',
					'description' => 'The same app can expose product pages, admin screens, browser-event docs, import/export tools, and internal widgets.',
				],
			],
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
