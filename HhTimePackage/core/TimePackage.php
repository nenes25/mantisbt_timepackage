<?php

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
        $t_db_query = "SELECT * 
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
     * @param $time
     * @param null $comment
     */
    public function add_time($time, $comment = null)
    {

    }

    /**
     * Remove Time
     * @param $time
     * @param $bug_id
     * @param $bugnote_id
     * @param string $message
     * @return bool|IteratorAggregate
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
}