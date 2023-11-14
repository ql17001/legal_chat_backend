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

    public function findAllWithPagination(int $currentPage, int $limit): Paginator
    {
        
        $query = $this->createQueryBuilder('p')
        ->getQuery();

        $paginator = Functions::paginate($query, $currentPage, $limit);

        return $paginator;
    }

    public function findAllByUserWithPagination(int $currentPage, int $idUsuario): Paginator
    {
      // Creamos nuestra query
      $queryBuilder = $this->createQueryBuilder('p');

      // Equivale a agregar despues del WHERE: AND id_asesor_id = $idUsuario
      $queryBuilder = $queryBuilder->andWhere('p.idAsesor = '.$idUsuario);

      // Equivale a agregar despues del condicional anterior: OR id_cliente_id = $idUsuario
      $queryBuilder = $queryBuilder->orWhere('p.idCliente = '.$idUsuario);
      

      $query = $queryBuilder->getQuery();

      // Creamos un paginator con la funcion paginate
      $paginator = Functions::paginate($query, $currentPage, 20);

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
