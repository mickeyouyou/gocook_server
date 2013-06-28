<?php
/**
* ChangeUserInfoForm
* 
* Created By Panda on 16/06/13
*/

namespace User\Form;

use User\Entity\User;
use Zend\Form\Form;
use Zend\Form\Element\Email;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class ChangeUserInfoForm extends Form
{
    public function __construct($name = 'changeuserinfo-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new User());


        $this->add(array(
            'name' => 'nickname',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'nickname',
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
            'name' => 'gender',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'gender',
            ),
        ));

        $this->add(array(
            'name' => 'age',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'age',
            ),
        ));

        $this->add(array(
            'name' => 'career',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'career',
            ),
        ));

        $this->add(array(
            'name' => 'city',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'city',
            ),
        ));

        $this->add(array(
            'name' => 'province',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'province',
            ),
        ));

        $this->add(array(
            'name' => 'tel',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'tel',
            ),
        ));

        $this->add(array(
            'name' => 'intro',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'intro',
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