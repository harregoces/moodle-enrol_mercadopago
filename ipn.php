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


// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
//define('NO_DEBUG_DISPLAY', true);

// @codingStandardsIgnoreLine This script does not require login.
require("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . "/enrol/mercadopago/vendor/autoload.php");

set_exception_handler(\enrol_mercadopago\util::get_exception_handler());

// Make sure we are enabled in the first place.
if (!enrol_is_enabled('mercadopago')) {
	http_response_code(503);
	throw new moodle_exception('errdisabled', 'enrol_mercadopago');
}

/// Keep out casual intruders
if (!empty($_POST) or empty($_GET)) {
	http_response_code(400);
	throw new moodle_exception('invalidrequest', 'core_error');
}

$data = new stdClass();
$data->userid = required_param('userid', PARAM_INT);
$data->courseid = required_param('courseid', PARAM_INT);
$data->instanceid = required_param('instanceid', PARAM_INT);
$data->collection_id = required_param('collection_id', PARAM_INT);
$data->timeupdated = time();

$user = $DB->get_record("user", array("id" => $data->userid), "*", MUST_EXIST);
$course = $DB->get_record("course", array("id" => $data->courseid), "*", MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

$PAGE->set_context($context);

$plugin_instance = $DB->get_record("enrol", array("id" => $data->instanceid, "enrol" => "mercadopago", "status" => 0), "*", MUST_EXIST);
$plugin = enrol_get_plugin('mercadopago');

MercadoPago\SDK::setAccessToken($plugin->get_config('access_token'));
$payment = MercadoPago\Payment::find_by_id($data->collection_id);

$external_reference = $payment->external_reference;
$data->external_reference = $external_reference;
$external_reference = explode("-",$external_reference);

$courseid = $external_reference[0];
$userid = $external_reference[1];
$instanceid = $external_reference[2];

if($data->userid != $userid) die("Data do not match");
if($data->courseid != $courseid) die("Data do not match");
if($data->instanceid != $instanceid) die("Data do not match");


$data->payment_id = $payment->id;
$data->external_reference = $payment->external_reference;
$data->payment_status = $payment->status;
$data->status_detail = $payment->status_detail;
$data->date_created = strtotime($payment->date_created);
$data->date_last_update = strtotime($payment->date_last_updated);
$data->date_approved = strtotime($payment->date_approved);
$data->money_release_date = strtotime($payment->money_release_date);
$data->currency = $payment->currency_id;
$data->transaction_amount = $payment->transaction_amount;
$data->transaction_amount_refunded = $payment->transaction_amount_refunded;
$data->payment_method_id = $payment->payment_method_id;
$data->payment_type_id = $payment->payment_type_id;
$data->merchant_order_id = $payment->order->id;
$data->payment_status = $payment->status;

if ($payment->transaction_details->total_paid_amount >= $payment->transaction_amount) {

	if ($data->payment_status != "approved") {
		$plugin->unenrol_user($plugin_instance, $data->userid);
		\enrol_mercadopago\util::message_mercadopago_error_to_admin("Status not completed or pending. User unenrolled from course",
			$data);
		die;
	}

	// If currency is incorrectly set then someone maybe trying to cheat the system
	if ($data->currency != $plugin_instance->currency) {
		\enrol_mercadopago\util::message_mercadopago_error_to_admin(
			"Currency does not match course settings, received: " . $data->currency,
			$data);
		die;
	}

	if ($payment->status == "opened") {
		$eventdata = new \core\message\message();
		$eventdata->courseid = empty($data->courseid) ? SITEID : $data->courseid;
		$eventdata->modulename = 'moodle';
		$eventdata->component = 'enrol_mercadopago';
		$eventdata->name = 'mercadopago_enrolment';
		$eventdata->userfrom = get_admin();
		$eventdata->userto = $user;
		$eventdata->subject = "Moodle: MercadoPago payment";
		$eventdata->fullmessage = "Your MercadoPago payment is pending.";
		$eventdata->fullmessageformat = FORMAT_PLAIN;
		$eventdata->fullmessagehtml = '';
		$eventdata->smallmessage = '';
		message_send($eventdata);

		\enrol_mercadopago\util::message_mercadopago_error_to_admin("Payment pending", $data);
		die;
	}

	// Make sure this transaction doesn't exist already.
	if ($existing = $DB->get_record("enrol_mercadopago", array("collection_id" => $data->collection_id), "*", IGNORE_MULTIPLE)) {
		\enrol_mercadopago\util::message_mercadopago_error_to_admin("Transaction $data->collection_id is being repeated!", $data);
		die;
	}

	if (!$user = $DB->get_record('user', array('id' => $data->userid))) {   // Check that user exists
		\enrol_mercadopago\util::message_mercadopago_error_to_admin("User $data->userid doesn't exist", $data);
		die;
	}

	if (!$course = $DB->get_record('course', array('id' => $data->courseid))) { // Check that course exists
		\enrol_mercadopago\util::message_mercadopago_error_to_admin("Course $data->courseid doesn't exist", $data);
		die;
	}

	$coursecontext = context_course::instance($course->id, IGNORE_MISSING);

	// Check that amount paid is the correct amount
	if ((float)$plugin_instance->cost <= 0) {
		$cost = (float)$plugin->get_config('cost');
	} else {
		$cost = (float)$plugin_instance->cost;
	}

	// Use the same rounding of floats as on the enrol form.
	$cost = format_float($cost, 2, false);

	if ($data->transaction_amount < $cost) {
		\enrol_mercadopago\util::message_mercadopago_error_to_admin("Amount paid is not enough ($data->transaction_amount < $cost))", $data);
		die;
	}

	// ALL CLEAR !
	$DB->insert_record("enrol_mercadopago", $data);

	if ($plugin_instance->enrolperiod) {
		$timestart = time();
		$timeend = $timestart + $plugin_instance->enrolperiod;
	} else {
		$timestart = 0;
		$timeend = 0;
	}

	// Enrol user
	$plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);

	// Pass $view=true to filter hidden caps if the user cannot see them
	if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
		'', '', '', '', false, true)) {
		$users = sort_by_roleassignment_authority($users, $context);
		$teacher = array_shift($users);
	} else {
		$teacher = false;
	}

	$mailstudents = $plugin->get_config('mailstudents');
	$mailteachers = $plugin->get_config('mailteachers');
	$mailadmins = $plugin->get_config('mailadmins');
	$shortname = format_string($course->shortname, true, array('context' => $context));


	if (!empty($mailstudents)) {
		$a = new stdClass();
		$a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
		$a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

		$eventdata = new \core\message\message();
		$eventdata->courseid = $course->id;
		$eventdata->modulename = 'moodle';
		$eventdata->component = 'enrol_mercadopago';
		$eventdata->name = 'mercadopago_enrolment';
		$eventdata->userfrom = empty($teacher) ? core_user::get_noreply_user() : $teacher;
		$eventdata->userto = $user;
		$eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
		$eventdata->fullmessage = get_string('welcometocoursetext', '', $a);
		$eventdata->fullmessageformat = FORMAT_PLAIN;
		$eventdata->fullmessagehtml = '';
		$eventdata->smallmessage = '';
		message_send($eventdata);

	}

	if (!empty($mailteachers) && !empty($teacher)) {
		$a->course = format_string($course->fullname, true, array('context' => $coursecontext));
		$a->user = fullname($user);

		$eventdata = new \core\message\message();
		$eventdata->courseid = $course->id;
		$eventdata->modulename = 'moodle';
		$eventdata->component = 'enrol_mercadopago';
		$eventdata->name = 'mercadopago_enrolment';
		$eventdata->userfrom = $user;
		$eventdata->userto = $teacher;
		$eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
		$eventdata->fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
		$eventdata->fullmessageformat = FORMAT_PLAIN;
		$eventdata->fullmessagehtml = '';
		$eventdata->smallmessage = '';
		message_send($eventdata);
	}

	if (!empty($mailadmins)) {
		$a->course = format_string($course->fullname, true, array('context' => $coursecontext));
		$a->user = fullname($user);
		$admins = get_admins();
		foreach ($admins as $admin) {
			$eventdata = new \core\message\message();
			$eventdata->courseid = $course->id;
			$eventdata->modulename = 'moodle';
			$eventdata->component = 'enrol_mercadopago';
			$eventdata->name = 'mercadopago_enrolment';
			$eventdata->userfrom = $user;
			$eventdata->userto = $admin;
			$eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
			$eventdata->fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
			$eventdata->fullmessageformat = FORMAT_PLAIN;
			$eventdata->fullmessagehtml = '';
			$eventdata->smallmessage = '';
			message_send($eventdata);
		}
	}

	//Clear cache and redirect
	redirect(new moodle_url('/course/view.php', array('id'=>$course->id)));

}