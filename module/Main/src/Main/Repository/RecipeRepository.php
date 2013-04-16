<?php

namespace Main\Repository;

use Doctrine\ORM\EntityRepository;

class RecipeRepository extends EntityRepository
{
    public function findRecipesByCatgory($catgory, $limit, $offset=0)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Recipe', 'r')
            ->where('r.catgory LIKE :cat')
            ->setParameter('cat', '%'.$catgory.'%')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function getRecipesByCollectedCount($limit, $offset)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Recipe', 'r')
            ->add('orderBy', 'r.collected_count DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function getRecipesByCreateDate($limit, $offset)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Main\Entity\Recipe', 'r')
            ->add('orderBy', 'r.create_time DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }
}