<?php

namespace User\Form;

use Zend\InputFilter\InputFilter;

class RegisterFilter extends InputFilter {

    public function __construct() {

        $this->add(array(
            'name' => 'tel',
            'required' => true,
            'allow_empty' => false,
            'filters' => array(
                array('name' => 'Int'),
            ),
            'validators' => array(
                array(
                    'name' => 'Between',
                    'options' => array(
                        'min' => 11,
                        'max' => 11,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'nickname',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 3,
                        'max'      => 20,
                    ),
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 128,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'repassword',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
             ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 6 ),
                ),
                array(
                    'name' => 'identical',
                    'options' => array('token' => 'password')
                ),
            ),
        ));
    }

}