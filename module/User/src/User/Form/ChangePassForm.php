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


class ChangePassForm extends Form
{
    public function __construct($name = 'changepass-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new User());


        $this->add(array(
            'name' => 'oripassword',
            'attributes' => array(
                'type' => 'password',
            ),
            'options' => array(
                'label' => 'Old Password',
            ),
        ));
        
        
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
            ),
            'options' => array(
                'label' => 'New Password',
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
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Change',
                'id' => 'submitbtn',
            ),
        ));
    }
}