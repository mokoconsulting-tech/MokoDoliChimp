<?php
/**
 * Copyright (C) 2025 Moko Consulting <hello@mokoconsulting.tech>
 *
 * This file is part of a Moko Consulting project.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * FILE INFORMATION
 * DEFGROUP: MokoDoliChimp.Security
 * INGROUP: MokoDoliChimp
 * REPO: https://github.com/mokoconsulting-tech/MokoDoliChimp
 * FILE: index.php
 * VERSION: 01.00.00
 * BRIEF: Blank file to prevent directory listing
 * PATH: /src/core/index.php
 */

// Prevent directory listing
header('HTTP/1.0 403 Forbidden');
exit;
