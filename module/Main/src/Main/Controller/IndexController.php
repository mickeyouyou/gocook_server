<?php

/**
 * Main IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController {

    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'postDispatch'), -100);
    }

    public function postDispatch()
    {

    }
  
  public function indexAction() {
      $result = new JsonModel(array(
	    'some_parameter' => 'some value',
            'success'=>true,
        ));
 
        return $result;
  }

  public function showAction() {
    $actionName = $this->params('action');
    $langName = $this->params('lang');
    $controllerName = $this->params('controller');

    $result = new JsonModel(array(
        'success'=>true,
        'lang' => $langName,
        'action' => $actionName,
        'controller' => $controllerName,
    ));
    return $result;
  }

}
