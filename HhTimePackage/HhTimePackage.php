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

    const CONFIGURATION_KEY_ENABLED = 'timepackage_enabled';

    public function register()
    {
        $this->name = plugin_lang_get('title');
        $this->description = plugin_lang_get('description');
        $this->page = 'config.php';
        $this->version = '0.0.1';
        $this->requires = array(
            'MantisCore' => '2.0.0',
        );

        $this->author = 'Hennes Hervé';
        $this->contact = 'contact@h-hennes.fr';
        $this->url = 'https://www.h-hennes.fr/blog/';
    }

    public function init()
    {
        plugin_require_api('core/TimePackage.php');
    }

    /**
     * @return array
     */
    public function config()
    {
        return array(
            self::CONFIGURATION_KEY_ENABLED => OFF,
        );
    }

    /**
     * plugin hooks
     * @return array
     */
    function hooks()
    {
        $t_hooks = array(
            'EVENT_MENU_MAIN' => 'main_menu',
            'EVENT_BUGNOTE_ADD' => 'bugnote_add',
        );
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
            $title = plugin_lang_get('menu_description').'<br />';
            $time = db_minutes_to_hhmm($timePackage->get_time());
            $timePackage->get_time() < 0 ?
                $title .= '<strong style="color:#FF0000">'.$time.'H</strong>':
                $title .= '<strong>'.$time.'H</strong>';

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
     * Executed after bugnote added
     */
    public function bugnote_add($eventName, $bug_id, $bugnote_id)
    {
        if ($this->_isActive()) {
            if ( gpc_isset( 'time_tracking' )) {
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
}