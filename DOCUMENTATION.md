# MokoDoliChimp Module - Complete Documentation

## Table of Contents
1. [Overview](#overview)
2. [Installation & Setup](#installation--setup)
3. [Configuration](#configuration)
4. [Features & Functionality](#features--functionality)
5. [User Interface Guide](#user-interface-guide)
6. [Database Schema](#database-schema)
7. [API Integration](#api-integration)
8. [Sync Process](#sync-process)
9. [Field Mapping](#field-mapping)
10. [Troubleshooting](#troubleshooting)
11. [Development & Customization](#development--customization)
12. [License & Copyright](#license--copyright)

---

## Overview

**MokoDoliChimp** is a comprehensive Dolibarr custom module that provides bidirectional synchronization between your Dolibarr ERP system and Mailchimp marketing platform. The module enables seamless data exchange between third parties, contacts, users, and Mailchimp audience lists with advanced field mapping, tag management, and real-time synchronization capabilities.

### Key Features
- **Bidirectional Synchronization**: Data flows both ways between Dolibarr and Mailchimp
- **Real-time Triggers**: Automatic sync when entities are modified in Dolibarr
- **Manual Sync Control**: On-demand synchronization for specific entities or bulk operations
- **Advanced Field Mapping**: Custom mapping including date of birth (DOB) and other custom fields
- **Tag & Segment Management**: Sophisticated audience targeting and segmentation
- **Comprehensive Logging**: Detailed sync history and error tracking
- **Professional UI**: Modern, responsive admin interface

### System Requirements
- Dolibarr 14.0+ (compatible with 17.0.0)
- PHP 7.4+ (tested with PHP 8.2)
- MySQL/MariaDB or PostgreSQL database
- cURL extension enabled
- Valid Mailchimp account with API access

---

## Installation & Setup

### Step 1: Module Installation
1. Copy the module files to your Dolibarr installation:
   ```
   /dolibarr/htdocs/custom/mailchimpsync/
   ```

2. Set proper file permissions:
   ```bash
   chmod 755 /path/to/dolibarr/htdocs/custom/mailchimpsync/
   chmod 644 /path/to/dolibarr/htdocs/custom/mailchimpsync/admin/*.php
   ```

### Step 2: Database Setup
The module will automatically create required database tables on first access:
- `llx_mailchimp_sync_config` - Configuration settings
- `llx_mailchimp_sync_field_mapping` - Field mapping definitions
- `llx_mailchimp_sync_history` - Sync operation logs

### Step 3: Module Activation
1. Access Dolibarr admin interface
2. Navigate to Module Setup
3. Find "MokoDoliChimp" in the custom modules section
4. Click "Activate"

---

## Configuration

### Mailchimp API Setup

1. **Get Your API Key**:
   - Log in to your Mailchimp account
   - Go to Account → Extras → API Keys
   - Generate a new API key
   - Copy the key for use in configuration

2. **Find Your Server Prefix**:
   - Your API key contains your server prefix (e.g., if your key ends with `-us19`, your server prefix is `us19`)

3. **Module Configuration**:
   - Access `/custom/mailchimpsync/admin/setup.php`
   - Enter your API Key
   - Enter your Server Prefix
   - Select your default Mailchimp list
   - Configure auto-sync settings
   - Test the connection

### Configuration Options

| Setting | Description | Required |
|---------|-------------|----------|
| API Key | Your Mailchimp API key | Yes |
| Server Prefix | Mailchimp server prefix (e.g., us19) | Yes |
| Default List | Primary Mailchimp list for syncing | Yes |
| Auto Sync | Enable automatic real-time synchronization | No |
| Sync Frequency | How often to run automatic syncs (if enabled) | No |
| Error Notifications | Email notifications for sync errors | No |

---

## Features & Functionality

### 1. Dashboard Overview
**Location**: `/custom/mailchimpsync/admin/dashboard.php`

The dashboard provides:
- Connection status indicators
- Real-time sync statistics (last 24 hours)
- Pending sync counts by entity type
- Quick action buttons
- Recent activity summary

### 2. Field Mapping
**Location**: `/custom/mailchimpsync/admin/fieldmapping.php`

Configure how Dolibarr fields map to Mailchimp merge fields:

#### Supported Entity Types:
- **Third Parties (Companies)**:
  - Company name → FNAME
  - Email → EMAIL
  - Phone → PHONE
  - Address fields → ADDRESS
  - Custom fields → Custom merge fields

- **Contacts**:
  - First name → FNAME
  - Last name → LNAME
  - Email → EMAIL
  - Date of birth → DOB (custom field)
  - Phone → PHONE
  - All address components

- **Users**:
  - User name → FNAME
  - Email → EMAIL
  - Administrative data → Custom fields

#### Special Field Support:
- **Date of Birth (DOB)**: Automatically formatted for Mailchimp
- **Address Components**: Full address parsing and mapping
- **Custom Fields**: Flexible mapping to any Mailchimp merge field
- **Tags**: Automatic tag assignment based on entity properties

### 3. Sync History & Monitoring
**Location**: `/custom/mailchimpsync/admin/synchistory.php`

Track all synchronization activities:
- Detailed sync logs with timestamps
- Success/failure status tracking
- Error message details
- Entity-specific sync history
- Export capabilities for reporting

### 4. Real-time Triggers
The module includes Dolibarr triggers that automatically sync data when:
- New third party/contact/user is created
- Existing entity is modified
- Entity is deleted (removes from Mailchimp)
- Status changes occur

---

## User Interface Guide

### Dashboard Navigation
- **Home**: Overview and statistics
- **Setup**: Configuration and API settings
- **Field Mapping**: Configure field relationships
- **Sync History**: View logs and troubleshoot issues

### Common Tasks

#### 1. Test API Connection
1. Go to Setup page
2. Enter your API credentials
3. Click "Test Connection"
4. Verify green status indicator

#### 2. Configure Field Mapping
1. Navigate to Field Mapping page
2. Select entity type (Third Party, Contact, or User)
3. Map Dolibarr fields to Mailchimp merge fields
4. Include DOB mapping if needed
5. Save configuration

#### 3. Run Manual Sync
1. From Dashboard, click "Run Manual Sync"
2. Select entity types to sync
3. Choose sync direction (to Mailchimp, from Mailchimp, or bidirectional)
4. Monitor progress in Sync History

#### 4. View Sync Results
1. Go to Sync History page
2. Filter by date range, entity type, or status
3. Click on individual sync entries for details
4. Export logs if needed for analysis

---

## Database Schema

### llx_mailchimp_sync_config
Stores module configuration settings.

| Column | Type | Description |
|--------|------|-------------|
| rowid | int | Primary key |
| name | varchar(128) | Configuration parameter name |
| value | text | Configuration parameter value |
| entity | int | Dolibarr entity ID |

### llx_mailchimp_sync_field_mapping
Defines field mapping between Dolibarr and Mailchimp.

| Column | Type | Description |
|--------|------|-------------|
| rowid | int | Primary key |
| entity_type | varchar(32) | Entity type (thirdparty, contact, user) |
| dolibarr_field | varchar(64) | Source field in Dolibarr |
| mailchimp_field | varchar(64) | Target merge field in Mailchimp |
| is_active | tinyint | Whether mapping is active |
| date_creation | datetime | Creation timestamp |

### llx_mailchimp_sync_history
Logs all synchronization operations.

| Column | Type | Description |
|--------|------|-------------|
| rowid | int | Primary key |
| entity_type | varchar(32) | Entity type synchronized |
| entity_id | int | Dolibarr entity ID |
| mailchimp_id | varchar(64) | Mailchimp subscriber ID |
| operation | varchar(32) | Operation type (create, update, delete) |
| direction | varchar(16) | Sync direction (to_mc, from_mc, bidirectional) |
| status | varchar(16) | Operation status (success, error, pending) |
| message | text | Status message or error details |
| date_sync | datetime | Synchronization timestamp |

---

## API Integration

### Mailchimp API Implementation
The module uses the official Mailchimp Marketing API v3.0 through the PHP SDK.

#### Key API Endpoints Used:
- `/lists` - Manage audience lists
- `/lists/{list_id}/members` - Subscriber management
- `/lists/{list_id}/merge-fields` - Custom field definitions
- `/lists/{list_id}/segments` - Audience segmentation

#### Error Handling:
- Automatic retry logic for temporary failures
- Rate limiting compliance
- Detailed error logging
- Graceful degradation when API is unavailable

#### Security Features:
- API key encryption in database
- Secure transmission (HTTPS only)
- Request validation and sanitization
- Access control and permission checks

---

## Sync Process

### Synchronization Flow

#### 1. Real-time Sync (Trigger-based)
```
Dolibarr Entity Modified → Trigger Activated → Queue Sync → Process → Log Result
```

#### 2. Manual Sync
```
User Initiates → Select Entities → Map Fields → Send to Mailchimp → Update Status
```

#### 3. Scheduled Sync (Cron)
```
Cron Job → Check Pending → Process Queue → Update Statuses → Generate Report
```

### Sync Strategies

#### Create Operation:
1. Validate entity data
2. Map fields according to configuration
3. Check for existing subscriber in Mailchimp
4. Create new subscriber or update existing
5. Apply tags and segments
6. Log operation result

#### Update Operation:
1. Retrieve current Mailchimp data
2. Compare with Dolibarr data
3. Apply only changed fields
4. Update subscriber information
5. Sync tags and segments
6. Log changes made

#### Delete Operation:
1. Locate subscriber in Mailchimp
2. Remove from all lists (or mark as unsubscribed)
3. Clean up associated data
4. Log deletion

### Conflict Resolution
- **Timestamp-based**: Most recent modification wins
- **Manual override**: Admin can force specific direction
- **Field-level**: Individual fields can have different sync rules

---

## Field Mapping

### Standard Field Mappings

#### Third Parties
| Dolibarr Field | Mailchimp Field | Type | Notes |
|----------------|-----------------|------|--------|
| name | FNAME | text | Company name |
| email | EMAIL | email | Primary email |
| phone | PHONE | phone | Primary phone |
| address | ADDRESS | address | Full address object |
| town | ADDRESS.city | text | City component |
| zip | ADDRESS.zip | text | Postal code |
| country_code | ADDRESS.country | text | ISO country code |

#### Contacts
| Dolibarr Field | Mailchimp Field | Type | Notes |
|----------------|-----------------|------|--------|
| firstname | FNAME | text | First name |
| lastname | LNAME | text | Last name |
| email | EMAIL | email | Primary email |
| birthday | DOB | date | Date of birth (YYYY-MM-DD) |
| phone_pro | PHONE | phone | Professional phone |
| address | ADDRESS | address | Complete address |

#### Users
| Dolibarr Field | Mailchimp Field | Type | Notes |
|----------------|-----------------|------|--------|
| firstname | FNAME | text | User first name |
| lastname | LNAME | text | User last name |
| email | EMAIL | email | User email |
| admin | ISADMIN | number | Administrator flag |
| datec | JOINDATE | date | Account creation date |

### Custom Field Configuration
1. Create merge field in Mailchimp
2. Add mapping in Field Mapping interface
3. Test with sample data
4. Deploy to production

### DOB Field Special Handling
The Date of Birth field requires special formatting:
- **Input**: Various date formats from Dolibarr
- **Processing**: Standardized to YYYY-MM-DD
- **Output**: Mailchimp-compatible date format
- **Validation**: Age validation and format checking

---

## Troubleshooting

### Common Issues

#### 1. API Connection Failed
**Symptoms**: Red status indicator, connection test fails
**Solutions**:
- Verify API key is correct
- Check server prefix matches your account
- Ensure network connectivity
- Verify Mailchimp account is active

#### 2. Sync Failures
**Symptoms**: Errors in sync history, pending syncs not processing
**Solutions**:
- Check field mapping configuration
- Verify required fields are mapped
- Review error messages in sync history
- Test with individual records

#### 3. Missing Data
**Symptoms**: Some fields not syncing to Mailchimp
**Solutions**:
- Verify field mapping is active
- Check data exists in Dolibarr
- Ensure Mailchimp merge fields exist
- Review field type compatibility

#### 4. Performance Issues
**Symptoms**: Slow sync operations, timeouts
**Solutions**:
- Reduce batch sizes
- Enable background processing
- Check server resources
- Optimize database queries

### Debug Mode
Enable debug logging by adding to configuration:
```php
$conf->global->MAILCHIMPSYNC_DEBUG = 1;
```

### Log Analysis
Sync history provides detailed information:
- **Success logs**: Confirm operations completed
- **Error logs**: Specific failure reasons
- **Warning logs**: Non-critical issues
- **Debug logs**: Detailed operation traces

---

## Development & Customization

### Module Structure
```
mailchimpsync/
├── admin/                  # Admin interface pages
│   ├── dashboard.php      # Main dashboard
│   ├── setup.php          # Configuration
│   ├── fieldmapping.php   # Field mapping interface
│   └── synchistory.php    # Sync logs
├── class/                  # Core classes
│   ├── mailchimpapi.class.php    # API wrapper
│   ├── syncservice.class.php     # Sync engine
│   ├── fieldmapping.class.php    # Field mapping logic
│   └── synchistory.class.php     # History management
├── core/                   # Core module files
│   ├── modules/           # Module descriptor
│   └── triggers/          # Real-time triggers
├── css/                    # Stylesheets
├── js/                     # JavaScript files
├── langs/                  # Language files
├── scripts/                # Utility scripts
└── sql/                    # Database schema
```

### Customization Examples

#### 1. Add Custom Field Mapping
```php
// In fieldmapping.class.php
public function addCustomMapping($entity_type, $dolibarr_field, $mailchimp_field) {
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."mailchimp_sync_field_mapping";
    $sql .= " (entity_type, dolibarr_field, mailchimp_field, is_active, date_creation)";
    $sql .= " VALUES ('".$entity_type."', '".$dolibarr_field."', '".$mailchimp_field."', 1, NOW())";
    
    return $this->db->query($sql);
}
```

#### 2. Custom Sync Filter
```php
// In syncservice.class.php
public function shouldSyncEntity($entity) {
    // Add custom logic here
    if ($entity->status == 'draft') {
        return false;
    }
    
    return true;
}
```

#### 3. Additional Triggers
Create new trigger files in `core/triggers/` following the naming convention:
```php
interface_99_modMailchimpSync_CustomTrigger.class.php
```

### API Extensions
The module's API can be extended for custom integrations:

#### Add REST Endpoints
```php
// In api/mailchimpsync.class.php
public function customSync($entity_id, $force = false) {
    // Custom sync logic
    return $this->syncService->syncEntity($entity_id, $force);
}
```

### Testing
The module includes demo data and simulation capabilities:
- Database simulation for testing
- Mock API responses
- Sample data generation
- Unit test framework integration

---

## License & Copyright

### Copyright Information
```
/**
 * MokoDoliChimp Module for Dolibarr
 * 
 * @package     MokoDoliChimp
 * @author      Moko Consulting <hello@mokoconsulting.tech>
 * @copyright   2025 Moko Consulting
 * @license     GNU General Public License v3.0 or later
 * @version     1.0.0
 * @link        https://mokoconsulting.tech
 */
```

### GNU General Public License v3.0
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.

### Third-party Dependencies
- **Mailchimp Marketing PHP SDK**: Licensed under Apache 2.0
- **Dolibarr Framework**: Licensed under GPL v3.0
- **Additional libraries**: See composer.json for complete list

---

## Support & Contact

### Technical Support
- **Developer**: Moko Consulting
- **Email**: hello@mokoconsulting.tech
- **Documentation**: See this file and inline code comments
- **Issues**: Report through your preferred channel

### Community Resources
- Dolibarr official documentation
- Mailchimp API documentation
- PHP SDK documentation

### Version History
- **v1.0.0** (2025-08-27): Initial release with full bidirectional sync capabilities

---

*This documentation covers the complete MokoDoliChimp module functionality. For specific implementation details, refer to the inline code documentation and the Dolibarr development documentation.*