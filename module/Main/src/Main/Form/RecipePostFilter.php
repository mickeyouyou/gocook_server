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
            'name' => 'recipe_id',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'Int'),
            ),
            'validators' => array(
                array(
                    'name' => 'GreaterThan',
                    'options' => array( 'min' => 0),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'name',
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
            'name' => 'desc',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array( 'min' => 0 ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'category',
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
            'name' => 'materials',
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
            'name' => 'steps',
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
            'name' => 'tips',
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

    }

}