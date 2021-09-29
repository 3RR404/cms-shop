<?php

namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\PaymentMethods;
use Weblike\Cms\Shop\Utilities\Price;

class PaymentMethodsManager
{

	/** @var null|Response */
	protected $response;

	function __construct()
	{
		if (!isset($_SESSION['cart-payment-method']) || empty($_SESSION['cart-payment-method'])) $_SESSION['cart-payment-method'] = [];
	}

	public function getMethods(?bool $as_array = false)
	{
		return Db::get()->getRepository(PaymentMethods::class)
			->createQueryBuilder('pm')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getMethod($id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(PaymentMethods::class)
			->createQueryBuilder('pm')
			->where('pm.id = ?0')
			->setParameter(0, $id)
			->getQuery()
			->getOneOrNUllResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getActive(?string $id = null, ?bool $as_array = false)
	{
		$result = Db::get()->getRepository(PaymentMethods::class)
			->createQueryBuilder('pm')
			->where('pm.active = :active')
			->setParameter('active', 1);

		if ($id) {
			$result = $result->where('pm.id = :id')->setParameter('id', $id);
			return $result->getQuery()->getSingleResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
		}

		return $result->getQuery()->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function save(?string $id = null, $data)
	{
		if (!$id) $repository = new PaymentMethods;
		else $repository = Db::get()->getReference(PaymentMethods::class, $id);

		$repository->setName($data['name'])
			->setPrice(Price::parseFloat($data['price']));

		Db::get()->persist($repository);
		Db::get()->flush();
	}

	public function remove(string $id): void
	{
		$repository = Db::get()->getReference(PaymentMethods::class, $id);

		Db::get()->remove($repository);
		Db::get()->flush();
	}

	public function toggleActive(string $id, array $data)
	{
		$repository = Db::get()->getReference(PaymentMethods::class, $id);

		$repository->setActive($data['active']);

		Db::get()->persist($repository);
		Db::get()->flush();
	}

	public function setMethod(string $id): self
	{
		$_SESSION['cart-payment-method'] = $id;

		$this->response = new Response('Payment method is set up !', 'success');

		return $this;
	}

	public function getResponse(): ?Response
	{
		return $this->response;
	}
}
