<?php

namespace tas2580\seourls\tests\base;

abstract class database_test extends \phpbb_database_test_case
{
	protected static function setup_extensions()
	{
		return array('tas2580/seourls');
	}
	protected $db;
	public function setUp()
	{
		parent::setUp();
		global $db;
		$db = $this->db = $this->new_dbal();
	}
}
