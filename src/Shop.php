<?php 

namespace Weblike\Cms\Shop;

use Latte\Engine;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Configuration;
use Weblike\Cms\Shop\Interfaces\IShop;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Cms\Shop\Utilities\Strings;

/**
 * Vytvorenie tabuliek pre spravne fungovanie pluginu,\
 * nacitanie tabulky a urcenie globalnej premennej pre rozsirenia
 */
class Shop implements IShop
{
    public $currency;
    
    public static $api_valid = false;

    protected $product_concepts_table;
    protected $product_parameters_table;

    public static $order_num_start = 2019;
    public static $order_num_count = 5;
    public static $order_pos = 1;

    protected $payment_config = [];

    /** @var string TAX_RATE - tax rate value  */
    const TAX_RATE = 20;

    /** @var string TAX_RATE - tax rate value  */
    const TAX_RATE_LOW = 10;

    /** @var string GO_ID - gopay id  */
    const GO_ID = '8046237969';

    /** @var string CID - gopay client id  */
    const CID = '1843868312';
    
    /** @var string CSECRET - gopay client secret key  */
    const CSECRET = 'Tc8z5v5C';

    /** @var bool DEVELOPMENT - development mode  */
    const DEVELOPMENT = false;

    /** @var bool PRODUCTION - production mode  */
    const PRODUCTION = true;

    /** @var bool PRODUCTION / DEVELOPMENT - production or dev mode  */
    const AUTO = null;

    /** @var integer LOYALITY_MANAGEMENT vernostny program */
    const LOYALITY_MANAGEMENT = 0;

    function __construct() {}

    public function setPayment( string $repository, array $connection_data ) : void
    {
        $this->payment_config = [
            'repository' => $repository,
            $connection_data
        ];
    }

    public function getConfig()
    {
        return $this;
    }

    public function __get( $name )
    {
        $result = Db::get()->getRepository( Configuration::class )->findOneBy(['name' => $name]);

        if ( $result )
            return $result->getValue();
            
        return null;
    }

}