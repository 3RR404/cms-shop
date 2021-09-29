<?php

declare(strict_types=1);

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Strings\Others;
use Weblike\Strings\Texts;
use Weblike\Strings\Translate;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_product__variant")
 */
class ProductVariant
{
	/** 
	 * @ORM\Id
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $id;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $stock;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	protected $price;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	protected $price_with_tax;

	/**
	 * @ORM\ManyToOne(targetEntity="Weblike\Cms\Shop\Entity\Product", inversedBy="productVariants")
	 * @var Product
	 */
	protected $product;

	/**
	 * @ORM\ManyToOne(targetEntity="Weblike\Cms\Shop\Entity\Product", inversedBy="crossellProductVariants")
	 * @ORM\JoinColumn(nullable=true)
	 * @var null|Product
	 */
	protected $crossellProduct;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\VariantOption", inversedBy="productVariants")
	 * @ORM\OrderBy({"position" = "ASC", "name" = "ASC"})
	 * @var VariantOption[]|Collection
	 */
	protected $variantOptions;

	public function __construct()
	{
		$this->variantOptions = new ArrayCollection();
	}

	public function __get($name)
	{
		return $this->{$name};
	}

	public function isSimpleProduct(): bool
	{
		return false;
	}

	public function setId(string $sku): self
	{
		$this->id = $sku;

		return $this;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setStock(?int $stock = null): self
	{
		$this->stock = $stock;

		return $this;
	}

	public function getStock(): int
	{
		return $this->stock;
	}

	public function setPrice(?float $price = null): self
	{
		$this->price = $price;

		return $this;
	}

	/**
	 * Price without tax
	 *
	 * @param boolean|null $formated
	 * @return string|float
	 */
	public function getPrice(?bool $formated = false)
	{
		if ($formated) return Price::format($this->price);

		return Price::whoolPrice($this->price, 3, false);
	}

	public function setPriceWthTax(float $price, ?int $tax = null): self
	{
		if ($tax === null && $this->product) $tax = $this->product->getTaxrate() ?: 20;

		$this->price_with_tax = $price < 0.01 ? $this->price * ($tax / 100) + $this->price : $price;

		return $this;
	}

	/**
	 * Price with tax
	 *
	 * @param boolean|null $formated
	 * @return string|float
	 */
	public function getPriceWthTax(?bool $formated = false)
	{
		if ($this->price_with_tax < 0.01) $price_with_tax = $this->price * (($this->product->getTaxrate() ?: 20) / 100) + $this->price;

		else $price_with_tax = $this->price_with_tax;

		if ($formated) return Price::format($price_with_tax);

		return Price::whoolPrice($price_with_tax, 3, false);
	}

	public function getWithTax(?bool $formated = false)
	{
		if ($formated) return Price::format(Price::taxPlus($this->price, $this->product->getTaxrate()));

		return (float) Price::whoolPrice(Price::taxPlus($this->price, $this->product->getTaxrate()));
	}


	public function setProduct(Product $product): self
	{
		$this->product = $product;

		return $this;
	}

	public function getProduct(): Product
	{
		return $this->product;
	}

	public function setCrosselProduct(?Product $product = null): self
	{
		$this->crossellProduct = $product;

		return $this;
	}

	public function hasCrossellSet(): bool
	{
		return $this->crossellProduct ? true : false;
	}

	public function getCrossellProduct(): ?Product
	{
		return $this->crossellProduct;
	}

	public function removeVariantOption(VariantOption $variantOption): void
	{
		if (!$this->variantOptions->contains($variantOption))
			return;

		$this->variantOptions->removeElement($variantOption);
		$variantOption->removeProductVariant($this);
	}

	public function addVariantOption(VariantOption $variantOption): void
	{
		if ($this->variantOptions->contains($variantOption))
			return;

		$this->variantOptions->add($variantOption);

		$variantOption->setProductVariant($this);
	}

	public function getVariantOption(): Collection
	{
		return $this->variantOptions;
	}
}
