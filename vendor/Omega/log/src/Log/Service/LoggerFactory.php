<?php

/**
 * This file is part of the Omega package.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace Omega\Log\Service;

use Zend\Log\Logger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoggerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $config = $config['omega_log'];

        $logger  = new Logger;
        $plugins = $logger->getWriterPluginManager();
        foreach ($config['writers'] as $name => $options) {
            if (!$options['enabled']) {
                continue;
            }
            unset($options['enabled']);

            $writer = $plugins->get($name, $options);
            $logger->addWriter($writer);
        }

        return $logger;
    }
}