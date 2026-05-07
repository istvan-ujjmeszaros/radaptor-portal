<?php

class ThemeDataRadaptorPortal extends AbstractThemeData
{
	public const string ID = 'radaptor_portal';
	public const string SLUG = 'radaptor-portal';
	public const string LIBRARIESCLASSNAME = 'LibrariesRadaptorPortal';

	public static function getName(): string
	{
		return t('theme.' . self::ID . '.name');
	}

	public static function getDescription(): string
	{
		return t('theme.' . self::ID . '.description');
	}

	public static function getSlug(): string
	{
		return self::SLUG;
	}

	public static function getListVisibility(): bool
	{
		return Roles::hasRole(RoleList::ROLE_SYSTEM_DEVELOPER);
	}

	public static function getLibrariesClassName(): string
	{
		return self::LIBRARIESCLASSNAME;
	}

	public static function getIconLibrary(): string
	{
		return 'Bootstrap';
	}
}
