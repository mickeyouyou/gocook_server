<?php
/**
* RecipePostForm
* 
* Created By Panda on 16/04/21
*/

namespace Main\Form;

use Main\Entity\Recipe;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class RecipePostForm extends Form
{
    
    public function __construct($name = 'recipe-post-form')
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethodsHydrator(false))
             ->setObject(new Recipe());

        $this->add(array(
            'name' => 'login',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Email or User Name',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
            ),
            'options' => array(
                'label' => 'Password',
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