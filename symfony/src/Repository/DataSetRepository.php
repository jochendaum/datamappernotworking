<?php

namespace App\Repository;

use App\Entity\DataSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DataSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataSet[]    findAll()
 * @method DataSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataSetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DataSet::class);
    }

    public function disableUploadOthers($id, $type)
    {
        $this->createQueryBuilder('d')
            ->update()
            ->set('d.upload', 0)
            ->where('d.id <> :id')
            ->setParameter('id', $id)
            ->andWhere('d.Type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->execute();
    }
    // /**
    //  * @return DataSet[] Returns an array of DataSet objects
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
    public function findOneBySomeField($value): ?DataSet
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
