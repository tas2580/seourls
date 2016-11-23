<?php

/**
 * @group functional
 */
class phpbb_functional_test extends \tas2580\seourls\tests\base\functional_test
{
	public function test_version_check()
	{
		// Log in to the ACP
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/extensions');
		// Load the Pages extension details
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=tas2580%2Fseourls&sid=' . $this->sid);
		// Assert extension is up to date
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
		$this->assertContains($this->lang('UP_TO_DATE', 'SEO URLs'), $crawler->text());
	}
}
