<?php

namespace App\Repository;

use App\Entity\DataSetType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DataSetType|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataSetType|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataSetType[]    findAll()
 * @method DataSetType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataSetTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DataSetType::class);
    }

    // /**
    //  * @return DataSetType[] Returns an array of DataSetType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DataSetType
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
