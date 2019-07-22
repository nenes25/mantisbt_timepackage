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
access_ensure_project_level(ADMINISTRATOR);
if (gpc_isset('timepackage_time') != "") {
    $t_timepackage_time = gpc_get_string('timepackage_time');
    $t_timepackage_comment = gpc_get_string('timepackage_comment');
    $timePackage = new TimePackage(helper_get_current_project());
    $timePackage->add_time($t_timepackage_time, $t_timepackage_comment);
    print_successful_redirect(plugin_page('timepackage', true));
} else {
    print_header_redirect(plugin_page('add_timepackage', true) . '&error=true');
}
