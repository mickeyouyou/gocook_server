<?php
/**
* Recipe
* 
* Created By Panda on 18/03/13
*/

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="recipe")
 * @property integer $recipe_id
 * @property integer $user_id
 * @property datetime $create_time
 * @property string $name
 * @property text $desc
 * @property integer $collected_count
 * @property integer $dish_count
 * @property integer $comment_count
 * @property integer $browser_count
 * @property string $catgory
 * @property bigint $cover_img_id
 * @property text $materials
 * @property text $recipe_steps
 * @property text $tips
 */
class Recipe
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $recipe_id;
    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $user_id;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_time;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $name;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $desc;    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $collected_count;    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $dish_count;        
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $comment_count;        
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $browser_count;    
    
    /**
     * @ORM\Column(type="string")
     */
    protected $catgory;        

    /**
     * @ORM\Column(type="bigint")
     */
    protected $cover_img_id;    
    
    /**
     * @ORM\Column(type="text")
     */
    protected $materials;      
    
    /**
     * @ORM\Column(type="text")
     */
    protected $recipe_steps;  
    
    /**
     * @ORM\Column(type="text")
     */
    protected $tips;
    
    
    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) 
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) 
    {
        $this->$property = $value;
    }  
    
}