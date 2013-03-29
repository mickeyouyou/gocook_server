<?php
/**
* LoginForm
* 
* Created By Panda on 16/03/13
*/

namespace User\Form;

use User\Entity\User;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class LoginForm
    extends Form
{
    
    public function __construct($name = 'login-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new User());

        $this->add(array(
            'name' => 'login',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Email or User Name',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
            ),
            'options' => array(
                'label' => 'Password',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Login',
                'id' => 'submitbtn',
            ),
        ));
    }
}