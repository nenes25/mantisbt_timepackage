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
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
layout_page_header(plugin_lang_get('title'));
layout_page_begin();
print_manage_menu();

$t_project_id = helper_get_current_project();
$t_timepackage_enabled = plugin_config_get(HhTimePackagePlugin::CONFIGURATION_KEY_ENABLED, OFF, false, null, $t_project_id);
$t_cron_reminder_enabled = plugin_config_get(HhTimePackagePlugin::CONFIGURATION_KEY_CRON_NOTIFY_ENABLED, OFF, false, null, $t_project_id);
$t_query = "SELECT id,username
        FROM {user}
        ORDER BY username ASC";
$t_users = db_query($t_query);
?>
    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="form-container">
            <form action="<?php echo plugin_page('config_edit') ?>" method="post">
                <?php echo form_security_field('plugin_HhTimePackage_config_edit') ?>
                <div class="widget-box widget-color-blue2">
                    <div class="widget-header widget-header-small">
                        <h4 class="widget-title lighter">
                            <?php echo plugin_lang_get('config_description') ?>
                        </h4>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed table-striped">
                                <tr>
                                    <th class="category">
                                        <?php echo plugin_lang_get('config_enable_for_project'); ?>
                                    </th>
                                    <td>
                                        <select name="<?php echo HhTimePackagePlugin::CONFIGURATION_KEY_ENABLED; ?>">
                                            <option value="0" <?php if ($t_timepackage_enabled == 0):?> selected="selected"<?php endif;?>><?php echo lang_get('no'); ?></option>
                                            <option value="1" <?php if ($t_timepackage_enabled == 1):?> selected="selected"<?php endif;?>><?php echo lang_get('yes'); ?></option>
                                        </select>
                                        <br>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="category">
                                        <?php echo plugin_lang_get('config_enable_cron_reminder'); ?>
                                    </th>
                                    <td>
                                        <select name="<?php echo HhTimePackagePlugin::CONFIGURATION_KEY_CRON_NOTIFY_ENABLED; ?>">
                                            <option value="0" <?php if ($t_cron_reminder_enabled == 0):?> selected="selected"<?php endif;?>><?php echo lang_get('no'); ?></option>
                                            <option value="1" <?php if ($t_cron_reminder_enabled == 1):?> selected="selected"<?php endif;?>><?php echo lang_get('yes'); ?></option>
                                        </select>
                                        <br>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="category">
                                        <?php echo plugin_lang_get('config_select_user_to_notify'); ?>
                                    </th>
                                    <td>
                                        <select name="<?php echo HhTimePackagePlugin::CONFIGURATION_KEY_USER_ID_TO_NOTIFY; ?>">
                                            <option value="0"><?php echo plugin_lang_get('config_select_user_to_notify'); ?></option>
                                            <?php while ($user = db_fetch_array($t_users)) : ?>
                                                <option value="<?php echo $user['id']; ?>" <?php if (plugin_config_get(HhTimePackagePlugin::CONFIGURATION_KEY_USER_ID_TO_NOTIFY )== $user['id']):
                                                    ?> selected="selected"<?php endif; ?> >
                                                    <?php echo $user['username']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <br>
                                        <span class="small"><?php echo plugin_lang_get('config_select_user_to_notify_description'); ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="widget-toolbox padding-8 clearfix">
                        <input type="submit" class="btn btn-primary btn-white btn-round"
                               value="<?php echo lang_get('change_configuration') ?>"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
layout_page_end();