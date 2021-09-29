<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_customer__wishlist")
 */
class Wishlist
{

	public function __construct()
	{
		$this->products = new ArrayCollection;
	}

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="\Weblike\Cms\Core\Entity\UserManager")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 * @var \Weblike\Cms\Core\Entity\UserManager
	 */
	protected $user;


	/**
	 * @ORM\ManyToMany(targetEntity="\Weblike\Cms\Shop\Entity\Product", inversedBy="wishlist")
	 * @var \weblike\Cms\Shop\Entity\Product[]|Collection
	 */
	protected $products;

	public function getId()
	{
		return $this->id;
	}

	public function setUser(\Weblike\Cms\Core\Entity\UserManager $user): self
	{
		$this->user = $user;

		return $this;
	}

	public function setProduct(Product $product): void
	{
		if ($this->products->contains($product))
			return;

		$this->products->add($product);

		$product->setProductToWishlist($this);
	}

	public function getProducts(): Collection
	{
		return $this->products;
	}

	public function unsetProduct(Product $product): void
	{
		if (!$this->products->contains($product))
			return;

		$this->products->removeElement($product);

		$product->unsetProductInWishlist($this);
	}

	public function isProductInWishList(int $product_id): bool
	{

		foreach ($this->getProducts() as $product) {
			if ($product->getId() === $product_id) return true;
		}

		return false;
	}
}
