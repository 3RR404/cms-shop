<?php

namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Category;
use Weblike\Cms\Shop\Entity\Order;
use Weblike\Cms\Shop\Entity\Product;
use Weblike\Cms\Shop\Entity\Sale;
use Weblike\Cms\Shop\Entity\Variant;
use Weblike\Cms\Shop\Entity\VariantAttribute;
use Weblike\Cms\Shop\Entity\VariantOption;
use Weblike\Cms\Shop\Interfaces\IProduct;
use Weblike\Strings\Others;

/**
 * Sprava jedneho produktu
 */
class ProductManager implements IProduct
{
	protected $product_data;

	/** @var Shop $shopConfig */
	protected $shopConfig;

	/** Query result */
	protected $query;

	/** Array results */
	protected $result;

	function __construct()
	{
		$this->shopConfig = (new Shop)->getConfig();

		$this->variantManager = (new VariantManager);
	}

	public function getQueryResult()
	{
		return $this->query;
	}

	public function getFetchResult()
	{
		return $this->result;
	}

	public function getAll(?bool $as_array = false)
	{
		return Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getOne(int $product_id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->where('p.id = ?0')
			->setParameter(0, $product_id)
			->getQuery()
			->getOneOrNullResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	/**
	 * Show all product while is on stock
	 *
	 * @param boolean|null $as_array
	 * @return Product[]|array
	 */
	public function allOnStock(?bool $as_array = false)
	{
		$onStock = Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->where('p.availability = :avalable')
			->setParameter('avalable', 'enable')
			->orderBy('p.product_code', 'DESC');

		// if ($this->shopConfig->show_price_off && $this->shopConfig->show_price_off === 'off') {
		// 	$onStock->andWhere(
		// 		'(p.price > :isNull AND (p.has_variants = :isNull OR p.has_variants IS NULL)) 
		//             OR
		//         (p.price = :isNull AND p.has_variants > :isNull AND attrs.price > :isNull)'
		// 	)->setParameter('isNull', 0);
		// }

		// if ($this->shopConfig->show_null_stock && $this->shopConfig->show_null_stock === 'off')
		// 	$onStock->andWhere(
		// 		'(p.stock > :onStock AND (p.has_variants = :onStock OR p.has_variants IS NULL))
		//             OR
		//         p.stock = :onStock AND p.has_variants > :onStock and attrs.stock > :onStock'
		// 	)->setParameter('onStock', 0);


		return $onStock->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function allOnStockPaginate(?bool $as_array = false): self
	{
		$onStock = Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->where('p.availability = :avalable')
			->setParameter('avalable', 'enable')
			->orderBy('p.product_code', 'DESC');

		// if ($this->shopConfig->show_price_off && $this->shopConfig->show_price_off === 'off') {
		// 	$onStock->andWhere(
		// 		'(p.price > :isNull AND (p.has_variants = :isNull OR p.has_variants IS NULL)) 
		//             OR
		//         (p.price = :isNull AND p.has_variants > :isNull AND attrs.price > :isNull)'
		// 	)->setParameter('isNull', 0);
		// }

		// if ($this->shopConfig->show_null_stock && $this->shopConfig->show_null_stock === 'off')
		// 	$onStock->andWhere(
		// 		'(p.stock > :onStock AND (p.has_variants = :onStock OR p.has_variants IS NULL))
		//             OR
		//         p.stock = :onStock AND p.has_variants > :onStock and attrs.stock > :onStock'
		// 	)->setParameter('onStock', 0);


		$this->query = $onStock->getQuery();

		$this->result = $this->query->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);

		return $this;
	}

	public function allOnStockByPrice(?string $sort = 'asc'): self
	{
		$this->query = Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->leftJoin('p.productVariants', 'prodVar', Join::WITH, 'p.has_variants = :hasVar AND prodVar.price > :isNull')
			->where('p.availability = :avalable')
			->orWhere('p.has_variants = :isNull AND p.availability = :avalable AND p.price > :isNull')
			->setParameters([
				'avalable' => 'enable',
				'hasVar' => 1,
				'isNull' => 0
			]);

		if (@$sort && $sort !== 'null') {
			$this->query = $this->query->orderBy('p.has_variants', 'DESC')
				->addOrderBy("prodVar.price", $sort)
				->addOrderBy('p.price', $sort)
				->addOrderBy('p.product_code', 'DESC');
		} else $this->query = $this->query->orderBy('p.product_code', 'DESC');

		$this->query = $this->query->getQuery();

		$this->result = $this->query->getResult();

		return $this;
	}

	/**
	 * While is ONE product onStock
	 *
	 * @param integer $product_id
	 * @param boolean|null $as_array
	 * @return Product|array
	 */
	public function onStock(int $product_id, ?bool $as_array = false)
	{
		$onStock = Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->where('p.id = ?0')
			->setParameters([0 => $product_id]);

		// if ($this->shopConfig->show_price_off && $this->shopConfig->show_price_off === 'off') {
		// 	$onStock->andWhere(
		// 		'(p.price > :isNull AND (p.has_variants = :isNull OR p.has_variants IS NULL)) 
		//             OR
		//         (p.price = :isNull AND p.has_variants > :isNull AND attrs.price > :isNull)'
		// 	)->setParameter('isNull', 0);
		// }

		// if ($this->shopConfig->show_null_stock && $this->shopConfig->show_null_stock === 'off')
		// 	$onStock->andWhere(
		// 		'(p.stock > :onStock AND (p.has_variants = :onStock OR p.has_variants IS NULL))
		//             OR
		//         p.stock = :onStock AND p.has_variants > :onStock and attrs.stock > :onStock'
		// 	)->setParameter('onStock', 0);

		return $onStock->getQuery()
			->getOneOrNullResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function bySlug(string $slug, ?string $lang = null): ?int
	{
		if ($lang === null) $lang = 'sk';

		$product = Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->select('p.id, p.slug')
			->where('p.slug LIKE ?0')
			->setParameter(0, "{%\"$lang\":\"$slug\"%}")
			->getQuery()
			->getOneOrNullResult();

		return $product['id'];
	}

	public function getByCategorySlug(string $category_slug, ?string $sort = null): self
	{
		$categoryManager = new CategoryManager;
		$category = $categoryManager->getBySlug($category_slug);

		$this->query = Db::get()->getRepository(Product::class)
			->createQueryBuilder('p')
			->join('p.category', 'cats', Join::WITH, 'cats.id = :categoryId OR ( cats.parent IS NOT NULL AND cats.parent = :categoryId) ')
			->leftJoin('p.productVariants', 'prodVar', Join::WITH, 'p.has_variants = :hasVar')
			->where('p.availability = :isAvailable')
			->orWhere('(cats.id = :categoryId OR ( cats.parent IS NOT NULL AND cats.parent = :categoryId) ) 
				AND p.has_variants = :isNull 
				AND p.availability = :isAvailable
				AND p.price > :isNull')
			->setParameters([
				'isAvailable' => 'enable',
				'categoryId' => $category->getId(),
				// 'categoryParent' => $category->getParent(),
				'hasVar' => 1,
				'isNull' => 0
			]);

		if (@$sort && $sort !== 'null')
			$this->query = $this->query->orderBy('p.has_variants', 'DESC')
				->addOrderBy("prodVar.price", $sort, 'p.price', $sort)
				->addOrderBy('p.name', 'ASC');
		else
			$this->query = $this->query->orderBy('p.name', 'ASC');

		$this->query = $this->query->getQuery();

		$this->result = $this->query->getResult();

		return $this;
	}

	public function __get($name)
	{
		return $this->product_data[$name];
	}

	public function setData(?array $data = null): void
	{
		$this->product_data = $data;
	}

	public function save(?int $id = null): void
	{
		$em = new Product;

		if ($id) $em = Db::get()->getReference(Product::class, $id);

		$part_no = null;
		if ($this->part_no)
			$part_no = Db::get()->getReference(Product::class, $this->part_no);

		$em->setName($this->name);
		$em->setSlug($this->slug);
		$em->setAmount($this->amount);
		$em->setStatus($this->status);
		$em->setAvailability($this->availability);
		$em->setMinimumOrder($this->minimum_order);
		$em->setPackage($this->package);
		$em->setEanCode($this->ean_code);
		$em->setCatalogueNumber($this->catalogue_number);
		$em->setSeoTitle($this->seo_title);
		$em->setSeoKeywords($this->seo_keywords);
		$em->setModel($this->model);
		$em->setProductTax($this->product_tax);
		$em->setProductCode($this->product_code);
		$em->setPartNo($part_no);
		$em->setImage($this->image);
		$em->setMeasure($this->measure);
		$em->setCreatedAt($this->created_at);
		$em->setInserted($this->inserted);
		$em->setContent($this->content);
		$em->setDescription($this->description);
		$em->setTechnicalParams($this->technical_params);
		$em->setSeoDescription($this->seo_description);
		$em->setPrice(Others::parseFloat($this->price));
		$em->setPriceWthTax(Others::parseFloat($this->pricewTax));
		$em->setStock($this->stock);
		$em->setHasVariant($this->has_variants);

		foreach ($em->getCategory() as $cat) $em->removeCategory($cat);

		if (@$this->category_id) {
			foreach ($this->category_id as $category_id) {
				$category = Db::get()->getReference(Category::class, $category_id);
				$em->setCategory($category);
			}
		}

		// if (@$this->new_price) {

		$saleRepository = new Sale();

		if ($em->getSale()) $saleRepository = Db::get()->getReference(Sale::class, $em->getSale()->getId());

		if ($this->new_price > 0) { // pri nulovej zlave bude zlava odstranena
			$em->setInSale(1); // nastavi produktu in_sale 1

			// Data zlavy pre produkt
			$saleRepository
				->setPrice(Others::parseFloat($this->new_price))
				->setPriceWthTax(Others::parseFloat($this->new_price_wTax));

			// id zlavy - referancia produktu
			$em->setSale($saleRepository);
		} else if ($em->getSale() && $saleRepository->getId()) {
			$em->setInSale(0);

			if (@$saleRepository) {
				Db::get()->remove($saleRepository);
			}
		}
		$em->setSale(null);

		Db::get()->persist($em);
		Db::get()->flush();
	}

	public function getRelatedProducts(?int $limit = 4, ?Product $product = null): array
	{
		$related = Db::get()->getRepository(Product::class)
			->createQueryBuilder('product')
			->where('product.availability = :isAvailable')
			->setParameters(['isAvailable' => 'enable']);

		if ($product) {
			$related = $related->innerJoin('product.category', 'category', Join::WITH, 'category.id IN(:category_id)')
				->where('product.id <> :category_product')
				->setParameters([
					'category_id' => $product->getCategory(),
					'category_product' => $product->getId()
				]);
		}
		$related = $related->setMaxResults($limit)
			->getQuery()
			->getResult();

		return $related;
	}

	public function toggleActive(string $id, string $availavility)
	{
		$em = Db::get()->getReference(Product::class, $id);

		$em->setAvailability($availavility);

		Db::get()->persist($em);
		Db::get()->flush();
	}

	public function bestsellers(): self
	{
		$products = Db::get()->getRepository(Order::class)
			->createQueryBuilder('order')
			->join('order.productSolds', 'ops')
			->select('ops.id, count(ops.id) AS counter')
			->groupBy('ops.id')
			->orderBy('counter', 'DESC')
			->getQuery()
			->getResult();

		$this->query = Db::get()->getRepository(Product::class)
			->createQueryBuilder('product')
			->where('product.id IN (:pIds)')
			->join('product.soldProducts', 'psp')
			->setParameter('pIds', $products)
			->orderBy('psp.id', 'ASC');

		$this->query = $this->query->getQuery();

		$this->result = $this->query->getResult();

		return $this;
	}
}
