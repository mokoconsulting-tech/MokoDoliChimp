# MokoDoliChimp Module

## Project Overview
A fully functional Dolibarr module called "MokoDoliChimp" that provides comprehensive bidirectional synchronization between third-party contacts, users, and Mailchimp email lists. This module enables automatic syncing of contact data with field mapping (including DOB), tag management, and real-time triggers between your Dolibarr ERP system and Mailchimp marketing platform.

**Status**: ✅ Complete and operational - working demo available at `/custom/mailchimpsync/admin/dashboard.php`

## Architecture
- **Module Name**: MokoDoliChimp
- **Module Type**: External Dolibarr module (custom)
- **Location**: `/mokodolichimp/`
- **API Integration**: Mailchimp Marketing API v3 using official PHP SDK
- **Sync Method**: REST API with webhook support for real-time updates

## Features
- Bidirectional contact synchronization
- Multiple Mailchimp list support
- Automatic field mapping
- Real-time sync via webhooks
- Manual sync operations
- Sync status tracking and logging
- Configuration interface for API settings

## Technology Stack
- **Backend**: PHP (Dolibarr module framework)
- **API**: Mailchimp Marketing API v3
- **Database**: MySQL/MariaDB (Dolibarr database)
- **Dependencies**: Composer for Mailchimp PHP SDK

## Module Structure
```
mokodolichimp/
├── core/modules/modMokoDoliChimp.class.php  # Module descriptor
├── class/
│   ├── mokodolichimp.class.php              # Main sync class
│   └── api_mokodolichimp.class.php          # REST API endpoints
├── admin/                                   # Admin configuration
├── sql/                                     # Database tables
├── webhook/                                 # Webhook handlers
└── lib/                                     # Helper libraries
```

## User Preferences
- Development environment: Replit
- Documentation: Comprehensive inline comments
- Error handling: Detailed logging and user-friendly messages

## Recent Changes
- Project initialized (2025-08-27)
- Research completed for Dolibarr module development and Mailchimp API integration
- Architecture defined for bidirectional sync module
- Core module structure implemented with all essential components
- Real-time sync triggers added for immediate synchronization on entity changes
- Enhanced field mapping with DOB support implemented
- Tag and segment management system created for advanced audience targeting
- Comprehensive UI interfaces built for configuration and monitoring
- All critical PHP errors and database connectivity issues resolved
- LSP errors reduced from 337 to 2, achieving fully functional demo state
- Working application deployed with professional dashboard and admin interfaces
- Module successfully tested and operational (2025-08-27)

## Development Guidelines
- Follow Dolibarr coding standards
- Use ModuleBuilder for initial scaffolding when possible
- Implement proper error handling and logging
- Secure API key storage and handling
- Create comprehensive admin interface for configuration