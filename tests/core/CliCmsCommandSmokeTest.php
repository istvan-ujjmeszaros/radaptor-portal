<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CliCmsCommandSmokeTest extends TestCase
{
	public function testTreeCheckCommandReturnsHealthyTrees(): void
	{
		$this->bootstrapPortalCmsState();

		$result = CLICommandWebRunner::execute(
			'tree:check',
			'',
			['tree' => 'all'],
			['json'],
			30
		);

		$this->assertTrue($result['ok'], $result['error'] !== '' ? $result['error'] : $result['output']);
		$this->assertIsArray($result['json_data']);
		$this->assertTrue($result['json_data']['ok'] ?? false);
		$this->assertSame('resource_tree', $result['json_data']['trees']['resource']['table'] ?? null);
	}

	public function testWidgetListCommandShowsOnlyLoginFormOnLoginPage(): void
	{
		$this->bootstrapPortalCmsState();

		$result = CLICommandWebRunner::execute(
			'widget:list',
			'/login.html',
			['slot' => 'content'],
			['json'],
			30
		);

		$this->assertTrue($result['ok'], $result['error'] !== '' ? $result['error'] : $result['output']);
		$this->assertIsArray($result['json_data']);
		$this->assertCount(1, $result['json_data']['widgets'] ?? []);
		$this->assertSame('Form', $result['json_data']['widgets'][0]['widget'] ?? null);
		$this->assertSame('UserLogin', $result['json_data']['widgets'][0]['attributes']['form_id'] ?? null);
	}

	public function testWebpageExportSpecCommandReturnsComparisonSpec(): void
	{
		$this->bootstrapPortalCmsState();

		$result = CLICommandWebRunner::execute(
			'webpage:export-spec',
			'/login.html',
			[],
			['json'],
			30
		);

		$this->assertTrue($result['ok'], $result['error'] !== '' ? $result['error'] : $result['output']);
		$this->assertIsArray($result['json_data']);
		$this->assertSame('/login.html', $result['json_data']['spec']['path'] ?? null);
		$this->assertSame('Form', $result['json_data']['spec']['slots']['content'][0]['widget'] ?? null);
	}

	public function testResourceAclListCommandShowsAdminBoundary(): void
	{
		$this->bootstrapPortalCmsState();

		$result = CLICommandWebRunner::execute(
			'resource:acl-list',
			'/admin/',
			[],
			['json'],
			30
		);

		$this->assertTrue($result['ok'], $result['error'] !== '' ? $result['error'] : $result['output']);
		$this->assertIsArray($result['json_data']);
		$this->assertFalse($result['json_data']['inherit'] ?? true);
		$this->assertTrue(($result['json_data']['local'][0]['allow_view'] ?? 0) === 1);
		$this->assertTrue(($result['json_data']['local'][0]['allow_edit'] ?? 0) === 1);
	}

	private function bootstrapPortalCmsState(): void
	{
		RequestContextHolder::initializeRequest(server: [
			'REQUEST_URI' => '/admin/index.html',
			'HTTP_HOST' => 'localhost',
			'SERVER_PORT' => '80',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'HTTP_ACCEPT' => 'text/html',
		]);
		RequestContextHolder::disablePersistentCacheWrite();

		$context = new SeedContext('app', 'mandatory', DEPLOY_ROOT . 'app', false);
		(new SeedSkeletonBootstrap())->run($context);
		(new SeedPortalAdminSurface())->run($context);
	}
}
