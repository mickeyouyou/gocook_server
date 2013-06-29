<?php

namespace User\Form;

use Zend\InputFilter\InputFilter;

class ChangeUserInfoFilter extends InputFilter {

    public function __construct() {

        $this->add(array(
            'name' => 'nickname',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 6 ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'gender',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'Int'),
            ),
            'validators' => array(
                array(
                    'name' => 'Between',
                    'options' => array(
                        'min' => 0,
                        'max' => 2,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'age',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'Int'),
            ),
            'validators' => array(
                array(
                    'name' => 'Between',
                    'options' => array(
                        'min' => 1,
                        'max' => 100,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'career',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 2 ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'city',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 2 ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'province',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 4 ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'tel',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 8 ),
                ),
            ),
        ));


        $this->add(array(
            'name' => 'intro',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 6 ),
                ),
            ),
        ));
    }

}