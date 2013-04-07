<?php
/**
* Photo
* 
* Created By Panda on 18/03/13
*/

namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="photo")
 * @property integer $photo_id
 * #property integer $recipe_id
 * @property integer $user_id
 * @property datetime $upload_time
 * @property string $image_name
 * @property string $image_path
 * @property string $thumb_path
 * @property string $desc
 * @property integer refer_count
 */
class Photo
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $photo_id;

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
    protected $upload_time;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $image_name;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $image_path;    
    
    /**
     * @ORM\Column(type="string")
     */
    protected $thumb_path;    

    /**
     * @ORM\Column(type="string")
     */
    protected $desc;      
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $refer_count;      
    
   
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