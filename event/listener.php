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
	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\request\request */
	private $request;
	
	/**
	* Constructor
	*
	* @param \phpbb\template\template $template
	* @param \phpbb\template\template $request
	* @access public
	*/
	public function __construct(\phpbb\template\template $template,  \phpbb\request\request $request)
	{
		$this->template = $template;
		$this->request = $request;
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
			'core.append_sid'								=> 'append_sid',
			'core.display_forums_modify_template_vars'		=> 'display_forums_modify_template_vars',
			'core.pagination_generate_page_link'			=> 'pagination_generate_page_link',
			'core.viewforum_modify_topicrow'				=> 'viewforum_modify_topicrow',
			'core.viewforum_get_topic_data'					=> 'viewforum_get_topic_data',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_assign_template_vars_before',
			'core.viewtopic_modify_page_title'				=> 'viewtopic_modify_page_title',
		);
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
			'U_CANONICAL'	=> generate_board_url() . '/' . $this->generate_forum_link($event['forum_data']['forum_id'], $event['forum_data']['forum_name'], $start),
		));
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
			'U_CANONICAL'	=> generate_board_url() . '/' . $this->generate_topic_link($event['topic_data']['forum_id'], $event['topic_data']['forum_name'], $event['topic_data']['topic_id'], $event['topic_data']['topic_title'], $start),
		));
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
		if(!empty($this->topic_title))
		{
			$event['generate_page_link_override'] = $this->generate_topic_link($this->forum_id, $this->forum_title, $this->topic_id, $this->topic_title, $start);
		}
		elseif(!empty($this->forum_title))
		{
			$event['generate_page_link_override'] = $this->generate_forum_link($this->forum_id, $this->forum_title, $start);
		}
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
		$event['viewtopic_url'] = $this->generate_topic_link($event['topic_data']['forum_id'], $event['topic_data']['forum_name'], $event['topic_data']['topic_id'], $event['topic_data']['topic_title'], $event['start']);
	}
	
	/**
	* Rewrite links to forums and subforums in forum index 
	*
	* @param	object	$event	The event object
	* @return	null
	* @access	public
	*/
	public function display_forums_modify_template_vars($event)
	{
		$subforums_row = $event['subforums_row'];
		foreach($subforums_row as $i => $subforum)
		{
			$id = str_replace('./viewforum.php?f=', '', $subforum['U_SUBFORUM']);
			$subforums_row[$i]['U_SUBFORUM'] = $this->generate_forum_link($id, $subforum['SUBFORUM_NAME']);
		}
		$forum_row = $event['forum_row'];
		$forum_row['U_VIEWFORUM'] = $this->generate_forum_link($forum_row['FORUM_ID'], $forum_row['FORUM_NAME']);
		$event['forum_row'] = $forum_row;
		$event['subforums_row'] = $subforums_row;
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


	
	public function append_sid($event)
	{
		$url = str_replace(array('../', './'), '', $event['url']);
		if($url == 'index.php')
		{
			$event['append_sid_overwrite'] = 'forum.html';
		}
	}
	
	
	/**
	 * Generate the SEO link for a topic
	 * 
	 * @param int		$forum_id		The ID of the forum
	 * @param string	$forum_name		The title of the forum
	 * @param int		$topic_id		The ID if the topic
	 * @param string	$topic_title	The title of the topic
	 * @param int		$start			Optional start parameter
	 * @return string	The SEO URL
	 * @access private
	 */
	private function generate_topic_link($forum_id, $forum_name, $topic_id, $topic_title, $start = 0)
	{
		return $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . $this->title_to_url($topic_title) . '-t' . $topic_id . ($start ? '-s' . $start : '') . '.html';
	}
	
	/**
	 * Generate the SEO link for a forum
	 * 
	 * @param int		$forum_id		The ID of the forum
	 * @param string	$forum_name		The title of the forum
	 * @param int		$start			Optional start parameter
	 * @return type
	 * @access private
	 */
	private function generate_forum_link($forum_id, $forum_name, $start = 0)
	{
		return $this->title_to_url($forum_name) . '-f' . $forum_id . '/' . ($start ? 'index-s' . $start . '.html': '');
	}
	
	/**
	 * Replace letters to use title in URL
	 * 
	 * @param	string	$title	The title to use in the URL	
	 * @return	string	Title to use in URLs
	 */
	private function title_to_url($title)
	{
		$url = strtolower(utf8_normalize_nfc(censor_text($title)));

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
		
	private function generate_seo_lastpost($replies, $url)
	{
		global $config, $_SID;

		$url = str_replace(array('?sid=' . $_SID, '.html'), '', $url);
		$per_page = ($config['posts_per_page'] <= 0) ? 1 : $config['posts_per_page'];
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

}
