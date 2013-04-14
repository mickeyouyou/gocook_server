<?php

namespace Main\Repository;

use Doctrine\ORM\EntityRepository;

class RecipeRepository extends EntityRepository
{
    public function findRecipesByCatgory($catgory)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Recipe', 'r')
            ->where('r.catgory LIKE :cat')
            ->setParameter('cat', '%'.$catgory.'%')

        return $qb->getQuery()->getResult();
    }
}