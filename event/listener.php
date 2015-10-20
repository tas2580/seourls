<?php

/**
 *
 * @package phpBB Extension - tas2580 Social Media Buttons
 * @copyright (c) 2014 tas2580 (https://tas2580.net)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace tas2580\seourls\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\path_helper */
	protected $helper;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth				auth				Authentication object
	 * @param \phpbb\config\config			$config				Config Object
	 * @param \phpbb\template\template		$template			Template object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\user					$user				User Object
	 * @param \phpbb\path_helper				$path_helper		Controller helper object
	 * @param string                         $phpbb_root_path	phpbb_root_path
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\user $user, \phpbb\path_helper $path_helper, $phpbb_root_path)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->user = $user;
		$this->path_helper = $path_helper;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.append_sid'						=> 'append_sid',
			'core.display_forums_modify_sql'			=> 'display_forums_modify_sql',
			'core.display_forums_modify_template_vars'	=> 'display_forums_modify_template_vars',
			'core.pagination_generate_page_link'		=> 'pagination_generate_page_link',
		//	'core.modify_username_string'				=> 'modify_username_string',
			'core.viewforum_modify_topicrow'			=> 'viewforum_modify_topicrow',
			'core.search_modify_tpl_ary'				=> 'search_modify_tpl_ary',
			'core.viewforum_get_topic_data'			=> 'viewforum_get_topic_data',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_assign_template_vars_before',
			'core.viewtopic_modify_page_title'			=> 'viewtopic_modify_page_title',
			'vse.similartopics.modify_topicrow'			=> 'similartopics_modify_topicrow',
			'rmcgirr83.topfive.sql_pull_topics_data'		=> 'topfive_sql_pull_topics_data',
			'rmcgirr83.topfive.modify_tpl_ary'			=> 'topfive_modify_tpl_ary',
		);
	}

	/**
	 * Rewrite forum index from index.php to forum.html
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function append_sid($event)
	{
		if ($event['url'] == $this->path_helper->update_web_root_path($this->phpbb_root_path . 'index.php') && empty($event['params']))
		{
			//$event['append_sid_overwrite'] = $this->path_helper->update_web_root_path($this->phpbb_root_path . 'forum.html');
			$event['append_sid_overwrite'] = $this->path_helper->update_web_root_path($this->phpbb_root_path);
		}
	}

	/**
	 * Remove links to profiles for not logged in users
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function modify_username_string($event)
	{
		// if user is logged in do nothing
		if ($this->user->data['user_id'] != ANONYMOUS)
		{
			return;
		}

		// if user is not logged in output no links to profiles
		if ($event['username_colour'])
		{
			$event['username_string'] = '<span style="color: ' . $event['username_colour'] . ';" class="username-coloured">' . $event['username'] . '</span>';
		}
		else
		{
			$event['username_string'] = '<span class="username">' . $event['username'] . '</span>';
		}
	}

	/**
	 * Get informations for the last post
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function display_forums_modify_sql($event)
	{
		$sql_array = $event['sql_ary'];
		$sql_array['LEFT_JOIN'][] = array(
			'FROM' => array(TOPICS_TABLE => 't'),
			'ON' => "f.forum_last_post_id = t.topic_last_post_id"
		);
		$sql_array['SELECT'] .= ', t.topic_title, t.topic_id, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted';
		$event['sql_ary'] = $sql_array;
	}

	/**
	 * Rewrite links to forums and subforums in forum index
	 * also correct the path of the forum images if we are in a forum
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function display_forums_modify_template_vars($event)
	{
		// Rewrite URLs of sub forums
		$subforums_row = $event['subforums_row'];
		foreach ($subforums_row as $i => $subforum)
		{
			$id = str_replace('./viewforum.php?f=', '', $subforum['U_SUBFORUM']);
			$subforums_row[$i]['U_SUBFORUM'] = $this->generate_forum_link($id, $subforum['SUBFORUM_NAME']);
		}
		$event['subforums_row'] = $subforums_row;

		$forum_row = $event['forum_row'];

		// Update the image source in forums
		$img = $this->path_helper->update_web_root_path($forum_row['FORUM_IMAGE_SRC']);
		$forum_row['FORUM_IMAGE'] = preg_replace('#img src=\"(.*)\" alt#', 'img src="' . $img . '" alt', $forum_row['FORUM_IMAGE']);

		// Rewrite links to topics, posts and forums
		$replies = $this->get_count('topic_posts', $event['row'], $event['row']['forum_id']) - 1;
		$url = $this->generate_topic_link($event['row']['forum_id'], $event['row']['forum_name'], $event['row']['topic_id'], $event['row']['topic_title']);
		$forum_row['U_LAST_POST'] = $this->generate_seo_lastpost($replies, $url) . '#p' . $event['row']['forum_last_post_id'];
		$forum_row['U_VIEWFORUM'] = $this->generate_forum_link($forum_row['FORUM_ID'], $forum_row['FORUM_NAME']);
		$event['forum_row'] = $forum_row;
	}

	/**
	 * Rewrite pagination links
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function pagination_generate_page_link($event)
	{
		$start = (($event['on_page'] - 1) * $event['per_page']);
		if (!empty($this->topic_title))
		{
			$event['generate_page_link_override'] = $this->generate_topic_link($this->forum_id, $this->forum_title, $this->topic_id, $this->topic_title, $start);
		}
		else if (!empty($this->forum_title))
		{
			$event['generate_page_link_override'] = $this->generate_forum_link($this->forum_id, $this->forum_title, $start);
		}
	}

	/**
	 * Rewrite links in the search result
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function search_modify_tpl_ary($event)
	{
		$replies = $this->get_count('topic_posts', $event['row'], $event['row']['forum_id']) - 1;
		$url = $this->generate_topic_link($event['row']['forum_id'], $event['row']['forum_name'], $event['row']['topic_id'], $event['row']['topic_title']);

		$tpl_ary = $event['tpl_ary'];
		$tpl_ary['U_LAST_POST'] = $this->generate_seo_lastpost($replies, $url) . '#p' . $event['row']['topic_last_post_id'];
		$tpl_ary['U_VIEW_TOPIC'] = $this->generate_topic_link($event['row']['forum_id'], $event['row']['forum_name'], $event['row']['topic_id'], $event['row']['topic_title']);
		$tpl_ary['U_VIEW_FORUM'] = $this->generate_forum_link($event['row']['forum_id'], $event['row']['forum_name']);

		$event['tpl_ary'] = $tpl_ary;
	}

	/**
	 * Rewrite links to topics in forum view
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function viewforum_modify_topicrow($event)
	{
		$topic_row = $event['topic_row'];
		$this->forum_title = $topic_row['FORUM_NAME'];
		$this->forum_id = $topic_row['FORUM_ID'];
		$this->topic_title = $topic_row['TOPIC_TITLE'];
		$this->topic_id = $topic_row['TOPIC_ID'];

		$topic_row['U_VIEW_TOPIC'] = $this->generate_topic_link($this->forum_id, $this->forum_title, $this->topic_id, $this->topic_title);
		$topic_row['U_VIEW_FORUM'] = $this->generate_forum_link($this->forum_id, $this->forum_title);
		$topic_row['U_LAST_POST'] = $this->generate_seo_lastpost($event['topic_row']['REPLIES'], $topic_row['U_VIEW_TOPIC']) . '#p' . $event['row']['topic_last_post_id'];

		$event['topic_row'] = $topic_row;
	}

	/**
	 * Rewrite the canonical URL on viewforum.php
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function viewforum_get_topic_data($event)
	{
		$this->forum_title = $event['forum_data']['forum_name'];
		$this->forum_id = $event['forum_data']['forum_id'];
		$start = $this->request->variable('start', 0);
		$this->template->assign_vars(array(
			'U_VIEW_FORUM'	=> $this->generate_forum_link($event['forum_data']['forum_id'], $event['forum_data']['forum_name'], $start),
			'U_CANONICAL'		=> $this->generate_forum_link($event['forum_data']['forum_id'], $event['forum_data']['forum_name'], $start, true),
		));
	}

	/**
	 * Rewrite the topic URL for the headline of the topic page
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function viewtopic_assign_template_vars_before($event)
	{
		$this->forum_title = $event['topic_data']['forum_name'];
		$this->forum_id = $event['topic_data']['forum_id'];
		$this->topic_title = $event['topic_data']['topic_title'];
		$this->topic_id = $event['topic_data']['topic_id'];
		//$event['viewtopic_url'] = $this->generate_topic_link($event['topic_data']['forum_id'], $event['topic_data']['forum_name'], $event['topic_data']['topic_id'], $event['topic_data']['topic_title'], $event['start']);
	}

	/**
	 * Rewrite the canonical URL on viewtopic.php
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function viewtopic_modify_page_title($event)
	{
		$start = $this->request->variable('start', 0);
		$this->template->assign_vars(array(
			'U_CANONICAL'		=> $this->generate_topic_link($event['topic_data']['forum_id'], $event['topic_data']['forum_name'], $event['topic_data']['topic_id'], $event['topic_data']['topic_title'], $start, true),
		));
	}

	/**
	 * Rewrite URLs in Similar Topics Extension
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function similartopics_modify_topicrow($event)
	{
		$topic_row = $event['topic_row'];
		$topic_row['U_VIEW_TOPIC'] = $this->generate_topic_link($event['row']['forum_id'], $event['row']['forum_name'], $event['row']['topic_id'], $event['row']['topic_title']);
		$topic_row['U_VIEW_FORUM'] = $this->generate_forum_link($event['row']['forum_id'], $event['row']['forum_name']);
		$topic_row['U_LAST_POST'] = $this->generate_seo_lastpost($topic_row['TOPIC_REPLIES'], $topic_row['U_VIEW_TOPIC']) . '#p' . $event['row']['topic_last_post_id'];
		$event['topic_row'] = $topic_row;
	}

	/**
	 * Rewrite URLs in Top 5 Extension
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function topfive_sql_pull_topics_data($event)
	{
		$sql_array = $event['sql_array'];
		$sql_array['SELECT'] = array_merge($sql_array, array('SELECT' => 'f.forum_name'));
		$sql_array['LEFT_JOIN'] = array_merge($sql_array['LEFT_JOIN'], array('FROM' => array(FORUMS_TABLE => 'f'), 'ON' => 'f.forum_id = t.forum_id'));
		//	$event['sql_array'] = $sql_array;
	}

	/**
	 * Rewrite URLs in Top 5 Extension
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function topfive_modify_tpl_ary($event)
	{
		$tpl_ary = $event['tpl_ary'];
		$replies = $this->get_count('topic_posts', $event['row'], $event['row']['forum_id']) - 1;
		$u_topic = $this->generate_topic_link($event['row']['forum_id'], $event['row']['forum_name'], $event['row']['topic_id'], $event['row']['topic_title']);
		$tpl_ary['U_TOPIC'] = $this->generate_seo_lastpost($replies, $u_topic) . '#p' . $event['row']['topic_last_post_id'];
		$event['tpl_ary'] = $tpl_ary;
	}

	/**
	 * Generate the SEO link for a topic
	 *
	 * @param	int		$forum_id		The ID of the forum
	 * @param	string	$forum_name		The title of the forum
	 * @param	int		$topic_id		The ID if the topic
	 * @param	string	$topic_title	The title of the topic
	 * @param	int		$start			Optional start parameter
	 * @param	bool	$full			Return the full URL
	 * @return	string	The SEO URL
	 * @access private
	 */
	private function generate_topic_link($forum_id, $forum_name, $topic_id, $topic_title, $start = 0, $full = false)
	{
		if ($full)
		{
			return generate_board_url() . '/' . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . $this->title_to_url($topic_title) . '-t' . $topic_id . ($start ? '-s' . $start : '') . '.html';
		}
		return $this->path_helper->update_web_root_path($this->phpbb_root_path . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . $this->title_to_url($topic_title) . '-t' . $topic_id . ($start ? '-s' . $start : '') . '.html');
	}

	/**
	 * Generate the SEO link for a forum
	 *
	 * @param	int		$forum_id		The ID of the forum
	 * @param	string	$forum_name		The title of the forum
	 * @param	int		$start			Optional start parameter
	 * @param	bool	$full			Return the full URL
	 * @return	string	The SEO URL
	 * @access private
	 */
	private function generate_forum_link($forum_id, $forum_name, $start = 0, $full = false)
	{
		if ($full)
		{
			return generate_board_url() . '/' . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . ($start ? 'index-s' . $start . '.html' : '');
		}
		return $this->path_helper->update_web_root_path($this->phpbb_root_path . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . ($start ? 'index-s' . $start . '.html' : ''));
	}

	/**
	 * Replace letters to use title in URL
	 *
	 * @param	string	$title	The title to use in the URL
	 * @return	string	Title to use in URLs
	 */
	private function title_to_url($title)
	{
		$url = strtolower(censor_text(utf8_normalize_nfc(strip_tags($title))));

		// Let's replace
		$url_search = array(' ', 'í', 'ý', 'ß', 'ö', 'ô', 'ó', 'ò', 'ä', 'â', 'à', 'á', 'é', 'è', 'ü', 'ú', 'ù', 'ñ', 'ß', '²', '³', '@', '€', '$');
		$url_replace = array('-', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd');
		$url = str_replace($url_search, $url_replace, $url);
		$url_search = array('&amp;', '&quot;', '&', '"', "'", '¸', '`', '(', ')', '[', ']', '<', '>', '{', '}', '.', ':', ',', ';', '!', '?', '+', '*', '/', '=', 'µ', '#', '~', '"', '§', '%', '|', '°', '^', '„', '“');
		$url = str_replace($url_search, '-', $url);
		$url = str_replace(array('----', '---', '--'), '-', $url);

		$url = substr($url, 0, 50); // Max length for a title in URL
		return urlencode($url);
	}

	/**
	 *
	 * @global	type	$_SID
	 * @param	int		$replies	Replays in the topic
	 * @param	string	$url		URL oft the topic
	 * @return	string				The URL with start included
	 */
	private function generate_seo_lastpost($replies, $url)
	{
		global $_SID;

		$url = str_replace(array('?sid=' . $_SID, '.html'), '', $url);
		$per_page = ($this->config['posts_per_page'] <= 0) ? 1 : $this->config['posts_per_page'];
		if (($replies + 1) > $per_page)
		{
			$times = 1;
			for ($j = 0; $j < $replies + 1; $j += $per_page)
			{
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

	/**
	 * Get the topics post count or the forums post/topic count based on permissions
	 *
	 * @param $mode            string    One of topic_posts, forum_posts or forum_topics
	 * @param $data            array    Array with the topic/forum data to calculate from
	 * @param $forum_id        int        The forum id is used for permission checks
	 * @return int    Number of posts/topics the user can see in the topic/forum
	 */
	private function get_count($mode, $data, $forum_id)
	{
		if (!$this->auth->acl_get('m_approve', $forum_id))
		{
			return (int) $data[$mode . '_approved'];
		}

		return (int) $data[$mode . '_approved'] + (int) $data[$mode . '_unapproved'] + (int) $data[$mode . '_softdeleted'];
	}

}
