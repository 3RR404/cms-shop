<?php

namespace Weblike\Cms\Shop;

/**
 * ## Statistiky
 * 
 * - statistiky / zhromazdenie dat z objednavok a faktur
 * - vypisy statistik za aktualne / resp/ zvolene obdobie
 * - rok urcuje, za ake obdobie sa maju statistoky vyobrazit
 * - na plne fungovanie vyuziva triedy App\Shop\Orders a App\Shop\Invoices 
 * 
 * @param int|string $year 
 */
class Stats
{
    protected $invoices;
    protected $order;
    protected $year;

    function __construct( $invoices, $orders, $year = false )
    {
        $this->invoices = $invoices;
        $this->order = $orders;
        $this->year = $year ? $year : date( 'Y' );
    }

    /**
     * @return array roky v objednavkach
     */
    public function getBusinessYear()
    {
        return Db::get()->es_orders()->select( "DISTINCT DATE_FORMAT(created_at, '%Y') AS 'year'" );
    }
 
    /**
     * @param int|string $year
     * @return void
     */
    public function setYear( $year )
    {
        $this->year = $year;
    }

    /**
     * @see Orders::getMax()
     */
    public function getOrdersIncome()
    {
        return $this->order->getMax();
    }

    /**
     * @see Invoices::getTotalIncome()
     */
    public function getTotalIncome()
    {
        return $this->invoices->getTotalIncome( $this->year );
    }

    /**
     * @see Invoices::getTotalIncomeCurrentYear()
     */
    public function getTotalIncomeCurrentYear()
    {
        return $this->invoices->getTotalIncomeCurrentYear();
    }

    /**
     * @see Invoices::getTotalIncomeLastYear()
     */
    public function getTotalIncomeLastYear()
    {
        return $this->invoices->getTotalIncomeLastYear();
    }

    /**
     * @see Invoices::getTotalRefunded()
     */
    public function getRefunded()
    {
        return $this->invoices->getTotalRefunded( $this->year );
    }

    /**
     * @see Invoices::getIncome()
     */
    public function getIncome()
    {
        return $this->invoices->getIncome( $this->year );
    }

    /**
     * @see Invoices::getIncome()
     */
    public function getCurrentMonthIncome( $year = false, $month = false )
    {
        if( $month === FALSE ) $month = date( 'm' );
        return $this->invoices->getIncome( $year, $month, $month );
    }

    /**
     * @see Invoices::getIncome()
     */
    public function getLastMonthIncome( $year = false, $month = false )
    {
        if( $month === FALSE ) $month = date( 'm', strtotime( "-1 month" ));
        return $this->invoices->getIncome( $year, $month, $month );
    }

    public function getExpectedIncome()
    {
        return $this->order->getExpectedIncome( $this->year );
    }

    public function todaySellOut()
    {
        return $this->order->getTodaysOrders();
    }

    public function getTotalIncomePercentageGrowth()
    {
        $last_year = $this->year - 1;

        return $this->invoices->getTotalIncomePercentageGrowth( $last_year, $this->year );
    }

    public function getExpectedIncomePercentageGrowth()
    {
        $last_year = $this->year - 1;

        return $this->invoices->getExpectedIncomePercentageGrowth( $last_year, $this->year );
    }

    /**
     * ## Predaj vyrobkov / mes
     * @param int|string|bool $month cislo mesiaca
     * @return array - predaj v dnoch
     */
    public function salesInMonth( $month = false )
    {
        $saleInDay = [];
        
        if( $month === FALSE ) $month = date('n');

        $year = date('Y');

        $date_from = "$year-$month-01 00:00:00";
        $date_to = "$year-$month-31 23:59:59";

        if( $year === date('Y') && $month === date('m') ) $month = date('Y-m-d H:i:s');

        $sales = Db::get()->es_orders()->where( "created_at >= '$date_from' AND created_at <= '$date_to'" );
        
        for( $int_key = 1; $int_key <= cal_days_in_month( CAL_GREGORIAN, $month, $year ); $int_key++ )
        {
            $saleInDay[ $int_key ] = 0;
        }

        foreach( $sales as $sale )
        {
            $day_key = date( 'j', strtotime( $sale['created_at'] ) );

            $saleInDay[ $day_key ] = $saleInDay[ $day_key ] + 1;
        }

        return $saleInDay;
    }

    /**
     * ## Predaj celkom za aktualny mesiac
     * @return string|int
     */
    public function totalSalesInMonth()
    {
        $year = date( 'Y' );
        $month = date( 'm' );

        $date_from = "$year-$month-01 00:00:00";
        $date_to = "$year-$month-31 23:59:59";

        return Db::get()->es_orders()->where( "created_at >= '$date_from' AND created_at <= '$date_to'" )->count();
    }

    /**
     * ## Predaj za 24h
     * @return array - predaj v hodinach 0 - 23
     */
    public function salesInLastTwentyFourHours()
    {
        $saleIn24hours = [];
        
        $date_from = date( "Y-m-d H:i:s", strtotime( '-24 hours' ) );
        $date_to = date( "Y-m-d H:i:s" );

        $sales = Db::get()->es_orders()->where( "created_at >= '$date_from' AND created_at <= '$date_to'" );
        
        for( $int_key = 0; $int_key <= 23; $int_key++  )
        {
            if( $int_key < 10 ) $int_key = "0$int_key";
            $saleIn24hours[ $int_key ] = 0;
        }

        foreach( $sales as $sale )
        {
            $hour_key = (string)date( 'H', strtotime( $sale['created_at'] ) );

            $saleIn24hours[ $hour_key ] = $saleIn24hours[ $hour_key ] + 1;
        }

        return $saleIn24hours;
    }

    /**
     * ## Celkom /24h v ks
     * - predanych kusov celkom za 24h
     * @return string|int
     */
    public function totalSalesInLastTwentyFourHours()
    {
        $date_from = date( "Y-m-d H:i:s", strtotime( '-24 hours' ) );
        $date_to = date( "Y-m-d H:i:s" );

        return Db::get()->es_orders()->where( "created_at >= '$date_from' AND created_at <= '$date_to'" )->count();
    }


    /** 
     * ## Najpredavanejsie produkty
     * - vrati zoznam usporiadany podla predajov
     * - produkty v objednavkach su predane produkty
     * - vratene produkty a storna sa nezapociatvaju, neberu do uvahy
     * 
     * @param bool $monthly - mesacny vypis / false = rocny
     * @param integer|string|bool $month - mesiac z ktoreho vypise zoznam
     * @return array
     */
    public function bestSeller( bool $montly = true, $month = false )
    {
        $products = [];
        if ( $this->year !== ( $year = date( 'Y' ) ) ) $year = $this->year;
        if ( $montly === TRUE )
        {
            if ( $month === FALSE ) $month = date( 'm' );
            $date_from = "$year-$month-01 00:00:00";
            $date_to = "$year-$month-31 23:59:59";
        } else 
        {
            $date_from = "$year-01-01 00:00:00";
            $date_to = "$year-12-31 23:59:59";
        }


        $orders = Db::get()->es_orders()->where( "created_at >= '$date_from' AND created_at <= '$date_to'" );
        $product_plugin = new Products;

        foreach( $orders as $order)
        {
            if( $order['products'] )
            {
                $all_products = json_decode( $order['products'], true );
                if( $all_products )
                {
                    foreach( $all_products as $product )
                    {
                        if( isset($product['product_id']) ) $product_key = $product['product_id'];
                        if( !isset( $products[ $product_key ]['sells'] ) ) $products[ $product_key ]['sells'] = 0;
                        $products[ $product_key ]['sells'] = $products[ $product_key ]['sells'] + 1;
                        foreach( $product_plugin->show( $product_key ) as $key => $data ) $products[ $product_key ][ $key ] = $data;
                    }
                }
            }
        }

        arsort( $products );

        $products = array_slice( $products, 0, 5 );

        return $products;
    }

    public function getProductMonthSale()
    {
        $months = [];

        
        
        return $months;
    }

    public static function completeStats( string $date_from = "2020-01-01", string $date_to = "2020-12-31" )
    {
        $result = [];

        $from_date = "$date_from 00:00:00";
        $to_date = "$date_to 23:59:59";

        $orders = Db::get()->es_orders()->where( "created_at >= '$from_date'" )->and( "created_at <= '$to_date'" );

        $result = $orders;

        return $result;
    }
}