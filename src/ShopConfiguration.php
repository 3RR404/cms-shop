<?php

namespace Weblike\Cms\Shop;

use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Configuration;

class ShopConfiguration
{
    public function getConfig( string $name ) : ?Configuration
    {
        return 
            Db::get()->getRepository( Configuration::class )
                ->createQueryBuilder( 'settings' )
                ->where( 'settings.name = :name' )
                ->setParameter( 'name', $name )
                ->getQuery()
                ->getOneOrNullResult();
    }

    public static function getConfiguration( string $name ) : ?Configuration
    {
        return 
            Db::get()->getRepository( Configuration::class )
                ->createQueryBuilder( 'settings' )
                ->where( 'settings.name = :name' )
                ->setParameter( 'name', $name )
                ->getQuery()
                ->getOneOrNullResult();
    }
}