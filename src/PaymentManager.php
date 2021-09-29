<?php

namespace Weblike\Cms\Shop;

use Tracy\Debugger;
use Weblike\Cms\Core\App;
use Weblike\Cms\Core\Application\RequestUri;
use Weblike\Cms\Core\Response;
use Weblike\Cms\Shop\Entity\Order;
use Weblike\Cms\Shop\Interfaces\IPayment;
use Weblike\Cms\Shop\Module\Payment\GoPayment\Payment;
use Weblike\Cms\Shop\Module\Payment\GoPayment\PaymentConfiguration;
use Weblike\Cms\Shop\Utilities\Others;

class PaymentManager implements IPayment
{

	/** @var null|Response */
	protected $response = null;

	/** @var PaymentConfiguration */
	protected $configuration;

	/** @var int */
	protected $data;

	public function __construct()
	{
		$this->load_module($_ENV['PAYMENT_COMPONENTE']);
	}

	public function load_module(string $module): void
	{
		switch ($module) {
			case 'GOPAY':
				if (!file_exists(__DIR__ . "/Module/Payment/GoPayment/Payment.php"))
					throw new \Exception('Loading module fail !');
				else $this->configGoPayment();
				break;
			case 'TPAY':
				if (!file_exists(__DIR__ . "/Module/Payment/TatraPay/Payment.php"))
					throw new \Exception('Loading module fail !');
				break;
		}
	}

	public function configGoPayment()
	{
		/** @var \Weblike\Cms\Core\Application\RequestUri $uri */
		$uri = new RequestUri;

		$this->configuration = new PaymentConfiguration($_ENV['GO_ID'], $_ENV['GO_CID'], $_ENV['GO_CSECRET']);
		$this->configuration->setCallbacks($uri->lroot . self::CALLBACK_URL, $uri->lroot . self::NOTIFICATION_URL);
	}

	public function setData(string $data): self
	{
		$this->data = $data;

		return $this;
	}

	public function parseData(string $data): ?Order
	{
		return (new OrderManager)->getOrder($data);
	}

	public function pay(): self
	{
		$parsed_order = $this->parseData($this->data);

		$payment = new Payment($this->configuration);

		$payment_result = $payment->forOrder($parsed_order)->payment();

		if ($payment_result->isSucceeded()) {
			$data = json_decode($payment_result->getResponse()->getData(), true);
			$this->response = new Response(
				'Payment has been success!',
				200,
				json_encode([
					'payment_transfer' => (new Cart)->getPaymentMethod()->getPaymentTransfer(),
					'gw_url' => $data['gw_url']
				])
			);
		} else $this->response = $payment_result->getResponse();

		return $this;
	}

	public function manualPay(?string $paid_amount = null): self
	{
		$order = $this->parseData($this->data);

		$paymentModul = new Payment($this->configuration);

		$paymentModul->setOrderData($order);

		$orderShipingData = $order->getShippingTo(true);

		$payment_data = [
			'order_number' => $order->getOrderNumber(),
			'amount' => Others::parseFloat($paid_amount) * 100,
			'currency' => $order->getCurrency(),
			'payer'	=> [
				'contact' => [
					"first_name" => $orderShipingData->user->fname,
					"last_name" => $orderShipingData->user->lname,
					"email" => $orderShipingData->user->email,
					"phone_number" => $orderShipingData->user->phone,
					"city" => $orderShipingData->user->city,
					"street" => $orderShipingData->user->address_line_1 . " " . $orderShipingData->user->address_line_2,
					"postal_code" => $orderShipingData->user->zip,
					"country_code" => $orderShipingData->user->country
				]
			],
			'additional_params' => ['name' => 'order_key', 'value' => $order->getToken()],
			'lang' => App::getActiveLang()
		];

		$response = $paymentModul->createPayment($payment_data);
		$paymentEntity = $paymentModul->saveResponse($response);

		$paymentModul->updatePayment($paymentEntity->getId(), [
			'amount' => Others::parseFloat($paid_amount) * 100,
			'state' => 'PAID',
			'payer' => $response->json['payer']
		]);

		$this->response = new Response('Úhrada prebehla úspešne');

		return $this;
	}

	/**
	 * Refund payment via modul
	 *
	 * @param float|null $amount
	 * @return self
	 */
	public function refundPayment(?float $amount = null): self
	{
		$order = $this->parseData($this->data);

		if ($order->payment->getPaymentTransfer() === self::ONLINE) {

			$paymentModul = new Payment($this->configuration);

			$payment = $paymentModul->getPaymentWthPaidState($order->getId());

			if (!@$payment) {
				$this->response = new Response('Nie je možné nájsť žiadnu platbu k tejto objednávke.', 400);
				return $this;
			}

			if (!$amount) $amount = $order->getTotalWthTax();

			if ($amount) $amount = (int)($amount * 100);

			$response = $paymentModul->refund($payment->getId(), $amount);

			if ($response->hasSucceed()) {

				$this->updatePayment($payment->getId());

				$this->response = new Response('Platba bola úspešne refundovaná', 200, json_encode($response->json));

				return $this;
			}

			$this->response = new Response('Nepodarilo sa refundovať platbu', 400);

			return $this;
		} else if ($order->payment->getPaymentTransfer() === self::OFFLINE) {

			$paymentModul = new Payment($this->configuration);

			if (!$amount) $amount = $order->getTotalWthTax();

			$payment = $paymentModul->getPaymentWthPaidState($order->getId());

			if (!@$payment) {
				$this->response = new Response('Nie je možné nájsť žiadnu platbu k tejto objednávke.', 400);
				return $this;
			}

			$paymentModul->updatePayment($payment->getId(), [
				'state' => 'REFUNDED',
				'amount' => $amount,
				'payer' => $payment->getPayer()
			]);

			$this->response = new Response('Platba bola úspešne refundovaná', 200, json_encode($response->json));

			return $this;
		}
	}

	public function updatePayment($payment_id)
	{

		$payment = new Payment($this->configuration);

		$payment_result = $payment->getStatus($payment_id);

		$payment_gateway_status = $payment->getGatewayStatus($payment_id);

		if ($payment_result->getState() !== $payment_gateway_status->json['state']) {
			$payment->updatePayment($payment_id, (array)$payment_gateway_status->json);

			$this->response = new Response('Payment state has been succesfuly updated', 200);
		} else
			$this->response = new Response('Payment have paid state', 200);

		return $this;
	}

	public function getPaymentState(string $order_id)
	{
		$payment = new Payment($this->configuration);
		foreach ($payment->getStatusByOrder($order_id) as $pmt) {
			switch ($pmt->getState()) {
				case 'PAID':
				case 'REFUNDED':
				case 'PARTIALLY_REFUNDED':
					$response = $payment->getGatewayStatus($pmt->getId());

					$payment->updatePayment($pmt->getId(), (array)$response->json);

					$this->response = new Response('Payment has been succesfully paid', 200);

					return $this;

					break;
				case 'CREATED':
				case 'PAYMENT_METHOD_CHOSEN':
				case 'AUTHORIZED':
				case 'CANCELED':
				case 'TIMEOUTED':
					$response = $payment->getGatewayStatus($pmt->getId());

					/** Ak sa "state" lokalne v DB nerovna "state" v GP DB */
					if ($response->json['state'] !== $pmt->getState()) {
						$payment->updatePayment($pmt->getId(), (array)$response->json);
					}

					/** Ak "state" lokalne v DB je PAID */
					if ($response->json['state'] === 'PAID') {
						$this->response = new Response('Payment has been succesfully paid', 200);
						return $this;
					}

					$this->response = new Response("Payment state is {$response->json['state']}", 400, json_encode($response->json));
					break;
			}
		}
		return $this;
	}

	public function getResponse(): ?Response
	{
		return $this->response;
	}

	public function getJsonResponse(): ?string
	{
		return json_encode([
			'type' => $this->response->getType(),
			'message' => $this->response->getMessage(),
			'code' => $this->response->getCode()
		]);
	}
}
