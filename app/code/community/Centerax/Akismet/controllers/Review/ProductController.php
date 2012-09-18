<?php

require_once 'Mage/Review/controllers/ProductController.php';

/**
 * Contacts index controller
 *
 * @category   Centerax
 * @package    Centerax_Akismet
 * @author      Centerax <cx@pablobenitez.com>
 */
class Centerax_Akismet_Review_ProductController extends Mage_Review_ProductController {

    public function postAction() {
        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $session    = Mage::getSingleton('core/session');
            /* @var $session Mage_Core_Model_Session */
            
            $review     = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {

	        	/**
	        	 * Check for SPAM
	        	 */
	        	 	 $isSpam = false;
		        	 try{
						$isSpam = Mage::getModel('akismet/api')->filterProductReviewPostData($product->getId());
						if($isSpam === true){
							$session->addSuccess(Mage::helper('contacts')->__('Your review has been accepted for moderation.'));
	                		$this->_redirectReferer();
	                		return;
						}
		        	 }catch(Exception $e){
		        	 	Mage::logException($e);
		        	 }
	        	/**
	        	 * Check for SPAM
	        	 */

                try {
                    $review->setEntityId(Mage_Review_Model_Review::ENTITY_PRODUCT)
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                    	   ->setRatingId($ratingId)
                    	   ->setReviewId($review->getId())
                    	   ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    	   ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $session->addSuccess($this->__('Your review has been accepted for moderation'));
                }
                catch (Exception $e) {
                    $session->setFormData($data);
                    $session->addError($this->__('Unable to post review. Please, try again later.'));
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to post review. Please, try again later.'));
                }
            }
        }

        if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }

}
