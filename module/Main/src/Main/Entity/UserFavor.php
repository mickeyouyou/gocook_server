<?php
/**
* UserFavor
* 
* Created By Panda on 19/03/13
*/

namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_favor")
 * @property bigint $id
 * @property string $user_id
 * @property integer $dish_id
 * @property smallint state
 */
class UserFavor
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user_id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $dish_id;
    
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