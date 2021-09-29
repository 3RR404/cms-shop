<?php

namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\ShippingMethods;
use Weblike\Cms\Shop\Utilities\Price;

class ShippingMethodsManager
{

	/** @var null|Response */
	protected $response;

	function __construct()
	{
		if (!isset($_SESSION['cart-shipping-method']) || empty($_SESSION['cart-shipping-method'])) $_SESSION['cart-shipping-method'] = [];
	}

	public function getMethods(?bool $as_array = false)
	{
		return Db::get()->getRepository(ShippingMethods::class)
			->createQueryBuilder('sm')
			->orderBy('sm.position', 'ASC')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getMethod($id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(ShippingMethods::class)
			->createQueryBuilder('sm')
			->where('sm.id = ?0')
			->setParameter(0, $id)
			->getQuery()
			->getOneOrNUllResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getActive(?string $id = null, ?bool $as_array = false)
	{
		$result = Db::get()->getRepository(ShippingMethods::class)
			->createQueryBuilder('sm')
			->where('sm.active = :active')
			->setParameter('active', 1)
			->orderBy('sm.position', 'ASC');

		if ($id) {
			$result = $result->where('sm.id = :id')->setParameter('id', $id);
			return $result->getQuery()->getSingleResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
		}

		return $result->getQuery()->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function last()
	{
		return Db::get()->getRepository(ShippingMethods::class)
			->createQueryBuilder('sm')
			->orderBy('sm.id', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	public function save(?string $id = null, $data)
	{
		if (!$id) $shipping = new ShippingMethods;
		else $shipping = Db::get()->getReference(ShippingMethods::class, $id);

		$shipping->setName($data['name']);
		$shipping->setPrice(Price::parseFloat($data['price']));
		$shipping->setPaymentMethod(json_encode($data['payment_method']));
		$shipping->setEnabledLocations(json_encode($data['enabled_locations']));
		$shipping->setCartValueHigher($data['cart_value_is_much']);
		$shipping->setCartValueDiscount($data['quantity_discount']);

		Db::get()->persist($shipping);
		Db::get()->flush();
	}

	public function remove(string $id): void
	{
		$repository = Db::get()->getReference(ShippingMethods::class, $id);

		Db::get()->remove($repository);
		Db::get()->flush();
	}

	public function toggleActive(string $id, array $data)
	{
		$shipping = Db::get()->getReference(ShippingMethods::class, $id);

		$shipping->setActive($data['active']);

		Db::get()->persist($shipping);
		Db::get()->flush();
	}

	public function setMethod(string $id): self
	{
		$_SESSION['cart-shipping-method'] = $id;

		$this->response = new Response('Shipping method is set up !', 'success');

		return $this;
	}

	public function getResponse(): ?Response
	{
		return $this->response;
	}
}
