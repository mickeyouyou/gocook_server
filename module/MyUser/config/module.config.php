<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            'myuser' => __DIR__ . '/../view',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'myuser' => 'MyUser\Controller\UserController',
            'zfcuser' => 'MyUser\Controller\UserController',            
        ),
    ),
    'router' => array(
        'routes' => array(
            'zfcuser' => array(
                'type' => 'Segment',
                'priority' => 1000,
                'options' => array(
                    'route' => '/user',
                    'defaults' => array(
                        'controller' => 'myuser',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
);
