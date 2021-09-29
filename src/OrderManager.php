<?php


namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Weblike\Cms\Core\App;
use Weblike\Plugins\doHash;
use Weblike\Plugins\User;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Core\Entity\UserManager;
use Weblike\Cms\Core\JSON;
use Weblike\Cms\Core\Response;
use Weblike\Cms\Shop\Entity\Order;
use Weblike\Cms\Shop\Interfaces\IOrder;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Cms\Shop\Utilities\Texts;
use Weblike\Plugins\BasePlugin;
use Weblike\Strings\Others;

class OrderManager extends BasePlugin implements IOrder
{

	function __construct()
	{
		$this->user = new User;
	}

	public function getOrders()
	{

		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->leftJoin('o.user', 'user')
			->andWhere('user.id = :uid')
			->setParameter('uid', $this->user->id)
			->getQuery()
			->getResult();
	}

	public function getUserOrders()
	{

		$this->query = Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->leftJoin('o.user', 'user')
			->andWhere('user.id = :uid')
			->setParameter('uid', $this->user->id)
			->orderBy('o.created_at', 'DESC')
			->getQuery();

		$this->result = $this->query->getResult();

		return $this;
	}

	public function getOrder(string $id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->where('o.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getUserOrder(string $id)
	{
		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->where('o.id = :id')
			->leftJoin('o.user', 'user')
			->andWhere('user.id = :uid')
			->setParameters(['id' => $id, 'uid' => $this->user->id])
			->getQuery()
			->getOneOrNullResult();
	}

	public function getLastOrder()
	{
		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->orderBy('o.id', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	public function getAllOrders()
	{
		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->getQuery()
			->getResult();
	}

	public function getOrderByToken(string $token): ?Order
	{
		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->where('o.token = :token')
			->setParameter('token', $token)
			->getQuery()
			->getOneOrNullResult();
	}

	public function getOrderByNumber(string $order_number): ?Order
	{
		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->where('o.orderNumber = :orderNumber')
			->setParameter('orderNumber', $order_number)
			->getQuery()
			->getOneOrNullResult();
	}

	public function removeOrder(string $order_id): Response
	{
		return new Response('Momentalne nie je možné odobrať objednávku', 500);
	}
}
