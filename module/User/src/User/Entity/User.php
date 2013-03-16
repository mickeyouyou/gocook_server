<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
    

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @property string $username
 * @property string $password
 * @property int $user_id
 */
class User
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $user_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $password;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $display_name;
    
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