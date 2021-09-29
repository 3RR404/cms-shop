<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Shop\Utilities\Price;

/**
 * @ORM\Entity
 * @ORM\Table(name="es__sale")
 */
class Sale
{

	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var null|float
	 */
	protected $price;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @var null|float
	 */
	protected $price_wht_tax;

	/**
	 * @ORM\OneToOne(targetEntity="Weblike\Cms\Shop\Entity\Product")
	 * @ORM\JoinColumn(nullable=true)
	 * @var Product|null
	 */
	protected $product;

	public function getId(): int
	{
		return $this->id;
	}

	public function setPrice(?float $price = null): self
	{
		$this->price = $price;

		return $this;
	}

	/**
	 * Price of product in "sale" without tax
	 *
	 * @param boolean|null $formated
	 * @return string|float|null
	 */
	public function getPrice(?bool $formated = false)
	{
		if ($formated) return Price::format($this->price);

		return Price::whoolPrice($this->price, 3, false);
	}

	public function setPriceWthTax(?float $priceWthTax = null): self
	{
		$this->price_wht_tax = $priceWthTax;

		return $this;
	}

	/**
	 * Price of product **in sale** with tax
	 *
	 * @param boolean|null $formated
	 * @return string|float|null
	 */
	public function getPriceWthTax(?bool $formated = false)
	{
		if ($formated) return Price::format($this->price_wht_tax);

		return $this->price_wht_tax;
	}



	public function setProduct(?Product $product = null): self
	{
		$this->product = $product->setSale($this);

		return $this;
	}
}
