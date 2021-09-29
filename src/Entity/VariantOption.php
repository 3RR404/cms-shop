<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Shop\Utilities\Texts;
use Weblike\Strings\Translate;

/**
 * @ORM\Entity
 * @ORM\Table(name="es__variant__option")
 */
class VariantOption
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
	 * @ORM\ManyToOne(targetEntity="Weblike\Cms\Shop\Entity\Variant", inversedBy="options", cascade={"all"})
	 * @ORM\JoinColumn(name="variant_id", referencedColumnName="id")
	 * @ORM\OrderBy({"position" = "ASC", "name" = "ASC"})
	 * @var Variant
	 */
	protected $variant;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\Skus", mappedBy="options", cascade={"all"})
	 * @var Collection
	 */
	protected $skus;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Shop\Entity\VariantOption", inversedBy="childs")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
	 * @ORM\OrderBy({"position" = "ASC", "name" = "ASC"})
	 * @var VariantOption
	 */
	protected $parent;

	/** 
	 * @ORM\OneToMany(targetEntity="\Weblike\Cms\Shop\Entity\VariantOption", mappedBy="parent", cascade={"all"})
	 * @ORM\JoinColumn(name="child_id", referencedColumnName="id", nullable=true)
	 * @ORM\OrderBy({"position" = "ASC", "name" = "ASC"})
	 * @var VariantOption[]|Collection
	 */
	protected $childs;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\ProductVariant", mappedBy="variantOptions", cascade={"all"})
	 * @var ProductVariant[]|Collection
	 */
	protected $productVariants;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var null|int
	 */
	protected $position;

	public function __construct()
	{
		$this->skus = new ArrayCollection();
		$this->childs = new ArrayCollection();
		$this->productVariants = new ArrayCollection();
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

	public function setVariant(Variant $variant): self
	{
		$this->variant = $variant;

		return $this;
	}

	public function getVariant(): Variant
	{
		return $this->variant;
	}

	public function setParent(?VariantOption $parent = null): self
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
		return $this->hasChilds();
	}

	public function setSkus(Skus $skus): self
	{
		$this->skus->add($skus);

		$skus->addOptions($this);

		return $this;
	}

	public function setProductVariant(ProductVariant $productVariant): void
	{
		if ($this->productVariants->contains($productVariant))
			return;

		$this->productVariants->add($productVariant);

		$productVariant->addVariantOption($this);
	}

	public function removeProductVariant(ProductVariant $productVariant): void
	{
		if (!$this->productVariants->contains($productVariant))
			return;

		$this->productVariants->removeElement($productVariant);
		$productVariant->removeVariantOption($this);
	}

	public function setPosition(?int $position = null): self
	{
		$this->position = $position;

		return $this;
	}

	public function getPosition(): ?int
	{
		return $this->position;
	}
}
