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

// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

namespace block_znanium_com\privacy;

use context;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

// For compatibility with Moodle 3.3 and earlier.
if (!interface_exists('\core_privacy\local\request\core_userlist_provider')) {
    interface core_userlist_provider {
    }
    class_alias(
        '\block_znanium_com\privacy\core_userlist_provider',
        '\core_privacy\local\request\core_userlist_provider'
    );
}

/**
 * Privacy Subsystem.
 *
 * @package    block_znanium_com
 * @copyright  2020 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // The plugin stores user provided data.
    \core_privacy\local\metadata\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider,

    // The plugin provides data directly to core.
    \core_privacy\local\request\plugin\provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
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
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid($userid) {
        global $DB;

        $contextlist = new contextlist();

        $sql = "SELECT DISTINCT contextid
            FROM {block_znanium_com_visits}
            WHERE userid = :userid";
        $params = ['userid' => $userid];
        $contextlist->add_from_sql($sql, $params);

        $deletedcontexts = "SELECT bzcv.contextid
                FROM {block_znanium_com_visits} bzcv
                WHERE userid = :userid
                AND NOT EXISTS (SELECT 1 FROM {context} c WHERE c.id = bzcv.contextid)";
        if ($DB->record_exists_sql($deletedcontexts, $params)) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $sql = "SELECT userid AS userid
                FROM {block_znanium_com_visits} bzcv
                WHERE contextid = :contextid";
        $params = ['contextid' => $context->id];
        $userlist->add_from_sql('userid', $sql, $params);

        if ($context instanceof context_system) {
            $sql = "SELECT userid AS userid
                FROM {block_znanium_com_visits} bzcv
                WHERE NOT EXISTS (SELECT 1 FROM {context} c WHERE c.id = bzcv.contextid)";
            $params = [];
            $userlist->add_from_sql('userid', $sql, $params);
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function _export_user_data($contextlist) {
        $userid = $contextlist->get_user()->id;
        $contextids = $contextlist->get_contextids();
        foreach ($contextids as $contextid) {
            $visits = self::get_visit_times_for_context($userid, $contextid);
            $context = context::instance_by_id($contextid);
            writer::with_context($context)->export_data(
                [get_string('privacy:metadata:block_znanium_com_visit_dates', 'block_znanium_com')],
                (object) $visits
            );
        }
    }

    /**
     * Extracts visit times for specified user and context and formats array
     *
     * @param int $userid
     * @param int $contextid
     * @return string[]
     */
    protected static function get_visit_times_for_context(int $userid, int $contextid) {
        global $DB;
        $conditions = [
            'userid' => $userid,
            'contextid' => $contextid,
        ];
        $visits = $DB->get_records('block_znanium_com_visits', $conditions);

        if ($contextid == SYSCONTEXTID) {
            // Adding values from missing contexts to system context.
            $deletedcontextssql = "SELECT bzcv.time
                    FROM {block_znanium_com_visits} bzcv
                    WHERE userid = :userid
                    AND NOT EXISTS (SELECT 1 FROM {context} c WHERE c.id = bzcv.contextid)";
            $deletedcontextparams = [
                'userid' => $userid,
            ];
            $visits = array_merge(
                $visits,
                $DB->get_records_sql($deletedcontextssql, $deletedcontextparams)
            );
        }
        $visittimes = array_map(function ($visit) {
            return $visit->time;
        }, $visits);
        sort($visittimes);
        $visittimes = array_map(function ($visittime) {
            return transform::datetime($visittime);
        }, $visittimes);
        return $visittimes;
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function _delete_data_for_all_users_in_context($context) {
        static::delete_data_for_users_and_contexts(
            [],
            [$context->id]
        );
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function _delete_data_for_user($contextlist) {
        static::delete_data_for_users_and_contexts(
            [$contextlist->get_user()->id],
            $contextlist->get_contextids()
        );
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        static::delete_data_for_users_and_contexts(
            $userlist->get_userids(),
            [$userlist->get_context()->id]
        );
    }

    /**
     * Unlinks visits for multiple users and contexts
     *
     * @param array $userids empty for all users
     * @param array $contextids empty array for all contexts
     */
    protected static function delete_data_for_users_and_contexts($userids, $contextids) {
        global $DB;
        if ($userids) {
            list($userwhere, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid');
            $userwhere = 'userid ' . $userwhere;
        }
        if ($contextids) {
            list($contextwhere, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'contextid');
            $contextwhere = 'contextid ' . $contextwhere;
        }
        $DB->set_field_select(
            'block_znanium_com_visits',
            'userid',
            null,
            $userwhere . ' AND ' . $contextwhere,
            array_merge($userparams, $contextparams)
        );

        if ($contextids && in_array(SYSCONTEXTID, $contextids)) {
            // Removing values for missing contexts, when requested to clear system context.
            $contextwhere = "NOT EXISTS (SELECT 1 FROM {context} c WHERE c.id = contextid)";
            $contextparams = [];

            $DB->set_field_select(
                'block_znanium_com_visits',
                'userid',
                null,
                $userwhere . ' AND ' . $contextwhere,
                array_merge($userparams, $contextparams)
            );
        }
    }
}
