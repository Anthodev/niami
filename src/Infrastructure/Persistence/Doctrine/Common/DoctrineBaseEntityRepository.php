<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Common;

use App\Domain\Model\Common\ModelInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends ServiceEntityRepository<ModelInterface>
 */
abstract class DoctrineBaseEntityRepository extends ServiceEntityRepository
{
    #[Required]
    public ValidatorInterface $validator;

    public function update(ModelInterface $entity, bool $doFlush = true): void
    {
        $this->validator->validate($entity);

        if ($doFlush) {
            $this->getEntityManager()->flush();
        }
    }

    public function persist(ModelInterface $entity): void
    {
        $this->validator->validate($entity);
        $this->getEntityManager()->persist($entity);
    }

    public function save(ModelInterface $entity, bool $doFlush = true): void
    {
        $this->validator->validate($entity);
        $this->getEntityManager()->persist($entity);

        if ($doFlush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(ModelInterface $entity, bool $doFlush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($doFlush) {
            $this->getEntityManager()->flush();
        }
    }

    public function rollback(): void
    {
        $this->getEntityManager()->rollback();
    }

    public function refresh(ModelInterface $entity): void
    {
        $this->getEntityManager()->refresh($entity);
    }
}
