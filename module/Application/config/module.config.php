<?php
/**
 * Application Module Config
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

return array(
    'router' => array(
        'routes' => array(
           'application' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '[/:lang]/application/',                    
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                        'lang' => 'en',
                    ),                  
                ),
                'may_terminate' => true,
                'child_routes' => array(   
                    //controller
                    'a_controller' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[:controller][/]',
                            'defaults' => array(
                                'action'  =>  'index',
                            )
                        )
                    ),
                    //all
                    'a_default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[:controller][/:action][/]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Application\Controller',
                            )
                        )
                    ), 
                ),
            ),
            'app_index' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '[/:lang]/application',                    
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                        'lang' => 'en',
                    ),                  
                ),
                'may_terminate' => true,
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Test' => 'Application\Controller\TestController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
