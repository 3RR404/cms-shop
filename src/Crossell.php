<?php

namespace Weblike\Cms\Shop;

/**
 * ## Podstrcenie podobneho / suvysiaceho produktu
 * 
 * - neprihlaseny uzivatel dostane vysledok produkt zvoleny v "Novinka"
 * - neprihlaseny uzivatel v pripade nulovej hodnoty v "Novinka" dostane\
 * vysledok produkt, ktory je v "Odporucane" - random triedenie
 * - ak nie je nastavene ani jedno z uvedeneho "Novinka" ci "Odporucane", vysledok\
 * bude akykolvek dostupny publikovany aktivny produkt - random triedenie
 * 
 * - prihlaseny uzivatel, ktory nakupoval dostane vysledok produkt, ktory nie je v jeho\
 * kosiku ale nachadza sa v jeho objednavkach
 * - pokial su produkty v kosiku i v objednavkach totozne vyuzije sa princip neprihlaseneho\
 * uzivatela
 * - pokial este nenakupil a tak nema ziadnu historiu objednavok, vyuzije sa princip\
 * neprihlaseneho uzivatela
 */
class Crossell 
{

    protected static $user;

    function __construct()
    {
        !@$_SESSION[ md5( APPSERVERNAME ) . "-user-crosselling" ] ? $_SESSION[ md5( APPSERVERNAME ) . "-user-crosselling" ] = '' : $_SESSION[ md5( APPSERVERNAME ) . "-user-crosselling" ] = $_SESSION[ md5( APPSERVERNAME ) . "-user-crosselling" ];

    }

    /**
     * ## Produkt crossell
     * 
     * vrati produkt podla kriterii
     * @param object $user
     * @return array|bool
     */
    public static function run( object $user )
    {
        $products = new Products;

        $have_history = self::userHaveHistory( $user );

        if ( !$user->isLoggedIn() ) return self::LoggOffCrossell(); // neprihlaseny uzivatel

        if ( !$have_history ) return self::LoggOffCrossell(); // prihlaseny bez historie

        return $products->show( $have_history, true, 1, false, true ); //Db::get()->es_product()->where( 'id', $have_history )->order( 'RAND()' )->fetch();

        return false;
    }


    /**
     * ## Filtrovanie produktov
     * - vrati id produktov v poli
     * @param object $orders - zoznam objednavok ako vypis z DB
     * @return array|bool
     */
    protected static function getProductsFromOrders( object $orders )
    {
        $products = false;

        foreach ( $orders as $order ) if ( @$order['products'] && $parsed_products = json_decode( $order['products'], true ) ) 
        foreach ( $parsed_products as $product ) if( isset( $product['id'] ) ) $products[ $product['id'] ] = $product['id'];
        $products = self::productAlreadyInCart( $products );

        return $products;
    }

    /**
     * ## zistenie historie nakupov
     * @param object $user
     * @see Weblike\Plugins\User
     * 
     * @return array|bool produkty
     */
    protected static function userHaveHistory( object $user )
    {
        if ( Db::get()->es_orders()->where( 'user_id', $user->id )->count() > 0 )
        {
            return self::getProductsFromOrders( Db::get()->es_orders()->where( 'user_id', $user->id ) ); // Db::get()->es_orders()->where( 'user_id', $user->id )
        }

        return false;
    }

    /**
     * ## Filtracia produktov v kosiku
     * @param array|string $products - produkty, produkt kt. sa hlada
     * @return array|bool
     */
    protected static function productAlreadyInCart( $products )
    {
        $cart = new Cart;
        
        $products_in_cart = $cart->getProducts()->products;

        if ( is_array( $products ) && $products !== false )
        
        foreach ( $products_in_cart as $product_in_cart ) if( key_exists( $pkey = $product_in_cart['product_id'], $products ) ) unset( $products[ $pkey ] );
        
        if( !empty( $products ) ) return $products;

        return false;
    }

    /**
     * ## Princip neprihlaseneho uzivatela
     * @return array|bool
     */
    protected static function LoggOffCrossell()
    {
        $products = new Products;

        $where = 'new_tag = 1';

        $product = $products->show( false, true, false, $where, true ) ?: false;

        if ( $product->count() === 0 )
        {
            $where = 'recommended = 1';

            $product = $products->show( false, true, false, $where, true ) ?: false;
        };

        foreach ( $product as $p )
        {
            $cart = new Cart;

            $products_in_cart = $cart->getProducts()->products;

            foreach ( $products_in_cart as $product_in_cart ) if ( $product_in_cart['product_id'] == $p['id'] ) $products_container[ $p['id'] ] = $p['id'];

        }
        if ( $products_container ) $product = $products->show( false, true, false, $where . ' AND id NOT IN('.implode(',', $products_container).')', true );

        if ( $product->count() === 0 )
        {
            $where = '1';

            $product = $products->show( false, true, false, false, true );
            
            foreach ( $product as $p )
            {
                $cart = new Cart;
    
                $products_in_cart = $cart->getProducts()->products;
    
                foreach ( $products_in_cart as $product_in_cart ) if ( $product_in_cart['product_id'] == $p['id'] ) $products_container[ $p['id'] ] = $p['id'];
    
            }
            if ( $products_container ) $product = $products->show( false, true, false, $where . ' AND id NOT IN('.implode(',', $products_container).')', true );
        }

        return $product->count() ? $products->show( $product, true, false, 1, true ) : false;
        
    }

}