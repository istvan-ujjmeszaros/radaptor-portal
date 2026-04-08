<?php

class SeedPortalAdminSurface extends AbstractSeed
{
	public function getVersion(): string
	{
		return '1.2.0';
	}

	/**
	 * @return list<class-string<AbstractSeed>>
	 */
	public function getDependencies(): array
	{
		return [SeedSkeletonBootstrap::class];
	}

	public function getDescription(): string
	{
		return 'Build the admin Radaptor Portal surface from JSON specs.';
	}

	public function run(SeedContext $context): void
	{
		$cms = new CmsSeedHelper($context);
		$spec = $cms->loadJson('seeds/specs/portal-admin.json');

		foreach ((array) ($spec['webpages'] ?? []) as $webpage_spec) {
			$cms->upsertWebpage($webpage_spec);
		}
	}
}
