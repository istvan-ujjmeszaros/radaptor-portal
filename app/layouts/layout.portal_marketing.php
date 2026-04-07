<?php

class LayoutTypePortalMarketing extends AbstractLayoutType
{
	public const string ID = 'portal_marketing';
	public const bool VISIBILITY = true;

	private static array $_SLOTS = ['content'];

	public static function getName(): string
	{
		return 'Portal marketing layout';
	}

	public static function getDescription(): string
	{
		return 'Public marketing layout for the portal landing surface.';
	}

	public static function getThemeName(): ?string
	{
		return 'RadaptorPortal';
	}

	public static function getListVisibility(): bool
	{
		return Roles::hasRole(RoleList::ROLE_SYSTEM_DEVELOPER);
	}

	public static function getSlots(): array
	{
		return self::$_SLOTS;
	}

	public function buildTree(iTreeBuildContext $webpage_composer, array $slot_trees, array $build_context = []): array
	{
		return $this->createLayoutTree('layout_portal_marketing', [
			'lang' => substr(Kernel::getLocale(), 0, 2),
			'site_name' => Config::APP_SITE_NAME->value(),
		], slots: [
			'content' => $slot_trees['content'] ?? [],
		]);
	}
}
