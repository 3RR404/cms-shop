<?php

namespace Weblike\Cms\Shop;

use Weblike\Cms\Core\Functions\Others;

class Currency 
{
    const EURO = 'EUR';

    const CZECH_KRONE = 'CZK';

    const HUNGARISH_FORINT = 'HUF';

    const GREAT_BRITAIN = 'GBP';

    const POLISH_ZLOTY = 'PLZ';

    public static $def_currency = self::EURO;
    protected static $currencies;

    
    function __construct()
    {
        $_SESSION[md5( APPSERVERNAME ).'-currency'] = self::$def_currency;
    }

    public static function load()
    {
        $currency = json_decode(file_get_contents(ROOT_DIR . '/Currency.json'), true);
        $currencyConfig = json_decode(file_get_contents(ROOT_DIR . '/CurrencyConfig.json'), true);

        $currs = array();
        $cdef = $currencyConfig['default'];
        $currency[$cdef]['key'] = '_eur';

        foreach($currencyConfig['enabled'] as $key) {
            $currs[$key] = $currency[$key];
        }

        self::$currencies = $currs;
        self::$def_currency = $cdef;
    }
    
    public static function get() {
        return self::$currencies;
    }

    public static function setActiveCurrency( $currency ) {
        $_SESSION[md5( APPSERVERNAME ).'-currency'] = $currency;
    }

    /**
     * Vypise aktivnu menu
     */
    public static function getActiveCurrency() {
        return isset($_SESSION[md5( APPSERVERNAME ).'-currency']) ? $_SESSION[md5( APPSERVERNAME ).'-currency'] : self::detectCurrency();
    }

    static function getDefault() {
        return self::$def_currency;
    }

    public static function detectCurrency()
    {
        $curr = isset($_SESSION[md5( APPSERVERNAME ).'-currency']) ? $_SESSION[md5( APPSERVERNAME ).'-currency'] : self::$def_currency;

        if(isset($_SESSION[md5( APPSERVERNAME ).'-currency'])) {
            $curr = $_SESSION[md5( APPSERVERNAME ).'-currency'];
        } else {
            $curr = self::$def_currency;
        }

        return $_SESSION[md5( APPSERVERNAME ).'-currency'] = $curr;
    }

    public static function getAll()
    {
        return json_decode( file_get_contents( ROOT_DIR . '/Currency.json' ), true ); 
    }

    /**
     * Vratenie hodnoty podla meny
     * 
     * @param string|array|object $stringOrArray
     * @param string $key - default FALSE
     * @param string $currency - default FALSE
     * 
     * @return mixed|string
     */
    public static function decode( $stringOrArray, $key = false, $currency = false )
    {
        if(gettype($stringOrArray) === 'object') {
            $stringOrArray = iterator_to_array($stringOrArray);
        }
    
        if(gettype($stringOrArray) === 'array') {
            // Ak sa posiela POLE, tak dekoduje multijazycnost (ak existuje)
            $data = @$stringOrArray[$key];
    
            if( gettype( $data ) === 'array' )
            {
                $currency = $currency === false ? $_SESSION[md5( APPSERVERNAME ).'-currency'] : $currency;
                return @Others::whoolPrice( $data[$currency], 2 ); 
            }
            else
            {
                $decoded = @json_decode($data, true);
    
                if(!!$decoded && gettype( $decoded ) === 'array') {
                    
                    $currency = $currency === false ? $_SESSION[md5( APPSERVERNAME ).'-currency'] : $currency;
                    return @Others::whoolPrice( $decoded[$currency], 2 );
                } else {
                    return $data;
                }
            }
        }
    
    }

}