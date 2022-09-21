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
 * @package     mod_hva
 * @author      Loïc Hannecart <lhannecart@cblue.be>
 * @copyright   2022 CBlue (https://www.cblue.be/)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hva\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use \core_privacy\local\request\userlist;

class provider implements
    // This plugin does store personal user data.
    \core_privacy\local\metadata\provider {

    public static function get_metadata(collection $collection) : collection
    {

        // Here you will add more items into the collection.

        $collection->add_database_table(
            'hva_tracking',
            [
                'userid' => 'privacy:metadata:mod_hva_subs:userid',
                'hvaid' => 'privacy:metadata:mod_hva_subs:hvaid',
                'status' => 'privacy:metadata:mod_hva_subs:status',
                'score' => 'privacy:metadata:mod_hva_subs:score',
            ],
            'privacy:metadata:mod_hva_subs'
        );

        $collection->add_external_location_link('hva_api', [
            'userid' => 'privacy:metadata:mod_hva_api:userid',
            'score' => 'privacy:metadata:mod_hva_api:score',
            'status' => 'privacy:metadata:mod_hva_api:status'
        ], 'privacy:metadata:mod_hva_api');


        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist
    {
        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
           INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
           INNER JOIN {hva} h ON h.id = cm.instance
            LEFT JOIN {hva_tracking} t ON t.hva = h.id
                WHERE (
                t.userid        = :trackinguserid
                )
        ";

        $params = [
            'modname'           => 'hva',
            'contextlevel'      => CONTEXT_MODULE,
            'trackinguserid'  => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;

    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist)
    {

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $params = [
            'instanceid'    => $context->instanceid,
            'modulename'    => 'hva',
        ];

        // Tracking of users
        $sql = "SELECT d.userid
              FROM {course_modules} cm
              JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
              JOIN {hva} h ON h.id = cm.instance
              JOIN {hva_tracking} t ON t.hva = h.id
             WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist)
    {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT cm.id AS cmid,
                       co.text as answer,
                       ca.timemodified
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {hva} h ON h.id = cm.instance
            INNER JOIN {hva_tracking} t ON t.hvaid = h.id
                 WHERE c.id {$contextsql}
                       AND t.userid = :userid
              ORDER BY cm.id";

        $params = ['modname' => 'hva', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;

        $lastcmid = null;

        $trackings = $DB->get_recordset_sql($sql, $params);

        foreach ($trackings as $tracking) {
            // If we've moved to a new choice, then write the last choice data and reinit the choice data array.
            if ($lastcmid != $tracking->cmid) {
                if (!empty($trackingdata)) {
                    $context = \context_module::instance($lastcmid);
                    self::export_choice_data_for_user($trackingdata, $context, $user);
                }
                $trackingdata = [
                    'status' => [],
                    'score' => [],
                    'timemodified' => \core_privacy\local\request\transform::datetime($tracking->timemodified),
                ];
            }
            $trackingdata['status'][] = $tracking->status;
            $trackingdata['score'][] = $tracking->score;
            $lastcmid = $tracking->cmid;
        }
        $tracking->close();

        // The data for the last activity won't have been written yet, so make sure to write it now!
        if (!empty($trackingdata)) {
            $context = \context_module::instance($lastcmid);
            self::export_choice_data_for_user($trackingdata, $context, $user);
        }
    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('hva', $context->instanceid);
        if (!$cm) {
            return;
        }

        $DB->delete_records('hva_tracking', ['hvaid' => $cm->instance]);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_contextlist       $contextlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist)
    {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $DB->delete_records('hva_tracking', ['hvaid' => $instanceid, 'userid' => $userid]);
        }
    }



    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);
        $chat = $DB->get_record('hva', ['id' => $cm->instance]);

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['hvaid' => $chat->id], $userinparams);
        $sql = "hvaid = :hvaid AND userid {$userinsql}";

        $DB->delete_records_select('hva_traking', $sql, $params);
    }

}
