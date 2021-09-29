<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Shop\Utilities\Texts;
use Weblike\Strings\Translate;

/**
 * @ORM\Entity
 * @ORM\Table(name="es__variant")
 */
class Variant
{
	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $slug;

	/**
	 * @ORM\ManyToOne(targetEntity="Weblike\Cms\Shop\Entity\Product", inversedBy="variants")
	 * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true)
	 * @var null|Product
	 */
	protected $product;

	/**
	 * @ORM\OneToMany(targetEntity="Weblike\Cms\Shop\Entity\VariantOption", mappedBy="variant", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true)
	 * @ORM\OrderBy({"position" = "ASC", "name" = "ASC"})
	 * @var Collection
	 */
	protected $options;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\Skus", mappedBy="variants", cascade={"all"})
	 * @var Collection
	 */
	protected $skus;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Shop\Entity\Variant", inversedBy="childs")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
	 * @var null|Variant
	 */
	protected $parent;

	/** 
	 * @ORM\OneToMany(targetEntity="\Weblike\Cms\Shop\Entity\Variant", mappedBy="parent", cascade={"all"})
	 * @ORM\JoinColumn(name="child_id", referencedColumnName="id", nullable=true)
	 * @var Variant[]|Collection
	 */
	protected $childs;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var null|int
	 */
	protected $position;

	public function __construct()
	{
		$this->options = new ArrayCollection();
		$this->childs = new ArrayCollection();
		$this->product = new ArrayCollection();
	}

	public function __get($name)
	{
		return $this->{$name};
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getName(?string $lang = null): string
	{
		return Translate::translate(
			[
				'name' => $this->name
			],
			'name',
			($lang ?? false),
			($lang ? true : false)
		);
	}

	public function setSlug(string $slug): self
	{
		$this->slug = $slug;

		return $this;
	}

	public function getLink(?string $location = null): ?string
	{
		return Texts::itemSlug([
			'slug' => $this->slug
		], $location);
	}

	public function getSlug(?string $lang = null): string
	{
		return Translate::translate(
			[
				'slug' => $this->slug
			],
			'slug',
			($lang ?? false),
			($lang ? true : false)
		);
	}

	public function setOptions(VariantOption $options): self
	{
		$this->options->add($options);

		$options->setVariant($this);

		return $this;
	}

	public function setProduct(Product $product): self
	{
		// $this->product->add($product);

		// $product->setVariant($this);

		$this->product = $product;

		return $this;
	}

	public function getProduct(): ?Product
	{
		return $this->product;
	}

	public function setSkus(Skus $skus): self
	{
		$this->skus->add($skus);

		$skus->addVariants($this);

		return $this;
	}

	public function hasOptions(): bool
	{
		if (count($this->options) > 0) return true;

		return false;
	}

	public function getOptions(): Collection
	{
		return $this->options;
	}

	public function setParent(?Variant $parent = null): self
	{
		$this->parent = $parent;

		return $this;
	}

	public function getParent(): ?self
	{
		return $this->parent;
	}

	public function getChilds(): Collection
	{
		return $this->childs;
	}

	public function hasChilds(): bool
	{
		return count($this->childs) > 0;
	}

	public function isParent(): bool
	{
		return $this->parent === null;
	}

	public function isChild(): bool
	{
		return $this->isParent() === false;
	}

	public function setPosition(?int $position = null): self
	{
		$this->position = $position;

		return $this;
	}

	public function getPosition(): int
	{
		return $this->position;
	}
}
