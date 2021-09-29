<?php

namespace Weblike\Cms\Shop;

use Weblike\Plugins\Comments;
use Weblike\Plugins\Paginator;
use Weblike\Cms\Shop\Utilities\Strings;
use Weblike\Plugins\User;
use Weblike\Cms\Core\App;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Interfaces\IProduct;

/**
 * ## Sprava vsetkych produktov ##
 * - Rozsiruje triedu Shop
 * - Urcene pre insert, update a zobrazenie
 * - Pre spravne fungovanie musi byt nacitana trieda Shop, ako plugin.\
 *   Trieda Shop vytvori tabulky potrebne pre spravne fungovanie.
 */
class Products extends Shop implements IProduct
{
    public $usePaginator = false;
    public $paginatorCtrl;
    public $perPage;
    public $queryString;
    public $paginatorData;

    protected $products_table;

    function __construct()
    {
        $this->products_table = 'es_product';
    }

    /**
     * ## Zobrazenie vsetkych produktov
     * - vracia hodnoty v objekte
     * 
     * @param int|bool $id - __(optional)__
     *     - ak je odoslane id, bude vybrany len produkt s tymto id
     * @param bool $default - __(optional)__
     *     - ak nie je admin, zobrazi len produkty ktore maju 'avaibility' = 1 a 'status' = 1
     * 
     * @return object;
     */
    public function show( $id = false, bool $default = false, $limit = false, $where = false, $random_ordering = false )
    {
        // Vyberie vsetky produkty
        if( class_exists( 'Weblike\\Plugins\\Comments' ) ) $comments = new Comments(true);
        $table = Db::get()->{$this->products_table}();
        $item = [];

        // vyberie produkt podla ID
        if( $id !== FALSE && !is_array( $id ) )
        {
            $table = $table->where( 'id', $id );
            
            if( $default === FALSE )
            {
                $item = (object)$table->fetch();
                
                $table = $item;
            }

        }

        // vyberie produkt, kde je avaibility 1 a status 1
        // avaibility je dostupnost produktu
        // status moze byt od 1 do 3
        if( $default === TRUE ) {
            $table = $table
                ->where('status', self::PUBLISHED)
                ->where('avaibility', self::AVAILABLE);

                if( !$random_ordering ) $table = $table->order('product_code ASC');
                
            if( $where )
            {
                switch( gettype($where) )
                {
                    case 'string' : $table = $table->where( $where ); break;
                }
            }

            if( $random_ordering )
            {
                $table = $table->order('RAND()');
            }

            if( $limit > 1 ) $table = $table->limit( $limit );

            if( Strings::getGlobalSett()['show_null_stock'] === 'off' ) $table = $table->where('stock > ' . 0);

            if( !$id && $default && $this->usePaginator )
            {
                $paginator = new Paginator;
                $paginator->setController( $this->paginatorCtrl );       // Aktualny controller na ziskanie lokacie
                $paginator->setTable( $table );                          // Nastavenie tabulky s udajmi
                $paginator->setLimit( $this->perPage );                  // Kolko clankov zobrazit na 1 stranu
                $paginator->setQueryName( $this->queryString );

                $this->paginatorData = $paginator->getData();
                $table = $paginator->fetch();
            }

            if( $id === FALSE && !is_array( $id ) )
            {

                foreach( $table as $product )
                {
                    $product['reputation'] = 0;
                    $sale = new Sale;
                    $product_sale = $sale->show( $product['id'] ); // kontrola v zlavach
                    if( $product_sale )
                    {
                        $product['price'] = $product_sale['new_price'];
                        $product['old_price'] = $product_sale['old_price'];
                    }
                    if( class_exists( 'App\\Plugins\\Comments' ) ) $product['reputation'] = \whoolPrice($comments->getAverage( $product['id'] )['reputation'],0);
                    if ( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
                    {
                        $business = new B2b;
                        if( $group = $business->isInGroup() ) $product['price'] = $business->productPriceByGroup( $product['id'], $group )?:$product['price'];
                    }
                }

                if( $limit === 1 ) return $table->fetch();

            } else if( $id && !is_array( $id ) ){
                
                $product = $table->fetch();
                if( isset( $product['id'] ) )
                {
                    $product['reputation'] = 0;
                    $sale = new Sale;
    
                    if( $product_sale = $sale->show( $product['id'] ) )// kontrola v zlavach
                    {
                        $product['price'] = $product_sale['new_price'];
                        $product['old_price'] = $product_sale['old_price'];
                    }
                    if( class_exists( 'App\\Plugins\\Comments' ) ) $product['reputation'] = \whoolPrice($comments->getAverage( $product['id'] )['reputation'],0);
                    if ( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
                    {
                        $business = new B2b;
                        if( $group = $business->isInGroup() ) $product['price'] = $business->productPriceByGroup( $product['id'], $group )?:$product['price'];
                    }
                    $table = $product;
                }
                
            } else if( is_array($id) && $default ) // viac ID IN( id..., id... )
            {
                $table = $table->where( 'id', $id );
                
                if( $this->usePaginator )
                {
                    $paginator = \Plugin::get('Paginator');
                    $paginator->setController( $this->paginatorCtrl );       // Aktualny controller na ziskanie lokacie
                    $paginator->setTable( $table );                          // Nastavenie tabulky s udajmi
                    $paginator->setLimit( $this->perPage );                  // Kolko clankov zobrazit na 1 stranu
                    $paginator->setQueryName( $this->queryString );

                    $this->paginatorData = $paginator->getData();
                    $table = $paginator->fetch();
                }
                
                foreach( $table as $product ) // kontrole kazdeho jedneho produktu odoslaneho v poli
                {
                    $sale = new Sale;
                    $product_sale = $sale->show( $product['id'] ); // kontrola v zlavach
                    if( $product_sale )
                    {
                        $product['price'] = $product_sale['new_price'];
                        $product['old_price'] = $product_sale['old_price'];
                    }
                    if( class_exists( 'App\\Plugins\\Comments' ) ) $product['reputation'] = \whoolPrice($comments->getAverage( $product['id'] )['reputation'],0);
                    else $product['reputation'] = 0;
                    if ( \class_exists( 'App\\Shop\\BusinessToBusiness' ) )
                    {
                        $business = new B2b;
                        if( $group = $business->isInGroup() ) $product['price'] = $business->productPriceByGroup( $product['id'], $group )?:$product['price'];
                    }
                }
                if( $limit === 1 ) return $table->fetch();
                
                return $table;
            }

            return $table;
        }
        else return $table;

    }

    public function usePaginator( $controller, int $limit = 0, string $query = 'page' )
    {
        $this->usePaginator = true;
        $this->paginatorCtrl = $controller;
        $this->perPage = $limit;
        $this->queryString = $query;
    }
    
    function getData()
    {
        return $this->paginatorData;
    }

    public function getTopReleated( $limit = false )
    {
        if( class_exists( 'App\\Plugins\\Comments' ) )
        {
            $comments = new Comments(true);
            $comm_ids = $comments->getTopReleated( true, $limit );

            
            foreach( $comm_ids as $product )
            {
                $p[] = $this->show( $product['pageid'], true );
            }
            if( !empty( $p ) )
                return array_filter($p, [$this, 'filter_empty'] );
        }
    }

    public function getRecommended( $limit = false, int $exclude_product_id = 0 )
    {
        $where_string = 'recommended = 1';
        if( $exclude_product_id > 0 ) $where_string .= ' AND id <> ' . $exclude_product_id;

        return $this->show( false, true, $limit, $where_string, true );
    }

    public function inSale( $limit = false, int $exclude_product_id = 0 )
    {
        if( class_exists( 'App\\Shop\\Sale' ) )
        {
            $sale = new Sale;
            $sales = $sale->products_in_sale( $limit );
            foreach( $sales as $p_in_sale )
            {
                $p[] = $this->show( $p_in_sale['es_product_id'], true );
            }
            
            if( !empty( $p ) )
                return array_filter($p, [$this, 'filter_empty'] );
        }
    }

    function filter_empty( $p )
    {
        if( isset( $p['id'] ) ) return $p;
    }

    public function getIdBySlug( $slug, $default = false )
    {
        $lang = App::getActiveLang();
        $id = Db::get()->{$this->products_table}()->where("slug LIKE '{%\"{$lang}\":\"{$slug}\"%}'")->fetch('id');
        return $this->show( $id, $default );
    }

    /**
     * ## Minimalna hodnota v tabulke
     * @param string $column - nazov bunky, kde ma hladat min. hod.
     * 
     * @return string
     */
    public function minimalVal( string $column = '' )
    {
        return $this->show()->min( $column );
    }

    /**
     * Maximalna hodnota v tabulke
     * @param string $column - nazov bunky, kde ma hladat max. hod.
     * 
     * @return string
     */
    public function maximalVal( string $column = '' )
    {
        return $this->show()->max( $column );
    }

    /**
     * ## Najvyzsia cena v tabulke
     */
    public function maximalPrice( string $currency )
    {
        $all = $this->show();
        $price = [];

        foreach( $all as $val )
        {
            $prices = json_decode( $val['price'], true );
            $price[] = $prices[ $currency ];
        }

        return 0;//max( array_filter( $price ) );
        

    }

    /**
     * Ulozi odoslane data do tabulky
     * 
     * @param int|boolean $id - ak je odoslane id spravi UPDATE $data inak INSERT
     * @param array $data - pole s datami na vlozenie
     * 
     * @return boolean
     */
    public function save( $id = false, $data = [] )
    {
        if( $id ){
            return Db::get()->{$this->products_table}( 'id', $id )->update( $data );
        } else {
            return Db::get()->{$this->products_table}()->insert( $data );
        }
        return false;
    }

    public function duplicant( int $product_id )
    {
        $product_data = Db::get()->es_product()->where( 'id', $product_id )->fetch();
        // $product_slug = Db::get()->es_product()->where( "slug LIKE '{%\"sk\":\"".$product_data['slug']."%}'" )->count();
        $product_images = Db::get()->es_product_images()->where( 'es_product_id', $product_id );
        $product_categories = Db::get()->es_product_categories()->where( 'es_product_id', $product_id );
        $product_params = Db::get()->es_product_parameters()->where( 'es_product_id', $product_id );

        foreach ( $product_data as $column => $product_fields )
        {
            $product[ $column ] = $product_fields;
            if ( $column === 'id' ) unset( $product['id'] );
            if ( $column === 'status' ) $product['status'] = self::DRAFT;
            if ( $column === 'avaibility' ) $product['avaibility'] = self::UNAVAILABLE;
            
        }

        $duplicated = Db::get()->es_product()->insert( $product );
        
        foreach ( $product_images as $image )
        {
            foreach ( $image as $img_column => $img_data )
            {
                $duplicated_images[ $img_column ] = $img_data;
                if ( $img_column === 'id' ) unset( $duplicated_images['id'] );
                if ( $img_column === 'es_product_id' ) $duplicated_images['es_product_id'] = $duplicated['id'];
            }
            Db::get()->es_product_images()->insert( $duplicated_images );
        }
        
        
        foreach ( $product_categories as $category )
        {
            foreach ( $category as $cat_column => $cat )
            {
                $duplicated_categories[ $cat_column ] = $cat;
                if ( $cat_column === 'es_product_concepts_id' ) unset( $duplicated_categories['es_product_concepts_id'] );
                if ( $cat_column === 'es_product_id' ) $duplicated_categories['es_product_id'] = $duplicated['id'];
            }
            Db::get()->es_product_categories()->insert( $duplicated_categories );
        }
        
        foreach( $product_params as $parameter )
        {
            foreach ( $parameter as $param_column => $param )
            {
                $duplicated_param[ $param_column ] = $param;
                if ( $param_column === 'es_product_id' ) $duplicated_param['es_product_id'] = $duplicated['id'];
            }
            Db::get()->es_product_parameters()->insert( $duplicated_param );
        }

        return $duplicated;
    }

    public function save_params( $id, $data )
    {

        $tb = Db::get()->{$this->product_parameters_table}();
        if( $tb->where( 'id', $id )->fetch('id') )
        {
            $tb->where( 'id', $id )->update( $data );
        }
        else
        {
            $data['id'] = $id;
            $tb->insert( $data );
        }

    }


    /**
     * Ulozi data ako koncept do separatnej tabulky
     * 
     * @param int $id - (optional) defaultne nastavene na false
     * @param array|object $data
     * @return bool
     */
    public function save_concept( $id = false, $data = [] )
    {
        $ex_concept_id = Db::get()->{$this->product_concepts_table}('id', $id)->fetch('id');
        $ex_post_id = Db::get()->{$this->products_table}( 'id', $id )->fetch('id');

        if( empty($ex_post_id) ){
            $this->save( false, $data );
            $last = Db::get()->{$this->products_table}()->order('id DESC')->limit(1)->fetch('id');
            $data['id'] = $last;
        }

        if( !isset( $data['id'] ) ) $data['id'] = $id;
        if( empty($data['id'] ) ) $data['id'] = $id;
        unset( $data['status'] );

        if( !empty( $ex_concept_id ) ){
            Db::get()->{$this->product_concepts_table}('id', $id)->update( $data );
            return true;
        } else {
            Db::get()->{$this->product_concepts_table}()->insert( $data );
            return true;
        }
        return false;
    }


    /**
     * Vrati associativne pole riadku v DB podla ID => $id
     * 
     * @param int $id
     * @return array|bool
     */
    // public function has_concept( $id )
    // {
    //     if( !empty($concept = Db::get()->{$this->product_concepts_table}('id', $id)->fetch()) ){
    //         $concept['id'] = $id;
    //         return $concept;
    //     }
    //     return false;
    // }

    public function products(){
        return $this->products_table;
    }

    /**
     * ## Pridat do Wishlist
     * @param integer $product_id id produktu
     * @param integer $user_id id uzivatela
     * 
     * @return void
     */
    public function add_to_wishlist( int $product_id = 0, int $user_id = 0 )
    {
        if ( $product_id === 0 && $user_id === 0  ) return false;
        if ( !isset( $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] ) ) $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] = '';
        $user_wishlist = $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'];
        $products = explode( ';', $user_wishlist );
        $products = array_filter( $products );
        
        if( in_array( $product_id, $products ) ) return false;
        \array_push( $products, $product_id );
        $products = implode( ';', $products );

        setcookie( md5( APPSERVERNAME )."-wishlist", "{$products}", time() + 60 * 60 * 24 * 365.25, "/");
    }

    /**
     * ## Odobrat z Wishlist
     * @param integer $product_id id produktu
     * @param integer $user_id id uzivatela
     * 
     * @return void
     */
    public function remove_from_wishlist( int $product_id = 0, int $user_id = 0 )
    {
        if ( $product_id === 0 && $user_id === 0  ) return false;
        if ( !isset( $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] ) ) $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] = '';
        $user_wishlist = $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'];
        $products = explode( ';', $user_wishlist );

        if( in_array( $product_id, $products ) )
        {
            $key = array_search( $product_id, $products );
            unset( $products[$key] );
        }
        $products = array_filter( $products );
        $products = implode( ';', $products );

        setcookie( md5( APPSERVERNAME )."-wishlist", "{$products}", time() + 60 * 60 * 24 * 365.25, "/");
    }

    /**
     * ## Zistenie, ci je v zozname
     * @param integer $product_id id produktu
     * @param integer $user_id id uzivatela
     * @return bool
     */
    public function is_wished( int $product_id = 0 )
    {
        if ( $product_id === 0 ) return false;

        if ( !isset( $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] ) ) $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] = '';
        $user_wishlist = $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'];
        $products = explode( ';', $user_wishlist );

        if( in_array( $product_id, $products ) ) return true;
        return false;
    }

    /**
     * ## Zistenie, ci je v zozname
     * @static
     * @param integer $product_id id produktu
     * @param integer $user_id id uzivatela
     * @return bool
     */
    public static function isWished( int $product_id = 0 )
    {
        if ( $product_id === 0 ) return false;

        if ( !isset( $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] ) ) $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] = '';
        $user_wishlist = $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'];
        $products = explode( ';', $user_wishlist );

        if( in_array( $product_id, $products ) ) return true;
        return false;
    }

    /**
     * ## Zoznam zelani
     * @param integer $user_id
     * @return array
     */
    public function get_wishlist( int $user_id )
    {
        if ( $user_id === 0 ) return false;

        if ( !isset( $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] ) ) $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] = '';
        $user_wishlist = $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'];
        $user_wishlist_products = explode( ';', $user_wishlist );

        return $user_wishlist_products;

    }

    /**
     * ## Zoznam zelani
     * @param integer $user_id
     * @static
     * @return array
     */
    public static function getWishlist( int $user_id )
    {
        if ( $user_id === 0 ) return false;

        if ( !isset( $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] ) ) $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] = '';
        $user_wishlist = $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'];
        $user_wishlist_products = explode( ';', $user_wishlist );

        return $user_wishlist_products;
    }

    protected function clear_wishlist()
    {
        unset( $_COOKIE[ md5( APPSERVERNAME ) . '-wishlist'] );
        setcookie( md5( APPSERVERNAME ) . '-wishlist', null, time() - 3600, '/' );
        return true;
    }

}