<?php

namespace Weblike\Cms\Shop;

use Weblike\Plugins\TableCreator;

class HeurekaXMLFeed
{

    function __construct()
    {
        $tbl_heureka = new TableCreator('es_heureka');
        $tbl_heureka->integer('es_product_id');
        $tbl_heureka->tinyint('active');
        $tbl_heureka->integer('heureka_cat_id');
        $tbl_heureka->integer('categories_id');
        $tbl_heureka->up();
        
    }

    /**
     * ## Pridanie do feedu
     */

    /**
     * ## Odobranie z FEEDU
     */

    function heurekaGetFullName($object)
    {
        return isset($object->CATEGORY_FULLNAME) ? (string) $object->CATEGORY_FULLNAME : (string) $object->CATEGORY_NAME;
    }


    public function get_categories_tree( $items, $int = 0 )
    {

        foreach( $items as $category ) {
            
            $c[ $int ] = [
                'id' => (string)$category->CATEGORY_ID, 
                'name' => (string)$category->CATEGORY_NAME,
                'childs' => [],
                'is_parent' => false
            ];
            $parent[ $int ] = $category['is_parent'];

            if( $category->CATEGORY )
            {
                $c[ $int ]['childs'] = $this->get_categories_tree( $category->CATEGORY, $int );
                $c[ $int ]['is_parent'] = $parent[$int];
            }
            
            $int++;
        }

        return $c;
    }

    public static function isChild( $item )
    {
        return false;
    }

    function heurekaGet($plainPrint = false)
    {
        $data = simplexml_load_file( "https://www.heureka.sk/direct/xml-export/shops/heureka-sekce.xml" );
        $kategorie = array();

        foreach ($data as $cat) {
            $children = array();

            if (isset($cat->CATEGORY)) {

                foreach ($cat->CATEGORY as $ch_cat) {
                    $deep = array();

                    if (isset($ch_cat->CATEGORY)) {
                        foreach ($ch_cat->CATEGORY as $de_cat) {
                            if ($plainPrint) {
                                $kategorie[(string) $de_cat->CATEGORY_ID] = $this->heurekaGetFullName($de_cat);
                            } else {
                                $deep[] = array(
                                    'id' => (string) $de_cat->CATEGORY_ID,
                                    'name' => (string) $de_cat->CATEGORY_NAME,
                                    'children' => array(),
                                );
                            }
                        }
                    }

                    if ($plainPrint) {
                        $kategorie[(string) $ch_cat->CATEGORY_ID] = $this->heurekaGetFullName($ch_cat);
                    } else {
                        $children[] = array(
                            'id' => (string) $ch_cat->CATEGORY_ID,
                            'name' => (string) $ch_cat->CATEGORY_NAME,
                            'children' => $deep,
                        );
                    }
                }
            }

            if ($plainPrint) {
                $kategorie[(string) $cat->CATEGORY_ID] = $this->heurekaGetFullName($cat);
            } else {
                $kategorie[] = array(
                    'id' => (string) $cat->CATEGORY_ID,
                    'name' => (string) $cat->CATEGORY_NAME,
                    'children' => $children,
                );
            }
        }

        return $kategorie;
    }

    function heurekaGetCat($id)
    {
        $plain = $this->heurekaGet(true);
        $name = @$plain[$id] ?: '';
        $name = trim($name);

        return str_replace('Heureka.sk | ', '', $name);
    }
}
