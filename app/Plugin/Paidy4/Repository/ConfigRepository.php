<?php

namespace Plugin\Paidy4\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\Paidy4\Entity\Config;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ConfigRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Config::class);
    }

    public function get($id = 1)
    {
        return $this->find($id);
    }
}
