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

class forumIndexActivator
{

    private static $tpl = array();

    private static function getTpl()
    {
        global $db;


        self::$tpl[] = array(
            "tid" => NULL,
            "title" => 'forumIndex_thread',
            "template" => $db->escape_string('
                <td align="center" colspan="2" class="threadlist" width="2%"><img src="{$theme[\'imgdir\']}/threadlist.png" alt=""/></td>
                <td class="threadlist">
                	<a href="forumdisplay.php?fid={$fid}&action=indice&order=subject">{$lang->forumIndexPageTitle}</a>
                	<div class="author smalltext">{$userdata[\'profilelink\']}</div>
                </td>
                <td align="center" class="threadlist">-</td>
                <td align="center" class="threadlist">-</td>
                <td class="threadlist" align="center"></td>
                <td class="threadlist" style="white-space: nowrap; text-align: right"><span class="smalltext">{$lang->forumIndexToday} {$timenow}</span></td>
                {$modcheck}</tr>'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );

        self::$tpl[] = array(
            "tid" => NULL,
            "title" => 'forumIndex_page',
            "template" => $db->escape_string('
                <html>
                <head>
                <title>{$lang->forumIndexPageTitle}</title>
                {$headerinclude}
                </head>
                <body>
                {$header}
                <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="clear: both; border-bottom-width: 0;">
                <tr>
                <td class="thead" colspan="3"><strong>{$lang->forumIndexPageTitle}</strong></td>
                </tr>
                </table>
                {$post_content}
                <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="clear: both; border-bottom-width: 0;">
                <tr>
                <td class="tfoot" align="right" colspan="3"><form action="forumdisplay.php?fid=2&action=indice" method="get">
                <input name="fid" value="{$fid}" type="hidden">
                <input name="action" value="indice" type="hidden">
                					<select name="order">
                					<option value="subject"{$sortBy[\'subject\']}>{$lang->forumIndexSortByTitle}</option>					
                					<option value="tid"{$sortBy[\'tid\']}>{$lang->forumIndexSortByDate}</option>
                					<option value="lastpost"{$sortBy[\'lastpost\']}>{$lang->forumIndexSortByLastpost}</option>
                				</select>
                				<select name="ad">
                					<option value="asc"{$sortAd[\'asc\']}>{$lang->forumIndexSortASC}</option>
                					<option value="desc"{$sortAd[\'desc\']}>{$lang->forumIndexSortDESC}</option>
                				</select>
                <input class="button" value="{$lang->forumIndexSubmit}" type="submit">
                			</form></strong></td>
                </tr>
                </table>
                {$footer}
                </body>
                </html>'),
            "sid" => "-1",
            "version" => "1.0",
            "dateline" => TIME_NOW,
        );
    }

    public static function activate()
    {
        global $db;
        self::deactivate();

        for ($i = 0; $i < sizeof(self::$tpl); $i++)
        {
            $db->insert_query('templates', self::$tpl[$i]);
        }

        find_replace_templatesets('forumdisplay_threadlist', '#' . preg_quote('{$threads}') . '#', '{$forumIndex}{$threads}');
    }

    public static function deactivate()
    {
        global $db;
        self::getTpl();

        for ($i = 0; $i < sizeof(self::$tpl); $i++)
        {
            $db->delete_query('templates', "title = '" . self::$tpl[$i]['title'] . "'");
        }

        require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
        find_replace_templatesets('forumdisplay_threadlist', '#' . preg_quote('{$forumIndex}') . '#', '');
    }

}

?>