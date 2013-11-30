<?php
/**
* UserLike
* 
* Created By Panda on 30/11/13
*/

namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_like")
 * #property integer $user_id
 * @property integer $recipe_id
 */
class UserLike
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $user_id;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $recipe_id;
    
    
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