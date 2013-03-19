<?php
/**
* RegisterForm
* 
* Created By Panda on 16/03/13
*/

namespace User\Form;

use User\Entity\User;
use Zend\Form\Form;
use Zend\Form\Element\Email;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class RegisterForm extends Form
{
    public function __construct($name = 'register-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new User());

        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'Zend\Form\Element\Email',
            ),
            'options' => array(
                'label' => 'Email',
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
            'name' => 'repassword',
            'attributes' => array(
                'type' => 'password',
            ),
            'options' => array(
                'label' => 'Repeat Password',
            ),
        ));     
          
        $this->add(array(
            'name' => 'nickname',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'NickName',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 60000
                )
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Register',
                'id' => 'submitbtn',
            ),
        ));
    }
}