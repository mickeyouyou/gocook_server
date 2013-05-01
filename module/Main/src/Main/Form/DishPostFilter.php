<?php
/**
 * DishPostForm
 *
 * Created By Panda on 21/04/13
 */

namespace Main\Form;

use Zend\InputFilter\InputFilter;

class DishPostFilter extends InputFilter {

    public function __construct() {

        $this->add(array(
            'name' => 'recipe_id',
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
            'name' => 'content',
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
            'name' => 'photo_img',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Please select an icon to upload.',
                        ),
                    ),
                ),
                array(
                    'name' => '\Zend\Validator\File\IsImage',
                    'options' => array(
                        'messages' => array(
                            'fileIsImageFalseType' => 'Please select a valid icon image to upload.',
                            'fileIsImageNotDetected' => 'The icon image is missing mime encoding.',
                        ),
                    ),
                ),
            ),
        ));
    }

}