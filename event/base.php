<?php
/**
 *
 * @package phpBB Extension - tas2580 SEO URLs
 * @copyright (c) 2016 tas2580 (https://tas2580.net)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace tas2580\seourls\event;

/**
 * Event listener
 */
class base
{

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	public $config;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth				auth					Authentication object
	 * @param \phpbb\config\config			$config					Config Object
	 * @param string						$phpbb_root_path		phpbb_root_path
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, $phpbb_root_path)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
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
	public function generate_topic_link($forum_id, $forum_name, $topic_id, $topic_title, $start = 0, $full = false)
	{
		if ($full)
		{
			return generate_board_url() . '/' . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . $this->title_to_url($topic_title) . '-t' . $topic_id . ($start ? '-s' . $start : '') . '.html';
		}
		return $this->phpbb_root_path . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . $this->title_to_url($topic_title) . '-t' . $topic_id . ($start ? '-s' . $start : '') . '.html';
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
	public function generate_forum_link($forum_id, $forum_name, $start = 0, $full = false)
	{
		if ($full)
		{
			return generate_board_url() . '/' . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . ($start ? 'index-s' . $start . '.html' : '');
		}
		return $this->phpbb_root_path . $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . ($start ? 'index-s' . $start . '.html' : '');
	}

	/**
	 *
	 * @global	type	$_SID
	 * @param	int		$replies	Replays in the topic
	 * @param	string	$url		URL oft the topic
	 * @return	string				The URL with start included
	 */
	public function generate_lastpost_link($replies, $url)
	{
		$url = str_replace('.html', '', $url);
		$per_page = ($this->config['posts_per_page'] <= 0) ? 1 : $this->config['posts_per_page'];
		$last_post_link = '';
		if (($replies + 1) > $per_page)
		{
			for ($j = 0; $j < $replies + 1; $j += $per_page)
			{
				$last_post_link = $url . '-s' . $j . '.html';
			}
		}
		else
		{
			$last_post_link = $url . '.html';
		}
		return $last_post_link;
	}

	/**
	 * Replace letters to use title in URL
	 *
	 * @param	string	$title	The title to use in the URL
	 * @return	string	Title to use in URLs
	 */
	public static function title_to_url($title)
	{
		$url = mb_strtolower(censor_text(utf8_normalize_nfc(html_entity_decode(strip_tags($title)))));

		// Let's replace
		$url_search = array(
		' ', 'í', 'ý', 'ß', 'ö', 'ô', 'ó', 'ò', 'ä', 'â', 'à', 'á', 'é', 'è', 'ü', 'ú', 'ù', 'ñ', 'ß', '²', '³', '@', '€', '$',
		'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ż', 'ź', // polish letters
		'ç', 'ê', 'ë', 'ê', 'î', 'ï', 'œ', 'û', // french letters
		'ř', 'š', 'ž', 'ť', 'č', 'ý', 'ů', 'ě', 'ď', 'ň' //czech letters 
		);
		$url_replace = array(
		'-', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd',
		'a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', // polish letters
		'c', 'e', 'e', 'e', 'i', 'i', 'oe', 'u', // french letters
		'r', 's', 'z', 't', 'c', 'y', 'u', 'e', 'd', 'n' //czech letters
		);
		$url = str_replace($url_search, $url_replace, $url);

		$url = preg_replace('/[^\w\d]/', '-', $url);
		$url = preg_replace('/[-]{2,}/', '-', $url);
		$url = trim($url, '-');

		$url = substr($url, 0, 50); // Max length for a title in URL
		return urlencode($url);
	}

	/**
	 * Get the topics post count or the forums post/topic count based on permissions
	 *
	 * @param $mode            string    One of topic_posts, forum_posts or forum_topics
	 * @param $data            array    Array with the topic/forum data to calculate from
	 * @param $forum_id        int        The forum id is used for permission checks
	 * @return int    Number of posts/topics the user can see in the topic/forum
	 */
	public function get_count($mode, $data, $forum_id)
	{
		if (!$this->auth->acl_get('m_approve', $forum_id))
		{
			return (int) $data[$mode . '_approved'];
		}

		return (int) $data[$mode . '_approved'] + (int) $data[$mode . '_unapproved'] + (int) $data[$mode . '_softdeleted'];
	}
}
