<?php

namespace Weblike\Cms\Shop;

use Weblike\Cms\Core\Db;

/**
 * Zlavy, kupony a promo kody
 * 
 */
class SaleManager
{

	function __construct()
	{
	}

	function registerPromoCode($code)
	{
		$fields = [
			'name' => '',
			'code' => $code,
			'price' => null,
			'percentage' => 5
		];
		$promocode = new PromoCode;
		$promocode->save($fields);
	}

	/**
	 * Zistenie existencie promokodu
	 * 
	 * @param string $code
	 * @return void|bool
	 */
	function promoCodeExists(string $code)
	{
		$promocode = new PromoCode;
		$codes = $promocode->getBy('name', $code);
		if ($codes) return true;
		else return false;
	}

	/**
	 * # Pouzi show() metodu...
	 * @deprecated version 2.3.0
	 */
	public function showAll($active = false)
	{
		$tb = $this->table();
		if ($active === TRUE) $tb = $tb->where('active', 1);

		return $tb;
	}

	/**
	 * ## Vypis z tabulky
	 * - ak je zadane ID vypise len riadok podla ID v opacnom pripade vypise vysledok vsetkych vysledkov (Array)
	 * @param int|string $id
	 * @param bool $active - vyberie z tabulky iba tie, ktore maju zadane v bunke active cislo 1
	 * @param bool|string|array $fetch 
	 * - vypise iba bunku, podla zadaneho stringu
	 * - ak je pole, vyberie zhodne podla zadania v poli (napr: ['id', 'id'] -> vrati zhodne Array([8] => 8))
	 * - ak je defaultne false, vysledok je riadok
	 * @return object
	 */
	public function show($id = false, $active = false, $fetch = false)
	{
		$tb = Db::get()->es_sale();

		if ($active === TRUE) $tb = $tb->where('active', 1);

		if ($id) {
			$tb = $tb->where('es_product_id', $id);

			if ($fetch) {
				switch (gettype($fetch)) {
					case 'string':
						$tb = $tb->fetch($fetch);
						break;
					case 'array':
						$tb = $tb->fetchPairs(implode(',', $fetch));
						break;
				}
			} else {
				$tb = $tb->fetch();
			}
		} else {
			if ($fetch) {
				switch (gettype($fetch)) {
					case 'string':
						$tb = $tb->fetchPairs($fetch);
						break;
					case 'array':
						$tb = $tb->fetchPairs(implode(',', $fetch));
						break;
				}
			}
		}
		return $tb;
	}

	/**
	 * Save the sales data
	 *
	 * @param string|null $id
	 * @param array $data
	 * @return void
	 * @deprecated v2.5.0
	 */
	public function save_data(?string $id = null, array $data)
	{
		$tb = Db::get()->es_sale();

		if ($id) {
			if (!empty($this->show($id, false, 'es_product_id'))) {
				$tb->where('es_product_id', $id)->update($data);
				return true;
			} else {
				$tb->insert($data);
				return true;
			}
		} else if ($tb->insert($data)) return true;
		else return false;
	}

	/**
	 * Remove by ID
	 *
	 * @param string $id
	 * @return void
	 */
	public function remove($id)
	{
		return Db::get()->es_sale('es_product_id', $id)->delete();
	}

	public function is_sales()
	{
		if (!empty($this->show())) return true;
		return false;
	}

	// public static function isSale()
	// {
	//     if( !empty( self::show() ) ) return true;
	//     return false;
	// }

	public function products_in_sale($limit = false)
	{
		$isInSale = $this->show()->select('new_price,old_price')->order('new_price ASC');
		if ($limit && gettype($limit) === 'integer') $isInSale = $isInSale->limit($limit);
		$isInSale = $isInSale->fetchPairs('es_product_id');

		return $isInSale;
	}
}
