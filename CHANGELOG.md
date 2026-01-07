<!--
Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>

This file is part of a Moko Consulting project.

SPDX-License-Identifier: GPL-3.0-or-later

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this documentation. If not, see <https://www.gnu.org/licenses/>.

FILE INFORMATION
DEFGROUP: MokoDoliChimp.Documentation
INGROUP: MokoDoliChimp
REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
FILE: CHANGELOG.md
VERSION: 01.00.00
BRIEF: Version history and release notes for MokoDoliChimp
PATH: /CHANGELOG.md
-->

# Changelog

All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Makefile for automated build and deployment
  - `make install` - Install module to Dolibarr
  - `make build` - Create distribution package
  - `make check` - Validate PHP syntax
  - `make dev-install` - Create development symlink
  - `make help` - Show all available commands

### Changed
- Restructured repository according to MokoStandards
- Added MokoStandards as a git submodule
- Updated all file headers with FILE INFORMATION blocks
- Added `docs/` directory with index
- Added `scripts/` directory for build/validation scripts
- Updated folder structure documentation
- Enhanced README with multiple installation methods

## [1.0.0] - 2025-01-05
### Added
- Initial release of MokoDoliChimp module
- Dolibarr module structure following MokoStandards
- Mailchimp API integration for contact and user synchronization
- Admin configuration page for API key and list settings
- Manual sync button on contact and user cards
- Auto-sync functionality on contact/user create and update
- Support for double opt-in (pending status)
- Comprehensive documentation and installation guide
- GPL-3.0-or-later licensing with proper headers
- Permissions system (read, configure, sync)
- Multi-language support (English included)
- Error handling and user feedback messages


