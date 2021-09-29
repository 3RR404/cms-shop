<?php

namespace Weblike\Cms\Shop;

interface ILoyalityManagement
{
    const POINT = 1;
    const PAID_VALUE = 10;
    const CURRENCY_PAID_VALUE = 1;

    const POINTS_ADDED_TEXT = 'Pripočítanie bodov za objednávku';
    const POINTS_REMOED_TEXT = 'Uplatnenie bodov / zľava';

    /**
     * ## Pripocitanie bodov
     * - na jedno cislo objednavky jeden zaznam
     * - vypocet bodov podla urcenia sumy za jeden bod
     * - vypocet z celkovej sumy objednavky podla sumy za jeden bod
     * @param integer $order_id id objednavky
     * @return int body
     */
    public function addLoyalityPoints( int $order_id );

    /**
     * ## Vypis bodov
     * - pre daneho uzivatela vypise body za objednavky
     */
    public function getLoyalityPoints();

    /**
     * ## Hodnota za jeden bod
     * - suma za jeden bod
     * @return int
     */
    public function getLoyalityPointValue();

    /**
     * ## Pouzitie bodov
     * @param int $points_value hodnota bodov ktore sa odpocitaju
     * @return int
     */
    public function useLoyalityPoints( int $points_value );

    /**
     * ## Ulozenie pouzitych bodov
     * - ulozi pouzite body v objednavke do zaznamu v DB
     * @param integer $order_number cislo / id objednavky, pod ktorou bude zaznam vedeny
     * @return void
     */
    public function saveLoyalityPoints( int $order_number );

}

class LoyalityManagement extends OrderManager implements ILoyalityManagement
{
    public static $loyalityPointValue;
    public static $loyalityPointRecursive;

    function __construct()
    {
        parent::__construct();
        
        if( !isset( $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'] ) && empty( $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'] ) ) $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'] = [];

        if( $point_val = \globalSettings('point_value') ) self::$loyalityPointValue = $point_val;
        if( $rec_point_val = \globalSettings('currency_point_value') ) self::$loyalityPointRecursive = $rec_point_val;
    }

    function __get( $name )
    {
        return isset( $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'] ) && !empty( $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'] ) ? ( isset( $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'][$name] ) ? $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'][$name] : false ) : false;
    }

    /**
     * ## Hodnota 1 bodu
     * - vrati nastavenu hodnotu 1 bodu
     * @return int
     */
    public function getLoyalityPointValue()
    {
        return self::$loyalityPointValue > 0 ? self::$loyalityPointValue : self::PAID_VALUE;
    }

    /**
     * ## Hodnota 1 bodu
     * - vrati nastavenu hodnotu 1 bodu pre konverziu
     * @return int
     */
    public function getLoyalityPointRecursive()
    {
        return self::$loyalityPointRecursive > 0 ? self::$loyalityPointRecursive : self::CURRENCY_PAID_VALUE;
    }

    public function addLoyalityPoints( int $order_id )
    {
        $calculate_result = 0;
        $point_value = $this->getLoyalityPointValue();
        
        if ( !$this->user->isLoggedIn() ) return false;

        if ( empty( $order = $this->getByOrderNumber( $order_id ) ) ) $order = $this->getOne( $order_id );

        if ( !$order ) return false;

        if( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
        {
            $business = new B2b;
            $business->setUser( $order['user_id'] );
            if( $business->isBusinessman() ) return false;
        }

        $order_value = $order['total'];

        $calculate_result = (int)$order_value * ( self::POINT / $point_value );

        $tbl = Db::get()->es_loyality_management();

        $data = [
            'user_id' => $this->user->id,
            'es_orders_id' => $order['id'],
            'points' => $calculate_result
        ];
        if( !empty( $tbl->where( $order['id'] )->fetch('id') ) ) return false;
        $tbl->insert( $data );

        return $calculate_result;
    }

    /**
     * ## Sprava bodov
     * - zvysi bodovu hodnotu podla zadania
     * - nesmie sa jednat o B2B partnera
     * - je mozne vypocitat sumu podla vysky objednavky
     * 
     * @param integer $user_id unikatny identifikator uzivatela
     * @param integer $points_value hodnota bodov, ktore budu pridane
     * @param integer $order_value - hodnota objednavky/suma z ktorej sa vypocita hodnota bodov
     * @return object
     */
    public function addLoyalityPointsToUser( int $user_id = 0, int $points_value = 0, float $order_value = 0.00 )
    {
        if( $user_id <= 0 ) return false;

        $calculate_result = 0;
        $point_value = $this->getLoyalityPointValue();

        if( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
        {
            $business = new B2b;
            $business->setUser( $user_id );
            if( $business->isBusinessman() ) return false;
        }

        if( $points_value > 0 ) $calculate_result = (int) $points_value;
        
        if( $order_value > 0 ) $calculate_result = (int)$order_value * ( self::POINT / $point_value );

        $tbl = Db::get()->es_loyality_management();

        $data = [
            'user_id' => $user_id,
            'es_orders_id' => NULL,
            'points' => $calculate_result
        ];

        return $tbl->insert( $data );
    }

    /**
     * ## Sprava bodov
     * - znizi bodovu hodnotu podla zadania
     * - nesmie sa jednat o B2B partnera
     * - je mozne vypocitat sumu podla vysky objednavky
     * 
     * @param integer $user_id unikatny identifikator uzivatela
     * @param integer $points_value - hodnota bodov, ktore budu pridane vklada sa so znamienkom "-" automaticky
     * @param integer $order_value - hodnota objednavky/suma z ktorej sa vypocita hodnota bodov
     * @return object
     */
    public function removeLoyalityPointsToUser( int $user_id = 0, int $points_value = 0, float $order_value = 0.00 )
    {
        if( $user_id <= 0 ) return false;

        $calculate_result = 0;
        $point_value = $this->getLoyalityPointValue();

        if( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
        {
            $business = new B2b;
            $business->setUser( $user_id );
            if( $business->isBusinessman() ) return false;
        }

        if( $points_value > 0 ) $calculate_result = (int) $points_value * (-1);
        
        if( $order_value > 0 ) $calculate_result = (int)( $order_value * ( self::POINT / $point_value ) ) * (-1);

        $tbl = Db::get()->es_loyality_management();

        $data = [
            'user_id' => $user_id,
            'es_orders_id' => NULL,
            'points' => $calculate_result
        ];

        return $tbl->insert( $data );
    }

    /**
     * ## Celkovy sucet bodov
     * - nesmie sa jednat o B2B partnera
     * @return integer celkovy sucet bodov
     */
    public static function getLoyalityPointsByUser( int $user_id )
    {
        if( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
        {
            $business = new B2b;
            $business->setUser( $user_id );
            if( $business->isBusinessman( $user_id ) ) return false;
        }

        return Db::get()->es_loyality_management()->where( 'user_id', $user_id )->sum('points');
    }

    /**
     * ## Pouzitie bodov
     * - pouzitie v praxi
     * - ulozenie do session
     * 
     * @param integer $points_value - hodnota bodov, kt. sa ulozia
     * @return int
     */
    public function useLoyalityPoints( int $points_value )
    {
        $point_value = $this->getLoyalityPointRecursive();
        
        if ( !$this->user->isLoggedIn() ) return false;

        $calculated_result = ( $points_value * ( self::POINT / $point_value ) );

        $cart = new Cart;
        $shipping_method = new ShippingMethods;
        $payment_method = new PaymentMethods;

        $order_total = $cart->getSubtotal() + ( parseInt( $cart->productsInCart()['promocodes']['value'] ) / -100 ) + parseFloat( $cart->getShippingMethod()['price'] ) + parseFloat( $cart->getPaymentMethod()['price'] );
        if( $calculated_result > $order_total ){
            $calculated_result = $points_value = (int)$order_total;
        }

        $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'] = [
            'used' => $points_value,
            'value' => $calculated_result
        ];

        
        return $calculated_result;
    }

    /**
     * ## Zaznamenanie bodov
     * - Ulozenie do DB
     * - body sa ulozia do DB s cislom objednavky
     */
    public function saveLoyalityPoints( int $order_number )
    {
        if ( !$this->user->isLoggedIn() ) return false;

        if( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
        {
            $business = new B2b;
            if( $business->isBusinessman( $this->user->id ) ) return false;
        }

        if ( empty( $order = $this->getByOrderNumber( $order_number ) ) ) $order = $this->getOne( $order_number );

        if ( !$order ) return false;

        $point_value = $this->getLoyalityPointValue();
        $point_recursive = $this->getLoyalityPointRecursive();

        $tbl = Db::get()->es_loyality_management();

        $order_value = $order['total'];
        
        $calculate_result = (int)$order_value * ( self::POINT / $point_value );

        $data = [
            'user_id' => $this->user->id,
            'es_orders_id' => $order['id'],
            'points' => $calculate_result
        ];

        if ( isset( $order['loyality_points'] ) && $order['loyality_points'] !== NULL )
        {
            $sale_promocodes = json_decode( $order['loyality_points'], true );
            $sale = parseInt( $sale_promocodes['value'] ) / -100;
            $loyality_points_value = $sale;
        }

        if( isset( $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints'] ) && $_SESSION[md5( APPSERVERNAME ) . '-cart-loyalitypoints']['used'] > 0 )
            $data = [
                'user_id' => $this->user->id,
                'es_orders_id' => $order['id'],
                'points' => $loyality_points_value * $point_recursive
            ];

        if( !empty( $tbl->where( $order['id'] )->fetch('id') ) ) return false;
        $tbl->insert( $data );

    }

    public function getPointsByOrderNumber( int $order_number, $user_id )
    {
        $result = [];

        $point_value = $this->getLoyalityPointValue();

        if ( empty( $order = $this->getByOrderNumber( $order_number ) ) ) $order = $this->getOne( $order_number );

        if( !$this->user->isLoggedIn() ) return $result;

        $loyality_points_used = Db::get()->es_loyality_management( 'user_id', $user_id )->where( 'es_orders_id', $order['id'] )->fetch('points');
        
        if( $loyality_points_used )
        $result = [
            'value' => $loyality_points_used,
            'order_discount' => ( $loyality_points_used * ( self::POINT / $point_value ) )
        ];

        return $result;
    }

    public function getLoyalityPoints()
    {
        $result = [];

        if( !$this->user->isLoggedIn() ) return $result;

        if( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
        {
            $business = new B2b;
            if( $business->isBusinessman( $this->user->id ) ) return false;
        }

        $byPoints = Db::get()->es_loyality_management( 'user_id', $this->user->id )->fetchPairs('es_orders_id','es_orders_id');
        $orders = Db::get()->es_orders()->where( 'id', $byPoints );

        foreach( $orders as $order )
        {
            $result['points_by_order'][] = [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'value' => $order['total'],
                'points' => $order->es_loyality_management( 'user_id', $this->user->id )->sum('points')
            ];
        }
        $result['total_points'] = Db::get()->es_loyality_management( 'user_id', $this->user->id )->sum('points');
        
        return $result;
    }
}