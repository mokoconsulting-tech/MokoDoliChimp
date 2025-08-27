# MokoDoliChimp - Technical Specifications

## Architecture Overview

### Module Type
- **Framework**: Dolibarr Custom Module
- **Integration Pattern**: Native Dolibarr integration with external API
- **Architecture Style**: Event-driven with trigger-based real-time sync
- **Data Flow**: Bidirectional with conflict resolution

### Core Components

#### 1. Module Descriptor (`core/modules/modMokoDoliChimp.class.php`)
```php
class modMokoDoliChimp extends DolibarrModules
{
    public $numero = 500000;  // Module number
    public $family = "other"; // Module family
    public $module_position = 90;
    public $name = "MokoDoliChimp";
    public $description = "Mailchimp bidirectional synchronization";
    public $version = "1.0.0";
}
```

#### 2. Database Layer
**Connection Pattern**: PDO/MySQLi via Dolibarr database abstraction
**Transaction Management**: Atomic operations with rollback support
**Schema Management**: Automatic table creation and migration support

#### 3. API Integration Layer
**HTTP Client**: Guzzle HTTP for robust API communication
**Authentication**: API key-based authentication with secure storage
**Rate Limiting**: Built-in compliance with Mailchimp API limits
**Error Handling**: Exponential backoff retry mechanism

### Data Synchronization Architecture

#### Sync Engine (`class/syncservice.class.php`)
```php
class SyncService
{
    private $mailchimpApi;
    private $fieldMapping;
    private $syncHistory;
    
    public function syncEntity($entity, $direction = 'bidirectional') {
        // Multi-step sync process with validation
    }
}
```

#### Trigger System (`core/triggers/`)
- **Event Detection**: Dolibarr entity lifecycle hooks
- **Queue Management**: Asynchronous processing capability
- **Batch Processing**: Efficient bulk operations
- **Conflict Resolution**: Timestamp-based with manual override

## Database Schema

### Configuration Table
```sql
CREATE TABLE llx_mailchimp_sync_config (
    rowid INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    value TEXT,
    entity INT DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name_entity (name, entity)
);
```

### Field Mapping Table
```sql
CREATE TABLE llx_mailchimp_sync_field_mapping (
    rowid INT PRIMARY KEY AUTO_INCREMENT,
    entity_type VARCHAR(32) NOT NULL,
    dolibarr_field VARCHAR(64) NOT NULL,
    mailchimp_field VARCHAR(64) NOT NULL,
    field_type VARCHAR(32) DEFAULT 'text',
    is_required TINYINT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    transformation_rule TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity_type (entity_type),
    INDEX idx_active (is_active),
    UNIQUE KEY uk_mapping (entity_type, dolibarr_field, mailchimp_field)
);
```

### Sync History Table
```sql
CREATE TABLE llx_mailchimp_sync_history (
    rowid INT PRIMARY KEY AUTO_INCREMENT,
    entity_type VARCHAR(32) NOT NULL,
    entity_id INT NOT NULL,
    mailchimp_id VARCHAR(64),
    operation VARCHAR(32) NOT NULL,
    direction VARCHAR(16) NOT NULL,
    status VARCHAR(16) DEFAULT 'pending',
    message TEXT,
    sync_data JSON,
    processing_time_ms INT,
    date_sync DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_status_date (status, date_sync),
    INDEX idx_mailchimp_id (mailchimp_id)
);
```

## API Integration Specifications

### Mailchimp API Wrapper (`class/mailchimpapi.class.php`)
```php
class MailchimpAPI
{
    private $apiKey;
    private $serverPrefix;
    private $httpClient;
    
    public function __construct($apiKey, $serverPrefix) {
        $this->httpClient = new GuzzleHttp\Client([
            'base_uri' => "https://{$serverPrefix}.api.mailchimp.com/3.0/",
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'User-Agent' => 'MokoDoliChimp/1.0'
            ]
        ]);
    }
}
```

### Endpoint Mapping
| Operation | Mailchimp Endpoint | HTTP Method | Purpose |
|-----------|-------------------|-------------|---------|
| Get Lists | `/lists` | GET | Retrieve audience lists |
| Add Member | `/lists/{list_id}/members` | POST | Create subscriber |
| Update Member | `/lists/{list_id}/members/{subscriber_hash}` | PATCH | Update subscriber |
| Delete Member | `/lists/{list_id}/members/{subscriber_hash}` | DELETE | Remove subscriber |
| Get Merge Fields | `/lists/{list_id}/merge-fields` | GET | Field definitions |
| Batch Operations | `/batches` | POST | Bulk operations |

### Error Handling Strategy
```php
public function handleApiError($exception) {
    switch ($exception->getCode()) {
        case 400: return 'Invalid request data';
        case 401: return 'Authentication failed';
        case 403: return 'Forbidden operation';
        case 404: return 'Resource not found';
        case 429: return 'Rate limit exceeded';
        case 500: return 'Server error';
        default: return 'Unknown error';
    }
}
```

## Field Mapping System

### Transformation Engine
```php
class FieldTransformer
{
    public function transform($value, $rule) {
        switch ($rule['type']) {
            case 'date_format':
                return $this->formatDate($value, $rule['format']);
            case 'phone_format':
                return $this->formatPhone($value, $rule['country']);
            case 'address_component':
                return $this->extractAddressComponent($value, $rule['component']);
            case 'tag_assignment':
                return $this->assignTags($value, $rule['mapping']);
        }
    }
}
```

### DOB Field Handling
```php
public function formatDateOfBirth($dobValue) {
    // Input validation
    if (empty($dobValue)) return null;
    
    // Parse various formats
    $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y-m-d H:i:s'];
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dobValue);
        if ($date !== false) {
            return $date->format('Y-m-d'); // Mailchimp standard
        }
    }
    
    return null; // Invalid date
}
```

## Performance Optimization

### Caching Strategy
- **Configuration Cache**: In-memory caching of frequently accessed settings
- **Field Mapping Cache**: Cached mapping rules to avoid database queries
- **API Response Cache**: Temporary caching of Mailchimp list data
- **Query Optimization**: Indexed database queries with result limiting

### Batch Processing
```php
public function batchSync($entities, $batchSize = 100) {
    $batches = array_chunk($entities, $batchSize);
    
    foreach ($batches as $batch) {
        $operations = $this->prepareBatchOperations($batch);
        $response = $this->mailchimpApi->submitBatch($operations);
        $this->processBatchResponse($response);
    }
}
```

### Memory Management
- **Streaming Processing**: Large datasets processed in chunks
- **Memory Monitoring**: Built-in memory usage tracking
- **Resource Cleanup**: Proper resource disposal after operations
- **Garbage Collection**: Explicit cleanup of large objects

## Security Implementation

### Data Protection
```php
class SecurityManager
{
    public function encryptApiKey($apiKey) {
        return openssl_encrypt($apiKey, 'AES-256-CBC', $this->getEncryptionKey(), 0, $this->getIV());
    }
    
    public function validateRequest($data) {
        // CSRF protection
        if (!$this->validateCSRFToken($data['token'])) {
            throw new SecurityException('Invalid CSRF token');
        }
        
        // Input sanitization
        return $this->sanitizeInput($data);
    }
}
```

### Access Control
- **Permission Checks**: Integration with Dolibarr user permission system
- **API Key Security**: Encrypted storage with secure retrieval
- **Input Validation**: Comprehensive sanitization of all inputs
- **CSRF Protection**: Token-based request validation

## Error Handling & Logging

### Logging Framework
```php
class SyncLogger
{
    const LOG_DEBUG = 1;
    const LOG_INFO = 2;
    const LOG_WARNING = 3;
    const LOG_ERROR = 4;
    
    public function log($level, $message, $context = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $this->getLevelName($level),
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'execution_time' => microtime(true) - $this->startTime
        ];
        
        $this->writeLogEntry($logEntry);
    }
}
```

### Error Recovery
- **Automatic Retry**: Exponential backoff for transient failures
- **Queue Management**: Failed operations queued for retry
- **Rollback Support**: Transaction-based operations with rollback
- **Alert System**: Email notifications for critical failures

## Testing Framework

### Unit Testing
```php
class SyncServiceTest extends PHPUnit\Framework\TestCase
{
    public function testEntitySync() {
        $mockEntity = $this->createMockEntity();
        $result = $this->syncService->syncEntity($mockEntity);
        $this->assertTrue($result['success']);
    }
}
```

### Integration Testing
- **API Mocking**: Simulated Mailchimp responses for testing
- **Database Testing**: Isolated test database with sample data
- **End-to-End Testing**: Complete sync workflow validation
- **Performance Testing**: Load testing with large datasets

## Deployment Specifications

### Server Requirements
- **PHP**: 7.4+ (optimized for 8.2)
- **Memory**: 256MB minimum, 512MB recommended
- **Disk Space**: 50MB for module files
- **Network**: HTTPS capability for secure API communication

### Configuration Files
```php
// Configuration constants
define('MAILCHIMPSYNC_VERSION', '1.0.0');
define('MAILCHIMPSYNC_BATCH_SIZE', 100);
define('MAILCHIMPSYNC_TIMEOUT', 30);
define('MAILCHIMPSYNC_RETRY_ATTEMPTS', 3);
define('MAILCHIMPSYNC_CACHE_TTL', 3600);
```

### Monitoring & Maintenance
- **Health Checks**: Automated API connectivity testing
- **Performance Metrics**: Sync speed and success rate tracking
- **Log Rotation**: Automatic cleanup of old log entries
- **Update Mechanism**: Version checking and update notifications

## Extension Points

### Custom Hooks
```php
// Allow custom processing before sync
$this->executeHook('mailchimpSyncBeforeEntity', $entity, $direction);

// Allow custom processing after sync
$this->executeHook('mailchimpSyncAfterEntity', $entity, $result);
```

### Plugin Architecture
- **Custom Field Transformers**: Pluggable field transformation rules
- **Custom Triggers**: Additional trigger points for sync operations
- **Custom APIs**: Extension points for additional API integrations
- **Custom UI**: Hooks for additional admin interface components

---

*This technical specification provides comprehensive implementation details for the MokoDoliChimp module. For usage instructions, see README.md and DOCUMENTATION.md.*