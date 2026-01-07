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
FILE: README.md
VERSION: 01.00.00
BRIEF: Readme and usage documentation for the MokoDoliChimp Dolibarr module
PATH: /README.md
-->
# MokoDoliChimp

A Dolibarr module that seamlessly syncs contacts and users with Mailchimp subscriber lists, enabling automated mailing list management directly from your Dolibarr ERP/CRM.

---

## 📋 Features

- **Automatic Sync**: Automatically sync Dolibarr contacts and users to Mailchimp when created or updated
- **Manual Sync**: Sync individual contacts/users to Mailchimp with a single click
- **Flexible Configuration**: Configure API keys, audience lists, and subscription settings
- **Double Opt-in Support**: Choose between immediate subscription or email confirmation
- **Seamless Integration**: Integrates directly into Dolibarr contact and user cards

---

## 🚀 Installation

1. Navigate to your Dolibarr custom modules directory:
	```bash
	cd /path/to/dolibarr/htdocs/custom
	```
2. Clone the repository:
	```bash
	git clone https://github.com/mokoconsulting-tech/MokoDoliChimp.git mokodolichimp
	```
3. Ensure proper permissions:
	```bash
	chown -R www-data:www-data mokodolichimp
	chmod -R 755 mokodolichimp
	```
4. In Dolibarr, go to **Home → Setup → Modules/Applications**
5. Find **MokoDoliChimp** and click **Activate**

---

## 🛠 Usage

### Configuration

1. After activation, click the **Settings** button for MokoDoliChimp
2. Enter your Mailchimp API Key (found in Mailchimp Account → Extras → API Keys)
3. Enter your Mailchimp Audience/List ID (found in Audience → Settings → Audience name and defaults)
4. Configure sync options:
   - **Auto-sync on Save**: Automatically sync contacts/users when they are created or updated
   - **Default Subscription Status**: Choose between "subscribed" (immediate) or "pending" (double opt-in)
5. Click **Save**

### Manual Sync

1. Open any Contact or User card in Dolibarr
2. Click the **Sync to Mailchimp** button at the bottom of the card
3. The contact/user will be added or updated in your Mailchimp audience

### Auto-Sync

When auto-sync is enabled, contacts and users are automatically synced to Mailchimp whenever they are created or updated in Dolibarr.

---

## 📂 Folder Structure

```plaintext
mokodolichimp/
├── admin/                      # Admin configuration pages
│   └── setup.php              # Module setup page
├── class/                      # Business logic classes
│   ├── actions_mokodolichimp.class.php  # Hook handlers
│   └── mailchimpclient.class.php        # Mailchimp API client
├── core/                       # Core module files
│   └── modules/
│       └── modMokoDoliChimp.class.php   # Module descriptor
├── docs/                       # Documentation
│   └── index.md               # Documentation index
├── lang/                       # Language files
│   └── en_US/
│       └── mokodolichimp.lang  # English translations
├── scripts/                    # Build and validation scripts
│   └── index.md               # Scripts directory index
├── MokoStandards/             # MokoStandards submodule (coding standards)
├── mokodolichimp.php          # Module main page
├── LICENSE                     # GPL-3.0-or-later license
└── README.md                   # This file
```

This module follows the [MokoStandards](https://github.com/mokoconsulting-tech/MokoCodingDefaults) structure for Moko Consulting projects.

---

## 📋 Requirements

- Dolibarr 11.0 or higher
- PHP 7.0 or higher
- cURL PHP extension
- Valid Mailchimp account with API access

---

## 🔑 Mailchimp API Setup

1. Log in to your Mailchimp account
2. Navigate to **Account → Extras → API Keys**
3. Click **Create A Key** to generate a new API key
4. Copy the API key (it will look like: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-us5`)
5. Navigate to **Audience → Settings → Audience name and defaults**
6. Copy the **Audience ID** (it will look like: `a1b2c3d4e5`)

---

## 🔒 Permissions

The module defines three permission levels:

- **Read**: View MokoDoliChimp settings and status
- **Configure**: Modify MokoDoliChimp settings
- **Sync**: Manually sync contacts and users to Mailchimp

Assign permissions to users via **Home → Setup → Users & Groups**.

---

## 🐛 Troubleshooting

### "Mailchimp is not configured" error
- Ensure you have entered both the API Key and List ID in the module settings
- Verify that the API Key is valid and not expired

### Sync fails with HTTP 400 error
- Check that the email address is valid
- Ensure the List ID is correct

### Sync fails with HTTP 401 error
- Your API key may be invalid or expired
- Generate a new API key in Mailchimp and update the module settings

### Auto-sync not working
- Ensure "Auto-sync on Save" is enabled in module settings
- Verify that users have the "Sync" permission
- Check that contacts/users have valid email addresses

---

## 📄 License

This project is licensed under the [GPL-3.0-or-later](LICENSE) license.

---

## 👤 Author

**Moko Consulting**\
📧 [hello@mokoconsulting.tech](mailto\:hello@mokoconsulting.tech)\
🌐 [mokoconsulting.tech](https://mokoconsulting.tech)

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

© 2025 Moko Consulting. Licensed under the GNU General Public License v3.0 or later (GPL-3.0-or-later).

