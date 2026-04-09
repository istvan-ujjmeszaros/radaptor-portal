<?php

declare(strict_types=1);

/**
 * @phpstan-type ShapePortalAccessRequest array{
 *   request_id?: int,
 *   email?: string,
 *   email_normalized?: string,
 *   locale?: string|null,
 *   timezone?: string|null,
 *   wants_updates?: int|bool,
 *   status?: string,
 *   confirmation_token_hash?: string|null,
 *   confirmation_expires_at?: string|null,
 *   confirmed_at?: string|null,
 *   created_at?: string,
 *   updated_at?: string
 * }
 *
 * @extends SQLEntity<ShapePortalAccessRequest>
 */
class EntityPortalAccessRequest extends SQLEntity
{
	public const string TABLE_NAME = 'portal_access_requests';
}
