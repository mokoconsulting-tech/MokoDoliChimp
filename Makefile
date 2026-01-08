# Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>
#
# This file is part of a Moko Consulting project.
#
# SPDX-License-Identifier: GPL-3.0-or-later
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.
#
# FILE INFORMATION
# DEFGROUP: MokoDoliChimp.Build
# INGROUP: MokoDoliChimp
# REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
# FILE: /Makefile
# VERSION: 01.00.00
# BRIEF: Build and deployment automation for MokoDoliChimp module

# Module information
MODULE_NAME = mokodolichimp
MODULE_VERSION = 1.0.0

# Default Dolibarr paths (can be overridden)
DOLIBARR_PATH ?= /var/www/html/dolibarr
CUSTOM_PATH = $(DOLIBARR_PATH)/htdocs/custom
MODULE_PATH = $(CUSTOM_PATH)/$(MODULE_NAME)

# Deployment user and group (typically www-data for Apache)
WEB_USER ?= www-data
WEB_GROUP ?= www-data

# Build directory
BUILD_DIR = build
DIST_DIR = dist

# Files and directories to include in distribution
DIST_FILES = src/admin src/class src/core src/lang docs scripts \
	src/mokodolichimp.php \
	LICENSE README.md CHANGELOG.md CONTRIBUTING.md CODE_OF_CONDUCT.md

# Exclusion patterns for installation
EXCLUDE_PATTERNS = --exclude='.git*' --exclude='MokoStandards' --exclude='$(BUILD_DIR)' \
	--exclude='$(DIST_DIR)' --exclude='Makefile' --exclude='*.md'

# Colors for output
COLOR_RESET = \033[0m
COLOR_BOLD = \033[1m
COLOR_GREEN = \033[32m
COLOR_YELLOW = \033[33m
COLOR_BLUE = \033[34m

.PHONY: help
help: ## Show this help message
	@echo "$(COLOR_BOLD)MokoDoliChimp Makefile$(COLOR_RESET)"
	@echo ""
	@echo "$(COLOR_BLUE)Available targets:$(COLOR_RESET)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(COLOR_GREEN)%-20s$(COLOR_RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(COLOR_BLUE)Configuration:$(COLOR_RESET)"
	@echo "  DOLIBARR_PATH=$(DOLIBARR_PATH)"
	@echo "  MODULE_PATH=$(MODULE_PATH)"
	@echo "  WEB_USER=$(WEB_USER)"
	@echo "  WEB_GROUP=$(WEB_GROUP)"

.PHONY: check
check: ## Check PHP syntax for all PHP files
	@echo "$(COLOR_BOLD)Checking PHP syntax...$(COLOR_RESET)"
	@errors=0; \
	for file in $$(find . -name "*.php" ! -path "./MokoStandards/*" ! -path "./.git/*" ! -path "./$(BUILD_DIR)/*"); do \
		if ! php -l "$$file" > /dev/null 2>&1; then \
			php -l "$$file"; \
			errors=$$((errors + 1)); \
		fi; \
	done; \
	if [ $$errors -eq 0 ]; then \
		echo "$(COLOR_GREEN)✓ PHP syntax check completed$(COLOR_RESET)"; \
	else \
		echo "$(COLOR_YELLOW)✗ Found $$errors syntax error(s)$(COLOR_RESET)"; \
		exit 1; \
	fi

.PHONY: validate
validate: check ## Validate module structure and requirements
	@echo "$(COLOR_BOLD)Validating module structure...$(COLOR_RESET)"
	@test -f src/core/modules/modMokoDoliChimp.class.php || (echo "$(COLOR_YELLOW)Missing module descriptor$(COLOR_RESET)" && exit 1)
	@test -f src/mokodolichimp.php || (echo "$(COLOR_YELLOW)Missing main module file$(COLOR_RESET)" && exit 1)
	@test -d src/admin || (echo "$(COLOR_YELLOW)Missing admin directory$(COLOR_RESET)" && exit 1)
	@test -d src/class || (echo "$(COLOR_YELLOW)Missing class directory$(COLOR_RESET)" && exit 1)
	@test -d src/lang || (echo "$(COLOR_YELLOW)Missing lang directory$(COLOR_RESET)" && exit 1)
	@echo "$(COLOR_GREEN)✓ Module structure validated$(COLOR_RESET)"

.PHONY: clean
clean: ## Clean build artifacts
	@echo "$(COLOR_BOLD)Cleaning build artifacts...$(COLOR_RESET)"
	@rm -rf $(BUILD_DIR) $(DIST_DIR)
	@find . -name "*.bak" -type f -delete
	@find . -name "*~" -type f -delete
	@echo "$(COLOR_GREEN)✓ Clean completed$(COLOR_RESET)"

.PHONY: build
build: clean validate ## Build distribution package
	@echo "$(COLOR_BOLD)Building distribution package...$(COLOR_RESET)"
	@mkdir -p $(DIST_DIR)
	@mkdir -p $(BUILD_DIR)/$(MODULE_NAME)
	@echo "Copying files..."
	@# Copy source files from src/ to root of build
	@cp -r src/admin $(BUILD_DIR)/$(MODULE_NAME)/
	@cp -r src/class $(BUILD_DIR)/$(MODULE_NAME)/
	@cp -r src/core $(BUILD_DIR)/$(MODULE_NAME)/
	@cp -r src/lang $(BUILD_DIR)/$(MODULE_NAME)/
	@cp src/mokodolichimp.php $(BUILD_DIR)/$(MODULE_NAME)/
	@# Copy documentation and support files
	@if [ -d docs ]; then cp -r docs $(BUILD_DIR)/$(MODULE_NAME)/; fi
	@if [ -d scripts ]; then cp -r scripts $(BUILD_DIR)/$(MODULE_NAME)/; fi
	@if [ -f LICENSE ]; then cp LICENSE $(BUILD_DIR)/$(MODULE_NAME)/; fi
	@if [ -f README.md ]; then cp README.md $(BUILD_DIR)/$(MODULE_NAME)/; fi
	@if [ -f CHANGELOG.md ]; then cp CHANGELOG.md $(BUILD_DIR)/$(MODULE_NAME)/; fi
	@if [ -f CONTRIBUTING.md ]; then cp CONTRIBUTING.md $(BUILD_DIR)/$(MODULE_NAME)/; fi
	@if [ -f CODE_OF_CONDUCT.md ]; then cp CODE_OF_CONDUCT.md $(BUILD_DIR)/$(MODULE_NAME)/; fi
	@echo "Creating archive..."
	@cd $(BUILD_DIR) && zip -r ../$(DIST_DIR)/$(MODULE_NAME)-$(MODULE_VERSION).zip $(MODULE_NAME)
	@echo "$(COLOR_GREEN)✓ Build completed: $(DIST_DIR)/$(MODULE_NAME)-$(MODULE_VERSION).zip$(COLOR_RESET)"

.PHONY: install
install: validate ## Install module to Dolibarr (requires permissions)
	@echo "$(COLOR_BOLD)Installing module to $(MODULE_PATH)...$(COLOR_RESET)"
	@if [ ! -d "$(DOLIBARR_PATH)" ]; then \
		echo "$(COLOR_YELLOW)Error: Dolibarr path not found: $(DOLIBARR_PATH)$(COLOR_RESET)"; \
		echo "Set DOLIBARR_PATH variable: make install DOLIBARR_PATH=/path/to/dolibarr"; \
		exit 1; \
	fi
	@mkdir -p $(CUSTOM_PATH)
	@echo "Copying module files from src/..."
	@mkdir -p $(MODULE_PATH)
	@rsync -av src/admin/ $(MODULE_PATH)/admin/
	@rsync -av src/class/ $(MODULE_PATH)/class/
	@rsync -av src/core/ $(MODULE_PATH)/core/
	@rsync -av src/lang/ $(MODULE_PATH)/lang/
	@cp src/mokodolichimp.php $(MODULE_PATH)/
	@if [ -d docs ]; then rsync -av docs/ $(MODULE_PATH)/docs/; fi
	@if [ -d scripts ]; then rsync -av scripts/ $(MODULE_PATH)/scripts/; fi
	@if [ -f LICENSE ]; then cp LICENSE $(MODULE_PATH)/; fi
	@if [ -f README.md ]; then cp README.md $(MODULE_PATH)/; fi
	@if [ -f CHANGELOG.md ]; then cp CHANGELOG.md $(MODULE_PATH)/; fi
	@echo "Setting permissions..."
	@chmod -R 755 $(MODULE_PATH)
	@if command -v chown >/dev/null 2>&1 && [ -n "$(WEB_USER)" ]; then \
		if chown -R $(WEB_USER):$(WEB_GROUP) $(MODULE_PATH) 2>/dev/null; then \
			echo "$(COLOR_GREEN)✓ Ownership set to $(WEB_USER):$(WEB_GROUP)$(COLOR_RESET)"; \
		else \
			echo "$(COLOR_YELLOW)⚠ Could not set ownership (may require sudo)$(COLOR_RESET)"; \
		fi; \
	fi
	@echo "$(COLOR_GREEN)✓ Module installed to $(MODULE_PATH)$(COLOR_RESET)"
	@echo "$(COLOR_YELLOW)Next steps:$(COLOR_RESET)"
	@echo "  1. Go to Dolibarr: Home → Setup → Modules/Applications"
	@echo "  2. Find 'MokoDoliChimp' and click Activate"
	@echo "  3. Configure the module settings"

.PHONY: uninstall
uninstall: ## Remove module from Dolibarr
	@echo "$(COLOR_BOLD)Uninstalling module from $(MODULE_PATH)...$(COLOR_RESET)"
	@if [ -d "$(MODULE_PATH)" ]; then \
		echo "Removing module directory..."; \
		rm -rf $(MODULE_PATH); \
		echo "$(COLOR_GREEN)✓ Module uninstalled$(COLOR_RESET)"; \
	else \
		echo "$(COLOR_YELLOW)Module not found at $(MODULE_PATH)$(COLOR_RESET)"; \
	fi
	@echo "$(COLOR_YELLOW)Note: Deactivate the module in Dolibarr before uninstalling$(COLOR_RESET)"

.PHONY: dev-install
dev-install: ## Create symlink for development (requires permissions)
	@echo "$(COLOR_BOLD)Creating development symlink...$(COLOR_RESET)"
	@if [ ! -d "$(DOLIBARR_PATH)" ]; then \
		echo "$(COLOR_YELLOW)Error: Dolibarr path not found: $(DOLIBARR_PATH)$(COLOR_RESET)"; \
		exit 1; \
	fi
	@mkdir -p $(CUSTOM_PATH)
	@if [ -e "$(MODULE_PATH)" ]; then \
		echo "$(COLOR_YELLOW)Removing existing installation...$(COLOR_RESET)"; \
		rm -rf $(MODULE_PATH); \
	fi
	@ln -s $(PWD)/src $(MODULE_PATH)
	@echo "$(COLOR_GREEN)✓ Development symlink created$(COLOR_RESET)"
	@echo "$(COLOR_YELLOW)Note: Changes in src/ directory will be immediately reflected in Dolibarr$(COLOR_RESET)"

.PHONY: dev-sync
dev-sync: ## Sync local changes to remote Dolibarr installation (for FTP workflows)
	@echo "$(COLOR_BOLD)Syncing changes to $(MODULE_PATH)...$(COLOR_RESET)"
	@if [ ! -d "$(MODULE_PATH)" ]; then \
		echo "$(COLOR_YELLOW)Module not installed at $(MODULE_PATH)$(COLOR_RESET)"; \
		echo "Creating directory and performing initial sync..."; \
		mkdir -p $(MODULE_PATH); \
	fi
	@echo "Syncing files from src/ (excluding development artifacts)..."
	@rsync -av --delete src/admin/ $(MODULE_PATH)/admin/
	@rsync -av --delete src/class/ $(MODULE_PATH)/class/
	@rsync -av --delete src/core/ $(MODULE_PATH)/core/
	@rsync -av --delete src/lang/ $(MODULE_PATH)/lang/
	@rsync -av src/mokodolichimp.php $(MODULE_PATH)/
	@if [ -d docs ]; then rsync -av --delete docs/ $(MODULE_PATH)/docs/; fi
	@if [ -d scripts ]; then rsync -av --delete scripts/ $(MODULE_PATH)/scripts/; fi
	@echo "$(COLOR_GREEN)✓ Files synced successfully$(COLOR_RESET)"
	@echo "$(COLOR_BLUE)Synced from:$(COLOR_RESET) $(PWD)/src/"
	@echo "$(COLOR_BLUE)Synced to:$(COLOR_RESET)   $(MODULE_PATH)"
	@echo ""
	@echo "$(COLOR_YELLOW)Note: For FTP-based development:$(COLOR_RESET)"
	@echo "  1. Edit files locally in the src/ directory"
	@echo "  2. Run 'make dev-sync' to sync changes to Dolibarr"
	@echo "  3. Test changes in Dolibarr"
	@echo "  4. Repeat as needed"

.PHONY: update
update: ## Update existing installation
	@echo "$(COLOR_BOLD)Updating module installation...$(COLOR_RESET)"
	@if [ ! -d "$(MODULE_PATH)" ]; then \
		echo "$(COLOR_YELLOW)Module not installed. Use 'make install' first.$(COLOR_RESET)"; \
		exit 1; \
	fi
	@$(MAKE) install
	@echo "$(COLOR_GREEN)✓ Module updated$(COLOR_RESET)"

.PHONY: test
test: check ## Run tests (placeholder for future test implementation)
	@echo "$(COLOR_BOLD)Running tests...$(COLOR_RESET)"
	@echo "$(COLOR_YELLOW)Note: Test suite not yet implemented$(COLOR_RESET)"

.PHONY: dist
dist: build ## Create distribution package (alias for build)

.PHONY: all
all: validate build ## Run validation and build

# Default target
.DEFAULT_GOAL := help
