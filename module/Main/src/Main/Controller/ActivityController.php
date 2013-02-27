<?php
/**
 * Main ActivityController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ActivityController extends AbstractActionController
{
    public function indexAction()
    {
        $actionName = $this->params('action');
        $langName = $this->params('lang');
        $controllerName = $this->params('controller');
        
        echo "222";
    }
    
    public function viewAction()
    {
        $actionName = $this->params('action');
        $langName = $this->params('lang');
        $controllerName = $this->params('controller');
        
        return array(
            'lang'  => $langName,
            'action' =>$actionName,
            'controller'  =>  $controllerName,
        );
    }
}
