<?php
/**
 * This file is part of Thread Index plugin for MyBB.
 * Copyright (C) Lukasz Tkacz <lukasamd@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class forumIndexInstaller
{

    public static function install()
    {
        global $db, $lang, $mybb;
        self::uninstall();

        $result = $db->simple_select('settinggroups', 'MAX(disporder) AS max_disporder');
        $max_disporder = $db->fetch_field($result, 'max_disporder');
        $disporter = 1;
        
        $settings_group = array(
            'gid' => 'NULL',
            'name' => 'forumIndex',
            'title' => $lang->forumIndexName,
            'description' => $lang->forumIndexSettingGroupDesc,
            'disporder' => $max_disporder + 1,
            'isdefault' => '0'
        );
        $db->insert_query('settinggroups', $settings_group);
        $gid = (int) $db->insert_id();
        
        $setting = array(
            'sid' => 'NULL',
            'name' => 'forumIndexFids',
            'title' => $lang->forumIndexFids,
            'description' => $lang->forumIndexFidsDesc,
            'optionscode' => 'text',
            'value' => "",
            'disporder' => $disporter++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);
    
        $setting = array(
            'sid' => 'NULL',
            'name' => 'forumIndexUser',
            'title' => $lang->forumIndexUser,
            'description' => $lang->forumIndexUserDesc,
            'optionscode' => 'text',
            'value' => "",
            'disporder' => $disporter++,
            'gid' => $gid
        );
        $db->insert_query('settings', $setting);

        $sql = "CREATE TABLE " . TABLE_PREFIX . "forums_index (
              fid int unsigned NOT NULL default '0',
              content text NOT NULL default '',
              time int unsigned NOT NULL default '0',
              PRIMARY KEY (fid)
            ) ENGINE=MyISAM;";
        $db->query($sql);
    }

    public static function uninstall()
    {
        global $db;

        $db->delete_query('settinggroups', "name = 'forumIndex'");
        $db->delete_query('settings', "name = 'forumIndexFids'");
        $db->delete_query('settings', "name = 'forumIndexUser'");
        
        $db->drop_table('forums_index');
    }
}

?>
