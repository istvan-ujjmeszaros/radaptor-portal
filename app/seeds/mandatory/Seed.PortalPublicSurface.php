<?php

class SeedPortalPublicSurface extends AbstractSeed
{
	public function getVersion(): string
	{
		$hash_inputs = [];
		$spec_path = DEPLOY_ROOT . 'app/seeds/specs/portal-public.json';
		$spec_json = (string) file_get_contents($spec_path);
		$hash_inputs[] = 'public-root-acl-v1';
		$hash_inputs[] = $spec_json;

		$spec = json_decode($spec_json, true);

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
		$this->ensurePublicRootAcl();

		foreach ((array) ($spec['webpages'] ?? []) as $webpage_spec) {
			$cms->upsertWebpage($this->hydrateContentFiles($context, $webpage_spec));
		}
	}

	private function ensurePublicRootAcl(): void
	{
		$root_resource_id = (int) DbHelper::selectOneColumn('resource_tree', ['parent_id' => 0], '', 'node_id');

		if ($root_resource_id <= 0) {
			throw new RuntimeException('Root resource not found.');
		}

		ResourceAcl::setInheritance($root_resource_id, false);
		$this->ensureUsergroupAcl($root_resource_id, 'Everyone', [
			'view' => true,
			'list' => true,
			'create' => false,
			'edit' => false,
			'delete' => false,
			'publish' => false,
		]);
		$this->ensureUsergroupAcl($root_resource_id, 'Administrators', [
			'view' => true,
			'list' => true,
			'create' => true,
			'edit' => true,
			'delete' => true,
			'publish' => true,
		]);
		$this->ensureUsergroupAcl($root_resource_id, 'Developers', [
			'view' => true,
			'list' => true,
			'create' => true,
			'edit' => true,
			'delete' => true,
			'publish' => true,
		]);
	}

	/**
	 * @param array{view: bool, list: bool, create: bool, edit: bool, delete: bool, publish: bool} $acl
	 */
	private function ensureUsergroupAcl(int $resource_id, string $description, array $acl): void
	{
		$usergroup_id = (int) DbHelper::selectOneColumn('usergroups_tree', ['description' => $description], '', 'node_id');

		if ($usergroup_id <= 0) {
			throw new RuntimeException("Usergroup not found: {$description}");
		}

		ResourceAcl::assignToUsergroup($usergroup_id, $resource_id);
		$acl_id = (int) DbHelper::selectOneColumn('resource_acl', [
			'resource_id' => $resource_id,
			'subject_type' => 'usergroup',
			'subject_id' => $usergroup_id,
		], '', 'acl_id');

		if ($acl_id <= 0) {
			throw new RuntimeException("Unable to load ACL for usergroup: {$description}");
		}

		ResourceAcl::updateAcl($acl_id, [
			'allow_view' => $acl['view'] ? 1 : 0,
			'allow_list' => $acl['list'] ? 1 : 0,
			'allow_create' => $acl['create'] ? 1 : 0,
			'allow_edit' => $acl['edit'] ? 1 : 0,
			'allow_delete' => $acl['delete'] ? 1 : 0,
			'allow_publish' => $acl['publish'] ? 1 : 0,
		]);
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
