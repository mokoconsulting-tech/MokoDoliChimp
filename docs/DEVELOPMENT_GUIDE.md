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
FILE: /docs/DEVELOPMENT_GUIDE.md
VERSION: 01.00.00
BRIEF: Comprehensive development guide for FTP-based and local development workflows
PATH: /docs/DEVELOPMENT_GUIDE.md
-->

# MokoDoliChimp Development Guide

This comprehensive guide covers the complete development workflow for MokoDoliChimp, with detailed steps for FTP-based development (editing files on remote server) and local development (editing files locally).

---

## Table of Contents

- [Prerequisites](#prerequisites)
- [Initial Setup](#initial-setup)
- [FTP-Based Development Workflow](#ftp-based-development-workflow)
- [Local Development Workflow](#local-development-workflow)
- [Common Development Tasks](#common-development-tasks)
- [Testing Your Changes](#testing-your-changes)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software

1. **Git** - Version control
   ```bash
   git --version
   ```

2. **PHP 7.0+** - For syntax checking
   ```bash
   php --version
   ```

3. **Make** - Build automation
   ```bash
   make --version
   ```

4. **rsync** - File synchronization (for FTP workflow)
   ```bash
   rsync --version
   ```

5. **FTP Client** (Optional - for direct FTP editing)
   - FileZilla, Cyberduck, or command-line FTP

### Access Requirements

- SSH/FTP access to your Dolibarr server
- Write permissions to Dolibarr's `htdocs/custom/` directory
- Dolibarr admin access for module activation

---

## Initial Setup

### Step 1: Clone the Repository

Clone the repository with submodules:

```bash
# Navigate to your development directory
cd ~/projects

# Clone with submodules
git clone https://github.com/mokoconsulting-tech/MokoDoliChimp.git

# Navigate to project directory
cd MokoDoliChimp
```

### Step 2: Verify Project Structure

```bash
# Check all required directories exist
ls -la

# Expected output should include:
# - src/            (Source code)
#   - src/admin/    (Admin pages)
#   - src/class/    (Business logic)
#   - src/core/     (Module descriptor)
#   - src/lang/     (Translations)
# - docs/           (Documentation)
# - scripts/        (Build scripts)
# - Makefile        (Build automation)
```

### Step 3: Validate Installation

```bash
# Check PHP syntax for all files
make check

# Expected output:
# Checking PHP syntax...
# ✓ PHP syntax check completed

# Validate module structure
make validate

# Expected output:
# Validating module structure...
# ✓ Module structure validated
```

---

## FTP-Based Development Workflow

This workflow is ideal when you have FTP/SSH access to your Dolibarr server and want to edit files locally, then sync to the server.

### Overview

1. Edit files locally on your computer
2. Use `make dev-sync` to upload changes to server
3. Test changes in Dolibarr
4. Repeat as needed

### Step-by-Step Guide

#### Step 1: Configure Server Path

Set your Dolibarr installation path. You have two options:

**Option A: Set environment variable (recommended)**
```bash
# Add to your ~/.bashrc or ~/.zshrc for permanent setting
export DOLIBARR_PATH=/var/www/html/dolibarr

# Or set for current session only
export DOLIBARR_PATH=/path/to/your/dolibarr
```

**Option B: Specify path with each command**
```bash
# You'll need to add DOLIBARR_PATH=/path/to/dolibarr to each command
make dev-sync DOLIBARR_PATH=/var/www/html/dolibarr
```

#### Step 2: Initial Installation to Server

First, install the module to your Dolibarr server:

```bash
# If you set DOLIBARR_PATH as environment variable:
make install

# Or specify path directly:
make install DOLIBARR_PATH=/var/www/html/dolibarr

# You may need sudo if the directory requires elevated permissions:
sudo make install DOLIBARR_PATH=/var/www/html/dolibarr
```

**Expected Output:**
```
Installing module to /var/www/html/dolibarr/htdocs/custom/mokodolichimp...
Copying module files...
Setting permissions...
✓ Ownership set to www-data:www-data
✓ Module installed to /var/www/html/dolibarr/htdocs/custom/mokodolichimp

Next steps:
  1. Go to Dolibarr: Home → Setup → Modules/Applications
  2. Find 'MokoDoliChimp' and click Activate
  3. Configure the module settings
```

#### Step 3: Activate Module in Dolibarr

1. Open your Dolibarr installation in a web browser
2. Navigate to: **Home → Setup → Modules/Applications**
3. Search for "MokoDoliChimp"
4. Click the **Activate** button
5. Click the **Settings** button to configure API keys

#### Step 4: Development Cycle

Now you're ready to develop! Follow this iterative cycle:

**A. Edit Files Locally**

Use your favorite editor to modify files:

```bash
# Example: Edit the main module file
code src/mokodolichimp.php

# Or edit a class file
code src/class/mailchimpclient.class.php

# Or edit the admin page
code src/admin/setup.php
```

**B. Validate Changes Locally**

Before syncing, check for syntax errors:

```bash
# Check PHP syntax
make check

# Expected output:
# Checking PHP syntax...
# ✓ PHP syntax check completed
```

**C. Sync Changes to Server**

Upload your changes to the server:

```bash
# Sync files to server
make dev-sync

# Expected output:
# Syncing changes to /var/www/html/dolibarr/htdocs/custom/mokodolichimp...
# Syncing files from src/ (excluding development artifacts)...
# sending incremental file list
# mokodolichimp.php
# class/mailchimpclient.class.php
# 
# ✓ Files synced successfully
# Synced from: /home/user/projects/MokoDoliChimp/src/
# Synced to:   /var/www/html/dolibarr/htdocs/custom/mokodolichimp
```

**D. Test in Dolibarr**

1. Refresh your Dolibarr page in the browser
2. Test your changes
3. Check for errors in Dolibarr's error logs if needed:
   ```bash
   # On the server
   tail -f /var/www/html/dolibarr/documents/dolibarr.log
   ```

**E. Repeat**

Continue the cycle: Edit → Check → Sync → Test

### Understanding dev-sync Behavior

The `make dev-sync` command:

- **Syncs all module files** from your local directory to server
- **Excludes development files** (.git, build artifacts, etc.)
- **Uses rsync with --delete** to mirror your local changes exactly
- **Creates directory** if it doesn't exist on server
- **Preserves permissions** on the server

**Files that are excluded from sync:**
- `.git/` and `.gitignore`
- `build/` and `dist/` directories
- `Makefile`
- `.editorconfig`

### Step 5: Committing Changes to Git

After testing and verifying your changes work:

```bash
# Check what files changed
git status

# View specific changes
git diff src/mokodolichimp.php

# Stage your changes
git add src/mokodolichimp.php
git add src/class/mailchimpclient.class.php

# Commit with descriptive message
git commit -m "feat: add new feature to sync contacts"

# Push to your branch
git push origin your-branch-name
```

---

## Local Development Workflow

This workflow is for local development where Dolibarr runs on the same machine as your development environment.

### Step 1: Install Dolibarr Locally

Ensure Dolibarr is installed locally (e.g., via XAMPP, MAMP, or Docker).

### Step 2: Create Development Symlink

This creates a symbolic link, so changes in your development directory immediately reflect in Dolibarr:

```bash
# Create symlink
sudo make dev-install DOLIBARR_PATH=/path/to/local/dolibarr

# Expected output:
# Creating development symlink...
# ✓ Development symlink created
# Note: Changes in this directory will be immediately reflected in Dolibarr
```

### Step 3: Activate Module

Follow the same activation steps as in FTP workflow.

### Step 4: Development Cycle

1. **Edit files** in your development directory
2. **Changes are immediate** (no sync needed)
3. **Refresh browser** to see changes in Dolibarr
4. **Test** your changes
5. **Commit** when done

---

## Common Development Tasks

### Checking Syntax Before Sync

Always validate your code before syncing:

```bash
# Check all PHP files
make check

# If there are errors, they'll be shown with line numbers
```

### Building Distribution Package

Create a ZIP package for distribution:

```bash
# Build package
make build

# Output will be in dist/mokodolichimp-1.0.0.zip
ls -lh dist/
```

### Updating Existing Installation

If you've already installed and want to update:

```bash
# Update installation
make update

# Or for FTP workflow, just use dev-sync:
make dev-sync
```

### Cleaning Build Artifacts

Remove temporary build files:

```bash
# Clean build and dist directories
make clean

# Expected output:
# Cleaning build artifacts...
# ✓ Clean completed
```

### Viewing All Available Commands

```bash
# Show help
make help

# Output shows all available commands with descriptions
```

---

## Testing Your Changes

### Manual Testing Checklist

After making changes, test the following:

1. **Module Activation**
   - Module appears in Modules/Applications list
   - Activation completes without errors

2. **Configuration Page**
   - Navigate to module settings
   - All fields display correctly
   - Can save configuration

3. **Mailchimp Integration**
   - API key validation works
   - Contact sync functionality works
   - User sync functionality works

4. **Error Handling**
   - Invalid API keys show appropriate errors
   - Network errors are handled gracefully

### Checking Dolibarr Logs

```bash
# On the server, monitor logs in real-time
tail -f /var/www/html/dolibarr/documents/dolibarr.log

# Or check recent errors
tail -50 /var/www/html/dolibarr/documents/dolibarr.log | grep ERROR
```

### PHP Error Logs

```bash
# Check Apache error logs (Ubuntu/Debian)
sudo tail -f /var/log/apache2/error.log

# Check PHP-FPM logs (if using PHP-FPM)
sudo tail -f /var/log/php-fpm/error.log
```

---

## Troubleshooting

### Issue: "make: command not found"

**Solution:** Install make utility

```bash
# Ubuntu/Debian
sudo apt-get install make

# macOS (via Homebrew)
brew install make

# CentOS/RHEL
sudo yum install make
```

### Issue: "rsync: command not found"

**Solution:** Install rsync

```bash
# Ubuntu/Debian
sudo apt-get install rsync

# macOS (usually pre-installed, but if needed)
brew install rsync

# CentOS/RHEL
sudo yum install rsync
```

### Issue: Permission Denied When Syncing

**Solution:** Use sudo or fix permissions

```bash
# Option 1: Use sudo
sudo make dev-sync

# Option 2: Fix directory permissions (on server)
sudo chown -R $USER:www-data /var/www/html/dolibarr/htdocs/custom
sudo chmod -R 775 /var/www/html/dolibarr/htdocs/custom
```

### Issue: Module Not Appearing in Dolibarr

**Checklist:**

1. Verify files are in correct directory:
   ```bash
   ls -la /var/www/html/dolibarr/htdocs/custom/mokodolichimp/
   ```

2. Check file permissions:
   ```bash
   ls -la /var/www/html/dolibarr/htdocs/custom/mokodolichimp/*.php
   # Files should be readable by web server (644 or 755)
   ```

3. Check Dolibarr module cache:
   - In Dolibarr, go to: Home → Setup → Other
   - Clear all caches
   - Refresh the Modules/Applications page

4. Check for PHP errors:
   ```bash
   tail -f /var/www/html/dolibarr/documents/dolibarr.log
   ```

### Issue: Changes Not Reflected After Sync

**Solutions:**

1. **Clear browser cache:** Use Ctrl+F5 (Windows/Linux) or Cmd+Shift+R (Mac)

2. **Clear Dolibarr cache:**
   - Home → Setup → Other → "Clear all cache"

3. **Verify sync completed:**
   ```bash
   # Check file timestamps on server
   ls -lat /var/www/html/dolibarr/htdocs/custom/mokodolichimp/ | head -10
   ```

4. **Force full sync:**
   ```bash
   # Remove module directory and re-sync
   sudo rm -rf /var/www/html/dolibarr/htdocs/custom/mokodolichimp
   sudo make dev-sync
   ```

### Issue: PHP Syntax Errors

**Solution:** Run syntax check and fix errors

```bash
# Check syntax
make check

# Fix the reported errors
# Then re-sync
make dev-sync
```

---

## Best Practices

### 1. Always Check Syntax Before Syncing

```bash
make check && make dev-sync
```

### 2. Use Version Control Branches

```bash
# Create feature branch
git checkout -b feature/my-new-feature

# Make changes, sync, test
# ...

# Commit and push
git add .
git commit -m "feat: add my new feature"
git push origin feature/my-new-feature
```

### 3. Test in Staging Before Production

- Use a separate Dolibarr staging instance
- Test all changes thoroughly
- Only deploy to production after validation

### 4. Keep Development Environment Updated

```bash
# Pull latest changes
git pull origin main

# Update submodules
git submodule update --remote

# Re-sync to server
make dev-sync
```

### 5. Document Your Changes

- Update CHANGELOG.md for significant changes
- Add comments to complex code
- Update README.md if adding new features

---

## Quick Reference

### Common Commands

```bash
# Initial setup
git clone --recurse-submodules https://github.com/mokoconsulting-tech/MokoDoliChimp.git
cd MokoDoliChimp

# Set Dolibarr path (add to ~/.bashrc for permanence)
export DOLIBARR_PATH=/var/www/html/dolibarr

# Validate code
make check

# Sync to server (FTP workflow)
make dev-sync

# Create symlink (Local workflow)
sudo make dev-install

# Build distribution
make build

# Show all commands
make help
```

### File Locations

- **Module files on server:** `/var/www/html/dolibarr/htdocs/custom/mokodolichimp/`
- **Dolibarr logs:** `/var/www/html/dolibarr/documents/dolibarr.log`
- **Apache logs:** `/var/log/apache2/error.log`
- **Distribution packages:** `./dist/mokodolichimp-1.0.0.zip`

### Key Files to Edit

- **Main module file:** `src/mokodolichimp.php`
- **Module descriptor:** `src/core/modules/modMokoDoliChimp.class.php`
- **Mailchimp client:** `src/class/mailchimpclient.class.php`
- **Hook actions:** `src/class/actions_mokodolichimp.class.php`
- **Admin setup page:** `src/admin/setup.php`
- **Translations:** `src/lang/en_US/mokodolichimp.lang`

---

## Getting Help

- **Issues:** https://github.com/mokoconsulting-tech/MokoDoliChimp/issues
- **MokoStandards:** https://github.com/mokoconsulting-tech/MokoStandards
- **Dolibarr Wiki:** https://wiki.dolibarr.org/

---

**Happy Coding! 🚀**
