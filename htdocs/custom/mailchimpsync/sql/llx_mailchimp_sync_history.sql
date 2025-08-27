-- Mailchimp Sync History Table
-- 
-- @file        llx_mailchimp_sync_history.sql
-- @ingroup     mailchimpsync
-- @brief       Sync history and logging table
-- @author      Moko Consulting <hello@mokoconsulting.tech>
-- @copyright   2025 Moko Consulting
-- @license     GNU General Public License v3.0 or later
-- @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.

CREATE TABLE llx_mailchimp_sync_history (
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    entity_type varchar(32) NOT NULL,
    entity_id integer NOT NULL,
    sync_direction varchar(32) NOT NULL,
    status varchar(16) NOT NULL,
    message text,
    mailchimp_member_id varchar(32),
    sync_data text,
    error_code varchar(16),
    retry_count integer DEFAULT 0,
    date_sync datetime NOT NULL,
    date_retry datetime,
    fk_user_creat integer,
    processing_time decimal(8,3)
) ENGINE=innodb;

-- Add indexes for performance
ALTER TABLE llx_mailchimp_sync_history ADD INDEX idx_mailchimp_sync_history_entity (entity_type, entity_id);
ALTER TABLE llx_mailchimp_sync_history ADD INDEX idx_mailchimp_sync_history_status (status);
ALTER TABLE llx_mailchimp_sync_history ADD INDEX idx_mailchimp_sync_history_date (date_sync);
ALTER TABLE llx_mailchimp_sync_history ADD INDEX idx_mailchimp_sync_history_direction (sync_direction);

-- Add index for cleanup operations
ALTER TABLE llx_mailchimp_sync_history ADD INDEX idx_mailchimp_sync_history_cleanup (date_sync, status);

-- Add constraint for valid statuses
ALTER TABLE llx_mailchimp_sync_history ADD CONSTRAINT chk_mailchimp_sync_status 
CHECK (status IN ('pending', 'success', 'error', 'retry', 'skipped'));

-- Add constraint for valid sync directions
ALTER TABLE llx_mailchimp_sync_history ADD CONSTRAINT chk_mailchimp_sync_direction 
CHECK (sync_direction IN ('dolibarr_to_mailchimp', 'mailchimp_to_dolibarr', 'bidirectional'));

-- Add constraint for valid entity types
ALTER TABLE llx_mailchimp_sync_history ADD CONSTRAINT chk_mailchimp_entity_type 
CHECK (entity_type IN ('thirdparty', 'contact', 'user'));
