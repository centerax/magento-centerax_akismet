<?php

class Centerax_Akismet_Model_Api extends Mage_Core_Model_Abstract
{
	const READ_STATUS = 2;
	const UNREAD_STATUS = 1;

	protected $_akismetKey = null;

	protected function _getAkismetModel($setCA = true)
	{
		$model = Mage::getModel('akismet/akismet_akismet');

		if($setCA)
			$model->setCreatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));

		return $model;
	}

	public function getHttpHelper()
	{
		return Mage::helper('core/http');
	}

	public function getAkismetApiKey()
	{
		if(is_null($this->_akismetKey)){
			$this->_akismetKey = Mage::getStoreConfig('contacts/akismet/api_key');
		}
		return $this->_akismetKey;
	}

	protected function _getAkismet()
	{
		return new Zend_Service_Akismet($this->getAkismetApiKey(), Mage::getUrl('/'));
	}

	/**
	 * Filter spam contact messages
	 */
	public function filterContactsPostData()
	{
		$akismet = $this->_getAkismet();

		$isSpam = false;

		// Verify akismet api key
		if ($akismet->verifyKey($this->getAkismetApiKey())) {
			$post = Mage::getModel('core/url')->getRequest()->getPost();
			$data = array(
			    'user_ip'              => $this->getHttpHelper()->getRemoteAddr(),
			    'user_agent'           => $this->getHttpHelper()->getHttpUserAgent(),
			    'comment_type'         => 'contact',
			    'comment_author'       => $post['name'],
			    'comment_author_email' => $post['email'],
			    'comment_content'      => $post['comment']
			);

			// Check if the submit post is spam
			if ($akismet->isSpam($data)) {
			    $this->_getAkismetModel()->setExtra(serialize($data))->setType('contact')->save();
			    $isSpam = true;
			}
		}else{
			// Log exception for admin reference
			Mage::logException(new Exception('Invalid Akismet API Key'));
		}

		return $isSpam;
	}

	/**
	 * Filter spam contact messages
	 */
	public function filterProductReviewPostData($productId)
	{
		$akismet = $this->_getAkismet();

		$isSpam = false;

		// Verify akismet api key
		if ($akismet->verifyKey($this->getAkismetApiKey())) {
			$post = Mage::getModel('core/url')->getRequest()->getPost();
			$data = array(
			    'user_ip'              => $this->getHttpHelper()->getRemoteAddr(),
			    'user_agent'           => $this->getHttpHelper()->getHttpUserAgent(),
			    'comment_type'         => 'review',
			    'comment_author'       => $post['nickname'],
			    'comment_content'      => $post['detail']
			);
			// Check if the submit post is spam
			if ($akismet->isSpam($data)) {

				$data['product_id'] = $productId;
				$data['title'] = $post['title'];
				$data['store_id'] = Mage::app()->getStore()->getId();
				$data['customer_id'] = Mage::getSingleton('customer/session')->getCustomerId();

			    $this->_getAkismetModel()->setExtra(serialize($data))->setType('review')->save();
			    $isSpam = true;
			}
		}else{
			// Log exception for admin reference
			Mage::logException(new Exception('Invalid Akismet API Key'));
		}

		return $isSpam;
	}

	/**
	 * Submit FALSE POSITIVE
	 */
	public function submitHam()
	{
		//ToDo
	}

}
