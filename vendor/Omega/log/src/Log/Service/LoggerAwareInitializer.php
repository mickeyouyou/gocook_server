<?php

/**
 * This file is part of the Omega package.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace Omega\Log\Service;

use Zend\Log\LoggerAwareInterface;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoggerAwareInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof LoggerAwareInterface) {
            static $logger;
            if (!$logger) {
                $logger = $serviceLocator->get('Omega\Log\Logger');
            }

            $instance->setLogger($logger);
        }
    }
}