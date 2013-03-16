<?php

namespace User;

return array(
    'view_manager' => array(
        'template_path_stack' => array(
           'user' => __DIR__ . '/../view',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'user' => 'User\Controller\UserController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'user' => array(
                'type' => 'Segment',
                'priority' => 1000,
                'options' => array(
                    'route' => '/user[/:action]',
                    'defaults' => array(
                        'controller' => 'user',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        ),
        'authentication' => array(
            'orm_default' => array(
                'object_manager' => 'Doctrine\ORM\EntityManager',
                'identity_class' => __NAMESPACE__ . '\Entity\User',
                'identity_property' => 'login',
                'credential_property' => 'password',
                'credential_callable' => function(\User\Entity\User $user, $password) {
                    $bcrypt = new \Zend\Crypt\Password\Bcrypt();
                    return $bcrypt->verify($password, $user->getPassword());
                },
            ),
        ),
    ),
);
