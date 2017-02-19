<?PHP

if (!defined("IN_MYBB"))
{
    die("This file cannot be accessed directly.");
}
$plugins->add_hook("forumdisplay_start", "forumIndex_action");

function forumIndex_info()
{
    global $lang;
    $lang->load('forumIndex');
    return array(
        "name"          => $lang->forumIndexName,
        "description"   => $lang->forumIndexDesc,
        "website"       => "http://mybb-es.com",
        "author"        => "Himura fixed by LukasAMD",
        "authorsite"    => "http://mybb-es.com/member.php?action=profile&uid=501",
        "version"       => "1.0",
        "guid"          => "",
        "compatibility" => "*",
    );
}



// START - Standard MyBB installation functions
function forumIndex_install()
{
    require_once('forumIndex.settings.php');
    forumIndexInstaller::install();

    rebuild_settings();
}

function forumIndex_is_installed()
{
    global $mybb;

    return (isset($mybb->settings['forumIndexFids']));
}

function forumIndex_uninstall()
{
    require_once('forumIndex.settings.php');
    forumIndexInstaller::uninstall();

    rebuild_settings();
}

function forumIndex_activate()
{
    require_once('forumIndex.tpl.php');
    forumIndexActivator::activate();
}

function forumIndex_deactivate()
{
    require_once('forumIndex.tpl.php');
    forumIndexActivator::deactivate();
}




function forumIndex_action()
{
    global $db, $mybb, $header, $headerinclude, $footer, $forumIndex, $theme, $timenow, $forum, $lang, $templates;
    
    $lang->load('forumIndex');
    
    $fid = (int) $mybb->input['fid'];
    $supported_fids = explode(',', $mybb->settings['forumIndexFids']);
    
    // No in supported list? Escape!
    if (!in_array($fid, $supported_fids))
    {
        return;
    }

    // Get forum data
    $result = $db->simple_select('forums', 'name, threads', "fid = '{$fid}'");
    $forum_data = $db->fetch_array($result);
    
    // No threads? Escape!
    if (!$forum_data['threads'])
    {
        return;
    }
    
    $lang->forumIndexPageTitle = $lang->sprintf($lang->forumIndexPageTitle, $forum_data['name']);
    
    $sql = "SELECT u.*, u.username AS userusername, f.*
    		FROM ".TABLE_PREFIX."users u
    		LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
    		WHERE uid = '{$mybb->settings['forumIndexUser']}'";
    $result = $db->query($sql);
    $postbit = $db->fetch_array($result);
    
    $postbit['username'] = format_name($postbit['username'], $postbit['usergroup'], $postbit['displaygroup']);
    $postbit['profilelink'] = build_profile_link($postbit['username'], $postbit['uid']);
      
    // Action for index as thread on threadslist
    if (!$mybb->input['action'] == "threads_list")
    {   
        // Is mod?
        if (is_moderator($fid))
        {
            $modcheck = "<td class=\"trow1 forumdisplay_sticky\" style=\"white-space: nowrap; text-align: center\"><span class=\"smalltext\">-</span></td>";
        } 
     
        eval("\$forumIndex .= \"" . $templates->get('forumIndex_thread') . "\";");
            
        return;
    }
    
    // Action for index as page
    build_forum_breadcrumb($fid);
    add_breadcrumb($lang->forumIndexPageTitle);

    // Get data from cache    
    $post_content = '';
    if ($mybb->input['order'] == 'subject' && $mybb->input['ad'])
    {
        $result = $db->simple_select('forums_index', '*', "fid = '{$fid}'");
        $cache = $db->fetch_array($result);
        if (!empty($cache) && $cache['time'] > TIME_NOW)
        {
            $post_content = stripslashes($cache['content']); 
        }
    }

    // Generate data if needed
    if ($post_content == '')
    {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "threads t
                LEFT JOIN " . TABLE_PREFIX . "users u ON (t.username = u.username) 
                LEFT JOIN " . TABLE_PREFIX . "usergroups g ON (u.usergroup = g.gid) 
                LEFT JOIN " . TABLE_PREFIX . "threadprefixes p ON (t.prefix = p.pid)
                WHERE fid = '{$fid}' 
                AND t.visible='1'
                ORDER BY subject asc";
        $result = $db->query($sql);
                    
        $postbit['message'] = '<ul>';        
        while ($row = $db->fetch_array($result))
        {
            $username    = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
            $profilelink = build_profile_link($username, $row['uid']);
            $postbit['message'] .= "<li>{$row['displaystyle']} <a href=\"{$mybb->settings['bburl']}/" . get_thread_link($row['tid']) . "\" target=\"_blank\">{$row['subject']}</a> {$lang->forumIndexBy} {$profilelink}</li>";
        }
        
        $lang->forumIndexMessageFooter = $lang->sprintf($lang->forumIndexMessageFooter, $mybb->settings['bbname']);
        //$postbit['message'] .= $lang->forumIndexMessageFooter;
        $postbit['message'] .= '</ul>';
        $postbit['subject'] = $lang->forumIndexPageTitle;
        $forum['allowhtml'] = 1;
            
        // Force classic post
        $post_content = build_postbit($postbit, 1);
        
        // Save cache
        $sql = "REPLACE INTO " . TABLE_PREFIX . "forums_index  (fid, content, time)
                VALUES ('{$fid}', '" . $db->escape_string($post_content) . "' , '" . (TIME_NOW + 86400) ."')";
        $db->query($sql);
    }


    // Get page data and send to output
    eval("\$output .= \"" . $templates->get('forumIndex_page') . "\";");      
    output_page($output);
    
    exit();
}
