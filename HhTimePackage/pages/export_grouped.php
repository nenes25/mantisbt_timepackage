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
require_api('csv_api.php');
plugin_require_api('core/TimePackage.php');

$time_package = new TimePackage(helper_get_current_project());

helper_begin_long_process();

$filters = array();
if (gpc_isset('from_date')) {
    $from_date = gpc_get_string('from_date');
    $to_date = gpc_get_string('to_date');
    $filter = '1&from_date=' . $from_date . '&to_date=' . $to_date;
    $filters['date'] = [
        'from' => $from_date,
        'to' => $to_date,
    ];
}

$t_data = $time_package->export_details_grouped($filters);
$t_export_file_name = 'export_timepackage_grouped.csv';
$t_new_line = csv_get_newline();
$t_separator = csv_get_separator();

csv_start($t_export_file_name);

#Csv Header
echo csv_escape_string(lang_get('issue_id')) . $t_separator;
echo csv_escape_string(lang_get('summary')) . $t_separator;
echo csv_escape_string(lang_get('minutes')) . $t_separator;
echo $t_new_line;

$t_global_time = 0;
foreach ($t_data as $t_row) {
    echo csv_escape_string($t_row['issue_id']) . $t_separator;
    echo csv_escape_string($t_row['summary']) . $t_separator;
    echo csv_escape_string(abs($t_row['minutes'])) . $t_separator;
    echo $t_new_line;
    $t_global_time += abs($t_row['minutes']);
}

#Empty Line Between rows details and global time
echo csv_escape_string("") . $t_separator;
echo csv_escape_string("") . $t_separator;
echo csv_escape_string("") . $t_separator;
echo $t_new_line;

#Global Time
echo csv_escape_string(lang_get('total')) . $t_separator;
echo csv_escape_string($t_global_time . ' m') . $t_separator;
echo csv_escape_string(round($t_global_time / 60, 2) . ' h') . $t_separator;
echo $t_new_line;
