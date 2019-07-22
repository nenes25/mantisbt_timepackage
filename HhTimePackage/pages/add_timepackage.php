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
#Require ADMINISTRATOR role to manage this page
access_ensure_project_level(ADMINISTRATOR);
layout_page_header(plugin_lang_get('title'));
layout_page_begin();

$t_has_error = gpc_get_bool('error');
$t_current_project = helper_get_current_project();

if ( $t_current_project != 0):
?>
    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="form-container">
            <?php if ( $t_has_error): ?>
                <div class="alert alert-danger">
                    <?php echo plugin_lang_get('add_timepackage_form_error');?>
                </div>
            <?php endif; ?>
            <form action="<?php echo plugin_page('add_timepackage_post') ?>" method="post" class="form">
                <div class="widget-box widget-color-blue2">
                    <div class="widget-header widget-header-small">
                        <h4 class="widget-title lighter">
                            <?php echo plugin_lang_get('add_timepackage') ?>
                        </h4>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed table-striped">
                                <tr>
                                    <th class="category">
                                        <?php echo plugin_lang_get('timepackage_time'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="timepackage_time" class="input input-sm" placeholder="hh:mm" />
                                        <br>
                                        <span class="small"><?php echo plugin_lang_get('timepackage_time_description'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="category">
                                        <?php echo plugin_lang_get('timepackage_comment'); ?>
                                    </th>
                                    <td>
                                        <textarea type="text" name="timepackage_comment" rows="2" cols="70"></textarea>
                                        <br>
                                        <span class="small"><?php echo plugin_lang_get('timepackage_comment_description'); ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="widget-toolbox padding-8 clearfix">
                        <input type="submit" class="btn btn-primary btn-white btn-round"
                               value="<?php echo plugin_lang_get('add_timepackage') ?>"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: #Error if not project is selected ?>
 <div class="alert alert-danger align-center">
     <?php echo plugin_lang_get('add_timepackage_select_project'); ?>
 </div>
<?php endif;
layout_page_end();