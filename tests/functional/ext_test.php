<?php
/**
*
* @package phpBB Extension - tas2580 Social Media Buttons
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\socialbuttons\tests\functional;
/**
* @group functional
*/
class ext_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('tas2580/seourls');
	}
	public function test_page()
	{
		$crawler = self::request('GET', 'index.php');
	}
}
