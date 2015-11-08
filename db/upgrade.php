<?php

/**
 * Authentication on znanium.com
 *
 * @package    block
 * @subpackage znanium_com
 * @copyright  2015 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_znanium_com_upgrade($oldversion, $block) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2015022500) {

        // Define table block_znanium_com_visits to be created.
        $table = new xmldb_table('block_znanium_com_visits');

        // Adding fields to table block_znanium_com_visits.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_znanium_com_visits.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('contextid', XMLDB_KEY_FOREIGN, array('contextid'), 'context', array('id'));

        // Adding indexes to table block_znanium_com_visits.
        $table->add_index('time', XMLDB_INDEX_NOTUNIQUE, array('time'));

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

}
