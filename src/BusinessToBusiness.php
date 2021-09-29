<?php

namespace Weblike\Cms\Shop;

use Weblike\Plugins\doHash as PluginsDoHash;
use Weblike\Plugins\User;
use \Authorization;
use TableCreator\Src\TableCreator;
use Weblike\Cms\Core\Db;

abstract class BusinessMan
{
	protected $businessman;
	protected $user;
	protected static $_user;

	public function setUserToBusinessman()
	{
		$this->businessman = new User;
	}

	public function setUser(int $user_id)
	{
		$this->user = $user_id;
	}

	public static function set_user(int $user_id)
	{
		self::$_user = $user_id;
	}
}

class BusinesInstruments extends BusinessMan
{
	const TABLE_BUSINESSMEN = 'es_business';
	const TABLE_BUSINESS_GROUP = 'es_business_group';
	const TABLE_BUSINESS_GROUP_PRICES = 'es_business_group_prices';

	public function isBusinessman()
	{
		if (Db::get()->{BusinesInstruments::TABLE_BUSINESSMEN}()->where('user_id', $this->user)->fetch('id')) return true;
		return false;
	}

	public static function is_businessman()
	{
		if (Db::get()->{BusinesInstruments::TABLE_BUSINESSMEN}()->where('user_id', self::$_user)->fetch('id')) return true;
		return false;
	}
}

interface Business
{
}

trait BusinessMen
{

	public function getBusinessmen(bool $businessmen_only = false)
	{
		$businessmen = Db::get()->user()->where('id <> ' . $this->businessman->id)->and('flag <> "z"');
		if ($businessmen_only) {
			$businessmen_only_ids = Db::get()->{BusinesInstruments::TABLE_BUSINESSMEN}()->fetchPairs('user_id', 'user_id');
			$businessmen = $businessmen->where('id', $businessmen_only_ids);
		}

		foreach ($businessmen as $businessman) {
			$businessman['customer'] = $businessman->es_customer()->fetch();
			$businessman['group_id'] = $businessman->{BusinesInstruments::TABLE_BUSINESSMEN}()->fetch('es_business_group_id');
			$businessman['orders'] = $businessman->es_orders();
			$businessman['yearly_spend'] = Db::get()->es_orders()->where("user_id = " . $businessman['id'] . " AND created_at >= '" . date('Y') . "-01-01 00:00:00' AND created_at <= NOW()")->sum('total');
			$businessman['this_month_avg'] = Db::get()->es_orders()
				->select("user_id, AVG(total) as this_month_avg")
				->where("user_id = " . $businessman['id'] . " AND created_at >= '" . date('Y') . "-" . date('m') . "-01 00:00:00' AND created_at <= NOW()")
				->fetch('this_month_avg');
			$businessman['prev_month_avg'] = Db::get()->es_orders()
				->select("user_id, AVG(total) as prev_month_avg")
				->where("user_id = " . $businessman['id'] . " AND created_at >= '" . date('Y') . "-" . date('m', strtotime('-1month')) . "-01 00:00:00' AND created_at <= '" . date('Y') . "-" . date('m') . "-01 23:59:59'")
				->fetch('prev_month_avg');
			$businessman['last_order'] = $businessman->es_orders()->order('created_at DESC')->limit(1)->fetch();
		}

		return $businessmen;
	}

	public function getBusinessman(int $user_id)
	{
		$businessman = Db::get()->user()->where('id', $user_id)->fetch();
		$businessman['customer'] = $businessman->es_customer()->fetch();
		$businessman['group_id'] = $businessman->{BusinesInstruments::TABLE_BUSINESSMEN}()->fetch('es_business_group_id');

		return $businessman;
	}

	public function promoteUserToBusinessman(int $user_id)
	{
		$data = [
			'user_id' => $user_id
		];

		if (!empty($this->isBusinessman())) return false;

		return Db::get()->{BusinesInstruments::TABLE_BUSINESSMEN}()->insert($data);
	}

	public function promoteBusinessman(int $businessman_id, int $group_id)
	{
		return Db::get()->{BusinesInstruments::TABLE_BUSINESSMEN}()->where('user_id', $businessman_id)->update(['es_business_group_id' => $group_id]);
	}

	public static function isUserBusinessman(int $user_id = 0)
	{
		if ($user_id === 0) {
			$user = new User;
			if (Db::get()->{BusinesInstruments::TABLE_BUSINESSMEN}()->where('user_id', $user->id)->fetch('id')) return true;
		}

		if (Db::get()->{BusinesInstruments::TABLE_BUSINESSMEN}()->where('user_id', $user_id)->fetch('id')) return true;

		return false;
	}

	public function saveBusinessmanData(int $user_id, array $data)
	{
		$data['customer']['buy_as_company'] = $data['customer']['ship_address'] = $data['gdpr'] = 1;

		if ($user_id) {
			if ($backrolling_data = Db::get()->user('id', $user_id)->fetch()) {
				$data['user']['active'] = $backrolling_data['active'];
			}

			Db::get()->user('id', $user_id)->update($data['user']);

			if (!empty(Db::get()->es_customer('user_id', $user_id)->fetch('id'))) Db::get()->es_customer('user_id', $user_id)->update($data['customer']);
			else {
				$data['customer']['user_id'] = $user_id;
				Db::get()->es_customer()->insert($data['customer']);
			}
			if (!empty(Db::get()->es_business('user_id', $user_id)->fetch('id'))) Db::get()->es_business('user_id', $user_id)->update($data['businessman']);
			else {
				$data['businessman']['user_id'] = $user_id;
				Db::get()->es_business()->insert($data['businessman']);
			}

			return true;
		} else {
			$authorization = new Authorization;

			if ($message = Authorization::checkRequired($data)) return $message;

			$tokenizator = new PluginsDoHash;
			$token = $tokenizator->accioUserToken(['email' => $data['user']['email']], 10);
			$authorization->sendAuthorization($data['user']['email'], $token, '', '', false);

			$last = Db::get()->user()->insert($data['user']);
			$data['customer']['user_id'] = $data['businessman']['user_id'] = $last['id'];
			Db::get()->es_customer()->insert($data['customer']);
			Db::get()->es_business()->insert($data['businessman']);

			return true;
		}

		return false;
	}
}

trait BusinessGroups
{

	public function save_group_data(?int $id = null, array $data)
	{
		if ($id > 0 && Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP}('id', $id)->fetch('id')) {
			Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP}('id', $id)->update($data);
		} else return Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP}()->insert($data);
	}

	public function groups(?int $id = null)
	{
		$table = Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP}();

		if ($id > 0) $table = $table->where('id', $id)->fetch();

		return $table;
	}

	public static function product_price_by_group(int $product_id, int $group_id, string $fetch = '')
	{
		return Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP_PRICES}('es_product_id', $product_id)->where('es_business_group_id', $group_id)->fetch($fetch);
	}

	public function getUserGroup(int $user_id)
	{
		return Db::get()->es_business()->where('user_id', $user_id)->fetch('es_business_group_id');
	}
}

trait ProductBusinessGroups
{
	public static function hasBusinessesPrice(int $product_id, int $group_id)
	{
		if (!empty(Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP_PRICES}()->where('es_product_id', $product_id)->and('es_business_group_id', $group_id)->fetch('product_price'))) return true;
		return false;
	}

	public function productPriceByGroup(int $product_id, int $group_id)
	{
		$product_price = Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP_PRICES}()->where('es_product_id', $product_id)->and('es_business_group_id', $group_id)->fetch('product_price');
		if (!empty($product_price)) return $product_price;
		return false;
	}

	public static function getGroupData($id)
	{
		return Db::get()->{BusinesInstruments::TABLE_BUSINESS_GROUP}()->where('id', $id)->fetch();
	}
}

class BusinessToBusiness extends BusinesInstruments implements Business
{
	protected $customer;
	protected $application_run = false;

	use BusinessMen, BusinessGroups, ProductBusinessGroups;

	function __construct()
	{
		$tbl_business = new TableCreator('es_business');
		$tbl_business->integer('id', 11, '', true);
		$tbl_business->integer('user_id');
		$tbl_business->integer('es_business_group_id');
		$tbl_business->up();

		$tbl_business_group = new TableCreator('es_business_group');
		$tbl_business_group->integer('id', 11, '', true);
		$tbl_business_group->string('name');
		$tbl_business_group->integer('m_limit');
		$tbl_business_group->up();

		$tbl_business_group_prices = new TableCreator('es_business_group_prices');
		$tbl_business_group_prices->integer('es_product_id');
		$tbl_business_group_prices->decimal('product_price', '10,2');
		$tbl_business_group_prices->integer('es_business_group_id');
		$tbl_business_group_prices->up();


		$b2b_payment = [
			'id'            => Payment::B2B_PAYMENT,
			'pos'           => '5',
			'name'          => '{"sk":"B2B Payment / Punchout"}',
			'price'         => '0.00',
			'pm_transfer'   => Payment::OFFLINE
		];

		if (empty(Db::get()->es_payment_methods()->where('id', 5)->fetch())) Db::get()->es_payment_methods()->insert($b2b_payment);

		$this->setUserToBusinessman();
	}

	function __get($name)
	{
		return @$_SESSION[\md5(\APPSERVERNAME) . '-businessman'][$name];
	}

	public function init()
	{
		if (!isset($_SESSION[\md5(\APPSERVERNAME) . '-businessman'])) $_SESSION[\md5(\APPSERVERNAME) . '-businessman'] = [];
		$_SESSION[\md5(\APPSERVERNAME) . '-businessman'] = '';

		if ($this->businessman->isLoggedIn())
			$_SESSION[\md5(\APPSERVERNAME) . '-businessman'] = [
				'group' => $this->getUserGroup($this->businessman->id)
			];
	}

	/**
	 * ## Ak je v skupine
	 * @return int|bool group_id
	 */
	public function isInGroup()
	{
		if ($this->group)
			return (int)$this->group;
		return false;
	}
}

class B2b extends BusinessToBusiness
{
}
