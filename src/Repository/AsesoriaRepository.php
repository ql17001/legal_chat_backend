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

    public function findAllWithPagination(int $currentPage, int $limit, string $filtro = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('p');

        if ($filtro !== null) {
            if ($filtro === 's' || $filtro === 't' || $filtro === 'e') {
                $queryBuilder->andWhere('p.estado = :filtro')
                    ->setParameter('filtro', $filtro);
            }
            // Añadir más condiciones según sea necesario
        }

        $query = $queryBuilder->getQuery();
        return Functions::paginate($query, $currentPage, $limit);
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
