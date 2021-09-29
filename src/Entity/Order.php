<?php

namespace Weblike\Cms\Shop\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\OrderManager;
use Weblike\Cms\Shop\Utilities\Price;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_order")
 */
class Order
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", nullable=false)
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 */
	protected $orderNumber;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Core\Entity\UserManager")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
	 * @var Collection
	 */
	protected $user;

	/**
	 * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP") 
	 * @var DateTime
	 */
	protected $created_at;

	/**
	 * @ORM\Column(type="text", columnDefinition="LONGTEXT", nullable=true)
	 * @var string
	 */
	protected $products;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Shop\Entity\ShippingMethods")
	 * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id", nullable=false)
	 * @var Collection
	 */
	protected $shipping;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Shop\Entity\PaymentMethods")
	 * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=false)
	 * @var Collection
	 */
	protected $payment;

	/**
	 * @ORM\Column(type="text", columnDefinition="LONGTEXT", nullable=true)
	 */
	protected $shipping_to;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var float
	 */
	protected $subtotal;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var float
	 */
	protected $discount;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var float
	 */
	protected $total_tax;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var float
	 */
	protected $total_wthTax;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var float
	 */
	protected $total;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var float
	 */
	protected $paid;

	/**
	 * @ORM\Column(type="integer", columnDefinition="TINYINT(1)", nullable=true)
	 * @var int
	 */
	protected $state;

	/**
	 * @ORM\Column(type="integer", columnDefinition="TINYINT(1)", nullable=true)
	 * @var int
	 */
	protected $status;

	/**
	 * @ORM\Column(type="string", columnDefinition="VARCHAR(5)", nullable=true)
	 */
	protected $lang;

	/**
	 * @ORM\Column(type="string", columnDefinition="VARCHAR(5)", nullable=true)
	 */
	protected $currency;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $seller_instructions;

	/**
	 * @ORM\Column(type="string", columnDefinition="VARCHAR(10)", nullable=true)
	 */
	protected $token;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Shop\Entity\Promocode")
	 * @ORM\JoinColumn(name="promocode_id", referencedColumnName="id", nullable=true)
	 * @var Collection|null
	 */
	protected $promocode;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\Product", mappedBy="soldProducts", cascade={"all"})
	 * @var Product[]|Collection
	 */
	protected $productSolds;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $package;

	public function __construct()
	{
		$this->user = $this->user !== NULL ? new ArrayCollection() : null;
		$this->payment = new ArrayCollection();
		$this->shipping = new ArrayCollection();
		$this->orderPayment = new ArrayCollection();
		$this->productSolds = new ArrayCollection();
	}

	public function __get($name)
	{
		return $this->{$name};
	}

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set orderNumber.
	 *
	 * @param int $orderNumber
	 *
	 * @return Order
	 */
	public function setOrderNumber($orderNumber)
	{
		$this->orderNumber = $orderNumber;

		return $this;
	}

	/**
	 * Get orderNumber.
	 *
	 * @return int
	 */
	public function getOrderNumber()
	{
		return $this->orderNumber;
	}

	/**
	 * Set createdAt.
	 *
	 * @param DateTime|null $createdAt
	 *
	 * @return Order
	 */
	public function setCreatedAt($createdAt = null)
	{
		$this->created_at = $createdAt;

		return $this;
	}

	/**
	 * Get createdAt.
	 *
	 * @return DateTime|null
	 */
	public function getCreatedAt(): ?DateTime
	{
		return $this->created_at;
	}

	/**
	 * Set products.
	 *
	 * @param string|null $products
	 *
	 * @return Order
	 */
	public function setProducts($products = null)
	{
		$this->products = $products;

		return $this;
	}

	/**
	 * Get products.
	 *
	 * @return array|null
	 */
	public function getProducts()
	{
		return json_decode($this->products, true);
	}

	/**
	 * Get products.
	 *
	 * @return array|null
	 */
	public function getProductsAsObject()
	{
		return json_decode($this->products);
	}

	/**
	 * Set shippingTo.
	 *
	 * @param string|null $shippingTo
	 *
	 * @return Order
	 */
	public function setShippingTo($shippingTo = null)
	{
		$this->shipping_to = $shippingTo;

		return $this;
	}

	/**
	 * Get shippingTo.
	 *
	 * @param null|bool $as_object return object while is true
	 * @return string|object|null
	 */
	public function getShippingTo(?bool $as_object = false)
	{
		if ($as_object) return \json_decode($this->shipping_to);
		return $this->shipping_to;
	}

	/**
	 * Set subtotal.
	 *
	 * @param float|null $subtotal
	 *
	 * @return Order
	 */
	public function setSubtotal($subtotal = null)
	{
		$this->subtotal = $subtotal;

		return $this;
	}

	/**
	 * Get subtotal.
	 *
	 * @return float|null
	 */
	public function getSubtotal()
	{
		return $this->subtotal;
	}

	/**
	 * Set discount.
	 *
	 * @param float|null $discount
	 *
	 * @return Order
	 */
	public function setDiscount($discount = null)
	{
		$this->discount = $discount;

		return $this;
	}

	/**
	 * Get discount.
	 *
	 * @return float|null
	 */
	public function getDiscount()
	{
		return $this->discount;
	}

	/**
	 * Set totalTax.
	 *
	 * @param float|null $totalTax
	 *
	 * @return Order
	 */
	public function setTotalTax($totalTax = null)
	{
		$this->total_tax = $totalTax;

		return $this;
	}

	/**
	 * Get totalTax.
	 *
	 * @return float|null
	 */
	public function getTotalTax()
	{
		return $this->total_tax;
	}

	/**
	 * Set totalWthTax.
	 *
	 * @param float|null $totalWthTax
	 *
	 * @return Order
	 */
	public function setTotalWthTax($totalWthTax = null)
	{
		$this->total_wthTax = $totalWthTax;

		return $this;
	}

	/**
	 * Get totalWthTax.
	 *
	 * @return float|string|null
	 */
	public function getTotalWthTax(?bool $formated = false)
	{
		if ($formated) return Price::format($this->total_wthTax);
		return $this->total_wthTax;
	}

	/**
	 * Set total.
	 *
	 * @param float|null $total
	 *
	 * @return Order
	 */
	public function setTotal(float $total = null)
	{
		$this->total = $total;

		return $this;
	}

	/**
	 * Get total.
	 *
	 * @return float|null
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Set paid.
	 *
	 * @param float|null $paid
	 *
	 * @return Order
	 */
	public function setPaid(?float $paid = null)
	{
		$this->paid = $paid;

		return $this;
	}

	/**
	 * Get paid.
	 *
	 * @return float|null
	 */
	public function getPaid()
	{
		return $this->paid;
	}

	public function isPaid(): bool
	{
		return $this->paid === null ? false : true;
	}

	/**
	 * Set state.
	 *
	 * @param int|null $state
	 *
	 * @return Order
	 */
	public function setState($state = null)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * Get state.
	 *
	 * @return int|null
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Set status.
	 *
	 * @param int|null $status
	 *
	 * @return Order
	 */
	public function setStatus($status = null)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Get status.
	 *
	 * @return int|null
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Set lang.
	 *
	 * @param string|null $lang
	 *
	 * @return Order
	 */
	public function setLang($lang = null)
	{
		$this->lang = $lang;

		return $this;
	}

	/**
	 * Get lang.
	 *
	 * @return string|null
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * Set currency.
	 *
	 * @param string|null $currency
	 *
	 * @return Order
	 */
	public function setCurrency($currency = null)
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * Get currency.
	 *
	 * @return string|null
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * Set sellerInstructions.
	 *
	 * @param string|null $sellerInstructions
	 *
	 * @return Order
	 */
	public function setSellerInstructions($sellerInstructions = null)
	{
		$this->seller_instructions = $sellerInstructions;

		return $this;
	}

	/**
	 * Get sellerInstructions.
	 *
	 * @return string|null
	 */
	public function getSellerInstructions()
	{
		return $this->seller_instructions;
	}

	/**
	 * Set token.
	 *
	 * @param string|null $token
	 *
	 * @return Order
	 */
	public function setToken($token = null)
	{
		$this->token = $token;

		return $this;
	}

	/**
	 * Get token.
	 *
	 * @return string|null
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Set promocode.
	 *
	 * @param \Weblike\Cms\Shop\Entity\Promocode|null $promocode
	 *
	 * @return Order
	 */
	public function setPromocode(?\Weblike\Cms\Shop\Entity\Promocode $promocode = null)
	{
		$this->promocode = $promocode;

		return $this;
	}

	/**
	 * Get promocode.
	 *
	 * @return \Weblike\Cms\Shop\Entity\Promocode|null
	 */
	public function getPromocode()
	{
		return $this->promocode;
	}

	/**
	 * Set package.
	 *
	 * @param string|null $package
	 *
	 * @return Order
	 */
	public function setPackage($package = null)
	{
		$this->package = $package;

		return $this;
	}

	/**
	 * Get package.
	 *
	 * @return string|null
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * Set user.
	 *
	 * @param \Weblike\Cms\Core\Entity\UserManager $user
	 *
	 * @return null|Order
	 */
	public function setUser(?\Weblike\Cms\Core\Entity\UserManager $user = null)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user.
	 *
	 * @return null|\Weblike\Cms\Core\Entity\UserManager
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set shipping.
	 *
	 * @param \Weblike\Cms\Shop\Entity\ShippingMethods $shipping
	 *
	 * @return Order
	 */
	public function setShipping(\Weblike\Cms\Shop\Entity\ShippingMethods $shipping)
	{
		$this->shipping = $shipping;

		return $this;
	}

	/**
	 * Get shipping.
	 *
	 * @return \Weblike\Cms\Shop\Entity\ShippingMethods
	 */
	public function getShipping()
	{
		return $this->shipping;
	}

	/**
	 * Set payment.
	 *
	 * @param \Weblike\Cms\Shop\Entity\PaymentMethods $payment
	 *
	 * @return Order
	 */
	public function setPayment(\Weblike\Cms\Shop\Entity\PaymentMethods $payment)
	{
		$this->payment = $payment;

		return $this;
	}

	/**
	 * Get payment.
	 *
	 * @return \Weblike\Cms\Shop\Entity\PaymentMethods
	 */
	public function getPayment()
	{
		return $this->payment;
	}

	public function setProductSold(Product $product): void
	{
		if ($this->productSolds->contains($product))
			return;

		$this->productSolds->add($product);
		$product->setSoldProduct($this);
	}

	/**
	 * @return Product[]|Collection
	 */
	public function getProductSold()
	{
		return $this->productSolds;
	}

	public function removeProductSold(Product $product): void
	{
		if (!$this->productSolds->contains($product)) {
			return;
		}
		$this->productSolds->removeElement($product);
		$product->removeSoldProduct($this);
	}

	public function getOrderStateText(): string
	{
		switch ($this->state) {
			case OrderManager::PAID:
				return 'Uhradena';
				break;
			case OrderManager::PARTLY_PAID:
				return 'Ciastocne uhradena';
				break;
			case OrderManager::CANCELED:
				return 'Zrusena';
				break;
			case OrderManager::REFUNDED:
				return 'Refundovana';
				break;
			case OrderManager::STORNO:
				return 'Storno';
				break;
			default:
				return 'Caka na platbu';
		}
	}

	/**
	 * @return null?string
	 */
	public function getClass(): ?string
	{

		switch ($this->state) {
			case OrderManager::PAID:
				return 'success';
				break;
				// case OrderManager::PENDING :
				//     return 'danger';
				// break;
			case OrderManager::CANCELED:
				return 'danger';
				break;
			case OrderManager::REFUNDED:
				return 'warning';
				break;
			case OrderManager::PARTLY_PAID:
				return 'info';
				break;
		}

		return null;
	}

	public function getProductVariantById(string $sku): ?ProductVariant
	{
		return Db::get()->getRepository(ProductVariant::class)
			->createQueryBuilder('pv')
			->where('pv.id = :sku')
			->setParameter('sku', $sku)
			->getQuery()
			->getOneOrNullResult();
	}
}
