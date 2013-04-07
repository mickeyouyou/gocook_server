<?php
/**
* UserComment
* 
* Created By Panda on 18/03/13
*/

namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_comment")
 * @property bigint $comment_id
 * @property integer $user_id
 * @property integer $owner_id
 * @property datetime $create_time
 * @property text $content
 * @property smallint $state
 */
class UserComment
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
    protected $owner_id;    
    
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