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
        $this->setAttribute('enctype','multipart/form-data');

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
            'name' => 'avatar',
            'attributes' => array(
                'type' => 'file',
            ),
            'options' => array(
            ),
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