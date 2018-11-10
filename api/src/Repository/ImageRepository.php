<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ImageRepository
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class ImageRepository extends EntityRepository
{


    public function findFrontPage( $from, $to, $album_id = null )
    {
        $qb = $this->createQueryBuilder('q')
            ->where('q.private = 0')
            ->orderBy('q.uploadedAt', 'DESC')
            ->setFirstResult( $from )
            ->setMaxResults( $to );

        if ($album_id) {
            $qb->andWhere('q.album = :album')
                ->setParameter('album', $album_id);
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function findAlbumPage( $from, $to )
    {
        return $this->createQueryBuilder('q')
            ->where('q.private = 0')
            ->orderBy('q.uploadedAt', 'DESC')
            ->setFirstResult( $from )
            ->setMaxResults( $to )
            ->getQuery()
            ->getResult();
    }

    public function countAllAlbumNull()
    {
        $qb = $this->createQueryBuilder('q');
        return $qb
            ->select('count(q.id)')
            ->where($qb->expr()->isNull('q.album'))
            ->getQuery()
            ->getResult();

    }

    public function findAllForUserQuery($id)
    {
        return $this->createQueryBuilder('q')
            ->where('q.user = :id')
            ->setParameter('id', $id)
            ->orderBY('q.uploadedAt', 'DESC')
            ->getQuery();
    }
}
