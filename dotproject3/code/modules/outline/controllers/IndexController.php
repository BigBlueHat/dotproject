<?php
class Outline_IndexController extends Zend_Controller_Action 
{
   public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('save', 'json')
                      ->initContext();
    }
	
	
	public function indexAction()
	{
		
	}
	
	public function saveAction()
	{
		$this->_helper->layout->disableLayout();
		$jsondata = $this->getRequest()->json;
		//Zend_Debug::dump($jsondata);
		$wbsdata = Zend_Json::decode($jsondata);
		//Zend_Debug::dump($wbsdata);
		/*
		$wbsdata = Array(
				'Task 1',
				'Task 2',
				Array('Task 3', 
					  'Task 4',
					  Array('Task 5')
						),
				'Task 6'
		);*/
		
		//Zend_Debug::dump($wbsdata);
		
		$wbs = new Wbs($wbsdata);
		//$this->_helper->json(Array('status'=>'success'));
		
	}
}
?>