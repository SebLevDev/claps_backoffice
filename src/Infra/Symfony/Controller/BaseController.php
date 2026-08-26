<?php

declare(strict_types=1);

namespace Infra\Symfony\Controller;

use Infra\Symfony\Utils\SqlParameterBag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

class BaseController extends AbstractController
{
    private SqlParameterBag $sqlParameterBag;

    public function __construct(RequestStack $requestStack, private EntityManagerInterface $entityManager)
    {
        $this->sqlParameterBag  = new SqlParameterBag();

        if ($request = $requestStack->getCurrentRequest()) {
            $this->sqlParameterBag->setRequest($request);
        }
    }

    protected function getSqlParameterBag(): SqlParameterBag
    {
        return $this->sqlParameterBag;
    }

    protected function setSqlParameterBag(SqlParameterBag $sqlParameterBag): self
    {
        $this->sqlParameterBag = $sqlParameterBag;

        return $this;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function setEntityManager(EntityManagerInterface $entityManager): self
    {
        $this->entityManager = $entityManager;

        return $this;
    }
}
