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
            'name' => 'recipe_id',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'recipe id',
            ),
        ));

        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->add(array(
            'name' => 'desc',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'DEsc',
            ),
        ));

        $this->add(array(
            'name' => 'category',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Category',
            ),
        ));

        $this->add(array(
            'name' => 'cover_img',
            'attributes' => array(
                'type' => 'file',
            ),
            'options' => array(
                'label' => 'cover image',
            ),
        ));

        $this->add(array(
            'name' => 'materials',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'materials',
            ),
        ));

        $this->add(array(
            'name' => 'steps',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'materials',
            ),
        ));

        $this->add(array(
            'name' => 'tips',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'tips',
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