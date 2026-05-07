<?php

class WidgetPortalValueProps extends AbstractWidget
{
	public const string ID = 'portal_value_props';

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
		return $this->createComponentTree('portalValueProps', [
			'items' => [
				[
					'icon' => 'bi-lightning-charge',
					'title' => t('portal.value_props.item.explicit_request.title'),
					'description' => t('portal.value_props.item.explicit_request.description'),
				],
				[
					'icon' => 'bi-puzzle',
					'title' => t('portal.value_props.item.widget_contract.title'),
					'description' => t('portal.value_props.item.widget_contract.description'),
				],
				[
					'icon' => 'bi-diagram-3',
					'title' => t('portal.value_props.item.sdui.title'),
					'description' => t('portal.value_props.item.sdui.description'),
				],
				[
					'icon' => 'bi-shield-check',
					'title' => t('portal.value_props.item.authorization.title'),
					'description' => t('portal.value_props.item.authorization.description'),
				],
				[
					'icon' => 'bi-eye',
					'title' => t('portal.value_props.item.control_flow.title'),
					'description' => t('portal.value_props.item.control_flow.description'),
				],
				[
					'icon' => 'bi-feather',
					'title' => t('portal.value_props.item.operational_surface.title'),
					'description' => t('portal.value_props.item.operational_surface.description'),
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
