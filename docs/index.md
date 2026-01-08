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
FILE: /docs/index.md
VERSION: 01.00.00
BRIEF: Documentation index for MokoDoliChimp module
PATH: /docs/index.md
-->

# MokoDoliChimp Documentation

This directory contains documentation for the MokoDoliChimp module.

## Documentation Files

### Developer Guides

- **[DEVELOPMENT_GUIDE.md](./DEVELOPMENT_GUIDE.md)** - Comprehensive development guide with step-by-step instructions
  - FTP-based development workflow
  - Local development workflow
  - Common development tasks
  - Testing and troubleshooting
  - Quick reference commands

### Main Documentation

- [README.md](../README.md) - Main project documentation and usage guide
- [CHANGELOG.md](../CHANGELOG.md) - Version history and release notes
- [CONTRIBUTING.md](../CONTRIBUTING.md) - Contribution guidelines

## Getting Started with Development

For detailed step-by-step instructions on setting up your development environment and syncing changes to your server via FTP, see the **[DEVELOPMENT_GUIDE.md](./DEVELOPMENT_GUIDE.md)**.

Quick start:
```bash
# Clone with submodules
git clone --recurse-submodules https://github.com/mokoconsulting-tech/MokoDoliChimp.git

# Set your Dolibarr path
export DOLIBARR_PATH=/var/www/html/dolibarr

# Initial installation
make install

# Edit files locally, then sync to server
make dev-sync
```

## Module Structure

The MokoDoliChimp module follows the MokoStandards structure:

- `/admin/` - Administration and configuration pages
- `/class/` - Business logic classes
- `/core/` - Core module files and descriptors
- `/lang/` - Translation files
- `/docs/` - Documentation (this directory)
- `/scripts/` - Build and validation scripts
- `/MokoStandards/` - MokoStandards submodule reference

For more information about MokoStandards, see the [MokoStandards repository](https://github.com/mokoconsulting-tech/MokoStandards).
