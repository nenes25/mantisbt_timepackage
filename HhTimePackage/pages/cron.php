<?php
# MantisBT - A PHP based bugtracking system
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
plugin_require_api('core/TimePackage.php');
plugin_push_current('HhTimePackage');
require_api('user_api.php');

#The cron page send reminders
$reminders = TimePackage::get_negative_timepackages();
if (count($reminders)) {
    foreach ($reminders as $reminder) {

        $t_user_id = plugin_config_get('timpackage_user_id_to_notify',
            OFF, false, null, $reminder['project_id']
        );

        if ($t_user_id != OFF) {
            #Manage email translation : Warning if preference language is set to "auto" email will be send in english
            lang_push( user_pref_get_language( $t_user_id ) );
            $t_subject = plugin_lang_get('email_subject');
            $t_message = sprintf(plugin_lang_get('email_message'), db_minutes_to_hhmm($reminder['time']));
            $t_email = user_get_email($t_user_id);
            TimePackage::send_notification_email($t_subject, $t_email, $t_message);
        }
    }
}