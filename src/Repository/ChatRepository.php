<?php

namespace App\Repository;

use App\Entity\Chat;
use App\Utils\Functions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chat>
 *
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    public function findAllByUserWithPagination(int $currentPage, int $idUsuario): Paginator
    {
      // Creamos nuestra query
      $queryBuilder = $this->createQueryBuilder('c');
      $queryBuilder->where(
        $queryBuilder->expr()->in(
          'c.idAsesoria', 
          $this->createQueryBuilder('a')
          ->select('asesoria.id')
          ->from('App\Entity\Asesoria', 'asesoria')
          ->andWhere('asesoria.idAsesor = :idUsuario')
          ->orWhere('asesoria.idCliente = :idUsuario')
          ->getDQL()
        )
      )->setParameter(':idUsuario', $idUsuario);

      $query = $queryBuilder->getQuery();

      // Creamos un paginator con la funcion paginate
      $paginator = Functions::paginate($query, $currentPage, 20);

      return $paginator;
    }

//    /**
//     * @return Chat[] Returns an array of Chat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Chat
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
