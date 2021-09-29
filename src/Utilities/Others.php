<?php

namespace Weblike\Cms\Shop\Utilities;

use TableCreator\Src\TableCreator;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Configuration;
use Weblike\Cms\Shop\ShopConfiguration;

class Others extends \Weblike\Strings\Others
{

    public static function globalSettings( string $name ) : ?Configuration
    {
        return (new ShopConfiguration)->getConfig( $name );
    }

}