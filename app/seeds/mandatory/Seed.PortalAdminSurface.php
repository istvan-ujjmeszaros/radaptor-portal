<?php

class SeedPortalAdminSurface extends AbstractSeed
{
	public function getVersion(): string
	{
		$spec_path = DEPLOY_ROOT . 'app/seeds/specs/portal-admin.json';
		$spec_contents = (string) file_get_contents($spec_path);

		return '1.3.0+' . substr(hash('sha256', $spec_contents), 0, 12);
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
