<?php

/**
 * This file is part of the Omega package.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

use Zend\Log\Logger;

return array(
    'omega_log' => array(
        'writers' => array(
            'firephp'   => array(
                'enabled'          => false,
                //'check_dependency' => 'FirePHP',
            ),
            'chromephp' => array(
                'enabled'          => false,
                //'check_dependency' => 'ChromePhp',
            ),
            'stream'    => array(
                'enabled'                  => true,
                'fingers_crossed'          => true,
                'fingers_crossed_priority' => Logger::ERR,
                'priority'                 => Logger::INFO,
                'stream'                   => 'data/log/application.log',
            ),
        ),
    ),

    'service_manager' => array(
        'aliases' => array(
            'logger' => 'Omega\Log\Logger',
        ),
        'factories' => array(
            'Omega\Log\Logger' => 'Omega\Log\Service\LoggerFactory',
        ),
        'initializers' => array(
            'logger' => 'Omega\Log\Service\LoggerAwareInitializer',
        ),
    )
);