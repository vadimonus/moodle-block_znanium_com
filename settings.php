<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Authentication on znanium.com
 *
 * @package    block_znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
            'block_znanium_com/domain',
            new lang_string('domain', 'block_znanium_com'),
            '',
            '',
            PARAM_RAW));

    $settings->add(new admin_setting_configtext(
            'block_znanium_com/secretkey',
            new lang_string('secretkey', 'block_znanium_com'),
            '',
            '',
            PARAM_RAW));

    $settings->add(new admin_setting_configtext(
            'block_znanium_com/title',
            new lang_string('title', 'block_znanium_com'),
            '',
            new lang_string('defaulttitle', 'block_znanium_com'),
            PARAM_RAW));

    $settings->add(new admin_setting_configtext(
            'block_znanium_com/link',
            new lang_string('link', 'block_znanium_com'),
            '',
            new lang_string('defaultlink', 'block_znanium_com'),
            PARAM_RAW));
}


