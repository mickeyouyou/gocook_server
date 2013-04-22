<?php
/**
* RecipeCommentForm
* 
* Created By Panda on 21/04/21
*/

namespace Main\Form;

use Main\Entity\RecipeComment;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class RecipeCommentForm extends Form
{
    
    public function __construct($name = 'recipe-comment-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new RecipeComment());

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