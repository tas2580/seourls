<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\seourls\controller;

class main
{
	/** @var \phpbb\request\request */
	protected $request;
	/** @var string phpbb_root_path */
	protected $phpbb_root_path;
	/** @var string php_ext */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\controller\helper		$helper				Controller helper object
	* @param \phpbb\request\request		$request				Request object
	* @param \phpbb\template\template	$template				Template object
	* @param \phpbb\user				$user				User object
	* @param \tas2580\wiki\wiki			$wiki					Wiki object
	* @param string					$phpbb_root_path
	* @param string					$php_ext
	*/
	public function __construct(\phpbb\request\request $request, $phpbb_root_path, $php_ext)
	{
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Display the wiki index page
	 *
	 * @return object
	 */
	public function forum()
	{
		include($this->phpbb_root_path . 'viewforum.' . $this->php_ext);


		return $this->helper->render('games_index.html', $this->user->lang('GAMES_INDEX'));
	}


}
