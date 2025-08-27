# MokoDoliChimp - Dolibarr Mailchimp Sync Module

A comprehensive Dolibarr custom module that provides bidirectional synchronization between your Dolibarr ERP system and Mailchimp marketing platform.

## 🚀 Features

- **Bidirectional Synchronization**: Sync data both ways between Dolibarr and Mailchimp
- **Real-time Triggers**: Automatic sync when entities are modified
- **Advanced Field Mapping**: Including date of birth (DOB) and custom fields
- **Tag & Segment Management**: Sophisticated audience targeting
- **Professional Admin Interface**: Modern, responsive UI
- **Comprehensive Logging**: Detailed sync history and error tracking
- **Manual & Automatic Sync**: Full control over synchronization timing

## 📋 Requirements

- Dolibarr 14.0+ (tested with 17.0.0)
- PHP 7.4+ (recommended: PHP 8.2)
- MySQL/MariaDB or PostgreSQL
- Valid Mailchimp account with API access
- cURL extension enabled

## 🔧 Quick Setup

1. **Copy module files** to `/dolibarr/htdocs/custom/mailchimpsync/`
2. **Access configuration** at `/custom/mailchimpsync/admin/setup.php`
3. **Enter your Mailchimp API key** and server prefix
4. **Configure field mappings** for your entities
5. **Test the connection** and start syncing!

## 🎯 Supported Entities

### Third Parties (Companies)
- Company name, email, phone, address
- Custom fields and tags
- Automatic list assignment

### Contacts
- First name, last name, email
- **Date of birth (DOB)** support
- Complete address information
- Professional and personal details

### Users
- User accounts and administrative data
- Email preferences
- Access level integration

## 📊 Admin Interface

### Dashboard (`/admin/dashboard.php`)
- Connection status indicators
- Real-time sync statistics
- Pending sync counts
- Quick action buttons

### Setup (`/admin/setup.php`)
- API configuration
- Connection testing
- Global sync settings
- Error notification setup

### Field Mapping (`/admin/fieldmapping.php`)
- Visual field mapping interface
- Support for all entity types
- DOB and custom field handling
- Mapping validation

### Sync History (`/admin/synchistory.php`)
- Detailed operation logs
- Success/failure tracking
- Error message details
- Export capabilities

## 🔄 Sync Process

### Automatic Sync
Real-time triggers activate when:
- New entities are created
- Existing entities are modified
- Entity status changes
- Deletion occurs

### Manual Sync
- On-demand synchronization
- Bulk operations support
- Selective entity sync
- Direction control (to/from Mailchimp)

### Scheduled Sync
- Cron job integration
- Background processing
- Queue management
- Progress reporting

## 🗃️ Database Schema

The module creates three main tables:
- `llx_mailchimp_sync_config` - Configuration settings
- `llx_mailchimp_sync_field_mapping` - Field mapping definitions  
- `llx_mailchimp_sync_history` - Sync operation logs

## 🛠️ Configuration

### Basic Setup
```php
// API Configuration
MAILCHIMPSYNC_API_KEY = "your-api-key-here"
MAILCHIMPSYNC_SERVER_PREFIX = "us19"  // From your API key
MAILCHIMPSYNC_DEFAULT_LIST = "list-id"
MAILCHIMPSYNC_AUTO_SYNC = 1
```

### Field Mapping Examples
```php
// Contact mapping
"firstname" → "FNAME"
"lastname" → "LNAME"  
"email" → "EMAIL"
"birthday" → "DOB"    // Special DOB handling
"phone_pro" → "PHONE"
```

## 🐛 Troubleshooting

### Common Issues

**API Connection Failed**
- Verify API key and server prefix
- Check network connectivity
- Confirm Mailchimp account status

**Sync Failures**
- Review field mapping configuration
- Check required fields are mapped
- Verify data exists in source system

**Performance Issues**
- Reduce batch sizes
- Enable background processing
- Check server resources

### Debug Mode
Enable detailed logging:
```php
$conf->global->MAILCHIMPSYNC_DEBUG = 1;
```

## 📝 Documentation

- **[Complete Documentation](DOCUMENTATION.md)** - Comprehensive guide
- **Inline Comments** - Detailed code documentation
- **Database Schema** - Full table definitions
- **API Reference** - Integration examples

## 📄 License

```
Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🤝 Support

- **Email**: hello@mokoconsulting.tech
- **Developer**: Moko Consulting
- **Version**: 1.0.0 (2025-08-27)

## 🔗 Links

- [Dolibarr Official Site](https://www.dolibarr.org/)
- [Mailchimp API Documentation](https://mailchimp.com/developer/)
- [Module Demo](http://localhost:5000/custom/mailchimpsync/admin/dashboard.php)

---

**MokoDoliChimp** - Seamlessly connect your Dolibarr ERP with Mailchimp marketing power! 🚀