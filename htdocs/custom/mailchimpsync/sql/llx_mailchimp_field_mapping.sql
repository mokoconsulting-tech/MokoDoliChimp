-- Mailchimp Field Mapping Table
-- 
-- @file        llx_mailchimp_field_mapping.sql
-- @ingroup     mailchimpsync
-- @brief       Field mapping configuration table
-- @author      Moko Consulting <hello@mokoconsulting.tech>
-- @copyright   2025 Moko Consulting
-- @license     GNU General Public License v3.0 or later
-- @warranty    This program comes with ABSOLUTELY NO WARRANTY. This is free software.

CREATE TABLE llx_mailchimp_field_mapping (
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    entity_type varchar(32) NOT NULL,
    dolibarr_field varchar(64) NOT NULL,
    mailchimp_field varchar(64) NOT NULL,
    sync_direction varchar(32) NOT NULL,
    field_type varchar(32) DEFAULT 'text',
    is_required tinyint DEFAULT 0,
    transform_function varchar(64),
    default_value varchar(255),
    validation_rules text,
    mapping_priority integer DEFAULT 100,
    active tinyint DEFAULT 1 NOT NULL,
    date_creation datetime NOT NULL,
    date_modification datetime,
    fk_user_creat integer,
    fk_user_modif integer
) ENGINE=innodb;

-- Add unique constraint on entity_type and dolibarr_field
ALTER TABLE llx_mailchimp_field_mapping ADD UNIQUE INDEX uk_mailchimp_field_mapping_entity_field (entity_type, dolibarr_field);

-- Add indexes for performance
ALTER TABLE llx_mailchimp_field_mapping ADD INDEX idx_mailchimp_field_mapping_entity (entity_type);
ALTER TABLE llx_mailchimp_field_mapping ADD INDEX idx_mailchimp_field_mapping_direction (sync_direction);
ALTER TABLE llx_mailchimp_field_mapping ADD INDEX idx_mailchimp_field_mapping_active (active);

-- Add constraint for valid sync directions
ALTER TABLE llx_mailchimp_field_mapping ADD CONSTRAINT chk_mailchimp_field_sync_direction 
CHECK (sync_direction IN ('dolibarr_to_mailchimp', 'mailchimp_to_dolibarr', 'bidirectional', 'none'));

-- Add constraint for valid entity types
ALTER TABLE llx_mailchimp_field_mapping ADD CONSTRAINT chk_mailchimp_field_entity_type 
CHECK (entity_type IN ('thirdparty', 'contact', 'user'));

-- Insert default field mappings for third parties
INSERT INTO llx_mailchimp_field_mapping (entity_type, dolibarr_field, mailchimp_field, sync_direction, field_type, is_required, date_creation, active) VALUES
('thirdparty', 'email', 'EMAIL', 'bidirectional', 'email', 1, NOW(), 1),
('thirdparty', 'nom', 'COMPANY', 'dolibarr_to_mailchimp', 'text', 0, NOW(), 1),
('thirdparty', 'phone', 'PHONE', 'dolibarr_to_mailchimp', 'phone', 0, NOW(), 1),
('thirdparty', 'address', 'ADDRESS', 'dolibarr_to_mailchimp', 'address', 0, NOW(), 1),
('thirdparty', 'town', 'CITY', 'dolibarr_to_mailchimp', 'text', 0, NOW(), 1),
('thirdparty', 'zip', 'ZIP', 'dolibarr_to_mailchimp', 'zip', 0, NOW(), 1);

-- Insert default field mappings for contacts
INSERT INTO llx_mailchimp_field_mapping (entity_type, dolibarr_field, mailchimp_field, sync_direction, field_type, is_required, date_creation, active) VALUES
('contact', 'email', 'EMAIL', 'bidirectional', 'email', 1, NOW(), 1),
('contact', 'firstname', 'FNAME', 'bidirectional', 'text', 0, NOW(), 1),
('contact', 'lastname', 'LNAME', 'bidirectional', 'text', 0, NOW(), 1),
('contact', 'phone', 'PHONE', 'dolibarr_to_mailchimp', 'phone', 0, NOW(), 1),
('contact', 'phone_mobile', 'MMERGE4', 'dolibarr_to_mailchimp', 'phone', 0, NOW(), 1),
('contact', 'birthday', 'BIRTHDAY', 'dolibarr_to_mailchimp', 'date', 0, NOW(), 1),
('contact', 'address', 'ADDRESS', 'dolibarr_to_mailchimp', 'address', 0, NOW(), 1),
('contact', 'town', 'CITY', 'dolibarr_to_mailchimp', 'text', 0, NOW(), 1),
('contact', 'zip', 'ZIP', 'dolibarr_to_mailchimp', 'zip', 0, NOW(), 1);

-- Insert default field mappings for users
INSERT INTO llx_mailchimp_field_mapping (entity_type, dolibarr_field, mailchimp_field, sync_direction, field_type, is_required, date_creation, active) VALUES
('user', 'email', 'EMAIL', 'bidirectional', 'email', 1, NOW(), 1),
('user', 'firstname', 'FNAME', 'bidirectional', 'text', 0, NOW(), 1),
('user', 'lastname', 'LNAME', 'bidirectional', 'text', 0, NOW(), 1),
('user', 'office_phone', 'PHONE', 'dolibarr_to_mailchimp', 'phone', 0, NOW(), 1),
('user', 'user_mobile', 'MMERGE4', 'dolibarr_to_mailchimp', 'phone', 0, NOW(), 1),
('user', 'birth', 'BIRTHDAY', 'dolibarr_to_mailchimp', 'date', 0, NOW(), 1),
('user', 'job', 'MMERGE5', 'dolibarr_to_mailchimp', 'text', 0, NOW(), 1);
