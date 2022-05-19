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


defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_mercadopago_settings', '', get_string('pluginname_desc', 'enrol_mercadopago')));

    $settings->add(new admin_setting_configtext('enrol_mercadopago/access_token', get_string('access_token', 'enrol_mercadopago'), get_string('access_token_desc', 'enrol_mercadopago'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('enrol_mercadopago/public_key', get_string('public_key', 'enrol_mercadopago'), get_string('public_key_desc', 'enrol_mercadopago'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('enrol_mercadopago/mailstudents', get_string('mailstudents', 'enrol_mercadopago'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_mercadopago/mailteachers', get_string('mailteachers', 'enrol_mercadopago'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_mercadopago/mailadmins', get_string('mailadmins', 'enrol_mercadopago'), '', 0));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_mercadopago/expiredaction', get_string('expiredaction', 'enrol_mercadopago'), get_string('expiredaction_help', 'enrol_mercadopago'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    $options = array();
    for ($i=0; $i<24; $i++) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('enrol_mercadopago/expirynotifyhour', get_string('expirynotifyhour', 'core_enrol'), '', 6, $options));


    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_mercadopago_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_mercadopago/status',
        get_string('status', 'enrol_mercadopago'), get_string('status_desc', 'enrol_mercadopago'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_mercadopago/cost', get_string('cost', 'enrol_mercadopago'), '', 0, PARAM_FLOAT, 4));

    $currencies = enrol_get_plugin('mercadopago')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_mercadopago/currency', get_string('currency', 'enrol_mercadopago'), '', 'USD', $currencies));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_mercadopago/roleid',
            get_string('defaultrole', 'enrol_mercadopago'), get_string('defaultrole_desc', 'enrol_mercadopago'), $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_mercadopago/enrolperiod',
        get_string('enrolperiod', 'enrol_mercadopago'), get_string('enrolperiod_desc', 'enrol_mercadopago'), 0));

    $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
    $settings->add(new admin_setting_configselect('enrol_mercadopago/expirynotify', get_string('expirynotify', 'core_enrol'), get_string('expirynotify_help', 'core_enrol'), 0, $options));

    $settings->add(new admin_setting_configduration('enrol_mercadopago/expirythreshold', get_string('expirythreshold', 'core_enrol'), get_string('expirythreshold_help', 'core_enrol'), 86400, 86400));

}
