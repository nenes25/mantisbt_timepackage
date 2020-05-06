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
     * Get Timepackage details
     * @param null $page_number
     * @param int $nb_per_page
     * @param array $filters
     * @return array
     */
    public function get_details($page_number=null,$nb_per_page=30,$filters=array())
    {
        $filtersConditions = '';
        if (count($filters)){
            if ( isset($filters['date'])){
                $filtersConditions = "AND n.date_submitted >= ".strtotime($filters['date']['from'])." AND n.date_submitted <=".strtotime($filters['date']['to'])."";
            }
        }

        $t_db_query = "SELECT d.*, n.date_submitted, t.summary 
                       FROM " . plugin_table('timepackage_details') . " d
                       INNER JOIN " . db_get_table('bugnote') . " n ON d.bugnote_id = n.id
                       LEFT JOIN ".db_get_table('bug')." t ON d.bug_id = t.id
                       WHERE d.project_id=" . db_param().'
                       '.$filtersConditions.' 
                       ORDER BY n.date_submitted DESC';

        if ( null !== $page_number ){
            $page_number = (int)$page_number;
            $t_db_query .= " LIMIT ".(($page_number-1)*$nb_per_page).",".$nb_per_page;
        }

        $t_query = db_query($t_db_query, array($this->_project_id));
        $results = array();
        while( $t_result = db_fetch_array($t_query)){
            $results[] = $t_result;
        }
        return $results;
    }

    /**
     * Get Timepackage details ( add time only )
     */
    public function get_add_details()
    {
        $t_db_query = "SELECT d.*
                       FROM " . plugin_table('timepackage_details') . " d
                       WHERE d.project_id=" . db_param().'
                       AND d.bug_id=0';
        $t_query = db_query($t_db_query, array($this->_project_id));
        $results = array();
        while( $t_result = db_fetch_array($t_query)){
            $results[] = $t_result;
        }
        return $results;
    }

    /**
     * Export Timepackage details
     * @param array $filters
     * @return array
     */
    public function export_details($filters=array()){

        $filtersConditions = '';
        if (isset($filters) && count($filters)){
            if ( isset($filters['date'])){
                $filtersConditions = "AND n.date_submitted >= ".strtotime($filters['date']['from'])." AND n.date_submitted <=".strtotime($filters['date']['to'])."";
            }
        }

        $t_db_query = "SELECT d.*, n.date_submitted,n.reporter_id, t.summary,nt.note,u.username
                       FROM " . plugin_table('timepackage_details') . " d
                       INNER JOIN " . db_get_table('bugnote') . " n ON d.bugnote_id = n.id
                       LEFT JOIN " . db_get_table('bugnote_text')." nt ON n.bugnote_text_id = nt.id
                       LEFT JOIN " . db_get_table('user')." u ON n.reporter_id = u.id
                       LEFT JOIN ".db_get_table('bug')." t ON d.bug_id = t.id
                       WHERE d.project_id=" . db_param().'
                       '.$filtersConditions.' 
                       ORDER BY n.date_submitted DESC';

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
                       SET `time` = (`time`+ $t_time)
                       WHERE project_id = " . db_param();
        db_query($t_db_global_query,array($this->_project_id));
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
                       SET `time` = (`time`- $time)
                       WHERE project_id = " . db_param();
        db_query($t_db_global_query,array($this->_project_id));
    }


    /**
     * Get All Timepackages remaining times
     * @return  array
     */
    public static function get_timepackages()
    {
        $results = array();
        $t_db_query = "SELECT t.*, p.name 
         FROM ".plugin_table('timepackage')." t
         LEFT JOIN ".db_get_table('project')." p ON t.project_id = p.id
         WHERE `time` IS NOT NULL";
        $t_query = db_query($t_db_query);
        while ($row = db_fetch_array($t_query)) {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Get sum of time of all timepackages remaining times combined
     * @return float
     */
    public static function get_timepackages_sum()
    {
        $t_db_query = "SELECT SUM(`time`) as total
        FROM " . plugin_table('timepackage');

        $t_query = db_query($t_db_query);
        return (int)db_result($t_query);
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

    /**
     * Get Bug statistics timepackage Time / Time tracked time
     * @param $bug_id
     * @return array
     */
    public static function get_bug_stats($bug_id)
    {
       $return = [];

        #Get sum of all time_tracking of the bug
        $t_db_query_time_tracking = "SELECT SUM(time_tracking) as total 
                                     FROM  ".db_get_table('bugnote')." 
                                     WHERE bug_id=".db_param();
        $t_query_time_tracking = db_query($t_db_query_time_tracking, array($bug_id));
        $t_time_tracking = (int)db_result($t_query_time_tracking);
        $return['time_tracking'] = $t_time_tracking;

        #Get sum of all time_tracking associated with timePackage
        $t_db_query_time_package = "SELECT SUM(ABS(`time`)) as total 
                                    FROM  ".plugin_table("timepackage_details")." 
                                    WHERE bug_id=".db_param();
        $t_query_time_package = db_query($t_db_query_time_package, array($bug_id));
        $t_time_package = (int)db_result($t_query_time_package);
        $return['time_package'] = $t_time_package;

       return $return;
    }

}