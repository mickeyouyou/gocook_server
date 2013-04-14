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
            ->setParameter('cat', '%'.$catgory.'%');

        return $qb->getQuery()->getResult();
    }

    public function getRecipesByCollectedCount()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Recipe', 'r')
            ->add('orderBy', 'r.collected_count DESC')
            ->setMaxResults(100);//取前100个

        return $qb->getQuery()->getResult();
    }

    public function getRecipesByCreateDate()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Recipe', 'r')
            ->add('orderBy', 'r.create_time DESC')
            ->setMaxResults(100);//取前100个

        return $qb->getQuery()->getResult();
    }
}