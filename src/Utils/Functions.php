<?php

namespace App\Utils;

use Doctrine\ORM\Tools\Pagination\Paginator;

class Functions{
  static public function paginate($dql, $page = 1, $limit = 5)
  {
    $paginator = new Paginator($dql);

    $paginator->getQuery()
        ->setFirstResult($limit * ($page - 1)) // Offset
        ->setMaxResults($limit); // Limite

    return $paginator;
  }
}
