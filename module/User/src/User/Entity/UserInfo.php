<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_info")
 * @property int $collect_count
 * @property int $dish_count
 * @property int $user_id
 * @property int $id
 * @property int $recipe_count
 * @property int $following_count
 * @property int $followed_count
 */
class UserInfo
{

     /**
     * @ORM\Id @ORM\OneToOne(targetEntity="User", inversedBy="user_info")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $user;    

    /**
     * @ORM\Column(type="integer")
     */
    protected $collect_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $dish_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $recipe_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $following_count;
        
    /**
     * @ORM\Column(type="integer")
     */
    protected $followed_count;
    
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