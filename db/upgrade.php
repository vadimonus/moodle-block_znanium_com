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
 * @copyright  2015 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the HTML block.
 *
 * @param int $oldversion
 * @param object $block
 * @return bool
 */
function xmldb_block_znanium_com_upgrade($oldversion, $block) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2015022500) {

        // Define table block_znanium_com_visits to be created.
        $table = new xmldb_table('block_znanium_com_visits');

        // Adding fields to table block_znanium_com_visits.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_znanium_com_visits.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('contextid', XMLDB_KEY_FOREIGN, ['contextid'], 'context', ['id']);

        // Adding indexes to table block_znanium_com_visits.
        $table->add_index('time', XMLDB_INDEX_NOTUNIQUE, ['time']);

        // Conditionally launch create table for block_znanium_com_visits.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Znanium_com savepoint reached.
        upgrade_block_savepoint(true, 2015022500, 'znanium_com');
    }

    if ($oldversion < 2015110800) {
        upgrade_block_savepoint(true, 2015110800, 'znanium_com');
    }

    if ($oldversion < 2021010601) {
        // Define table block_znanium_com_visits to be created.
        $table = new xmldb_table('block_znanium_com_visits');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'time');
        $key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $dbman->drop_key($table, $key);
        $dbman->change_field_notnull($table, $field);
        $dbman->add_key($table, $key);
        upgrade_block_savepoint(true, 2021010601, 'znanium_com');
    }

    return true;
}
