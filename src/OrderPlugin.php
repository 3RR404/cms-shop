<?php

namespace Weblike\Cms\Shop;

use Weblike\Cms\Core\App;
use Weblike\Plugins\doHash;
use Weblike\Plugins\User;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Core\Entity\UserManager;
use Weblike\Cms\Core\JSON;
use Weblike\Cms\Core\Mail;
use Weblike\Cms\Shop\Entity\Order;
use Weblike\Cms\Shop\Interfaces\IOrder;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Cms\Shop\Utilities\Texts;

class OrderPlugin implements IOrder
{
	/** @var object $data */
	protected $data;

	/** @var Response $response */
	protected $response;

	/** @var User */
	protected $user;

	/** @var Cart */
	protected $cart;

	/** @var string */
	protected $template = 'order';

	public function __construct(Cart $cart)
	{
		if (empty($this->response)) $this->response = new Response('Bad Getaway', 'error', 403);

		$this->user = new User();
		$this->cart = $cart;
	}

	public function setOrderMailTemplate(string $template): self
	{
		$this->template = $template;

		return $this;
	}

	public function getOrderMailTemplate(): string
	{
		return $this->template;
	}

	public function parseData(array $form_data): self
	{
		foreach ($form_data as $column => $value) {
			if (Texts::startsWith($column, 'ship_') && $column !== 'ship_address') {
				$column = \str_replace('ship_', '', $column);
				$data['shipping_to'][$column] = $value;
			} else {
				switch ($column) {
					case 'buy_as_company':
						if ($value === 'on') $data['isCompany'] = true;
						break;
					case 'ship_address':
						if ($value === 'on') $data['addressAreSame'] = false;
						break;
					case 'ico':
					case 'dic':
					case 'ic_dph':
					case 'company_name':
						$data['company'][$column] = $value;
						break;
					default:
						$data['user'][$column] = $value;
				}
			}
		}
		if (!isset($form_data['ship_address']) || $form_data['ship_address'] === 'off') $data['addressAreSame'] = true;
		if (!isset($form_data['buy_as_company']) || $form_data['buy_as_company'] === 'off') $data['isCompany'] = false;

		$this->data = json_decode(json_encode($data));

		return $this;
	}

	public function getParsedData(): object
	{
		return $this->data;
	}

	public function getJsonResponse(): string
	{
		return json_encode($this->response);
	}

	public function getResponse(): Response
	{
		return $this->response;
	}

	public function validate($data, ?array $exclude = null)
	{

		// if ( !isset( $data->gdpr ) || $data->gdpr === 0 || $data->gdpr === 'off' )
		// {
		//     $this->response = new Response( "Please read our terms and conditions and express your opinion !", 'error', 500 );
		//     return false;
		// }

		foreach ($data as $col => $val) {
			if (empty(trim($val))) {
				if ($exclude && in_array($col, $exclude)) continue;
				$this->response = new Response("Empty $col", 'error', 500);
				return false;
			}

			if ($col === 'email' && filter_var($val, \FILTER_VALIDATE_EMAIL) === false) {
				$this->response = new Response('Empty email', 'error', 500);
				return false;
			}
		}

		return true;
	}

	public function create()
	{
		$data = $this->getParsedData();

		if (empty($data)) {
			$this->response = new Response('No data to parse', 'error', 500);
			return false;
		}

		if (!$this->validate($data->user, ['address_line_2'])) return;
		if ($data->addressAreSame)
			if (!$this->validate($data->shipping_to, ['address_line_2'])) return;

		// All data done
		// save order with parsed data
		$order_saving_response = $this->save($data);

		// Submit customer email, then order has been created
		if (!empty($data) && @$data->user->email)
			$this->submitUserNotification($order_saving_response);

		return $order_saving_response;
	}

	protected function save(object $data)
	{
		$last_order = (new OrderManager)->getLastOrder();
		$last_order_number = $last_order ? (int)$last_order->getOrderNumber() : date('Ym') . "0000";
		$next_order_number = $last_order_number + 1;
		$seller_instructions = '';

		$cart = $this->cart;

		$order = new Order;
		if ($this->user->isLoggedIn())
			$order->setUser(Db::get()->getReference(UserManager::class, $this->user->id));

		foreach ($cart->getProducts() as $key => $product) {
			if ($product->isSimpleProduct()) {
				$products[$key] = [
					'id' => $product->getId(),
					'name' => $product->getName(),
					'ean' =>  $product->getEanCode(),
					'product_code' => $product->getProductCode(),
					'catalogue_number' => $product->getCatalogueNumber(),
					'product_url' => $product->getSlug('/e-shop'),
					'price_with_tax' => $product->getWithTax(),
					'price_without_tax' => $product->getPrice(),
					'qt' => $cart->getQuantity($product->getId()),
					'is_simple' => $product->isSimpleProduct()
				];

				// $productSold = Db::get()->getReference(Product::class, $product->getId());
				$order->setProductSold($product);
			} else {
				$products[$key] = [
					'id' => $product->product->getId(),
					'name' => $product->product->getName(),
					'ean' =>  $product->product->getEanCode(),
					'product_code' => $product->getProductCode(),
					'catalogue_number' => $product->getCatalogueNumber(),
					'product_url' => $product->product->getSlug('/e-shop'),
					'price_with_tax' => $product->getPriceWthTax(),
					'price_without_tax' => $product->getPrice(),
					'qt' => $cart->getQuantity($product->product->getId(), $product->getId()),
					'is_simple' => $product->isSimpleProduct(),
					'sku' => $product->getId()
				];

				$order->setProductSold($product->product);
			}
		}

		$order->setOrderNumber($next_order_number);
		$order->setShippingTo(json_encode($data));
		$order->setProducts(json_encode($products));
		$order->setShipping($cart->getShippingMethod());
		$order->setPayment($cart->getPaymentMethod());
		$order->setSubtotal(Price::whoolPrice($cart->getSubtotalWthTax()));
		$order->setTotal(Price::whoolPrice($cart->getTotal(), 3));
		$order->setTotalTax(Price::whoolPrice($cart->getTaxTotal(), 3));
		$order->setTotalWthTax(Price::whoolPrice($cart->getTotalWthTax()));
		$order->setState(self::PENDING);
		$order->setStatus(self::SHIPPING);
		$order->setLang(App::getActiveLang());
		$order->setCurrency('EUR');
		$order->setSellerInstructions($seller_instructions);
		$order->setToken(doHash::getHash(10, true));
		$order->setPromocode($cart->getPromocode());
		$order->setDiscount($cart->getDiscountWthTax());
		$order->setCreatedAt(new \DateTime('NOW'));

		Db::get()->persist($order);
		Db::get()->flush();

		$this->response = new Response("Order nr.$next_order_number has been successfuly created!", 'success');

		return $order->getId();
	}

	public function submitUserNotification(string $order_id)
	{
		$template = $this->getOrderMailTemplate();

		$order_data = (new OrderManager)->getOrder($order_id);

		$subject = "Notifikacia o objednavke {$order_data->getOrderNumber()} | {$_SERVER['SERVER_NAME']}";

		$user_email = $order_data->user->getEmail();

		$mail = new Mail($template, array(
			'title' => $subject,
			'email' => $user_email,
			'order' => [
				'created' => $order_data->getCreatedAt(),
				'number' => $order_data->getOrderNumber(),
				'token' => $order_data->getToken(),
				'status' => $order_data->getStatus(),
				'state' => [
					'integer' => $order_data->getState(),
					'string' => $order_data->getOrderStateText()
				],
				'lang' => $order_data->getLang(),
				'currency' => $order_data->getCurrency(),
				'is_paid' => $order_data->getPaid(),
			],
			'payment_method' => $order_data->getShipping(),
			'shipping_method' => $order_data->getPayment(),
			'promocode' => $order_data->getPromocode(),
			'order_package' => $order_data->getPackage(),
			'products' => $order_data->getProducts(),
			'shipping_to' => $order_data->getShippingTo(true),
			'subtotal' => $order_data->getSubtotal(),
			'discount' => $order_data->getDiscount(),
			'total' => $order_data->getTotal(),
			'totaltax' => $order_data->getTotalTax(),
			'total_with_tax' => $order_data->getTotalWthTax(),
			'seller_instructions' => $order_data->getSellerInstructions()
		));

		$mail->setSubject($subject)
			->setTo($user_email)
			->addImage(WEB_DIR . '/public/img/home/logo.png', 'logo');

		if (file_exists(WEB_DIR . '/upload/source/VOP.pdf')) $mail->addAttachment(WEB_DIR . '/upload/source/VOP.pdf');
		if (file_exists(WEB_DIR . '/upload/source/FNO.docx')) $mail->addAttachment(WEB_DIR . '/upload/source/FNO.docx');

		try {
			$mail->send($user_email);
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
		}
	}
}
