<?php

/**
 * This file is part of the VPCommon package.
 * @copyright Copyright (c) 2005-2013 BadPanda Inc.
 */
 
namespace Omega;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
// we use 'php composer.phar dump-autoload -o' to generate autoload classmap
//            'Zend\Loader\ClassMapAutoloader' => array(
//                __DIR__ .'/autoload_classmap.php',
//            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/lib/' . __NAMESPACE__,
                ),
            )
        );
    }
}


