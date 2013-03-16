<?php

namespace User\Form;

use Zend\InputFilter\InputFilter;

class ChangePassFilter extends InputFilter {

    public function __construct() {

        $this->add(array(
            'name' => 'oripassword',
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