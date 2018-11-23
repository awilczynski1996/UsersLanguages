<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function search($date)
    {
        $qb = $this->createQueryBuilder('u');

        if ($date !== null){
            $qb->where('u.created_at > :date')
                ->setParameter('date', new \DateTime($date));
        }

        return $qb->getQuery()->getResult();
    }

    public function create($data)
    {
        $em = $this->getEntityManager();
        $user = new User();

        $user->setName($data['name']);
        $user->setSurname($data['surname']);
        $user->setPesel($data['pesel']);
        $user->setEmail($data['email']);

        foreach ($data['languageObjects'] as $language) {
            $user->addLanguage($language);
        }

        $em->persist($user);
        $em->flush();
    }
}
