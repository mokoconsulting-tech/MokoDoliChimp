-- Mailchimp Sync Configuration Table
-- 
-- @file        llx_mailchimp_sync_config.sql
-- @ingroup     mailchimpsync
-- @brief       Sync configuration storage table
-- @author      Moko Consulting <hello@mokoconsulting.tech>
-- @copyright   2025 Moko Consulting
-- @license     GNU General Public License v3.0 or later
-- @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.

CREATE TABLE llx_mailchimp_sync_config (
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    entity integer DEFAULT 1 NOT NULL,
    config_key varchar(100) NOT NULL,
    config_value text,
    config_type varchar(32) DEFAULT 'string',
    description text,
    date_creation datetime NOT NULL,
    date_modification datetime,
    fk_user_creat integer,
    fk_user_modif integer,
    active tinyint DEFAULT 1 NOT NULL
) ENGINE=innodb;

-- Add unique constraint on entity and config_key
ALTER TABLE llx_mailchimp_sync_config ADD UNIQUE INDEX uk_mailchimp_sync_config_entity_key (entity, config_key);

-- Insert default configuration values
INSERT INTO llx_mailchimp_sync_config (entity, config_key, config_value, config_type, description, date_creation, active) VALUES
(1, 'default_list_id', '', 'string', 'Default Mailchimp list ID for synchronization', NOW(), 1),
(1, 'sync_batch_size', '100', 'integer', 'Number of records to sync in each batch', NOW(), 1),
(1, 'sync_timeout', '300', 'integer', 'Sync timeout in seconds', NOW(), 1),
(1, 'error_retry_count', '3', 'integer', 'Number of retries for failed syncs', NOW(), 1),
(1, 'last_full_sync', '', 'datetime', 'Timestamp of last full synchronization', NOW(), 1),
(1, 'sync_tags_enabled', '1', 'boolean', 'Enable automatic tagging of synced contacts', NOW(), 1),
(1, 'webhook_secret', '', 'string', 'Webhook secret for Mailchimp callbacks', NOW(), 1),
(1, 'sync_direction_default', 'bidirectional', 'string', 'Default sync direction for new mappings', NOW(), 1);
