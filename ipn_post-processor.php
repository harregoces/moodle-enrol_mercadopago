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
$back_url = required_param('back_url', PARAM_ALPHAEXT);
$payment_id = required_param('payment_id', PARAM_INT);
if (empty($_POST) or !empty($_GET)) {
	http_response_code(400);
	throw new moodle_exception('invalidrequest', 'core_error');
}


//Clear cache and redirect
redirect($back_url);
