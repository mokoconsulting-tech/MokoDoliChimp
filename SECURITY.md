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
FILE: SECURITY.md
VERSION: 01.00.00
BRIEF: Security policy and vulnerability reporting guidelines
PATH: /SECURITY.md
-->

# Security Policy

## Supported Versions

We release patches for security vulnerabilities. Which versions are eligible for receiving such patches depends on the CVSS v3.0 Rating:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report security vulnerabilities to our security team:

**Email:** security@mokoconsulting.tech

You should receive a response within 48 hours. If for some reason you do not, please follow up via email to ensure we received your original message.

Please include the following information in your report:

- Type of issue (e.g., buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit the issue

This information will help us triage your report more quickly.

## Preferred Languages

We prefer all communications to be in English.

## Security Update Policy

When we receive a security bug report, we will:

1. Confirm the problem and determine the affected versions
2. Audit code to find any similar problems
3. Prepare fixes for all supported versions
4. Release new security fix versions as soon as possible

## Responsible Disclosure

We kindly ask you to:

- Give us reasonable time to investigate and fix the issue before public disclosure
- Make a good faith effort to avoid privacy violations, destruction of data, and interruption or degradation of our services
- Not access or modify other users' data without explicit permission
- Not perform any attack that could harm the reliability/integrity of our services or data

## Recognition

We deeply appreciate the efforts of security researchers who help us maintain the security of our project. Researchers who follow this policy will be recognized in our security acknowledgments (unless they prefer to remain anonymous).

## Scope

This security policy applies to:

- The latest stable version of MokoDoliChimp
- The main development branch
- All officially supported versions listed above

## Out of Scope

The following are generally out of scope for security reports:

- Issues in third-party dependencies (please report to the respective projects)
- Issues that require physical access to a user's device
- Social engineering attacks
- Denial of service attacks

## Additional Information

For more information about our security practices, please visit:
- [Moko Consulting Security](https://mokoconsulting.tech/security)
- [Contributing Guidelines](CONTRIBUTING.md)

---

© 2025 Moko Consulting. Licensed under the GNU General Public License v3.0 or later (GPL-3.0-or-later).
