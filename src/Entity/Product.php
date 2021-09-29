<?php

declare(strict_types=1);

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr\Join;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Utilities\Price;
use Weblike\Plugins\User;
use Weblike\Strings\Others;
use Weblike\Strings\Texts;
use Weblike\Strings\Translate;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_product")
 */
class Product
{
	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $stock;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $availability;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 * @var int
	 */
	protected $status;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $minimum_order;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $package;

	/**
	 * @ORM\Column(type="bigint", nullable=true)
	 * @var int
	 */
	protected $ean_code;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $catalogue_number;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $seo_title;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $seo_keywords;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $amount;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $slug;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $model;

	/**
	 * @var Product[]|Collection
	 */
	protected $relevants;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $product_tax;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $product_code;

	/**
	 * @ORM\OneToOne(targetEntity="Product")
	 * @ORM\JoinColumn(name="part_id", referencedColumnName="id", nullable=true)
	 * @var Product
	 */
	protected $part;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $price;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $price_with_tax;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $image;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $measure;

	/**
	 * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP")
	 * @var \DateTime
	 */
	protected $created_at;

	/**
	 * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
	 * @var \DateTime
	 */
	protected $inserted;

	/**
	 * @ORM\Column(type="text", columnDefinition="LONGTEXT", nullable=true)
	 * @var string
	 */
	protected $content;

	/**
	 * @ORM\Column(type="text", columnDefinition="LONGTEXT", nullable=true)
	 * @var string
	 */
	protected $description;

	/**
	 * @ORM\Column(type="text", columnDefinition="LONGTEXT", nullable=true)
	 * @var string
	 */
	protected $technical_params;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $seo_description;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $has_variants;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var null|int
	 */
	protected $in_sale;

	/**
	 * @ORM\OneToOne(targetEntity="Weblike\Cms\Shop\Entity\Sale")
	 * @ORM\JoinColumn(nullable=true)
	 * @var null|Sale
	 */
	protected $sale;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\Category", inversedBy="product")
	 * @var Category[]|Collection
	 */
	protected $category;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\Order", inversedBy="productSolds")
	 * @var Order[]|Collection
	 */
	protected $soldProducts;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\Wishlist", mappedBy="products", cascade={"all"})
	 * @var Wishlist[]|Collection
	 */
	protected $wishlist;

	/**
	 * @ORM\OneToMany(targetEntity="Weblike\Cms\Shop\Entity\Skus", mappedBy="product", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true)
	 * @var Skus[]|Collection
	 */
	protected $skus;

	/**
	 * @ORM\OneToMany(targetEntity="Weblike\Cms\Shop\Entity\Variant", mappedBy="product", cascade={"all"})
	 * @ORM\OrderBy({"position" = "ASC"})
	 * @var Variant[]|Collection
	 */
	protected $variants;

	/**
	 * @ORM\OneToMany(targetEntity="Weblike\Cms\Shop\Entity\ProductVariant", mappedBy="product", cascade={"all"})
	 * @ORM\OrderBy({"price" = "ASC"})
	 * @var ProductVariant[]|Collection
	 */
	protected $productVariants;

	/**
	 * @ORM\OneToMany(targetEntity="Weblike\Cms\Shop\Entity\ProductVariant", mappedBy="crossellProduct", cascade={"all"})
	 * @var ProductVariant[]|Collection
	 */
	protected $crossellProductVariants;

	/**
	 * @var int
	 */
	protected $tag;

	public function __construct()
	{
		$this->category = new ArrayCollection();
		$this->soldProducts = new ArrayCollection();
	}

	public function __get($name)
	{
		return $this->{$name};
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(?string $lang = null): string
	{
		return Translate::translate(['name' => $this->name], 'name', ($lang ?? false), ($lang ? true : false));
	}

	public function getSlug(?string $location = null): ?string
	{
		return Texts::itemSlug(['slug' => $this->slug], $location);
	}

	public function isSimpleProduct(): bool
	{
		return true;
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

	/**
	 * Set price with tax
	 *
	 * @param float $price
	 * @param integer|null $tax
	 * @return self
	 */
	public function setPriceWthTax(float $price, ?int $tax = null): self
	{
		if ($tax === null) $tax = $this->product_tax ?: 20;

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
		if ($this->price_with_tax < 0.01) $price_with_tax = $this->price * (($this->product_tax ?: 20) / 100) + $this->price;

		else $price_with_tax = $this->price_with_tax;

		if ($formated) return Price::format($price_with_tax);

		return Price::whoolPrice($price_with_tax, 3, false);
	}

	/**
	 * Price with tax
	 *
	 * @param boolean|null $formated
	 * @return float|string
	 */
	public function getWithTax(?bool $formated = false)
	{
		if ($formated) return Price::format(Price::taxPlus($this->price, $this->getTaxrate()));

		return (float) Price::whoolPrice(Price::taxPlus($this->price, $this->getTaxrate()));
	}

	/**
	 * Tax rate
	 *
	 * @return integer|null
	 */
	public function getTaxrate(): ?int
	{
		return $this->product_tax;
	}

	/**
	 * Stock
	 *
	 * @return integer|null
	 */
	public function getStock(): ?int
	{
		return $this->stock;
	}

	/**
	 * Status number
	 * - published / sheduled / draft
	 *
	 * @return integer|null
	 */
	public function getStatus(): ?int
	{
		return $this->status;
	}

	/**
	 * String of image path
	 *
	 * @return string|null
	 */
	public function getImage(): ?string
	{
		return $this->image;
	}

	/**
	 * Availability string
	 * - enable / disable
	 *
	 * @return string|null
	 */
	public function getAvailability(): ?string
	{
		return $this->availability;
	}

	/**
	 * While is available return true
	 *
	 * @return boolean
	 */
	public function isAvailable(): bool
	{
		return $this->availability === 'enable';
	}

	/**
	 * Number of minimal pcs to buy
	 *
	 * @return integer|null
	 */
	public function getMinOrder(): ?int
	{
		return $this->minimum_order;
	}

	/**
	 * Check if product have image path
	 *
	 * @return boolean
	 */
	public function hasThumbnail(): bool
	{
		if ($this->image) return true;
		return false;
	}

	/**
	 * Return image path string
	 *
	 * @return string
	 */
	public function getThumbnail(): string
	{
		return $this->image;
	}

	/**
	 * Product description
	 *
	 * @param string|null $lang
	 * @return string|null
	 */
	public function getContent(?string $lang = null): ?string
	{
		return Translate::translate(['content' => $this->content], 'content', ($lang ?? false), ($lang ? true : false));
	}

	/**
	 * Product description
	 *
	 * @param string|null $lang
	 * @return string|null
	 */
	public function getDescription(?string $lang = null): ?string
	{
		return Translate::translate(['description' => $this->description], 'description', ($lang ?? false), ($lang ? true : false));
	}

	/**
	 * Product parameters from text editor - siml. desc
	 *
	 * @param string|null $lang
	 * @return string|null
	 */
	public function getTechnicalParams(?string $lang = null): ?string
	{
		return Translate::translate(['technicalParams' => $this->technical_params], 'technicalParams', ($lang ?? false), ($lang ? true : false));
	}

	/**
	 * Set product name
	 * - setup json format {"sk":"Test product","en":"Test product eng"}
	 *
	 * @param string $string
	 * @return void
	 */
	public function setName(string $string): void
	{
		$this->name = $string;
	}

	/**
	 * Slug a.k.a. product request address
	 * - example.com/e-shop/test-product
	 * - /e-shop is a param string
	 *
	 * @param string $string
	 * @return void
	 */
	public function setSlug(string $string): void
	{
		$this->slug = $string;
	}

	/**
	 * Set currency
	 *
	 * @param string $string
	 * @return void
	 */
	public function setAmount(string $string): void
	{
		$this->amount = $string;
	}

	/**
	 * Set price without tax
	 *
	 * @param float $number
	 * @return void
	 */
	public function setPrice(float $number): void
	{
		$this->price = $number;
	}

	/**
	 * Set number of status
	 * - published / sheduled / draft
	 *
	 * @param integer|null $number
	 * @return void
	 */
	public function setStatus(?int $number = null): void
	{
		$this->status = $number;
	}

	/**
	 * Set availability
	 * - enabled / disabled
	 *
	 * @param string|null $string
	 * @return void
	 */
	public function setAvailability(?string $string = null): void
	{
		$this->availability = $string;
	}

	/**
	 * Set stock.
	 *
	 * @param int|null $stock
	 *
	 * @return Product
	 */
	public function setStock($stock = null)
	{
		$this->stock = $stock;

		return $this;
	}

	/**
	 * Set minimumOrder.
	 *
	 * @param int|null $minimumOrder
	 *
	 * @return Product
	 */
	public function setMinimumOrder($minimumOrder = null)
	{
		$this->minimum_order = $minimumOrder;

		return $this;
	}

	/**
	 * Get minimumOrder.
	 *
	 * @return int|null
	 */
	public function getMinimumOrder()
	{
		return $this->minimum_order;
	}

	/**
	 * Set package.
	 *
	 * @param int|null $package
	 *
	 * @return Product
	 */
	public function setPackage($package = null)
	{
		$this->package = $package;

		return $this;
	}

	/**
	 * Get package.
	 *
	 * @return int|null
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * Set eanCode.
	 *
	 * @param int|null $eanCode
	 *
	 * @return Product
	 */
	public function setEanCode($eanCode = null)
	{
		$this->ean_code = $eanCode;

		return $this;
	}

	/**
	 * Get eanCode.
	 *
	 * @return int|null
	 */
	public function getEanCode()
	{
		return $this->ean_code;
	}

	/**
	 * Set catalogueNumber.
	 *
	 * @param int|null $catalogueNumber
	 *
	 * @return Product
	 */
	public function setCatalogueNumber($catalogueNumber = null)
	{
		$this->catalogue_number = $catalogueNumber;

		return $this;
	}

	/**
	 * Get catalogueNumber.
	 *
	 * @return int|null
	 */
	public function getCatalogueNumber()
	{
		return $this->catalogue_number;
	}


	/**
	 * Set seoTitle.
	 *
	 * @param string|null $seoTitle
	 *
	 * @return Product
	 */
	public function setSeoTitle($seoTitle = null)
	{
		$this->seo_title = $seoTitle;

		return $this;
	}

	/**
	 * Get seoTitle.
	 *
	 * @return string|null
	 */
	public function getSeoTitle(?string $lang = null)
	{
		return Translate::translate(['name' => $this->seo_title], 'name', ($lang ?? false), ($lang ? true : false));
	}

	/**
	 * Set seoKeywords.
	 *
	 * @param string|null $seoKeywords
	 *
	 * @return Product
	 */
	public function setSeoKeywords($seoKeywords = null)
	{
		$this->seo_keywords = $seoKeywords;

		return $this;
	}

	/**
	 * Get seoKeywords.
	 *
	 * @return string|null
	 */
	public function getSeoKeywords(?string $lang = null)
	{
		return Translate::translate(['keywords' => $this->seo_keywords], 'keywords', ($lang ?? false), ($lang ? true : false));
	}


	/**
	 * Get amount.
	 *
	 * @return string
	 */
	public function getAmount()
	{
		return $this->amount;
	}


	/**
	 * Set model.
	 *
	 * @param string|null $model
	 *
	 * @return Product
	 */
	public function setModel($model = null)
	{
		$this->model = $model;

		return $this;
	}

	/**
	 * Get model.
	 *
	 * @return string|null
	 */
	public function getModel(?string $lang = null)
	{
		return Translate::translate(['name' => $this->name], 'name', ($lang ?? false), ($lang ? true : false));
	}

	/**
	 * Set productTax.
	 *
	 * @param int|null $productTax
	 *
	 * @return Product
	 */
	public function setProductTax($productTax = null)
	{
		$this->product_tax = $productTax;

		return $this;
	}

	/**
	 * Get productTax.
	 *
	 * @return int|null
	 */
	public function getProductTax()
	{
		return $this->product_tax;
	}

	/**
	 * Set productCode.
	 *
	 * @param int|null $productCode
	 *
	 * @return Product
	 */
	public function setProductCode($productCode = null)
	{
		$this->product_code = $productCode;

		return $this;
	}

	/**
	 * Get productCode.
	 *
	 * @return int|null
	 */
	public function getProductCode()
	{
		return $this->product_code;
	}

	/**
	 * Set partNo.
	 *
	 * @param Product|null $partNo
	 *
	 * @return Product
	 */
	public function setPartNo(Product $partNo = null)
	{
		$this->part = $partNo;

		return $this;
	}

	/**
	 * Get partNo.
	 *
	 * @return Product|null
	 */
	public function getPartNo()
	{
		return $this->part;
	}

	/**
	 * Set image.
	 *
	 * @param string|null $image
	 *
	 * @return Product
	 */
	public function setImage($image = null)
	{
		$this->image = $image;

		return $this;
	}

	/**
	 * Set measure.
	 *
	 * @param string|null $measure
	 *
	 * @return Product
	 */
	public function setMeasure($measure = null)
	{
		$this->measure = $measure;

		return $this;
	}

	/**
	 * Get measure.
	 *
	 * @return string|null
	 */
	public function getMeasure()
	{
		return $this->measure;
	}

	/**
	 * Set createdAt.
	 *
	 * @param \DateTime|null $createdAt
	 *
	 * @return Product
	 */
	public function setCreatedAt($createdAt = null)
	{
		$this->created_at = $createdAt;

		return $this;
	}

	/**
	 * Get createdAt.
	 *
	 * @return \DateTime|null
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * Set inserted.
	 *
	 * @param \DateTime $inserted
	 *
	 * @return Product
	 */
	public function setInserted($inserted)
	{
		$this->inserted = $inserted ?: new \DateTime('NOW');

		return $this;
	}

	/**
	 * Get inserted.
	 *
	 * @return \DateTime
	 */
	public function getInserted()
	{
		return $this->inserted;
	}

	/**
	 * Set content.
	 *
	 * @param string|null $content
	 *
	 * @return Product
	 */
	public function setContent($content = null)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Set description.
	 *
	 * @param string|null $description
	 *
	 * @return Product
	 */
	public function setDescription($description = null)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Set description.
	 *
	 * @param string|null $description
	 *
	 * @return Product
	 */
	public function setTechnicalParams($technicalParams = null)
	{
		$this->technical_params = $technicalParams;

		return $this;
	}

	/**
	 * Set seoDescription.
	 *
	 * @param string|null $seoDescription
	 *
	 * @return Product
	 */
	public function setSeoDescription($seoDescription = null)
	{
		$this->seo_description = $seoDescription;

		return $this;
	}

	/**
	 * Set product insale on true / false
	 * - integer 1 / 0
	 *
	 * @param integer $isOrNot
	 * @return self
	 */
	public function setInSale(int $isOrNot = 0): self
	{
		$this->in_sale = $isOrNot;

		return $this;
	}

	/**
	 * Return while product is in sale is true
	 *
	 * @return boolean
	 */
	public function isInSale(): bool
	{
		return $this->in_sale === 1;
	}

	/**
	 * Setup sale id reference
	 *
	 * @param null|Sale $sale
	 * @return self
	 */
	public function setSale(?Sale $sale = null): self
	{
		$this->sale = $sale;

		return $this;
	}

	/**
	 * Return sale ID reference
	 *
	 * @return Sale|null
	 */
	public function getSale(): ?Sale
	{
		return $this->sale;
	}

	/**
	 * Get seoDescription.
	 *
	 * @return string|null
	 */
	public function getSeoDescription(?string $lang = null)
	{
		return Translate::translate(['description' => $this->seo_description], 'description', ($lang ?? false), ($lang ? true : false));
	}

	/**
	 * Return multiple categories
	 *
	 * @return null|Collection
	 */
	public function getCategory(): ?Collection
	{
		return $this->category;
	}

	/**
	 * Set multiple categories by product
	 *
	 * @param Category $category
	 * @return void
	 */
	public function setCategory(Category $category): void
	{
		if ($this->category->contains($category))
			return;

		$this->category->add($category);

		$category->setProduct($this);
	}

	/**
	 * Remove the category from reference
	 *
	 * @param Category $category
	 * @return void
	 */
	public function removeCategory(Category $category): void
	{
		if (!$this->category->contains($category)) {
			return;
		}
		$this->category->removeElement($category);
		$category->removeProduct($this);
	}

	public function setProductToWishlist(Wishlist $wishlist): void
	{
		if ($this->wishlist->contains($wishlist))
			return;

		$this->wishlist->add($wishlist);

		$wishlist->setProduct($this);
	}

	public function unsetProductInWishlist(Wishlist $wishlist): void
	{
		if (!$this->wishlist->contains($wishlist))
			return;

		$this->wishlist->removeElement($wishlist);

		$wishlist->unsetProduct($this);
	}

	/**
	 * Set multiple sold by product
	 *
	 * @param Order $order
	 * @return void
	 */
	public function setSoldProduct(Order $order): void
	{
		if ($this->soldProducts->contains($order))
			return;

		$this->soldProducts->add($order);

		$order->setProductSold($this);
	}

	public function getSoldProduct()
	{
		return $this->soldProducts;
	}

	/**
	 * Remove the sold from reference
	 *
	 * @param Order $order
	 * @return void
	 */
	public function removeSoldProduct(Order $order): void
	{
		if (!$this->soldProducts->contains($order)) {
			return;
		}
		$this->soldProducts->removeElement($order);
		$order->removeProductSold($this);
	}

	/**
	 * Set product variant true / false
	 *
	 * @param integer|null $has_variants
	 * @return self
	 */
	public function setHasVariant(?int $has_variants = null): self
	{
		$this->has_variants = $has_variants;

		return $this;
	}

	/**
	 * Set variant reference
	 *
	 * @param Variant $variant
	 * @return void
	 */
	public function setVariant(Variant $variant): void
	{
		if ($this->variants->contains($variant))
			return;

		$this->variants->add($variant);

		$variant->setProduct($this);
	}

	/**
	 * Return variant Reference
	 *
	 * @return Collection|null
	 */
	public function getVariants(): ?Collection
	{
		return $this->variants;
	}

	/**
	 * Return true while have product variants
	 *
	 * @return boolean
	 */
	public function hasVariants(): bool
	{
		return $this->has_variants === 1;
	}

	/**
	 * Return productVariants references
	 *
	 * @return Collection
	 */
	public function getProductVariants(): Collection
	{
		return $this->productVariants;
	}

	public function hasCrossellSet(): bool
	{
		return $this->crossellProductVariants && count($this->crossellProductVariants) > 0 ?: false;
	}

	/**
	 * Return productVariants references
	 *
	 * @return Collection
	 */
	public function getCrossellProductVariants(): Collection
	{
		return $this->crossellProductVariants;
	}

	/**
	 * @param string|int $sku
	 * @return ProductVariant|null
	 */
	public function getProductVariantById($sku): ?ProductVariant
	{
		return Db::get()->getRepository(ProductVariant::class)
			->createQueryBuilder('pv')
			->where('pv.id = :sku')
			->setParameter('sku', $sku)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Return lowest variant price
	 *
	 * @param boolean|null $formated while is true return string
	 * @return ProductVariant|null
	 */
	public function getProductVariantStartingPrice(): ?ProductVariant
	{
		return Db::get()->getRepository(ProductVariant::class)
			->createQueryBuilder('pv')
			->where('pv.product = :product_id')
			->andWhere('pv.price > 0')
			->setParameters(['product_id' => $this->id])
			->orderBy('pv.price', 'ASC')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * looking for product in user wishlist
	 * @param string|int $user_id
	 * @return boolean
	 */
	public function isInWishlist($user_id)
	{
		$result = Db::get()->getRepository(Wishlist::class)
			->createQueryBuilder('wishlist')
			->leftJoin('wishlist.products', 'product')
			->where('wishlist.user = :uid')
			->andWhere('product.id = :pid')
			->setParameters([
				'uid' => $user_id,
				'pid' => $this->id
			])
			->getQuery()
			->getOneOrNullResult();

		if ($result) return true;

		return false;
	}
}
