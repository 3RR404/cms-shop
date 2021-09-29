<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Strings\Translate;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_payment_methods")
 */
class PaymentMethods
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
	protected $pos;

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
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $payment_transfer;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $active;

	public function getId(): int
	{
		return $this->id;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getName(?string $lang = null): ?string
	{
		return Translate::translate(['name' => $this->name], 'name', ($lang ?? false), ($lang ? true : false));
	}

	public function getPosition(): ?int
	{
		return $this->position;
	}


	public function setPrice(?float $price = 0): self
	{
		$this->price = $price;

		return $this;
	}

	public function getPrice(?bool $formated = false)
	{
		if ($formated) return Price::format($this->price);

		return (float) Price::whoolPrice($this->price);
	}

	public function getPaymentTransfer(): ?int
	{
		return $this->payment_transfer;
	}

	public function isOnline(): bool
	{
		if ($this->payment_transfer === 1) return true;
		return false;
	}

	public function setActive(?int $number = null): void
	{
		$this->active = $number;
	}

	public function isActive(): bool
	{
		if ($this->active === 1) return true;
		return false;
	}
}
