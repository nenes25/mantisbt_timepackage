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
class TimePackage
{

    protected $_project_id;

    /**
     * TimePackage constructor.
     * @param $project_id
     */
    public function __construct($project_id)
    {
        $this->_project_id = $project_id;
        $this->_init();
    }

    /**
     * Create a default counter for the projet in table if not exists
     */
    protected function _init()
    {
        $t_db_query = "SELECT project_id 
                       FROM " . plugin_table('timepackage') . "
                       WHERE project_id = " . db_param();
        if ($query = db_query($t_db_query, array($this->_project_id))) {
            if (!db_num_rows($query)) {
                $t_init_query = "INSERT INTO " . plugin_table('timepackage') . "
                                 SELECT " . $this->_project_id . ",SUM(`time`) 
                                 FROM " . plugin_table('timepackage_details') . "
                                 WHERE project_id=" . $this->_project_id;
                db_query($t_init_query);
            }
        }
    }

    /**
     * Get Current Time
     * @return mixed
     */
    public function get_time()
    {
        $t_db_query = "SELECT `time` 
                       FROM " . plugin_table('timepackage') . "
                       WHERE project_id = " . db_param();

        $query = db_query($t_db_query, array($this->_project_id));
        $time = db_fetch_array($query);
        return $time['time'];
    }

    /**
     * Récupération des détails
     */
    public function get_details()
    {
        $t_db_query = "SELECT d.*, n.date_submitted, t.summary 
                       FROM " . plugin_table('timepackage_details') . " d
                       LEFT JOIN " . db_get_table('bugnote') . " n ON d.bugnote_id = n.id
                       LEFT JOIN ".db_get_table('bug')." t ON d.bug_id = t.id
                       WHERE d.project_id=" . db_param().'
                       ORDER BY n.date_submitted';
        $t_query = db_query($t_db_query, array($this->_project_id));
        $results = array();
        while( $t_result = db_fetch_array($t_query)){
            $results[] = $t_result;
        }
        return $results;
    }

    /**
     * Add Time to TimePackage
     * @param string $time time in HH:MM format
     * @param null $comment
     */
    public function add_time($time, $comment = null)
    {
        #convert time hh:mm in minutes
        try {
            $t_time = helper_duration_to_minutes($time);
        } catch ( \Mantis\Exceptions\ClientException $e){
            $t_time = 0;
        }
        #Add details
        $t_db_query = "INSERT INTO " . plugin_table('timepackage_details') . " 
                       ( project_id,bug_id,bugnote_id,`time`,`comment` )
                       VALUES (" . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . ")";
        db_query($t_db_query,
            array($this->_project_id, 0, 0, $t_time, $comment)
        );
        #Update Global counter
        $t_db_global_query = "UPDATE " . plugin_table('timepackage') . "
                       SET `time` = (`time`+ $t_time)";
        db_query($t_db_global_query);
    }

    /**
     * Remove Time
     * @param $time
     * @param $bug_id
     * @param $bugnote_id
     * @param string $message
     */
    public function remove_time($time, $bug_id, $bugnote_id, $message = '')
    {
        #Add details
        $t_db_query = "INSERT INTO " . plugin_table('timepackage_details') . " 
                       ( project_id,bug_id,bugnote_id,`time`,`comment` )
                       VALUES (" . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . ")";
        db_query($t_db_query,
            array($this->_project_id, $bug_id, $bugnote_id, -$time, $message)
        );
        #Update Global counter
        $t_db_global_query = "UPDATE " . plugin_table('timepackage') . "
                       SET `time` = (`time`- $time)";
        db_query($t_db_global_query);
    }

    /**
     * Get TimePackage with negative time
     * Use in cron to send reminders
     * @return array
     */
    public static function get_negative_timepackages(){

        $results = array();
        $t_db_query = "SELECT * FROM ".plugin_table('timepackage')
                      ." WHERE time <= 0";
        $t_query = db_query($t_db_query);
        while ($row = db_fetch_array($t_query)) {
            $results[] = $row;
        }

        return $results;
    }


    /**
     * Send notification email
     *
     * @param $subject
     * @param $email
     * @param $message
     */
    public static function send_notification_email($subject,$email,$message)
    {
        #Format Message
        $messageHeader = '<html><head><title>' . $subject. '</title></head><body>';
        $messageFooter = '</body></html>';
        $fullMessage = $messageHeader.$message.$messageFooter;

        #Format Header
        $t_from_email = config_get('from_name').' <'.config_get('from_email').'>';
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: ' . $t_from_email . "\r\n";

        #Send email
        #@Todo Use mantis api to send email
        mail($email, $subject, $fullMessage, $headers);
    }
}