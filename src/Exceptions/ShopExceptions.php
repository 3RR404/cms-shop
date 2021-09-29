<?php

namespace Weblike\Cms\Shop\Exceptions;

use Exception;

class ShopException extends Exception {}

class ShopProductNotFoundOnStockException extends ShopException
{
    protected $message = "Maly objem na sklade !";
}

class ShopProductUnexpectedFallException extends ShopException
{
    protected $message = 'Nepodarilo sa pridat produkt ! Nespecifikovana chyba !';
}