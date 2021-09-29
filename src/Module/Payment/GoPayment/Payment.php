<?php

namespace Weblike\Cms\Shop\Module\Payment\GoPayment;

use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\PaymentInstrument;
use Weblike\Cms\Core\Application\RequestUri;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Cart;
use Weblike\Cms\Shop\Entity\Order;
use Weblike\Cms\Shop\OrderManager;
use Weblike\Cms\Shop\PaymentManager;
use Weblike\Cms\Core\Response;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Strings\Others;

class Payment
{

	/** @var \Weblike\Cms\Shop\Entity\Order */
	protected $order_data;

	/** @var array */
	protected $payment_data;

	/** @var \GoPay\Definition\Payment\Currency */
	protected $currency;

	/** @var \GoPay\Definition\Language */
	protected $language;

	/** @var \GoPay\Payments $gopay */
	protected $gopay;

	/** @var array */
	protected $allowed_swifts;

	/** @var array */
	protected $allowed_payment_instruments;

	/** @var array */
	protected $callback_urls;

	/** @var null|Response */
	protected $response;

	function __construct(PaymentConfiguration $settings)
	{

		$this->gopay = \GoPay\Api::payments([
			'goid' => $settings->getId(),
			'clientId' => $settings->getClientId(),
			'clientSecret' => $settings->getSecretKey(),
			'isProductionMode' => $settings->isProduction(),
			'scope' => \GoPay\Definition\TokenScope::ALL,
			'language' => $settings->getLang(),
			'timeout' => 30
		]);

		$this->currency = $settings->getCurrency();
		$this->language = $settings->getLang();
		$this->allowed_swifts = $settings->getAllowedSwifts();
		$this->allowed_payment_instruments = $settings->getAllowedPaymentInstruments();
		$this->callback_urls = $settings->getCallbacks();
	}

	public function payment()
	{

		if ((new Cart)->getPaymentMethod()->getPaymentTransfer() === PaymentManager::ONLINE) {
			$response = $this->gopay->createPayment($this->payment_data);

			if (@$response->json['errors']) {
				$this->response = new Response('Error', 500, json_encode($response->json['errors']));
				return $this;
			}

			$this->saveResponse($response);

			if ($response->hasSucceed())
				$this->response = new Response('Success', 200, json_encode($response->json));
			else
				$this->response = new Response('Error', 400);
		} else {
			$uri = new RequestUri;

			$this->response = new Response('Success', 200, json_encode([
				'gw_url' => "{$uri->lroot}/e-shop/response?id={$this->payment_data['order_number']}"
			]));
		}

		return $this;
	}

	function setOrderData($data)
	{
		$this->order_data = $data;
	}

	function getOrderData(): Order
	{
		return $this->order_data;
	}

	public function forOrder($order_data): self
	{
		$this->setOrderData($order_data);

		$this->prepareDataForPayment();

		return $this;
	}

	public function getResponse(): ?Response
	{
		return $this->response;
	}

	public function isSucceeded(): bool
	{
		if ($this->getResponse()->getCode() === 200)
			return true;

		return false;
	}


	protected function prepareDataForPayment(): void
	{
		$products_ids = $this->getOrderData()->getProductsAsObject();

		$shipping_method = @$this->order_data->getShipping() ? @$this->order_data->getShipping() : (new Cart)->getShippingMethod();
		$payment_method = @$this->order_data->getPayment() ? $this->order_data->getPayment() : (new Cart)->getPaymentMethod();
		$discount = Price::whoolPrice($this->order_data->getDiscount());
		$promocode = @$this->order_data->getPromocode() ? $this->order_data->getPromocode() : (new Cart)->getPromocode();

		foreach ($products_ids as $p) {
			$products[] = [
				'type' => 'ITEM',
				'name' => $p->name,
				'product_url' => urldecode($p->product_url),
				'ean' => (int)@$p->ean,
				'amount' => Price::whoolPrice(
					(Others::parseFloat($p->price_with_tax)
						* Others::parseInt($p->qt))
				) * 100,
				'count' => (int)$p->qt,
			];
		}

		// pripocitanie dorucovacej metody
		$products[] = [
			'type' => 'DELIVERY',
			'name' => $shipping_method->getName(),
			'amount' => (int) (Others::parseFloat($shipping_method->getPrice()) * 100),
			'count' => 1
		];

		// pripocitanie platobnej metody
		$products[] = [
			'type' => 'DELIVERY',
			'name' => $payment_method->getName(),
			'amount' => (int) (Others::parseFloat($payment_method->getPrice()) * 100),
			'count' => 1
		];

		if (@$promocode)
			$products[] = [
				'type' => 'DISCOUNT',
				'name' => $promocode->getName(),
				'amount' => $discount * 100,
				'count' => 1
			];

		$payer_contact = json_decode($this->order_data->getShippingTo());

		$this->payment_data = [
			'amount' => (int)($this->order_data->getTotalWthTax() * 100),
			'currency' => $this->currency,
			'order_number' => $this->order_data->getOrderNumber(),
			'order_description' => '',
			'items' => $products,
			'callback' => $this->callback_urls,
			'lang' => $this->language
		];

		switch ($payment_method->getId()) {
			case PaymentManager::CARD:
				$default_payment_instrument = PaymentInstrument::PAYMENT_CARD;
				break;
			case PaymentManager::BANK_TRANSFER:
				$default_payment_instrument = PaymentInstrument::BANK_ACCOUNT;
				break;
			default:
				$default_payment_instrument = PaymentInstrument::BANK_ACCOUNT;
		}

		$this->payment_data['payer'] = [
			'default_swift' => BankSwiftCode::TATRA_BANKA,
			'allowed_swifts' => $this->allowed_swifts,
			'default_payment_instrument' => $default_payment_instrument,
			'allowed_payment_instruments' => $this->allowed_payment_instruments,
			'contact' => [
				'first_name' => $payer_contact->user->fname,
				'last_name' => $payer_contact->user->lname,
				'email' => $payer_contact->user->email,
				'phone_number' => $payer_contact->user->phone,
				'city' => $payer_contact->user->city,
				'street' => !empty(trim($payer_contact->user->address_line_2))
					? \implode(', ', [$payer_contact->user->address_line_1, $payer_contact->user->address_line_2])
					: $payer_contact->user->address_line_1,
				'postal_code' => $payer_contact->user->zip,
				'country_code' => \strtoupper($payer_contact->user->country)
			]
		];

		$this->payment_data['additional_params'] = [
			[
				'name' => 'order_key',
				'value' => $this->order_data->getToken()
			]
		];
	}

	public function saveResponse($response): PaymentEntity
	{

		$json_result = $response->json;

		$payment = new PaymentEntity;
		$payment->setId((int)$json_result['id']);
		$payment->setOrder($this->order_data);
		$payment->setState($json_result['state']);
		$payment->setAmount($json_result['amount']);
		$payment->setCurrency($json_result['currency']);
		$payment->setPayer(json_encode($json_result['payer']));
		$payment->setTarget(json_encode($json_result['target']));
		$payment->setAdditionalParams(json_encode($json_result['additional_params']));
		$payment->setLang($json_result['lang']);
		$payment->setGwUrl($json_result['gw_url']);
		$payment->setCreatedAt(new \DateTime());


		Db::get()->persist($payment);
		Db::get()->flush();

		return $payment;
	}

	public function getStatus($payment_id)
	{
		return Db::get()->getRepository(PaymentEntity::class)
			->createQueryBuilder('payment')
			->where('payment.id = :id')
			->setParameter('id', $payment_id)
			->getQuery()
			->getOneOrNullResult();
	}

	public function getStatusByOrder($order_id)
	{
		return Db::get()->getRepository(PaymentEntity::class)
			->createQueryBuilder('payment')
			->where('payment.order = :order')
			->setParameter('order', $order_id)
			->orderBy('payment.created_at', 'ASC')
			->getQuery()
			->getResult();
	}

	public function getPaymentWthPaidState(int $order_id)
	{
		return Db::get()->getRepository(PaymentEntity::class)
			->createQueryBuilder('payment')
			->where('payment.order = :order')
			->andWhere('payment.state = :paidState')
			->setParameters([
				'order' => $order_id,
				'paidState' => 'PAID'
			])
			->orderBy('payment.created_at', 'ASC')
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Return payment state
	 *
	 * @param [type] $payment_id
	 * @return \GoPay\Http\Response
	 */
	public function getGatewayStatus($payment_id)
	{
		return $this->gopay->getStatus($payment_id);
	}

	/**
	 * Refund the payment
	 *
	 * @param integer $paymentId (required)
	 * @param integer $amount (required) amound will be refunded
	 * @return \GoPay\Http\Response
	 */
	public function refund(int $paymentId, int $amount)
	{
		return $this->gopay->refundPayment($paymentId, $amount);
	}

	public function createPayment($payment_data)
	{
		$uri = new RequestUri();
		$response = (object)['json'];
		$response_data = [
			"id" => $payment_data['order_number'],
			"state" => "CREATED",
			"amount" => $payment_data['amount'],
			"currency" => $payment_data['currency'],
			"payer" => [
				"default_payment_instrument" => "CASH",
				"contact" => $payment_data['payer']['contact']
			],
			"target" => [
				"type" => "CASH",
			],
			"additional_params" => $payment_data['additional_params'],
			"lang" => $payment_data['lang'],
			"gw_url" => "{$uri->lroot}/e-shop/response?id={$payment_data['order_number']}",
		];
		$response->json = $response_data;

		return $response;
	}

	public function updatePayment($payment_id, array $data)
	{
		$payment = Db::get()->getReference(PaymentEntity::class, $payment_id);

		$order = $payment->getOrder();

		switch ($data['state']) {
			case 'PAID':
				$state = OrderManager::PAID;
				break;
			case 'REFUNDED':
			case 'PARTIALLY_REFUNDED':
				$state = OrderManager::REFUNDED;
				break;
			default:
				$state = OrderManager::PENDING;
		}

		$order->setState($state);

		if ($state === OrderManager::PAID)
			$order->setPaid($data['amount'] / 100);
		else if ($state === OrderManager::REFUNDED)
			$order->setPaid($data['amount'] / (-100));

		$payment->setState($data['state']);
		$payment->setPayer(json_encode($data['payer']));

		Db::get()->persist($payment);
		Db::get()->flush();
	}
}
