<?php

declare(strict_types=1);

final class PortalDemoBootstrapSeedTest extends TransactionedTestCase
{
	public function testPortalDemoSeedCreatesPublicPortalPagesAndPlaceholderFlow(): void
	{
		$skeleton_seed = new SeedSkeletonBootstrap();
		$skeleton_seed->run(new SeedContext('app', 'mandatory', DEPLOY_ROOT . 'app', false));

		(new SeedPortalPublicSurface())->run(new SeedContext('app', 'mandatory', DEPLOY_ROOT . 'app', false));
		(new SeedPortalAdminSurface())->run(new SeedContext('app', 'mandatory', DEPLOY_ROOT . 'app', false));

		$everyone_id = (int) DbHelper::selectOneColumn('usergroups_tree', ['title' => 'Everyone'], '', 'node_id');
		$administrators_id = (int) DbHelper::selectOneColumn('usergroups_tree', ['title' => 'Administrators'], '', 'node_id');
		$this->assertGreaterThan(0, $everyone_id);
		$this->assertGreaterThan(0, $administrators_id);

		$homepage = ResourceTreeHandler::getResourceTreeEntryData('/', 'index.html', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($homepage);
		$this->assertSame(1, (int) ($homepage['is_inheriting_acl'] ?? 0));
		$this->assertSame('portal_marketing', (string) ResourceTypeWebpage::getExtradata((int) $homepage['node_id'])['layout']);
		$this->assertSame(
			['PortalHero', 'PortalValueProps', 'PortalRequestAccessPlaceholder'],
			array_map(
				static fn (WidgetConnection $connection): string => $connection->getWidgetName(),
				WidgetConnection::getWidgetsForSlot((int) $homepage['node_id'], ResourceTypeWebpage::DEFAULT_SLOT_NAME)
			)
		);
		$homepage_acl = DbHelper::selectOne('resource_acl', [
			'resource_id' => (int) $homepage['node_id'],
			'subject_type' => 'usergroup',
			'subject_id' => $everyone_id,
		], '', 'acl_id');
		$this->assertFalse(is_array($homepage_acl));

		$comparison_page = ResourceTreeHandler::getResourceTreeEntryData('/comparison/', 'index.html', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($comparison_page);
		$this->assertSame(1, (int) ($comparison_page['is_inheriting_acl'] ?? 0));
		$this->assertSame('portal_marketing', (string) ResourceTypeWebpage::getExtradata((int) $comparison_page['node_id'])['layout']);
		$comparison_connection_id = Widget::getWidgetConnectionId((int) $comparison_page['node_id'], 'content', WidgetList::PLAINHTML);
		$this->assertIsInt($comparison_connection_id);
		$comparison_settings = PlainHtml::getSettings($comparison_connection_id);
		$this->assertStringContainsString('Comparison at a glance', (string) ($comparison_settings['content'] ?? ''));

		$request_page = ResourceTreeHandler::getResourceTreeEntryData('/request-access/', 'index.html', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($request_page);
		$this->assertSame(1, (int) ($request_page['is_inheriting_acl'] ?? 0));
		$this->assertSame('portal_marketing', (string) ResourceTypeWebpage::getExtradata((int) $request_page['node_id'])['layout']);
		$this->assertSame(
			['PortalRequestAccessPlaceholder'],
			array_map(
				static fn (WidgetConnection $connection): string => $connection->getWidgetName(),
				WidgetConnection::getWidgetsForSlot((int) $request_page['node_id'], ResourceTypeWebpage::DEFAULT_SLOT_NAME)
			)
		);

		$roadmap_page = ResourceTreeHandler::getResourceTreeEntryData('/roadmap/', 'index.html', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($roadmap_page);
		$this->assertSame(1, (int) ($roadmap_page['is_inheriting_acl'] ?? 0));
		$this->assertSame('portal_marketing', (string) ResourceTypeWebpage::getExtradata((int) $roadmap_page['node_id'])['layout']);
		$roadmap_connection_id = Widget::getWidgetConnectionId((int) $roadmap_page['node_id'], 'content', WidgetList::PLAINHTML);
		$this->assertIsInt($roadmap_connection_id);
		$roadmap_settings = PlainHtml::getSettings($roadmap_connection_id);
		$this->assertStringContainsString('Radaptor Roadmap', (string) ($roadmap_settings['content'] ?? ''));
		$this->assertStringContainsString('Drag and drop page editor', (string) ($roadmap_settings['content'] ?? ''));

		$login_page = ResourceTreeHandler::getResourceTreeEntryData('/', 'login.html', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($login_page);
		$this->assertSame(
			['Form'],
			array_map(
				static fn (WidgetConnection $connection): string => $connection->getWidgetName(),
				WidgetConnection::getWidgetsForSlot((int) $login_page['node_id'], ResourceTypeWebpage::DEFAULT_SLOT_NAME)
			)
		);
		$login_form_connection_id = Widget::getWidgetConnectionId((int) $login_page['node_id'], 'content', WidgetList::FORM);
		$this->assertIsInt($login_form_connection_id);
		$login_form_attributes = AttributeHandler::getAttributes(
			new AttributeResourceIdentifier(ResourceNames::WIDGET_CONNECTION, (string) $login_form_connection_id)
		);
		$this->assertSame(FormList::USERLOGIN, $login_form_attributes['form_id'] ?? null);

		$admin_folder = ResourceTreeHandler::getResourceTreeEntryData('/', 'admin', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($admin_folder);
		$this->assertSame(0, (int) ($admin_folder['is_inheriting_acl'] ?? 1));
		$admin_acl = DbHelper::selectOne('resource_acl', [
			'resource_id' => (int) $admin_folder['node_id'],
			'subject_type' => 'usergroup',
			'subject_id' => $administrators_id,
		], '', 'allow_view,allow_list,allow_create,allow_edit');
		$this->assertSame(1, (int) ($admin_acl['allow_view'] ?? 0));
		$this->assertSame(1, (int) ($admin_acl['allow_list'] ?? 0));
		$this->assertSame(1, (int) ($admin_acl['allow_create'] ?? 0));
		$this->assertSame(1, (int) ($admin_acl['allow_edit'] ?? 0));

		$admin_index_page = ResourceTreeHandler::getResourceTreeEntryData('/admin/', 'index.html', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($admin_index_page);
		$this->assertSame(
			['PlainHtml', 'EmailQueueStats'],
			array_map(
				static fn (WidgetConnection $connection): string => $connection->getWidgetName(),
				WidgetConnection::getWidgetsForSlot((int) $admin_index_page['node_id'], ResourceTypeWebpage::DEFAULT_SLOT_NAME)
			)
		);

		$email_outbox_page = ResourceTreeHandler::getResourceTreeEntryData('/admin/email-outbox/', 'index.html', Config::APP_DOMAIN_CONTEXT->value());
		$this->assertIsArray($email_outbox_page);
		$this->assertIsInt(Widget::getWidgetConnectionId((int) $email_outbox_page['node_id'], 'content', 'EmailOutbox'));

		$report = NestedSet::analyzeConsistency('resource_tree');
		$this->assertTrue($report['ok'], json_encode($report['issues']));
	}
}
