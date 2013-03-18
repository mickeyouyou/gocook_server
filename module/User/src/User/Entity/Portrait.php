<?php
/**
* Portrait
* 
* Created By Panda on 19/03/13
*/

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="portrait")
 * @property integer $portrait_id
 * #property string $filename
 * @property integer $user_id
 */
class Portrait
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $portrait_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $filename;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $user_id;
    
    
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