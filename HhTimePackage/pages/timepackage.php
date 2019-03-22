<?php
auth_reauthenticate();
layout_page_header();
layout_page_begin(plugin_page('timepackage'));
plugin_require_api('core/TimePackage.php');
$timePackage = new TimePackage(helper_get_current_project());
$t_details = $timePackage->get_details();
$time = db_minutes_to_hhmm($timePackage->get_time());
if ( $time < 0 ) {
    $out_of_time = true;
    $alert_class = 'alert-danger';
} else {
    $out_of_time = false;
    $alert_class = 'alter-info';
}
?>

    <div id="timepackage-header" class="widget-box widget-color-blue2">
    <div class="widget-header">
        <h4><?php echo plugin_lang_get('timepackage_page_title'); ?></h4>
    </div>
    <div class="widget-body">
        <div class="alert <?php echo $alert_class; ?>" style="padding: 20px;">
        <p>
        <?php echo plugin_lang_get('timepackage_page_count'); ?>:&nbsp;
        <?php
        echo $timePackage->get_time() < 0 ?
            $title = '<strong style="color:#FF0000">'.$time.'H</strong>':
            $title = '<strong>'.$time.'H</strong>';
        ?>
        </p>
            <?php if ( $out_of_time) : ?>
            <p><strong><?php echo plugin_lang_get('timepackage_page_out_of_time'); ?></strong></p>
<?php endif; ?>
        </div>
    </div>
    </div>

    <div id="timepackage-details" class="widget-box widget-color-blue2" style="margin-top: 30px">
        <div class="widget-header">
            <h4><?php echo plugin_lang_get('timepackage_page_detail_title'); ?></h4>
        </div>
        <div class="widget-body">
            <?php
            if (sizeof($t_details)):?>
                <table id="details_list" class="table">
                    <thead>
                    <tr>
                    <th><?php echo plugin_lang_get('timepackage_page_date'); ?></th>
                    <th><?php echo plugin_lang_get('timepackage_page_time'); ?></th>
                    <th><?php echo plugin_lang_get('timepackage_page_description'); ?></th>
                    <th><?php echo plugin_lang_get('timepackage_page_show_bug'); ?></th>
                    </tr>
                    </thead>
                    <tbody
                    <?php foreach ($t_details as $detail): ?>
                        <tr>
                            <td><?php echo date('d/m/Y',$detail['date_submitted']); ?></td>
                            <td><?php echo db_minutes_to_hhmm(abs($detail['time'])); ?></td>
                            <td>
                                <?php if ( $detail['comment'] != '' ) :?>
                                    <?php echo $detail['comment']; ?>
                                <?php else:?>
                                    <?php if ( $detail['description'] != ''): ?>
                                        <?php echo 'BUG : '.$detail['description']; ?>
                                    <?php endif;?>
                                <?php endif;?>
                            </td>
                            <td>
                                <?php if ( $detail['bugnote_id'] != 0 ): ?>
                                <a href="<?php echo string_get_bugnote_view_url_with_fqdn($detail['bug_id'],$detail['bugnote_id']);?>" target="_blank">
                                    <?php echo plugin_lang_get('timepackage_page_show_bug'); ?>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else : ?>
            <p><?php plugin_lang_get('timepackage_page_no_details'); ?>
                <?php endif; ?>
        </div>
    </div>
<?php
layout_page_end();