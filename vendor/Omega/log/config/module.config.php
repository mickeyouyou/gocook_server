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
                'name'             => 'firephp',
                'enabled'          => false,
                //'check_dependency' => 'FirePHP',
            ),
            'chromephp' => array(
                'name'             => 'stream',
                'enabled'          => false,
                //'check_dependency' => 'ChromePhp',
            ),
            'stream'    => array(
                'name'                     => 'stream',
                'enabled'                  => true,
                'priority'                 => Logger::INFO,
                'stream'                   => 'log/'. 'server_' . date('Y_m_d') .'.log',
            ),
            'stream_err'    => array(
                'name'                     => 'stream',
                'enabled'                  => true,
                'priority'                 => Logger::ERR,
                'stream'                   => 'log/'. 'server_error_' . date('Y_m_d') .'.log',
            ),
            'finger_crossed_err'    => array(
                'name'                     => 'fingerscrossed',
                'enabled'                  => true,
                'priority'                 => Logger::ERR,
                'writer'                   => array(
                    'name'                 => 'stream',
                    'options'              => array(
                        'priority'         => Logger::INFO,
                        'stream'           => 'log/'. 'server_fingercrossed_' . date('Y_m_d') .'.log',
                    ),
                ),

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