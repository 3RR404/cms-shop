<?php

namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Promocode;

class PromocodeManager
{
    /**
     * Ulozenie v percentach ( 50,100,... )
     */
    const PERCENTAGE = 1;

    /**
     * Ulozenie v mene (EUR, CZK, USD, ...)
     */
    const CURRENCY = 2;

    public function save( ?string $id = null, array $data )
    {
        if ( $id ) $promocode = Db::get()->getReference( Promocode::class, $id );
        else $promocode = new Promocode;
        
        $promocode->setName( $data['name'] );
        $promocode->setValue( $data['value'] );
        $promocode->setType( $data['type'] );
        $promocode->setActive( $data['active'] );

        Db::get()->persist( $promocode );
        Db::get()->flush();

        return false;
    }

    public function getPromocodes( ?bool $as_array = false )
    {
        return Db::get()->getRepository( Promocode::class )
            ->createQueryBuilder('code')
            ->getQuery()
            ->getResult( $as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT );
    }

    public function getPromocode( $id, ?bool $as_array = false )
    {
        return Db::get()->getRepository( Promocode::class )
            ->createQueryBuilder('code')
            ->where( 'code.id = :id' )
            ->setParameter( 'id', $id )
            ->getQuery()
            ->getOneOrNullResult( $as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT );
    }

    public function findPromocode( string $code, ?bool $as_array = false )
    {
        return Db::get()->getRepository( Promocode::class )
            ->createQueryBuilder('code')
            ->where( 'code.name = :code' )
            ->setParameter( 'code', $code )
            ->getQuery()
            ->getOneOrNullResult( $as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT );
    }

    public function toggleActive( string $id, array $data )
    {
        $promocode = Db::get()->getReference( Promocode::class, $id );

        $promocode->setActive( $data['active'] );
        
        Db::get()->persist( $promocode );
        Db::get()->flush();
    }
}