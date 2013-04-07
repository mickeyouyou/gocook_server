<?php
/**
* DishComment
* 
* Created By Panda on 18/03/13
*/

namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dish_comment")
 * @property bigint $comment_id
 * @property integer $user_id
 * @property integer $dish_id
 * @property integer $recipe_id
 * @property datetime $create_time
 * @property text $content
 * @property smallint $state
 */
class DishComment
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $comment_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user_id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $dish_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $recipe_id;    
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_time;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $content; 
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $state;    
    
   
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