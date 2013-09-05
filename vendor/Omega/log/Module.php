<?php

/**
 * This file is part of the Omega package.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace Omega\Log;

use Zend\Loader;
use Zend\ModuleManager\Feature;
use Zend\Log\LoggerAwareInterface;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\ControllerProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            Loader\AutoloaderFactory::STANDARD_AUTOLOADER => array(
                Loader\StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__ . '/src/Log',
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getControllerConfig()
    {
        return array(
            'initializers' => array(
                'ctrl_logger' => function($instance, $sm) {
                    if ($instance instanceof LoggerAwareInterface) {
                        static $logger;
                        if (!$logger) {
                            $logger = $sm->getServiceLocator()->get('Omega\Log\Logger');
                        }

                        $instance->setLogger($logger);
                    }
                },
            ),
        );
    }
}