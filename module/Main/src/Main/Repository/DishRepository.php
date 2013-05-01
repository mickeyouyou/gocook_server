<?php

namespace Main\Repository;

use Doctrine\ORM\EntityRepository;

class DishRepository extends EntityRepository
{

    public function getDishesByFavorCount($recipe_id, $limit, $offset)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Dish', 'r')
            ->where('r.recipe_id = :recipe_id')
            ->setParameter('recipe_id', $recipe_id)
            ->add('orderBy', 'r.favor_count DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function getDishesByCreateDate($recipe_id, $limit, $offset)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Dish', 'r')
            ->where('r.recipe_id = :recipe_id')
            ->setParameter('recipe_id', $recipe_id)
            ->add('orderBy', 'r.create_time DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }
}