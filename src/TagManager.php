<?php

namespace Weblike\Cms\Shop;

use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Tag;

class TagManager
{

    public function getTags()
    {
        return Db::get()->getRepository( Tag::class )
            ->createQueryBuilder('tag')
            ->getQuery()
            ->getResult();
    }

    public function getTag( string $id )
    {
        return Db::get()->getRepository( Tag::class )
            ->createQueryBuilder('tag')
            ->where('tag.id = :tagId')
            ->setParameter('tagId', $id)
            ->getQuery()
            ->getResult();
    }
    
    public function save( ?string $product_id = null, array $data )
    {
        
    }
}