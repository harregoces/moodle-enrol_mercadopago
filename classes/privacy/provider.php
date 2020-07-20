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
 * MercadoPago enrolment plugin
 *
 * @package    enrol_mercadopago
 * @copyright  2020 Hernan Arregoces
 * @author     Hernan Arregoces harregoces@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_mercadopago\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection) : collection {
        $collection->add_external_location_link(
            'mercadopago.com',
            [
                'os0'        => 'privacy:metadata:enrol_mercadopago:mercadopago_com:os0',
                'custom'     => 'privacy:metadata:enrol_mercadopago:mercadopago_com:custom',
                'first_name' => 'privacy:metadata:enrol_mercadopago:mercadopago_com:first_name',
                'last_name'  => 'privacy:metadata:enrol_mercadopago:mercadopago_com:last_name',
                'address'    => 'privacy:metadata:enrol_mercadopago:mercadopago_com:address',
                'city'       => 'privacy:metadata:enrol_mercadopago:mercadopago_com:city',
                'email'      => 'privacy:metadata:enrol_mercadopago:mercadopago_com:email',
                'country'    => 'privacy:metadata:enrol_mercadopago:mercadopago_com:country',
            ],
            'privacy:metadata:enrol_mercadopago:mercadopago_com'
        );

        // The enrol_mercadopago has a DB table that contains user data.
        $collection->add_database_table(
                'enrol_mercadopago',
                [
                    'business'            => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:business',
                    'receiver_email'      => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:receiver_email',
                    'receiver_id'         => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:receiver_id',
                    'item_name'           => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:item_name',
                    'courseid'            => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:courseid',
                    'userid'              => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:userid',
                    'instanceid'          => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:instanceid',
                    'memo'                => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:memo',
                    'tax'                 => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:tax',
                    'option_selection1_x' => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:option_selection1_x',
                    'payment_status'      => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:payment_status',
                    'pending_reason'      => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:pending_reason',
                    'reason_code'         => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:reason_code',
                    'txn_id'              => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:txn_id',
                    'parent_txn_id'       => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:parent_txn_id',
                    'payment_type'        => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:payment_type',
                    'timeupdated'         => 'privacy:metadata:enrol_mercadopago:enrol_mercadopago:timeupdated'
                ],
                'privacy:metadata:enrol_mercadopago:enrol_mercadopago'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {enrol_mercadopago} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {context} ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                  JOIN {user} u ON u.id = ep.userid OR LOWER(u.email) = ep.receiver_email OR LOWER(u.email) = ep.business
                 WHERE u.id = :userid";
        $params = [
            'contextcourse' => CONTEXT_COURSE,
            'userid'        => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        $sql = "SELECT u.id
                  FROM {enrol_mercadopago} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {user} u ON ep.userid = u.id OR LOWER(u.email) = ep.receiver_email OR LOWER(u.email) = ep.business
                 WHERE e.courseid = :courseid";
        $params = ['courseid' => $context->instanceid];

        $userlist->add_from_sql('id', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT ep.*
                  FROM {enrol_mercadopago} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {context} ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                  JOIN {user} u ON u.id = ep.userid OR LOWER(u.email) = ep.receiver_email OR LOWER(u.email) = ep.business
                 WHERE ctx.id {$contextsql} AND u.id = :userid
              ORDER BY e.courseid";

        $params = [
            'contextcourse' => CONTEXT_COURSE,
            'userid'        => $user->id,
            'emailuserid'   => $user->id,
        ];
        $params += $contextparams;

        $lastcourseid = null;

        $strtransactions = get_string('transactions', 'enrol_mercadopago');
        $transactions = [];
        $records = $DB->get_recordset_sql($sql, $params);
        foreach ($records as $record) {
            if ($lastcourseid != $record->courseid) {
                if (!empty($transactions)) {
                    $coursecontext = \context_course::instance($record->courseid);
                    writer::with_context($coursecontext)->export_data(
                            [$strtransactions],
                            (object) ['transactions' => $transactions]
                    );
                }
                $transactions = [];
            }

            $transaction = (object) [
                'receiver_id'         => $record->receiver_id,
                'item_name'           => $record->item_name,
                'userid'              => $record->userid,
                'memo'                => $record->memo,
                'tax'                 => $record->tax,
                'option_name1'        => $record->option_name1,
                'option_selection1_x' => $record->option_selection1_x,
                'option_name2'        => $record->option_name2,
                'option_selection2_x' => $record->option_selection2_x,
                'payment_status'      => $record->payment_status,
                'pending_reason'      => $record->pending_reason,
                'reason_code'         => $record->reason_code,
                'txn_id'              => $record->txn_id,
                'parent_txn_id'       => $record->parent_txn_id,
                'payment_type'        => $record->payment_type,
                'timeupdated'         => \core_privacy\local\request\transform::datetime($record->timeupdated),
            ];
            if ($record->userid == $user->id) {
                $transaction->userid = $record->userid;
            }
            if ($record->business == \core_text::strtolower($user->email)) {
                $transaction->business = $record->business;
            }
            if ($record->receiver_email == \core_text::strtolower($user->email)) {
                $transaction->receiver_email = $record->receiver_email;
            }

            $transactions[] = $record;

            $lastcourseid = $record->courseid;
        }
        $records->close();

        // The data for the last activity won't have been written yet, so make sure to write it now!
        if (!empty($transactions)) {
            $coursecontext = \context_course::instance($record->courseid);
            writer::with_context($coursecontext)->export_data(
                    [$strtransactions],
                    (object) ['transactions' => $transactions]
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_course) {
            return;
        }

        $DB->delete_records('enrol_mercadopago', array('courseid' => $context->instanceid));
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        $contexts = $contextlist->get_contexts();
        $courseids = [];
        foreach ($contexts as $context) {
            if ($context instanceof \context_course) {
                $courseids[] = $context->instanceid;
            }
        }

        list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $select = "userid = :userid AND courseid $insql";
        $params = $inparams + ['userid' => $user->id];
        $DB->delete_records_select('enrol_mercadopago', $select, $params);

        // We do not want to delete the payment record when the user is just the receiver of payment.
        // In that case, we just delete the receiver's info from the transaction record.

        $select = "business = :business AND courseid $insql";
        $params = $inparams + ['business' => \core_text::strtolower($user->email)];
        $DB->set_field_select('enrol_mercadopago', 'business', '', $select, $params);

        $select = "receiver_email = :receiver_email AND courseid $insql";
        $params = $inparams + ['receiver_email' => \core_text::strtolower($user->email)];
        $DB->set_field_select('enrol_mercadopago', 'receiver_email', '', $select, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $params = ['courseid' => $context->instanceid] + $userparams;

        $select = "courseid = :courseid AND userid $usersql";
        $DB->delete_records_select('enrol_mercadopago', $select, $params);

        // We do not want to delete the payment record when the user is just the receiver of payment.
        // In that case, we just delete the receiver's info from the transaction record.

        $select = "courseid = :courseid AND business IN (SELECT LOWER(email) FROM {user} WHERE id $usersql)";
        $DB->set_field_select('enrol_mercadopago', 'business', '', $select, $params);

        $select = "courseid = :courseid AND receiver_email IN (SELECT LOWER(email) FROM {user} WHERE id $usersql)";
        $DB->set_field_select('enrol_mercadopago', 'receiver_email', '', $select, $params);
    }
}
