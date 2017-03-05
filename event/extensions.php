<?php
/**
 *
 * @package phpBB Extension - tas2580 SEO URLs
 * @copyright (c) 2016 tas2580 (https://tas2580.net)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace tas2580\seourls\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class extensions implements EventSubscriberInterface
{
	/** @var \tas2580\seourls\event\base */
	protected $base;

	/**
	 * Constructor
	 *
	 * @param \tas2580\seourls\event\base		$base
	 * @access public
	 */
	public function __construct(\tas2580\seourls\event\base $base)
	{
		$this->base = $base;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'rmcgirr83.topfive.sql_pull_topics_data'	=> 'topfive_sql_pull_topics_data',
			'rmcgirr83.topfive.modify_tpl_ary'			=> 'topfive_modify_tpl_ary',
			'tas2580.sitemap.modify_before_output'		=> 'sitemap_modify_before_output',
			'vse.similartopics.modify_topicrow'			=> 'similartopics_modify_topicrow',
			'paybas.recenttopics.modify_tpl_ary'		=> 'recenttopics_modify_tpl_ary'
		);
	}

	public function recenttopics_modify_tpl_ary($event)
	{
		$tpl_ary = $event['tpl_ary'];
		$u_view_topic = $this->base->generate_topic_link($event['row']['forum_id'], $event['row']['forum_name'], $event['row']['topic_id'], $event['row']['topic_title']);
		$tpl_ary['U_VIEW_TOPIC'] = append_sid($u_view_topic);
		$tpl_ary['U_LAST_POST'] = append_sid($this->base->generate_lastpost_link($tpl_ary['REPLIES'], $u_view_topic) . '#p' . $event['row']['topic_last_post_id']);
		$tpl_ary['U_VIEW_FORUM'] = append_sid($this->base->generate_forum_link($event['row']['forum_id'], $event['row']['forum_name']));
		$tpl_ary['U_NEWEST_POST'] = $u_view_topic . '?view=unread#unread';

		$event['tpl_ary'] = $tpl_ary;
	}

	/**
	 * Rewrite URLs in tas2580 Sitemap Extension
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function sitemap_modify_before_output($event)
	{
		// Nothing to rewrite in the sitemap index
		if ($event['type'] == 'sitemapindex')
		{
			return;
		}

		$url_data =$event['url_data'] ;

		foreach ($url_data as $id => $data)
		{
			$row = $data['row'];
			if (isset($row['topic_id']))
			{
				$url_data[$id]['url'] = $this->base->generate_topic_link($row['forum_id'], $row['forum_name'], $row['topic_id'], $row['topic_title'],  $data['start'], true);
			}
			else if (isset($row['forum_id']))
			{
				$url_data[$id]['url'] = $this->base->generate_forum_link($row['forum_id'], $row['forum_name'], $data['start'], true);
			}
		}

		$event['url_data'] = $url_data;
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
		$this->forum_title = $event['row']['forum_name'];
		$this->forum_id = $event['row']['forum_id'];
		$this->topic_title = $event['row']['topic_title'];
		$this->topic_id = $event['row']['topic_id'];

		$topic_row = $event['topic_row'];
		$u_view_topic= $this->base->generate_topic_link($this->forum_id, $this->forum_title, $this->topic_id, $this->topic_title);
		$topic_row['U_VIEW_TOPIC'] = append_sid($u_view_topic);
		$topic_row['U_VIEW_FORUM'] = append_sid($this->base->generate_forum_link($this->forum_id, $this->forum_title));
		$topic_row['U_LAST_POST'] = append_sid($this->base->generate_lastpost_link($topic_row['TOPIC_REPLIES'], $u_view_topic) . '#p' . $event['row']['topic_last_post_id']);
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
		$event['sql_array'] = $sql_array;
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
		$replies = $this->base->get_count('topic_posts', $event['row'], $event['row']['forum_id']) - 1;
		$u_view_topic = $this->base->generate_topic_link($event['row']['forum_id'], $event['row']['forum_name'], $event['row']['topic_id'], $event['row']['topic_title']);
		$tpl_ary['U_TOPIC'] = append_sid($this->base->generate_lastpost_link($replies, $u_view_topic) . '#p' . $event['row']['topic_last_post_id']);
		$event['tpl_ary'] = $tpl_ary;
	}
}
