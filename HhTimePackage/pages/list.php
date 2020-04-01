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

plugin_require_api('core/TimePackage.php');
$timepackages = TimePackage::get_timepackages();
?>
<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="widget-box widget-color-blue2">
        <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter">
                <i class="ace-icon fa fa-file-o"></i>
                <?php echo plugin_lang_get( 'list_title' ); ?>
            </h4>
        </div>
        <div class="widget-body">
            <div class="widget-main no-padding">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-condensed">
                        <thead>
                        <tr class="row-category">
                            <th><?php echo plugin_lang_get("project") ?></th>
                            <th><?php echo plugin_lang_get("remaining_time") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($timepackages as $timepackage): ?>
                            <tr>
                                <td><?php echo $timepackage['name'];?></td>
                                <td><?php echo  db_minutes_to_hhmm($timepackage['time']);?></td>
                            </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
layout_page_end();
