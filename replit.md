# MokoDoliChimp Module

## Project Overview
A Dolibarr module called "MokoDoliChimp" that provides bidirectional synchronization between third-party contacts and Mailchimp email lists. This module allows automatic syncing of contact data between your Dolibarr ERP system and Mailchimp marketing platform.

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
- LSP errors resolved for standalone testing capability

## Development Guidelines
- Follow Dolibarr coding standards
- Use ModuleBuilder for initial scaffolding when possible
- Implement proper error handling and logging
- Secure API key storage and handling
- Create comprehensive admin interface for configuration