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

$string['assignrole'] = 'Assign role';
$string['access_token'] = 'Mercado Pago Access Token';
$string['access_token_desc'] = 'The access token from your Mercado Pago account';
$string['public_key'] = 'Mercado Pago Public Key';
$string['public_key_desc'] = 'The public key from your Mercado Pago account';
$string['cost'] = 'Enrol cost';
$string['costerror'] = 'The enrolment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrolment.';
$string['currency'] = 'Currency';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during mercadopago enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['errdisabled'] = 'The mercadopago enrolment plugin is disabled and does not handle payment notifications.';
$string['erripninvalid'] = 'Instant payment notification has not been verified by mercadopago.';
$string['errconnect'] = 'Could not connect to {$a->url} to verify the instant payment notification: {$a->result}';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['mailadmins'] = 'Notify admin';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:mercadopago_enrolment'] = 'mercadopago enrolment messages';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['mercadopago:config'] = 'Configure mercadopago enrol instances';
$string['mercadopago:manage'] = 'Manage enrolled users';
$string['mercadopago:unenrol'] = 'Unenrol users from course';
$string['mercadopago:unenrolself'] = 'Unenrol self from the course';
$string['accepted'] = 'mercadopago payments accepted';
$string['pluginname'] = 'MercadoPago';
$string['pluginname_desc'] = 'The Mercadopago module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.  There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago'] = 'Information about the mercadopago transactions for mercadopago enrolments.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:business'] = 'Email address or mercadopago account ID of the payment recipient (that is, the merchant).';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:courseid'] = 'The ID of the course that is sold.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:instanceid'] = 'The ID of the enrolment instance in the course.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:item_name'] = 'The full name of the course that its enrolment has been sold.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:memo'] = 'A note that was entered by the buyer in mercadopago website payments note field.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:option_selection1_x'] = 'Full name of the buyer.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:parent_txn_id'] = 'In the case of a refund, reversal, or canceled reversal, this would be the transaction ID of the original transaction.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:payment_status'] = 'The status of the payment.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:payment_type'] = 'Holds whether the payment was funded with an eCheck (echeck), or was funded with mercadopago balance, credit card, or instant transfer (instant).';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:pending_reason'] = 'The reason why payment status is pending (if that is).';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:reason_code'] = 'The reason why payment status is Reversed, Refunded, Canceled_Reversal, or Denied (if the status is one of them).';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:receiver_email'] = 'Primary email address of the payment recipient (that is, the merchant).';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:receiver_id'] = 'Unique mercadopago account ID of the payment recipient (i.e., the merchant).';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:tax'] = 'Amount of tax charged on payment.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:timeupdated'] = 'The time of Moodle being notified by mercadopago about the payment.';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:txn_id'] = 'The merchant\'s original transaction identification number for the payment from the buyer, against which the case was registered';
$string['privacy:metadata:enrol_mercadopago:enrol_mercadopago:userid'] = 'The ID of the user who bought the course enrolment.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com'] = 'The mercadopago enrolment plugin transmits user data from Moodle to the mercadopago website.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:address'] = 'Address of the user who is buying the course.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:city'] = 'City of the user who is buying the course.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:country'] = 'Country of the user who is buying the course.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:custom'] = 'A hyphen-separated string that contains ID of the user (the buyer), ID of the course, ID of the enrolment instance.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:email'] = 'Email address of the user who is buying the course.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:first_name'] = 'First name of the user who is buying the course.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:last_name'] = 'Last name of the user who is buying the course.';
$string['privacy:metadata:enrol_mercadopago:mercadopago_com:os0'] = 'Full name of the buyer.';
$string['processexpirationstask'] = 'mercadopago enrolment send expiry notifications task';
$string['sendpaymentbutton'] = 'Send payment via mercadopago';
$string['status'] = 'Allow mercadopago enrolments';
$string['status_desc'] = 'Allow users to use mercadopago to enrol into a course by default.';
$string['transactions'] = 'mercadopago transactions';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
