<?php

namespace App\Repository;

use App\Entity\Memo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Memo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Memo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Memo[]    findAll()
 * @method Memo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method
 */
class MemoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Memo::class);
    }

    public function findByLocation(string $path): array
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('m.id, m.location, m.title, m.fileName, m.onDisk, m.extension');
        $qb->where('m.location LIKE :pathFolders')
            ->orWhere('m.location = :pathFiles')
            ->orderBy('m.location', 'ASC')
            ->setParameter('pathFolders', $path . '/%')
            ->setParameter('pathFiles', $path );
        return $qb->getQuery()->getResult();
    }

    public function save(Memo $memo): void
    {
        $this->_em->persist($memo);
        $this->_em->flush();
    }

    public function findMemo(string $path)
    {
        $query = 'SELECT * FROM memo WHERE location || "/" || file_name = :path  LIMIT 0,1';
        $result = $this->_em->getConnection()->executeQuery($query, [$path]);

        return $result->fetchAssociative();
    }

    public function findAllWiki()
    {
        $qb = $this->createQueryBuilder('memo');
        $qb->select('memo.title, memo.fileName');
        $qb->where('memo.location = :location')
            ->orderBy('memo.title', 'ASC')
            ->setParameter('location', '/Wiki');
        return $qb->getQuery()->getResult();
    }

    public function findOneWiki(string $path)
    {
        $qb = $this->createQueryBuilder('memo');
        $qb->where('memo.location=:wiki')
            ->andWhere('memo.fileName=:entry')
            ->setParameter('wiki', '/Wiki')
            ->setParameter('entry', $path);

        return $qb->getQuery()->getSingleResult();
    }
}