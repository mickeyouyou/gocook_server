<?php
/**
* DishPostForm
* 
* Created By Panda on 29/04/13
*/

namespace Main\Form;

use Main\Entity\Dish;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class DishPostForm extends Form
{
    
    public function __construct($name = 'dish-post-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new Dish());

        $this->add(array(
            'name' => 'recipe_id',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'recipe id',
            ),
        ));

        $this->add(array(
            'name' => 'content',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'dish content',
            ),
        ));

        $this->add(array(
            'name' => 'photo_img',
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
                'value' => 'submit',
                'id' => 'submitbtn',
            ),
        ));
    }
}