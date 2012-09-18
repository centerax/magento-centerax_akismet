<?php

class Centerax_Akismet_Adminhtml_AkismetController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
       	->_setActiveMenu('system');
        return $this;
    }

	public function indexAction(){
		$this->_initAction()
        ->_addContent($this->getLayout()->createBlock('akismet/adminhtml_akismet_list'))
        ->renderLayout();
	}

    public function listGridAction(){
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('akismet/adminhtml_akismet_grid')->toHtml()
        );
    }
	
	public function massDeleteAction() {
		
        $ids = $this->getRequest()->getParam('spam');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select item(s)'));
        }
        else {
            try {
                foreach ($ids as $id) {
                    $obj = Mage::getModel('akismet/akismet_akismet')->load($id);
                    $obj->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($ids))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
		
	}
	
	public function deleteAction() {
		$id = $this->getRequest()->getParam('id');
		if(!$id){
			$this->_getSession()->addError($this->__('ID not provided'));
			$this->_redirect('*/*/');
			return;
		}
		$model = Mage::getModel('akismet/akismet_akismet');
		$obj = $model->load($id);

		if($obj->getId()){
			try{
				$obj->delete();
			}catch(Exception $e){
				$this->_getSession()->addError($this->__('Could not delete PDF on file system'));
				$this->_redirect('*/*/');
				return;
			}
		}

		$this->_getSession()->addSuccess($this->__('Record successfuly deleted'));
		$this->_redirect('*/*/');
		return;
	}

	public function markreadAction(){
		$id = $this->getRequest()->getParam('id');

		$model = Mage::getModel('akismet/akismet_akismet');
        if($id){
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This record no longer exists'));
                $this->_redirect('*/*/');
                return;
            }else{
            	try{
            			$model->setStatus(Centerax_Akismet_Model_Api::READ_STATUS)
            				->save();
	                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Success'));
	                $this->_redirect('*/*/');
	                return;
            	}catch(Exception $e){
	                Mage::getSingleton('adminhtml/session')->addError($this->__('An error ocurred: %s', $e->getMessage()));
	                $this->_redirect('*/*/');
	                return;
            	}
            }
        }
	}

	public function convertToReviewAction(){
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('akismet/akismet_akismet');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This record no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $spamD = unserialize($model->getExtra());

		try{
			$id = Mage::getModel('review/review')
				->setTitle($spamD['title'])
				->setNickname($spamD['comment_author'])
				->setDetail($spamD['comment_content'])
				->setEntityId(Mage_Review_Model_Review::ENTITY_PRODUCT)
                ->setEntityPkValue($spamD['product_id'])
                ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                ->setCustomerId($spamD['customer_id'])
                ->setStoreId($spamD['store_id'])
                ->setStores(array($spamD['store_id']))
                ->save();

				if($id->getReviewId()){

					Mage::getModel('akismet/akismet_akismet')->setId($model->getId())->setStatus(Centerax_Akismet_Model_Api::READ_STATUS)->save();
					$viewReviewURL = $this->getUrl('adminhtml/catalog_product_review/edit', array('id' => $id->getReviewId()));

                	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('A new review was created <a href="%s">Edit</a>', $viewReviewURL));
                	$this->_redirect('*/*/');
                	return;
				}
		}catch(Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
                $this->_redirect('*/*/');
                return;
		}
	}

    public function viewAction(){
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('akismet/akismet_akismet');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This record no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }
        // Restore previously entered form data from session
        $data = Mage::getSingleton('adminhtml/session')->getAkismetSpamData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('akismet_spam', $model);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('akismet/adminhtml_akismet_view'));

        $this->renderLayout();
    }

}
