<?php

namespace Weblike\Cms\Shop\Utilities;

use TableCreator\Src\TableCreator;
use Weblike\Cms\Core\Db;

class Price extends \Weblike\Strings\Others
{

	public static function format($number, ?string $amount = ' €', ?int $decimals = 2, ?string $dec_point = ',', ?string $thousands_sep = ' '): string
	{
		$formated = \number_format($number, $decimals, $dec_point, $thousands_sep);

		if (!empty($amount)) $formated = \number_format($number, $decimals, $dec_point, $thousands_sep) . $amount;

		return $formated;
	}

	public static function whoolPrice($number, ?int $round_to = 2, ?bool $round_up = true)
	{
		$round_up = \PHP_ROUND_HALF_DOWN;

		if ($round_up === true) $round_up = \PHP_ROUND_HALF_UP;

		return \round($number, $round_to, $round_up);
	}

	public static function priceFormat($price)
	{
		return self::format(self::whoolPrice($price));
	}

	public static function sellingPrice($number): float
	{
		return self::whoolPrice($number);
	}

	public static function taxFrom($price, int $tax_amount): float
	{
		return (float)$price / (($tax_amount + 100) / 100);
	}

	public static function taxPlus($price, ?int $tax_amount): float
	{
		return (float)$price * (($tax_amount + 100) / 100);
	}
}
