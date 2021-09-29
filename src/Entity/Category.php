<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Core\App;
use Weblike\Strings\Texts;
use Weblike\Strings\Translate;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_product_category")
 */
class Category
{

	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $slug;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	protected $icon;

	/** 
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Shop\Entity\Category", inversedBy="childs")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
	 * @var Collection|self
	 */
	protected $parent;

	/** 
	 * @ORM\OneToMany(targetEntity="\Weblike\Cms\Shop\Entity\Category", mappedBy="parent", cascade={"all"})
	 * @ORM\JoinColumn(name="child_id", referencedColumnName="id", nullable=true)
	 * @var Collection|self
	 */
	protected $childs;

	/**
	 * @ORM\ManyToMany(targetEntity="\Weblike\Cms\Shop\Entity\Product", mappedBy="category", cascade={"persist","detach"})
	 * @var Collection
	 */
	protected $product;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	protected $image;

	public function __construct()
	{
		$this->product = new ArrayCollection();
	}

	/**
	 * @return Collection|Product[]
	 */
	public function getProduct(): ?Collection
	{
		return $this->product;
	}

	public function setProduct(Product $product): void
	{
		if ($this->product->contains($product))
			return;

		$this->product->add($product);
		$product->setCategory($this);
	}

	public function removeProduct(Product $product): void
	{
		if (!$this->product->contains($product)) {
			return;
		}
		$this->product->removeElement($product);
		$product->removeCategory($this);
	}

	public function getChilds()
	{
		return $this->childs;
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
		return Translate::translate(['name' => $this->name], 'name', ($lang ?? false), ($lang ? true : false));
	}

	public function setSlug(string $slug): self
	{
		$this->slug = $slug;

		return $this;
	}

	public function getSlug(?string $location = null): ?string
	{
		if ($location === null)
			return Translate::translate(['slug' => $this->slug], 'slug', App::getActiveLang(), true);

		return Texts::itemSlug(['slug' => $this->slug], $location);
	}

	public function getLink(?string $location = null): ?string
	{
		return Texts::itemSlug(['slug' => $this->slug], $location);
	}

	public function setParent(?Category $parent = null): self
	{
		$this->parent = $parent;

		return $this;
	}

	public function getParent(): ?self
	{
		return $this->parent;
	}

	// public function isChild() : bool
	// {
	//     return $this->parent > 0 ?? false;
	// }

	// public function isParent() : bool
	// {
	//     return $this->parent == 0 ?? false;
	// }

	public function setImage(?string $image = null): self
	{
		$this->image = $image;

		return $this;
	}

	public function getImage(): ?string
	{
		return $this->image;
	}

	public function getThumbnail(): ?string
	{
		return $this->getImage();
	}

	public function hasThumbnail(): bool
	{
		return $this->image !== null;
	}

	public function setIcon(?string $icon = null): self
	{
		$this->icon = $icon;

		return $this;
	}

	public function hasIcon(): bool
	{
		return $this->icon !== null && !empty($this->icon);
	}

	public function getIcon(): ?string
	{
		return $this->icon;
	}
}
