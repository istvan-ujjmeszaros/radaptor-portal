<?php

class SeedPortalPublicSurface extends AbstractSeed
{
	public function getVersion(): string
	{
		$hash_inputs = [];
		$spec_path = DEPLOY_ROOT . 'app/seeds/specs/portal-public.json';
		$hash_inputs[] = (string) file_get_contents($spec_path);

		$spec = json_decode((string) $hash_inputs[0], true);

		if (is_array($spec)) {
			foreach ((array) ($spec['webpages'] ?? []) as $webpage_spec) {
				foreach ((array) ($webpage_spec['slots'] ?? []) as $widget_specs) {
					foreach ((array) $widget_specs as $widget_spec) {
						$content_file = (string) ($widget_spec['settings']['content_file'] ?? '');

						if ($content_file === '') {
							continue;
						}

						$file_path = DEPLOY_ROOT . 'app/' . ltrim($content_file, '/');

						if (is_file($file_path)) {
							$hash_inputs[] = (string) file_get_contents($file_path);
						}
					}
				}
			}
		}

		return '1.1.0+' . substr(hash('sha256', implode("\n--spec-boundary--\n", $hash_inputs)), 0, 12);
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
		return 'Build the public Radaptor Portal surface from JSON specs.';
	}

	public function run(SeedContext $context): void
	{
		$cms = new CmsSeedHelper($context);
		$spec = $cms->loadJson('seeds/specs/portal-public.json');

		foreach ((array) ($spec['webpages'] ?? []) as $webpage_spec) {
			$cms->upsertWebpage($this->hydrateContentFiles($context, $webpage_spec));
		}
	}

	/**
	 * @param array<string, mixed> $webpage_spec
	 * @return array<string, mixed>
	 */
	private function hydrateContentFiles(SeedContext $context, array $webpage_spec): array
	{
		if (!is_array($webpage_spec['slots'] ?? null)) {
			return $webpage_spec;
		}

		foreach ($webpage_spec['slots'] as $slot_name => $widget_specs) {
			if (!is_array($widget_specs)) {
				continue;
			}

			foreach ($widget_specs as $index => $widget_spec) {
				$content_file = (string) ($widget_spec['settings']['content_file'] ?? '');

				if ($content_file === '') {
					continue;
				}

				$file_path = rtrim($context->basePath, '/') . '/' . ltrim($content_file, '/');
				$content = file_get_contents($file_path);

				if (!is_string($content)) {
					throw new RuntimeException("Unable to read content file: {$file_path}");
				}

				$webpage_spec['slots'][$slot_name][$index]['settings']['content'] = trim($content);
				unset($webpage_spec['slots'][$slot_name][$index]['settings']['content_file']);
			}
		}

		return $webpage_spec;
	}
}
