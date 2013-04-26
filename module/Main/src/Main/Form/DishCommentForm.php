<?php
/**
* DishCommentForm
* 
* Created By Panda on 21/04/21
*/

namespace Main\Form;

use Main\Entity\DishComment;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class DishCommentForm extends Form
{
    
    public function __construct($name = 'dish-comment-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new DishComment());

        $this->add(array(
            'name' => 'dish_id',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'dish id',
            ),
        ));

        $this->add(array(
            'name' => 'content',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Content',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Login',
                'id' => 'submitbtn',
            ),
        ));
    }
}