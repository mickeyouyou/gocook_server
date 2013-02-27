<?php
/**
 * Main Module
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

//        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function($e) {
//            $matches    = $e->getRouteMatch();
//            $controller_str = $matches->getParam('controller');
//            
//            $controller = $e->getTarget();
//            if(0 !== strpos($controller_str, __NAMESPACE__)){
//              // not a controller from this module
//            }else{
//              
//              if(is_object($controller))
//              {
//                $controller->_helper->layout()->disableLayout();
//                $controller->_helper->viewRenderer->setNoRender(true);                
//              }
//            }
//        });
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
}
