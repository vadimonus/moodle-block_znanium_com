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
 * @copyright  2020 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_znanium_com\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem.
 *
 * @package    block_znanium_com
 * @copyright  2020 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // The block_html block stores user provided data.
    \core_privacy\local\metadata\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider,

    // The block_html block provides data directly to core.
    \core_privacy\local\request\plugin\provider
{

    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection     A listing of user data stored through this system.
     */
    public static function _get_metadata(collection $collection) {
        $collection->add_external_location_link('znanium.com', [
            'username' => 'privacy:metadata:block_znanium_com:username',
            'lastname' => 'privacy:metadata:block_znanium_com:lastname',
            'firstname' => 'privacy:metadata:block_znanium_com:firstname',
            'middlename' => 'privacy:metadata:block_znanium_com:middlename',
            'timestamp' => 'privacy:metadata:block_znanium_com:timestamp',
            'documentid' => 'privacy:metadata:block_znanium_com:documentid',
            'pagenumber' => 'privacy:metadata:block_znanium_com:pagenumber',
        ], 'privacy:metadata:block_znanium_com');

        $collection->add_database_table(
            'block_znanium_com_visits',
            [
                'time' => 'privacy:metadata:block_znanium_com_visits:time',
                'userid' => 'privacy:metadata:block_znanium_com_visits:userid',
                'contextid' => 'privacy:metadata:block_znanium_com_visits:contextid',
            ],
            'privacy:metadata:block_znanium_com_visits'
        );
        return $collection;
    }

    /**
     * @param int $userid
     * @return contextlist
     */
    public static function _get_contexts_for_userid($userid) {
        $sql = "SELECT DISTINCT contextid
              FROM {block_znanium_com_visits}
             WHERE userid = :userid";
        $params = ['userid' => $userid];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function _export_user_data($contextlist) {
        global $DB;
        $contextids = $contextlist->get_contextids();
        $user = $contextlist->get_user();
        foreach ($contextids as $contextid) {
            $conditions = [
                'userid' => $user->id,
                'contextid' => $contextid,
            ];
            $visits = $DB->get_records('block_znanium_com_visits', $conditions, 'time ASC');
            $visittimes = array_values(array_map(function ($visit) {
                return transform::datetime($visit->time);
            }, $visits));
            $context = \context::instance_by_id($contextid);
            writer::with_context($context)->export_data(
                [get_string('privacy:metadata:block_znanium_com_visit_dates', 'block_znanium_com')],
                (object) $visittimes
            );
        }
    }

    /**
     * @param \context $context
     */
    public static function _delete_data_for_all_users_in_context($context) {
        global $DB;
        $DB->set_field('block_znanium_com_visits', 'userid', null, ['contextid' => $context->id]);
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function _delete_data_for_user($contextlist) {
        global $DB;
        $contextids = $contextlist->get_contextids();
        $user = $contextlist->get_user();
        list($insql, $inparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'contextid');
        $params = array_merge(['userid' => $user->id], $inparams);
        $where = 'userid = :userid AND contextid ' . $insql;
        $DB->set_field_select('block_znanium_com_visits', 'userid', null, $where, $params);
    }

    /**
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $sql = "SELECT userid AS userid
                FROM {block_znanium_com_visits} bzcv
                WHERE contextid = :contextid";
        $params = ['contextid' => $context->id];
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * @param approved_userlist $userlist
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();
        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid');
        $params = array_merge(['contextid' => $context->id], $inparams);
        $where = 'contextid = :contextid AND userid ' . $insql;
        $DB->set_field_select('block_znanium_com_visits', 'userid', null, $where, $params);
    }

}
