<?php

declare(strict_types=1);

namespace Weblike\Cms\Shop;

use Weblike\Plugins\User;
use Weblike\Cms\Core\App;
use Weblike\Cms\Core\Application\RequestUri;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\CartEntity;
use Weblike\Cms\Shop\Entity\PaymentMethods;
use Weblike\Cms\Shop\Entity\Product;
use Weblike\Cms\Shop\Entity\Promocode;
use Weblike\Cms\Shop\Entity\ShippingMethods;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Strings\Others;
use Weblike\Strings\Translate;

/**
 * Sprava kosika
 */
class Cart
{
	protected $product;
	protected $products = [];
	protected $user;
	protected $promoCode;
	protected $products_data;

	/** @var null|ShippingMethods */
	protected $shipping_method;

	/** @var null|PaymentMethods */
	protected $payment_method;

	/** @var null|Promocode */
	protected $promocode;

	/** @var null|float */
	protected $discount;

	/** @var null|float */
	protected $discount_wthTax;

	protected $loyalityPoints;

	/** @var null|float */
	protected $total = 0;

	/** @var null|float */
	protected $total_wth_tax = 0;

	/** @var null|float */
	protected $subtotal = 0;

	/** @var null|float */
	protected $subtotal_wth_tax = 0;

	/** @var null|Response */
	protected $response;

	/** @var array */
	protected $productVariants = [];

	function __construct()
	{

		$this->user = new User;

		if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) $_SESSION['cart'] = null;
		if (!isset($_SESSION['cart-promocode']) || empty($_SESSION['cart-promocode'])) $_SESSION['cart-promocode'] = null;
		if (!isset($_SESSION['cart-shipping-method']) || empty($_SESSION['cart-shipping-method'])) $_SESSION['cart-shipping-method'] = null;
		if (!isset($_SESSION['cart-payment-method']) || empty($_SESSION['cart-payment-method'])) $_SESSION['cart-payment-method'] = null;
		if (!isset($_SESSION['cart-loyalitypoints']) || empty($_SESSION['cart-loyalitypoints'])) $_SESSION['cart-loyalitypoints'] = null;
		if (!isset($_SESSION['cart-seller-instructions']) || empty($_SESSION['cart-seller-instructions'])) $_SESSION['cart-seller-instructions'] = null;

		if ($_SESSION['cart-shipping-method'])
			$this->setShippingMethod((new ShippingMethodsManager)->getMethod($_SESSION['cart-shipping-method']));

		if ($_SESSION['cart-payment-method'])
			$this->setPaymentMethod((new PaymentMethodsManager)->getMethod($_SESSION['cart-payment-method']));

		$this->getCartFromDb();
	}

	public function setVariants(array $variants): self
	{
		$this->productVariants = $variants;

		return $this;
	}

	/**
	 * Vlozenie do kosika
	 * Vytvori session 'cart', do ktorej vlozi id produktu ako pole\
	 * z premennej $product a k nej vytvori index pola 'quantity', kde sa\
	 * uschovaju udaje o produkte.
	 * 
	 * @param mixed|bool|int $product 
	 *  - **(required)** - id produktu
	 * @param int $quantity 
	 *  - **(optional)** - kolko produktov bude pridanych po vykonani akcie
	 * 
	 * @return bool
	 */
	function addTo($product_id, ?string $sku_id = null, ?int $quantity = 1): self
	{

		$cart = $this->getCart();

		if ($this->user->isLoggedIn()) {
			$user_id = $this->user->id;
			// TODO: [CSP-25] save the cart to database
		}

		if (!is_array($cart)) $cart = [];

		if (!array_key_exists($product_id, $cart))
			array_push($cart, $product_id);

		unset($cart[0]);

		if ($sku_id) {
			if (!is_array($cart[$product_id])) $cart[$product_id] = [];
			if (!array_key_exists($sku_id, $cart[$product_id]))
				\array_push($cart[$product_id], $sku_id);

			$cart[$product_id]['variants'][$sku_id]['quantity'] += $quantity;
			unset($cart[$product_id][0]);
		} else {
			$cart[$product_id]['quantity'] += $quantity;
		}

		$_SESSION['cart'] = $cart;

		$this->response = \json_encode([
			'success' => [
				'message' => 'Product has been succesfully added to cart !'
			]
		]);

		return $this;
	}

	public function removeFrom($product_id, ?string $sku_id = null, ?int $quantity = 1): self
	{
		$cart = [];
		$cart = !isset($_SESSION['cart']) ? $_SESSION['cart'] : $_SESSION['cart'] = $this->getCart();

		if ($this->user->isLoggedIn()) {
			$user_id = $this->user->id;
			// save the cart to database
		}

		if (!is_array($cart)) {
			$this->response = \json_encode([
				'error' => [
					'message' => 'Cart is empty !'
				]
			]);
			return $this;
		}

		if (!array_key_exists($product_id, $cart)) {
			$this->response = \json_encode([
				'error' => [
					'message' => "Product isn't in cart !"
				]
			]);
			return $this;
		}

		if ($sku_id) {
			if (!array_key_exists($sku_id, $cart[$product_id]['variants'])) {
				$this->response = \json_encode([
					'error' => [
						'message' => "Product isn't in the cart !"
					]
				]);
				return $this;
			}

			$cart[$product_id]['variants'][$sku_id]['quantity'] -= $quantity;
			if ($cart[$product_id]['variants'][$sku_id]['quantity'] <= 0)
				unset($cart[$product_id]['variants'][$sku_id]);

			if (empty($cart[$product_id]['variants'])) unset($cart[$product_id]);
		} else {
			if (@$cart[$product_id]['quantity'] && $cart[$product_id]['quantity'] > 0) {
				$cart[$product_id]['quantity'] -= $quantity;
				if ($cart[$product_id]['quantity'] <= 0)
					unset($cart[$product_id]);
			} else
				unset($cart[$product_id]);
		}

		$_SESSION['cart'] = $cart;

		$this->response = \json_encode([
			'success' => [
				'message' => 'Product has been succesfully removed from cart !'
			]
		]);

		return $this;
	}

	/**
	 * ## Odobratie z kosika
	 * Odobranie jedneho kusu z kosika
	 * @param string|int $product - **(required)** - id produktu
	 * @param int $quantity - **(optional)** - kolko produktov z kosika odobrat
	 * 
	 * @return self
	 */
	public function updateQuantity($product_id, ?string $sku_id = null, ?int $quantity = 1): self
	{

		$product_id = (int)$product_id;

		$current_quantity = (int) $this->getQuantity($product_id, $sku_id);
		$quantity = (int) $quantity;

		if ($quantity < 1) {
			$this->response = [
				'error' => [
					'message' => 'Product removed!'
				]
			];

			return $this->removeFrom($product_id, $sku_id, $current_quantity);
		} elseif ($quantity < $current_quantity && $quantity > 0) {
			$this->response = [
				'success' => [
					'message' => 'Product removed!'
				]
			];
			return $this->removeFrom($product_id, $sku_id, ($current_quantity - $quantity));
		} elseif ($quantity > $current_quantity) {
			$this->response = [
				'success' => [
					'message' => 'Product added!'
				]
			];
			return $this->addTo($product_id, $sku_id, ($quantity - $current_quantity));
		}

		return $this;
	}

	public function getResponse(?bool $as_array = null)
	{
		return json_decode($this->response, $as_array ?: false);
	}

	public function getProducts()
	{
		return $this->products;
	}


	/**
	 * Check if product is on stock
	 */
	public function checkProductStock(int $product_id, ?int $quantity = 1): bool
	{
		return true;
	}

	function checkCart($id)
	{
	}

	public function saveCart(): bool
	{
		$json_cart = json_encode($this->getCart());

		$dbCart = Db::get()->getRepository(CartEntity::class)
			->createQueryBuilder('cart')
			->where('cart.user = :userId')
			->setParameter('userId', $this->user->getUsr())
			->getQuery()
			->getOneOrNullResult();

		if ($dbCart)
			$repository = Db::get()->getReference(CartEntity::class, $dbCart->getId());
		else $repository = new CartEntity;

		$repository->setUser($this->user->getUsr());
		$repository->setCart($json_cart);

		Db::get()->persist($repository);
		Db::get()->flush();

		$this->clearCart();

		return true;
	}

	public function getSkus(int $product_id): array
	{
		return $this->getCart()[$product_id]['variants'];
	}

	public function getQuantity(int $product_id, ?string $sku = null): ?int
	{
		if ($sku) return $this->getCart()[$product_id]['variants'][$sku]['quantity'];
		return $this->getCart()[$product_id]['quantity'];
	}

	public function setDiscount($discount)
	{
		$this->discount = $discount;
	}

	public function setDiscountWthTax($discount)
	{
		$this->discount_wthTax = $discount;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function getDiscountWthTax(?bool $formated = null)
	{
		if ($formated) return Price::priceFormat($this->discount_wthTax);
		return $this->discount_wthTax;
	}

	public function productsInCart(): self
	{
		$products = $this->getCart();
		$pm = new ProductManager();

		if (isset($products) && !empty($products) && is_array($products)) {
			foreach ($products as $key => $product) {

				if (@$product['variants']) {
					foreach ($product['variants'] as $sku => $variant) {
						array_push($this->products, $pm->onStock($key)->getProductVariantById($sku));
					}
				} else if (@$product['quantity']) {
					if ($product['quantity'] !== 0 && $product['quantity'] !== null) array_push($this->products, $pm->getOne($key));
					else $this->removeFrom((int) $key);
				} else if (!@$product['quantity']) $this->removeFrom((int) $key);
			}
		}

		if (@$this->getShippingMethod()) {
			$shipping_method = $this->getShippingMethod();
			$allowed_payments = json_decode($shipping_method->getPaymentMethod());
			if (@$this->getPaymentMethod() && !in_array($this->getPaymentMethod()->getId(), $allowed_payments)) {
				$this->setPaymentMethod(null);
			}
		}

		$this->products = array_filter($this->products);

		return $this;
	}

	public function setShippingMethod($shipping_method): void
	{
		$this->shipping_method = $shipping_method;
	}

	public function getShippingMethod(): ?ShippingMethods
	{
		return $this->shipping_method;
	}

	public function setPaymentMethod($payment_method): void
	{
		$this->payment_method = $payment_method;
	}

	public function getPaymentMethod(): ?PaymentMethods
	{
		return $this->payment_method;
	}

	public function setPromocode(?Promocode $promocode = null): void
	{
		$this->promocode = $promocode;
	}

	public function getPromocode(): ?Promocode
	{
		return (new PromocodeManager)->getPromocode($_SESSION['cart-promocode']);
	}

	public function addPromocode($promocode): void
	{
		$this->promocode = $_SESSION['cart-promocode'] = $promocode;
	}

	public function removePromocode(): void
	{
		unset($_SESSION['cart-promocode']);
		$this->promocode = null;
	}

	protected function setTotal(float $total): void
	{
		$this->total = $_SESSION['cart-total'] = $total;
	}

	protected function setSubtotal(float $number): void
	{
		$this->subtotal = $_SESSION['cart-subtotal'] = $number;
	}

	protected function setSubtotalWthTax(float $number): void
	{
		$this->subtotal_wth_tax = $_SESSION['cart-subtotal'] = $number;
	}

	protected function setTaxTotal(float $tax_total): void
	{
		$this->tax_total = $_SESSION['cart-tax-total'] = $tax_total;
	}

	protected function setTotalWthTax(float $total_wth_tax): void
	{
		$this->total_wth_tax = $_SESSION['cart-total_wht_tax'] = $total_wth_tax;
	}

	public function setLoyalityPoints($loyalityPoints): void
	{
		$this->loyalityPoints = $loyalityPoints;
	}

	public function getTotal(bool $as_string = false)
	{
		if ($as_string) return Price::priceFormat($this->total);
		return $this->total;
	}

	public function getSubtotal(?bool $as_string = false)
	{
		if ($as_string) return Price::priceFormat($this->subtotal);
		return $this->subtotal;
	}

	public function getSubtotalWthTax(?bool $as_string = false)
	{
		if ($as_string) return Price::priceFormat($this->subtotal_wth_tax);
		return $this->subtotal_wth_tax;
	}

	public function getTaxTotal(bool $as_string = false)
	{
		if ($as_string) return Price::priceFormat($this->tax_total);
		return $this->tax_total;
	}

	public function getTotalWthTax(bool $as_string = false)
	{
		if ($as_string) return Price::priceFormat($_SESSION['cart-total_wht_tax']);
		return $this->total_wth_tax;
	}

	public function getLoyalityPoints(): ?string
	{
		return $_SESSION['cart-loyalitypoints'];
	}

	public function getCart(): ?array
	{
		return @$_SESSION['cart'] && !empty($_SESSION['cart']) ? $_SESSION['cart'] : [];
	}

	public function setProducts(?array $products)
	{
		$this->products = $products;
	}

	public function isEmpty(): bool
	{
		if (count($this->getCart()) > 0) return false;
		return true;
	}

	public function getCartFromDb()
	{
		if ($this->user->isLoggedIn()) {
			$cart = Db::get()->getRepository(CartEntity::class)
				->createQueryBuilder('cart')
				->where('cart.user = :userId')
				->setParameter('userId', $this->user->getUsr())
				->getQuery()
				->getOneOrNullResult();


			if ($cart) {
				$_SESSION['cart'] = json_decode($cart->getCart(), true);

				$em = Db::get()->getReference(CartEntity::class, $cart->getId());

				Db::get()->remove($em);
				Db::get()->flush();
			}
		}
	}


	/**
	 * ## Prepocitanie cien v kosiku
	 * - vrati vyslednu hodnotu ako medzisucet a celkovu ciastku
	 * - k celkovej ciastke sa pripocita cena za dopravu a platbu
	 * - z medzisucetu sa odrata zlava
	 * 
	 * @return void
	 */
	function calculateCart(): void
	{
		$subtotal = $subtotal_with_tax = $discount = $discount_wthTax = $tax_total = $total_whtTax = $total = 0;

		// calculate total and subtotal w/tax for everyone product in the cart
		if (!$this->isEmpty())
			foreach ($this->products as $product) {
				$quantity = $product instanceof Product ? $this->getQuantity($product->getId()) : $this->getQuantity($product->product->getId(), $product->getId());

				$product_price_without_tax = $product->getPrice() * $quantity;
				$product_price_with_tax = $product->getPriceWthTax() * $quantity;

				if ($product instanceof Product && $product->isInSale() === true) {
					$product_price_without_tax = $product->getSale()->getPrice() * $quantity;
					$product_price_with_tax = $product->getSale()->getPriceWthTax() * $quantity;
				}

				$subtotal = $subtotal + $product_price_without_tax;
				$subtotal_with_tax = $subtotal_with_tax + $product_price_with_tax;
			}

		$total = $subtotal;
		$total_whtTax = $subtotal_with_tax;
		$tax_total = $total_whtTax - $total;

		$promocode = $this->getPromocode();
		if (@$promocode) {
			switch ($promocode->getType()) {
				case PromocodeManager::CURRENCY:
					$discount_value = $promocode->getValue() * (-1);
					$discount_wthTax = $discount_value;

					$total = $subtotal + $discount_value;
					$total_whtTax = $subtotal_with_tax + $discount_wthTax;
					break;
				case PromocodeManager::PERCENTAGE:
					$discount_value = $promocode->getValue();
					$discount_wthTax = $subtotal_with_tax * ($discount_value / -100);
					$discount = $subtotal * ($discount_value / -100);
					$total = $subtotal + $discount;
					$total_whtTax = $subtotal_with_tax + $discount_wthTax;
					$tax_total = $total_whtTax - $total;
					break;
			}
		}

		// While cart is empty set the payment and shipping methods to NULL
		if ($this->isEmpty()) {
			$this->setShippingMethod(null);
			$this->setPaymentMethod(null);
		}

		// check the shipping method, while is set return price of set method
		$shipping_method = $this->getShippingMethod();
		if (@$shipping_method) {
			$shipping_price = $shipping_method->getPrice();

			if ($subtotal_with_tax > $shipping_method->getCartValueHigher()) {
				$shipping_discount = $shipping_method->getPrice() * ($shipping_method->getCartValueDiscount() / -100);
				$shipping_price = $shipping_method->getPrice() + $shipping_discount;
				$discount_wthTax = $discount_wthTax + $shipping_discount;
			}

			$total_whtTax = $total_whtTax + $shipping_price;
		}

		// check the payment method, while is set return price of set method
		$payment_method = $this->getPaymentMethod();
		if (@$payment_method) {
			$total_whtTax = $total_whtTax + $payment_method->getPrice();
		}

		// summary of cart / total,subtotal,promocodes and shiping/payment methods
		$this->setSubtotal($subtotal);
		$this->setSubtotalWthTax($subtotal_with_tax);
		$this->setDiscount($discount);
		$this->setDiscountWthTax($discount_wthTax);
		$this->setTaxTotal($tax_total);
		$this->setTotalWthTax($total_whtTax);
		$this->setTotal($total);
	}

	/**
	 * ## Obnovenie dat
	 * Obnovi produkty v kosiku, ak je uzivatel prihlaseny, alebo ak sa prihlasi.\
	 * Ak ma uzivatel produkty v kosiku a bol odhlaseny z nejakeho dovodu\
	 * obnovia sa mu pri nacitani webu, ked sa znova prihlasi, alebo\
	 * ked sa nacita web.
	 * 
	 * @return void
	 */
	function restoreCart()
	{

		if ($this->user->isLoggedIn()) {
			// if( empty( $_SESSION['cart'] ) ) $_SESSION['cart'] = [];
			// if( count( Db::get()->es_cart( 'user_id', $this->user->id ) ) > 0 ){
			//     $products = Db::get()->es_cart( 'user_id', $this->user->id );
			//     foreach( $products as $key => $product ){
			//         $_SESSION['cart'][ $product['product_id'] ] = [
			//             'product_id' => $product['product_id'],
			//             'quantity' => $product['quantity']
			//         ];
			//     }
			// } else {
			//     if( empty( $_SESSION['cart'] ) ){
			//         $_SESSION['cart'] = [];
			//         $results['products'] = $_SESSION['cart'];
			//         $results['subtotal'] = Price::priceFormat(0);
			//         $results['total'] = Price::priceFormat(0);
			//     } else {
			//         $fields = $_SESSION['cart'];
			//         foreach( $fields as &$product ){
			//             $product['user_id'] = $this->user->id;
			//             Db::get()->es_cart()->insert( $product );
			//         }
			//     }
			// }
		}
	}

	/**
	 * Vycisti kosik
	 * 
	 * @return string
	 */
	function clearCart(): void
	{
		// if( $this->user->isLoggedIn() ){
		//     if( count( Db::get()->es_cart( 'user_id', $this->user->id ) ) > 0 ){
		//         Db::get()->es_cart( 'user_id', $this->user->id )->delete();
		//     }
		// }

		$this->setTotal(0);
		$this->setSubtotal(0);
		$this->setSubtotalWthTax(0);
		$this->setTaxTotal(0);
		$this->setTotalWthTax(0);
		$this->setProducts(null);

		if (isset($_SESSION['cart-seller-instructions'])) unset($_SESSION['cart-seller-instructions']);
		unset($_SESSION['cart']);
		unset($_SESSION['cart-promocode']);
		unset($_SESSION['cart-shipping-method']);
		unset($_SESSION['cart-payment-method']);
		unset($_SESSION['cart-loyalitypoints']);
	}
}

class ProductNotFoundOnStockException extends \Exception
{
	protected $message = "Maly objem na sklade !";
}
class ProductUnexpectedFallException extends \Exception
{
	protected $message = 'Nepodarilo sa pridat produkt ! Nespecifikovana chyba !';
}
