<?php
/**
*
* @package phpBB Extension - tas2580 Social Media Buttons
* @copyright (c) 2014 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\seourls\controller;

class sitemap
{
	/* @var \phpbb\auth\auth */
	protected $auth;
	/* @var \phpbb\db\driver\driver */
	protected $db;
	/* @var \phpbb\controller\helper */
	protected $helper;
	/* @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth			$auth		Auth object
	* @param \phpbb\controller\helper	$helper
	* @param \phpbb\template\template	$template
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\template\template $template)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
	}

	public function sitemap($id)
	{
		$board_url = generate_board_url();
		$sql = 'SELECT forum_name, forum_last_post_time
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);

		$forum_url = $board_url . '/' . $this->title_to_url($row['forum_name']) . '-f' . $id . '/';

		$this->template->assign_block_vars('urlset', array(
			'URL'		=> $forum_url,
			'TIME'		=> gmdate('Y-m-d\TH:i:s+00:00', (int) $row['forum_last_post_time']),
		));
		$sql = 'SELECT topic_id, topic_title, topic_last_post_time, topic_status
			FROM ' . TOPICS_TABLE . '
			WHERE forum_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['topic_status'] <> ITEM_MOVED)
			{
				$this->template->assign_block_vars('urlset', array(
					'URL'		=> $forum_url .  $this->title_to_url($row['topic_title']) . '-t' . $row['topic_id'] . '.html',
					'TIME'		=> gmdate('Y-m-d\TH:i:s+00:00', (int) $row['topic_last_post_time']),
				));
			}
		}

		return $this->helper->render('sitemap.html');
	}



	public function index()
	{
		header('Content-Type: application/xml');

		$board_url = generate_board_url();
		$sql = 'SELECT forum_id, forum_name, forum_last_post_time
			FROM ' . FORUMS_TABLE . '
			WHERE forum_type = ' . (int) FORUM_POST . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($this->auth->acl_get('f_list', $row['forum_id']))
			{
				$this->template->assign_block_vars('forumlist', array(
					'URL'			=> $board_url . $this->helper->route('tas2580_seourls_sitemap', array('id' => $row['forum_id'])),
					'TIME'		=> gmdate('Y-m-d\TH:i:s+00:00', (int) $row['forum_last_post_time']),
				));
			}
		}

		return $this->helper->render('sitemap_index.html');
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
		$url_search =  array(' ', 'í', 'ý', 'ß', 'ö', 'ô', 'ó', 'ò', 'ä', 'â', 'à', 'á', 'é', 'è', 'ü', 'ú', 'ù', 'ñ', 'ß', '²', '³', '@', '€', '$');
		$url_replace = array('-', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd');
		$url = str_replace($url_search, $url_replace, $url);
		$url_search =  array('&amp;', '&quot;', '&', '"', "'", '¸', '`',  '(', ')', '[', ']', '<', '>', '{', '}', '.', ':', ',', ';', '!', '?', '+', '*', '/', '=', 'µ', '#', '~', '"', '§', '%', '|', '°', '^', '„', '“');
		$url = str_replace($url_search, '-', $url);
		$url = str_replace(array('----', '---', '--'), '-', $url);

		$url = substr($url, 0, 50); // Max length for a title in URL
		return urlencode($url);
	}
}
