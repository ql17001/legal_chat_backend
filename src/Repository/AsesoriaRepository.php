<?php

namespace App\Repository;

use App\Entity\Asesoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Utils\Functions;

/**
 * @extends ServiceEntityRepository<Asesoria>
 *
 * @method Asesoria|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asesoria|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asesoria[]    findAll()
 * @method Asesoria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AsesoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asesoria::class);
    }

    public function findAllWithoutAsesorWithPagination(int $currentPage, int $limit): Paginator
    {
        
        $queryBuilder = $this->createQueryBuilder('a')
        ->andWhere('a.idAsesor is null');

        $query = $queryBuilder->getQuery();
        $paginator = Functions::paginate($query, $currentPage, $limit);

        return $paginator;
    }

//    /**
//     * @return Asesoria[] Returns an array of Asesoria objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Asesoria
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
