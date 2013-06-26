<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\UserInfo;
use Main\Entity\Recipe;
use User\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
//必须全部删除才能让另一方删除的就是owner，owner中inversedBy
/**
 * @ORM\Entity(repositoryClass="User\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @property string $username
 * @property string $password
 * @property integer $user_id
 */
class User
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $user_id;
    
    /**
     * @ORM\OneToOne(targetEntity="UserInfo", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $user_info;
    
    /**
     * @ORM\OneToMany(targetEntity="\Main\Entity\Recipe", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
   protected $recipes;

    /**
     * @ORM\OneToMany(targetEntity="\Main\Entity\RecipeComment", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $recipe_comments;

   /**
     * @ORM\Column(type="string")
     */
    protected $username;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $display_name;

    /**
     * @ORM\Column(type="string")
     */
    protected $portrait;    
    
    /**
     * @ORM\Column(type="string")
     */
    protected $email;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $password;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $gender;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $age;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $career;  

    /**
     * @ORM\Column(type="string")
     */
    protected $city;      
    
    /**
     * @ORM\Column(type="string")
     */
    protected $province;  
 
    /**
     * @ORM\Column(type="string")
     */
    protected $tel;    
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $register_time;   
    
    /**
     * @ORM\Column(type="text")
     */
    protected $intro;   

    public function __construct() 
    {
        $this->recipes = new ArrayCollection();
        $this->recipe_comments = new ArrayCollection();
    }
    
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