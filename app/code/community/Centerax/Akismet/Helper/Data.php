<?php

class Centerax_Akismet_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function slug($str)
	{
		$str = strtolower(trim(str_replace('comment_', '',$str)));
		$str = preg_replace('/[^a-z0-9-]/', '-', $str);
		$str = preg_replace('/-+/', " ", $str);
		return ucwords($str);
	}

}