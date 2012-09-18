<?php

class Centerax_Akismet_Model_Mysql4_Akismet_Akismet extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('akismet/akismet_akismet', 'id');
	}
}