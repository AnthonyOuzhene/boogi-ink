<?php

namespace App\Repository;

use App\Entity\HygieneCommitment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HygieneCommitment>
 */
class HygieneCommitmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HygieneCommitment::class);
    }
}
