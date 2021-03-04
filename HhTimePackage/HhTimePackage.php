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
class HhTimePackagePlugin extends MantisPlugin
{

    /** @var string Configuration Key to define if plugin is enabled */
    const CONFIGURATION_KEY_ENABLED = 'timepackage_enabled';

    /** @var string Configuration key to define if plugin cron reminder is enabled */
    const CONFIGURATION_KEY_CRON_NOTIFY_ENABLED = 'timpackage_enable_cron_reminder';

    /** @var string Configuration key to select user to notify */
    const CONFIGURATION_KEY_USER_ID_TO_NOTIFY = 'timpackage_user_id_to_notify';

    public function register()
    {
        $this->name = plugin_lang_get('title');
        $this->description = plugin_lang_get('description');
        $this->page = 'config.php';
        $this->version = '0.2.3';
        $this->requires = array(
            'MantisCore' => '2.0.0',
        );
        #Cron Manager
        $this->uses = array(
            'HhCronManager' => '0.1.0'
        );

        $this->author = 'Hennes Hervé';
        $this->contact = 'contact@h-hennes.fr';
        $this->url = 'https://www.h-hennes.fr/blog/';
    }

    /**
     * Plugin init
     */
    public function init()
    {
        plugin_require_api('core/TimePackage.php');
    }

    /**
     * Plugin config
     * @return array
     */
    public function config()
    {
        return array(
            self::CONFIGURATION_KEY_ENABLED => OFF,
            self::CONFIGURATION_KEY_USER_ID_TO_NOTIFY => 0
        );
    }

    /**
     * plugin hooks
     * @return array
     */
    function hooks()
    {
        global $g_event_cache;

        $t_hooks = array(
            'EVENT_MENU_MAIN' => 'main_menu',
            'EVENT_MENU_MANAGE' => 'menu_manage',
            'EVENT_BUGNOTE_ADD' => 'bugnote_add',
            'EVENT_BUGNOTE_ADD_FORM' => 'bugnote_add_form',
            'EVENT_VIEW_BUGNOTES_END' => 'bug_after_notes',
        );

        #Custom Hook from plugin HhCronManager
        if (array_key_exists('EVENT_PLUGIN_HHCRONMANAGER_COLLECT_CRON', $g_event_cache)) {
            $t_hooks['EVENT_PLUGIN_HHCRONMANAGER_COLLECT_CRON'] = 'collect_cron';
        }

        return $t_hooks;
    }

    /**
     * plugin schema
     * @return array
     */
    function schema()
    {
        return array(
            array('CreateTableSQL',
                array(plugin_table('timepackage'), "
	 	 		project_id		I		NOTNULL UNSIGNED PRIMARY,
	 	 		time I NOT NULL DEFAULT '0'",
                    array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8'))
            ),
            array('CreateTableSQL',
                array(plugin_table('timepackage_details'), "
	 	 		detail_id		I		NOTNULL UNSIGNED PRIMARY AUTOINCREMENT,
	 	 		project_id I NOTNULL UNSIGNED,
	 	 		bug_id I NOTNULL UNSIGNED,
	 	 		bugnote_id I NOTNULL UNSIGNED, 
	 	 		time I NOT NULL DEFAULT '0',
	 	 		comment C(250) NOTNULL DEFAULT ''
	 	 		",
                    array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8'))
            ),
        );
    }

    /**
     * Add new link to main menu
     * @return array
     */
    public function main_menu()
    {
        if ($this->_isActive()) {

            $timePackage = new TimePackage(helper_get_current_project());
            $title = plugin_lang_get('menu_description') . '<br />';
            $time = db_minutes_to_hhmm($timePackage->get_time());
            $timePackage->get_time() < 0 ?
                $title .= '<strong style="color:#FF0000">' . $time . 'H</strong>' :
                $title .= '<strong>' . $time . 'H</strong>';

            return array(
                array(
                    'title' => $title,
                    'access_level' => REPORTER,
                    'url' => plugin_page('timepackage'),
                    'icon' => 'fa-info'
                ),
            );
        }
    }

    /**
     * Add new link to manage menu
     * @return string
     */
    public function menu_manage(){
        $page = plugin_page( "list" );
        $label = plugin_lang_get( "list_title" );

        return "<a href=\"{$page}\">{$label}</a>";
    }

    /**
     * Executed after bugnote added
     * @param $eventName
     * @param $bug_id
     * @param $bugnote_id
     * @throws \Mantis\Exceptions\ClientException
     */
    public function bugnote_add($eventName, $bug_id, $bugnote_id)
    {
        if ($this->_isActive()) {

            if (gpc_isset('time_tracking') && !gpc_isset('timepackage_dont_track')) {
                $t_time_tracking = gpc_get_string('time_tracking');
                if ($t_time_tracking) {
                    $timePackage = new TimePackage(helper_get_current_project());
                    $p_time_tracking = helper_duration_to_minutes($t_time_tracking);
                    $timePackage->remove_time($p_time_tracking, $bug_id, $bugnote_id);
                }
            }
        }
    }

    /**
     * Add field in bugnote form to not track time
     * @param string $eventName
     * @param int $bug_id
     */
    public function bugnote_add_form($eventName, $bug_id)
    {
        if ($this->_isActive() && $this->_getCurrentUserRole() >= DEVELOPER) {
            echo '
            <tr>
                <th class="category">' . plugin_lang_get('timepackage') . '</th>
                <td>
                    <label for="bugnote_add_timepackage_dont_track">
                    <input type="checkbox" id="timepackage_dont_track" name="timepackage_dont_track">
                    <span class="lbl padding-6">' . plugin_lang_get('do_not_track_time') . '</span>
                    </label>
                </td>
            </tr>
            ';
        }
    }

    /**
     * Display statitics invoiced / real time under bugnotes
     * @param $eventName
     * @param $bug_id
     */
    public function bug_after_notes($eventName,$bug_id)
    {
        if ( $this->_isActive() ) {
            $stats_infos = TimePackage::get_bug_stats($bug_id);
            if ( $stats_infos['time_tracking'] > 0){

                //Calc the dif between time_tracking and time_package
                $t_diff_percent = (( $stats_infos['time_tracking'] - $stats_infos['time_package'] ) / $stats_infos['time_tracking'])* 100;
                echo '<tr>
                        <td class="category">
                        <i class="fa fa-clock-o grey"></i>
                        '.plugin_lang_get('timepackage_bug_stats').'
                        </td>
                        <td>
                        <div class="pull-left">
                        <i class="ace-icon fa fa-clock-o bigger-110 red"></i> 
                        '.plugin_lang_get('timepackage_reported_time').'  <span class="time-tracked bold"> '.db_minutes_to_hhmm($stats_infos['time_package']).'</span>
                        </div>';

                if ( $this->_getCurrentUserRole() >= DEVELOPER) {
                    echo '
                        <div class="pull-right">
                            <span class="bold">' . ceil(100 - $t_diff_percent) . '%</span>  ' . plugin_lang_get('timepackage_of_total_time') . '
                        </div>';
                }
                echo '</td>
                      </tr>';
            }
        }
    }

    /**
     * Executed by module HhCronManager when collecting plugins cron tasks
     * @param string $eventName
     * @return array
     */
    public function collect_cron($eventName)
    {
        $pluginName = str_replace('Plugin','',get_class($this));
        return [
            [
                'plugin' => $pluginName,
                'code' => $pluginName . '_cron_reminder',#unique code
                'description' => plugin_lang_get('cron_reminder_description'),
                'frequency' => '0 12 * * * *',#cron expression
                'url' => 'cron',#plugin page name
            ],
        ];
    }

    /**
     * Check if module can be active
     */
    private function _isActive()
    {
        $t_current_project = helper_get_current_project();
        $t_project_id = gpc_get_int('project_id', $t_current_project);
        $t_timetracking_enabled = config_get('time_tracking_enabled', null, null, $t_project_id);
        $t_timepackage_enabled = plugin_config_get(self::CONFIGURATION_KEY_ENABLED, OFF, false, null, $t_project_id);

        if ($t_timetracking_enabled == ON && $t_timepackage_enabled == ON) {
            return true;
        }

        return false;
    }

    /**
     * Get current user role in the current project
     * @return int|string
     */
    private function _getCurrentUserRole()
    {
        $t_user_id = auth_get_current_user_id();
        $t_current_project = helper_get_current_project();
        $t_project_id = gpc_get_int('project_id', $t_current_project);
        return user_get_access_level($t_user_id,$t_project_id);
    }
}