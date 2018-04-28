<?php
/**
*
* @package phpBB3
* @version $Id: functions_seophpbb.php,v 1.0.3 2008/08/25 01:48:12
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
 * @ignore
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

function check_forum_url($forum_data, $start1 = false)
{
	global $start, $config, $forum_id, $topics_count;

	if(isset($_GET['sk']) || isset($_GET['sd']) || isset($_GET['st']))
	{
		return;
	}

	// on the right page?
	if(($start && ($topics_count < $config['topics_per_page'])) || ($start > $topics_count) || (!is_int($start/$config['topics_per_page'])))
	{
		$script_path = ( $config['script_path'] != '/' ) ? $config['script_path'] . '/' : '/';
		$needed_forum_url = $script_path . title_to_url($forum_data['forum_name']) .'-f' . $forum_id . '/';
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: http://" . $config['server_name'] . $needed_forum_url);
		die;
	}

	// right SEO URL?
	$req_addon = '';
	$req_addon = ( $start ) ? 'index-s' . $start . '.html' : '';
	$req_addon .= ( isset($_GET['sid']) ) ? '?sid=' . $_GET['sid'] : '';
	$script_path = ( $config['script_path'] != '/' ) ? $config['script_path'] . '/' : '/';
	$needed_forum_url = $script_path . title_to_url($forum_data['forum_name']) .'-f' . $forum_id . '/' . $req_addon;

	if ($_SERVER['REQUEST_URI'] != $needed_forum_url && !isset($_GET['explain']))
	{

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: http://" . $config['server_name'] . $needed_forum_url);
		die;
	}
}

function check_topic_url($topic_data, $start1 = false)
{
	global $hilit_words, $total_posts, $config, $start;
	global $phpbb_root_path, $view, $voted_id;

	if($hilit_words || $view || $voted_id || isset($_GET['sk']) || isset($_GET['sd']) || isset($_GET['st']) || isset($_GET['bookmark']) || isset($_GET['watch']) || isset($_GET['unwatch']) || isset($_GET['explain']))
	{
		return;
	}

	$forum = (strstr($_SERVER['REQUEST_URI'], '/global/')) ? 'global' : $forum = title_to_url($topic_data['forum_name']) .'-f'. $topic_data['forum_id'];

	if ((isset($_GET['p']) || $view) && !$hilit_words)
	{
		$p_id = empty($_GET['p']) ? $topic_data['topic_last_post_id'] : request_var('p', 0);
		$post_url = "{$phpbb_root_path}". $forum . '/' . title_to_url($topic_data['topic_title']) . '-t' . $topic_data['topic_id'];
		$post_url = append_sid(generate_seo_lastpost($topic_data['topic_replies'], $post_url )). '#p' . $p_id;

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

		if($view != 'viewpoll')
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: $post_url");
			die;
		}
	}

	// on the right page?
	if(($start && ($total_posts < $config['posts_per_page'])) || ($start > $total_posts) || (!is_int($start/$config['posts_per_page'])) )
	{
		$script_path = ( $config['script_path'] != '/' ) ? $config['script_path'] . '/' : '/';
		$req_addon_sid = ( isset($_GET['sid']) ) ? '?sid=' . $_GET['sid'] : '';
		$needed_forum_url = $script_path . $forum . '/' . title_to_url($topic_data['topic_title']) . '-t' . $topic_data['topic_id'] . '.html' .$req_addon_sid;
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $needed_forum_url");
		die;
	}

	// right SEO URL?

	$req_addon = '';
	$req_addon = ( $start ) ? '-s' . $start : '';
	$req_addon_sid = ( isset($_GET['sid']) ) ? '?sid=' . $_GET['sid'] : '';
	$script_path = ( $config['script_path'] != '/' ) ? $config['script_path'] . '/' : '/';
	$needed_forum_url = $script_path . $forum . '/' . title_to_url($topic_data['topic_title']) . '-t' . $topic_data['topic_id'] . $req_addon . '.html' .$req_addon_sid;

	if ( $_SERVER['REQUEST_URI'] != $needed_forum_url )
	{

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $needed_forum_url");
		die;
	}

}

function generate_seourl_topic($topic_id, $topic_title = false, $forum_id = false, $forum_name = false, $use_start = false)
{
	global $forum_title, $phpbb_root_path, $db, $start;
	if($forum_id == 0)
	{
		$url_addon = ($start && $use_start) ? $url_addon = '-s' . $start : '';
		$url = "{$phpbb_root_path}" . 'global/' . title_to_url(censor_text($topic_title)) . '-t' . $topic_id . $url_addon. '.html';

	}
	else
	{
		$forum_id = (int) $forum_id;
		if(isset($forum_title[$forum_id]))
		{
			$forum_name = $forum_title[$forum_id];
		}
		if (!$forum_id || (empty($forum_name)) )
		{
			$sql = 'SELECT f.forum_name, f.forum_id, t.topic_title
				FROM ' . FORUMS_TABLE . ' as f, ' . TOPICS_TABLE . ' as t
				WHERE t.forum_id = f.forum_id
				AND t.topic_id = ' . (int) $topic_id . '
				AND t.topic_type != ' . POST_GLOBAL;
			$result = $db->sql_query($sql);
			$forum_data = $db->sql_fetchrow($result);
			$forum_id = $forum_data['forum_id'];
			$forum_name = $forum_data['forum_name'];
			$topic_title = empty($forum_data['topic_title']) ? $topic_title : $forum_data['topic_title'];
			$forum_title[$forum_id] = $forum_name;
		}

		elseif (empty($topic_title))
		{
			$sql = 'SELECT topic_title
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id . '
					AND topic_type != ' . POST_GLOBAL;
			$result = $db->sql_query($sql);
			$topic_data = $db->sql_fetchrow($result);
			$topic_title = $topic_data['topic_title'];
		}

		$url_addon = ($start && $use_start) ? $url_addon = '-s' . $start : '';
		$url = "{$phpbb_root_path}" . title_to_url(censor_text($forum_name)) . '-f' . $forum_id . '/' . title_to_url(censor_text($topic_title)) . '-t' . $topic_id . $url_addon. '.html';
	}
	return append_sid($url);
}

function generate_seourl_forum($forum_id, $forum_name = false, $use_start = false)
{
	global $phpbb_root_path, $db, $start;

	if (!$forum_name)
	{
		$sql = 'SELECT forum_name
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $db->sql_query($sql);
		$forum_data = $db->sql_fetchrow($result);
		$forum_name = $forum_data['forum_name'];
	}

	$url_addon = ($start && $use_start) ? $url_addon = 'index-s' . $start . '.html' : '';
	$url = "{$phpbb_root_path}" . title_to_url(censor_text($forum_name)) . '-f' . $forum_id . '/' . $url_addon;
	return append_sid($url);
}

function title_to_url($url)
{
	$url = strtolower(utf8_normalize_nfc($url));

	// Let's replace
	$url_search =  array(' ', 'í', 'ý', 'ß', 'ö', 'ô', 'ó', 'ò', 'ä', 'â', 'à', 'á', 'é', 'è', 'ü', 'ú', 'ù', 'ñ', 'ß', '²', '³', '@', '€', '$');
	$url_replace = array('-', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd');
	$url = str_replace($url_search, $url_replace, $url);
	$url_search =  array('&amp;', '&quot;', '&', '"', "'", '¸', '`',  '(', ')', '[', ']', '<', '>', '{', '}', '.', ':', ',', ';', '!', '?', '+', '*', '/', '=', 'µ', '#', '~', '"', '§', '%', '|', '°', '^', '„', '“');
	$url = str_replace($url_search, '-', $url);
	$url = str_replace(array('----', '---', '--'), '-', $url);

	$url = substr($url, 0, 50); // Max length for a title in URL
	return urlencode($url);
}

function generate_seo_lastpost($replies, $url)
{
	global $config, $user, $_SID;

	$url = str_replace('?sid=' . $_SID, '', $url);
	$url = str_replace('.html', '', $url);
	$per_page = ($config['posts_per_page'] <= 0) ? 1 : $config['posts_per_page'];
	if (($replies + 1) > $per_page)
	{
		$times = 1;
		for ($j = 0; $j < $replies + 1; $j += $per_page)
		{
			$last_post_link = '';
			$last_post_link = append_sid($url . '-s' . $j . '.html');
			$times++;
		}
	}
	else
	{
		$last_post_link = $url . '.html';
	}
	return $last_post_link;
}

function topic_generate_seo_pagination($replies, $url)
{
	global $config, $user, $_SID;

	$url = str_replace('?sid=' . $_SID, '', $url);
	$url = str_replace('.html', '', $url);
	// Make sure $per_page is a valid value
	$per_page = ($config['posts_per_page'] <= 0) ? 1 : $config['posts_per_page'];

	if (($replies + 1) > $per_page)
	{
		$total_pages = ceil(($replies + 1) / $per_page);
		$pagination = '';

		$times = 1;
		for ($j = 0; $j < $replies + 1; $j += $per_page)
		{
			if ($j == 0)
			{
				$pagination .= '<a href="' . append_sid($url . '.html') . '">' . $times . '</a>';
			}
			else
			{
				$pagination .= '<a href="' . append_sid($url . '-s' . $j . '.html') . '">' . $times . '</a>';
			}
			if ($times == 1 && $total_pages > 5)
			{
				$pagination .= ' ... ';

				// Display the last three pages
				$times = $total_pages - 3;
				$j += ($total_pages - 4) * $per_page;
			}
			else if ($times < $total_pages)
			{
				$pagination .= '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';
			}
			$times++;
		}
	}
	else
	{
		$pagination = '';
	}
	return $pagination;
}


function generate_seo_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = false, $tpl_prefix = '')
{
	global $template, $user;

	// Make sure $per_page is a valid value
	$per_page = ($per_page <= 0) ? 1 : $per_page;

	$seperator = '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';
	$total_pages = ceil($num_items / $per_page);

	if ($total_pages == 1 || !$num_items)
	{
		return false;
	}
	$on_page = floor($start_item / $per_page) + 1;
	$url_delim = (strpos($base_url, '?') === false) ? '?' : '&amp;';

	$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . append_sid($base_url . '.html') . '">1</a>';

	if ($total_pages > 5)
	{
		$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + 4), 6);

		$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

		for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . append_sid($base_url . "-s" . (($i - 1) * $per_page) . '.html') . '">' . $i . '</a>';
			if ($i < $end_cnt - 1)
			{
				$page_string .= $seperator;
			}
		}

		$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
	}
	else
	{
		$page_string .= $seperator;

		for ($i = 2; $i < $total_pages; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . append_sid($base_url . "-s" . (($i - 1) * $per_page) . '.html') . '">' . $i . '</a>';
			if ($i < $total_pages)
			{
				$page_string .= $seperator;
			}
		}
	}

	$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . append_sid($base_url . "-s" . (($total_pages - 1) * $per_page) . '.html') . '">' . $total_pages . '</a>';

	if ($add_prevnext_text)
	{
		if ($on_page != 1)
		{
			$page_string = '<a href="' . append_sid($base_url . "-s" . (($on_page - 2) * $per_page) . '.html') . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
		}

		if ($on_page != $total_pages)
		{
			$page_string .= '&nbsp;&nbsp;<a href="' . append_sid($base_url . "-s" . ($on_page * $per_page) . '.html') .'">' . $user->lang['NEXT'] . '</a>';
		}
	}

	$template->assign_vars(array(
		'A_' . $tpl_prefix . 'BASE_URL'   => addslashes($base_url),
		$tpl_prefix . 'PER_PAGE' => $per_page,

		$tpl_prefix . 'PREVIOUS_PAGE' => ($on_page == 1) ? '' : append_sid($base_url . "-s" . (($on_page - 2) * $per_page) . '.html'),
		$tpl_prefix . 'NEXT_PAGE' => ($on_page == $total_pages) ? '' : append_sid($base_url . "-s" . ($on_page * $per_page) . '.html'),
		$tpl_prefix . 'TOTAL_PAGES' => $total_pages)
	);
	$page_string = str_replace('index.html', '', $page_string);
	return $page_string;
}

?>
