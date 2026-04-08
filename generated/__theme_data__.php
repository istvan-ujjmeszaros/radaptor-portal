<?php

class ThemeList extends ThemeBase
{
	public const string RADAPTORPORTAL = 'RadaptorPortal';
	public const string RADAPTORPORTALADMIN = 'RadaptorPortalAdmin';
	public const string SOADMIN = 'SoAdmin';
	public const string _THEMEERROR = '_ThemeError';

	protected static array $_themeDataNames = [
		'RadaptorPortal',
		'RadaptorPortalAdmin',
		'SoAdmin',
		'_ThemeError',
	];
}
