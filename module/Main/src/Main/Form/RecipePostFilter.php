<?php
/**
 * RecipePostForm
 *
 * Created By Panda on 16/04/21
 */

namespace Main\Form;

use Zend\InputFilter\InputFilter;

class RecipePostFilter extends InputFilter {

    public function __construct() {

        $this->add(array(
            'name' => 'login',
            'required' => true,
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
    }

}