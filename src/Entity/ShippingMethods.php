<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Strings\Translate;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_shipping_methods")
 */
class ShippingMethods
{

	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $position;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $code;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	protected $price;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $enabled_locations;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $payment_method;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $active;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $cart_value_is_higher;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $cart_value_discount;

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(?string $lang = null): ?string
	{
		return Translate::translate(['name' => $this->name], 'name', ($lang ?? false), ($lang ? true : false));
	}

	public function getPosition(): ?int
	{
		return $this->position;
	}

	public function setPosition(?int $position = 1): self
	{
		$this->position = $position;

		return $this;
	}

	public function getPrice(?bool $formated = false)
	{
		if ($formated) return Price::format($this->price);

		return (float) Price::whoolPrice($this->price);
	}

	public function isActive(): bool
	{
		if ($this->active === 1) return true;
		return false;
	}

	public function setActive(?int $number = null): void
	{
		$this->active = $number;
	}

	public function getEnabledLocations(?bool $as_array = false): string
	{
		return json_decode($this->enabled_locations, $as_array);
	}

	public function getLinkedMethods(?bool $as_array = false): string
	{
		return json_decode($this->enabled_locations, $as_array);
	}

	/**
	 * Set pos.
	 *
	 * @param int|null $pos
	 *
	 * @return ShippingMethods
	 */
	public function setPos($pos = null)
	{
		$this->pos = $pos;

		return $this;
	}

	/**
	 * Get pos.
	 *
	 * @return int|null
	 */
	public function getPos()
	{
		return $this->pos;
	}

	/**
	 * Set name.
	 *
	 * @param string $name
	 *
	 * @return ShippingMethods
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Set code.
	 *
	 * @param string|null $code
	 *
	 * @return ShippingMethods
	 */
	public function setCode($code = null)
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * Get code.
	 *
	 * @return string|null
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Set price.
	 *
	 * @param float|null $price
	 *
	 * @return ShippingMethods
	 */
	public function setPrice($price = null)
	{
		$this->price = $price;

		return $this;
	}

	/**
	 * Set enabledLocations.
	 *
	 * @param string|null $enabledLocations
	 *
	 * @return ShippingMethods
	 */
	public function setEnabledLocations($enabledLocations = null)
	{
		$this->enabled_locations = $enabledLocations;

		return $this;
	}

	/**
	 * Set paymentMethod.
	 *
	 * @param string|null $paymentMethod
	 *
	 * @return ShippingMethods
	 */
	public function setPaymentMethod($paymentMethod = null)
	{
		$this->payment_method = $paymentMethod;

		return $this;
	}

	/**
	 * Get paymentMethod.
	 *
	 * @return string|null
	 */
	public function getPaymentMethod()
	{
		return $this->payment_method;
	}

	public function setCartValueHigher(?float $than_this = null): self
	{
		$this->cart_value_is_higher = $than_this;

		return $this;
	}

	public function getCartValueHigher(): int
	{
		return $this->cart_value_is_higher;
	}

	public function setCartValueDiscount(?int $discount = null): self
	{
		$this->cart_value_discount = $discount;

		return $this;
	}

	public function getCartValueDiscount(): int
	{
		return $this->cart_value_discount;
	}
}
