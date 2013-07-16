<?php
/**
* Recipe
* 
* Created By Panda on 18/03/13
*/

namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Main\Repository\RecipeRepository")
 * @ORM\Table(name="recipe")
 * @property integer $recipe_id
 * @property integer $user_id
 * @property datetime $create_time
 * @property string $name
 * @property text $desc
 * @property integer $collected_count
 * @property integer $dish_count
 * @property integer $comment_count
 * @property integer $browser_count
 * @property string $catgory
 * @property bigint $cover_img
 * @property text $materials
 * @property text $recipe_steps
 * @property text $tips
 */
class Recipe
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $recipe_id;

    /**
     * @ORM\OneToMany(targetEntity="\Main\Entity\RecipeComment", mappedBy="recipe")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="recipe_id")
     **/
    protected $recipe_comments;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="recipes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
   protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Dish", mappedBy="recipe")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="recipe_id")
     **/
   protected $dishes;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_time;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     */
    protected $collected_count;

    /**
     * @ORM\Column(type="integer")
     */
    protected $dish_count;

    /**
     * @ORM\Column(type="integer")
     */
    protected $comment_count;

    /**
     * @ORM\Column(type="integer")
     */
    protected $browse_count;

    /**
     * @ORM\Column(type="string")
     */
    protected $catgory;

    /**
     * @ORM\Column(type="string")
     */
    protected $cover_img;

    /**
     * @ORM\Column(type="text")
     */
    protected $materials;

    /**
     * @ORM\Column(type="text")
     */
    protected $recipe_steps;

    /**
     * @ORM\Column(type="text")
     */
    protected $tips;

    
    public function __construct()
    {
        $this->dishes = new ArrayCollection();
        $this->recipe_comments = new ArrayCollection();
        $this->collect_users = new ArrayCollection();
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