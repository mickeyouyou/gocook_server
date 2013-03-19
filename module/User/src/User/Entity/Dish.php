<?php
/**
* Dish
* 
* Created By Panda on 18/03/13
*/

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dish")
 * @property integer $dish_id
 * #property integer $recipe_id
 * @property integer $user_id
 * @property datetime $create_time
 * @property text $content
 * @property integer $photo_id
 * @property integer $favor_count
 * @property smallint state
 */
class Dish
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $dish_id;

    /**
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="text")
     */
    protected $content;    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $photo_id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $favor_count;    
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $state;    
    
    /**
     * @ManyToOne(targetEntity="Recipe", inversedBy="dishes")
     * @JoinColumn(name="recipe_id", referencedColumnName="recipe_id")
     **/
   protected $recipe;
   
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