<?php

namespace Weblike\Cms\Shop\Utilities;

use Weblike\Cms\Shop\ProductManager;
use Weblike\Strings\Texts as StringsTexts;

class Texts extends StringsTexts
{
    public static function status( ?int $id = null, ?string $key = null )
    {
        if ( $id === null ) return ProductManager::STATUS;

        if ( $key === null ) return ProductManager::STATUS[$id];
        
        return ProductManager::STATUS[$id][$key];
    }

    public static function availability( ?string $type = null, ?string $key = null )
    {
        if ( $type === null ) return ProductManager::AVAILABILITY;

        if ( $key === null ) return ProductManager::AVAILABILITY[$type];
        
        return ProductManager::AVAILABILITY[$type][$key];
    }

}