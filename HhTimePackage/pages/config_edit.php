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
form_security_validate( 'plugin_HhTimePackage_config_edit' );
auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$f_status = gpc_get_bool(HhTimePackagePlugin::CONFIGURATION_KEY_ENABLED,false);
$f_cron = gpc_get_bool(HhTimePackagePlugin::CONFIGURATION_KEY_CRON_NOTIFY_ENABLED,false);
$f_user_id =  gpc_get_int(HhTimePackagePlugin::CONFIGURATION_KEY_USER_ID_TO_NOTIFY,0);
$t_project_id = helper_get_current_project();

#Update configuration only if updated
if( plugin_config_get( HhTimePackagePlugin::CONFIGURATION_KEY_ENABLED,OFF,false ,null,$t_project_id) != $f_status) {
    plugin_config_set(
        HhTimePackagePlugin::CONFIGURATION_KEY_ENABLED,
        $f_status,
        NO_USER,
        $t_project_id
    );
}

if( plugin_config_get( HhTimePackagePlugin::CONFIGURATION_KEY_CRON_NOTIFY_ENABLED ,OFF,false ,null,$t_project_id) != $f_cron) {
    plugin_config_set(
        HhTimePackagePlugin::CONFIGURATION_KEY_CRON_NOTIFY_ENABLED,
        $f_cron,
        NO_USER,
        $t_project_id
    );
}

if( plugin_config_get( HhTimePackagePlugin::CONFIGURATION_KEY_USER_ID_TO_NOTIFY,0,false ,null,$t_project_id) != $f_user_id) {
    plugin_config_set(
        HhTimePackagePlugin::CONFIGURATION_KEY_USER_ID_TO_NOTIFY,
        $f_user_id,
        NO_USER,
        $t_project_id
    );
}

print_successful_redirect( plugin_page( 'config', true ) );