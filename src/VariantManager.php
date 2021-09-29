<?php

declare(strict_types=1);

namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Product;
use Weblike\Cms\Shop\Entity\ProductVariant;
use Weblike\Cms\Shop\Entity\Skus;
use Weblike\Cms\Shop\Entity\Variant;
use Weblike\Cms\Shop\Entity\VariantOption;
use Weblike\Strings\Others;

class VariantManager
{

	/** @var Product */
	protected $product;

	public function getAllVariants(?bool $as_array = false)
	{
		return Db::get()->getRepository(Variant::class)
			->createQueryBuilder('variant')
			->orderBy('variant.position', 'ASC', 'variant.product', 'ASC')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getAllOptions(?bool $as_array = false)
	{
		return Db::get()->getRepository(VariantOption::class)
			->createQueryBuilder('option')
			->orderBy('option.position', 'ASC')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getAllVariantsWithout(?string $without_variant_id = null, ?bool $as_array = false)
	{
		$repository = Db::get()->getRepository(Variant::class)
			->createQueryBuilder('variant');

		if ($without_variant_id)
			$repository = $repository->where('variant.id <> :withoutVar')
				->setParameter('withoutVar', $without_variant_id);

		$repository = $repository->orderBy('variant.position', 'ASC')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);

		return $repository;
	}

	/**
	 * One row result
	 *
	 * @param string $id
	 * @param boolean|null $as_array
	 * @return array|\Weblike\Cms\Shop\Entity\Variant
	 */
	public function getVariant(string $id, ?bool $as_array = null)
	{
		return Db::get()->getRepository(Variant::class)
			->createQueryBuilder('variant')
			->where('variant.id = :variantId')
			->setParameter('variantId', $id)
			->getQuery()
			->getOneOrNullResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	/**
	 * Result of many rows
	 *
	 * @param string $variant_id
	 * @param boolean|null $as_array
	 * @return array|VariantOption
	 */
	public function getVariantOptions(string $variant_id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(VariantOption::class)
			->createQueryBuilder('option')
			->where('option.variant = :variantId')
			->setParameter('variantId', $variant_id)
			->orderBy('option.position', 'ASC')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getVariantOptionsByVariantPosition(string $variant_position)
	{
		$variants = Db::get()->getRepository(Variant::class)
			->createQueryBuilder('variant')
			->where('variant.position = :variantPos')
			->setParameter('variantPos', $variant_position)
			->getQuery()
			->getResult();

		foreach ($variants as $variant) {
			$variant_options[$variant->product->getId()] = $variant->getOptions();
		}

		return $variant_options;
	}

	/**
	 * Result One row
	 *
	 * @param string $option_id
	 * @param boolean|null $as_array
	 * @return array|VariantOption
	 */
	public function getVariantOption(string $option_id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(VariantOption::class)
			->createQueryBuilder('option')
			->where('option.id = :optionId')
			->setParameters([
				'optionId' => $option_id
			])
			->getQuery()
			->getOneOrNullResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	/**
	 * Save the variant
	 *
	 * @param integer|null $id
	 * @param array[<name:string><slug:string>] $data
	 * @return void
	 */
	public function saveVariant(?string $id = null, array $data)
	{
		$repository = new Variant;
		if ($id !== null) $repository = Db::get()->getReference(Variant::class, $id);

		$product = $parent = null;

		if (@$data['product']) $product = Db::get()->getReference(Product::class, $data['product']);
		if (@$data['parent']) $parent = Db::get()->getReference(Variant::class, $data['parent']);

		$repository->setName($data['name'])
			->setSlug($data['slug'])
			->setPosition((int)$data['position']);

		if ($product) $repository->setProduct($product);

		// if ($parent) 
		$repository->setParent($parent);

		Db::get()->persist($repository);
		Db::get()->flush();
	}

	/**
	 * Save the variant option
	 *
	 * @param integer|null $id
	 * @param array $data
	 * @return void
	 */
	public function saveVariantOption(?string $id = null, array $data)
	{
		$repository = new VariantOption;
		if ($id !== null) $repository = Db::get()->getReference(VariantOption::class, $id);

		$variant_id = $data['variant_id'] ?: $repository->getVariant()->getId();

		$variant = Db::get()->getReference(Variant::class, $variant_id);

		$repository->setName($data['name'])
			->setSlug($data['slug'])
			->setVariant($variant);

		Db::get()->persist($repository);
		Db::get()->flush();
	}

	public function setProduct(Product $product)
	{
		$this->product = $product;
	}

	public function removeVariant(string $variant_id)
	{
		$repository = Db::get()->getReference(Variant::class, $variant_id);

		Db::get()->remove($repository);
		Db::get()->flush();
	}

	public function saveProductVariants(string $product_id, array $data)
	{
		$product = Db::get()->getReference(Product::class, $product_id);

		$repository = Db::get()->getRepository(ProductVariant::class)->findBy(['product' => $product_id]);

		foreach ($repository as $productVariant) {
			$this->removeProductVariant($productVariant);
		}

		foreach ($data as $option) {

			if (!empty($option['sku'])) {
				$repository = new ProductVariant;

				if (empty(trim($option['crossell'])))
					$crossellProduct = null;
				else $crossellProduct = Db::get()->getReference(Product::class, $option['crossell']);

				if (strlen($option['sku']) > 255) $option['sku'] = substr($option['sku'], 0, 255);

				if ($option['price'] > 0) {
					$repository->setId($option['sku']);

					$repository
						->setPrice(Others::parseFloat($option['price']))
						->setPriceWthTax(Others::parseFloat($option['price_with_tax']))
						->setStock(Others::parseInt($option['stock']))
						->setProduct($product)
						->setCrosselProduct($crossellProduct);


					$group = explode("-", $option['group']);
					array_filter($group);

					foreach ($group as $opt_id) {

						if ($opt_id !== '') {
							$variantOption = Db::get()->getReference(VariantOption::class, $opt_id);

							$repository->addVariantOption($variantOption);
						}
					}

					Db::get()->persist($repository);
				} else {
					Db::get()->remove($repository);
				}
				Db::get()->flush();
			}
		}
	}

	protected function removeProductVariant(ProductVariant $productVariant): void
	{
		$variant = Db::get()->getReference(ProductVariant::class, $productVariant->getId());

		Db::get()->remove($variant);
		Db::get()->flush();
	}

	public function getProductVariants()
	{
		return Db::get()->getRepository(ProductVariant::class)
			->createQueryBuilder('prodVar')
			->getQuery()
			->getResult();
	}

	public function getProductVariant(string $sku_id): ?ProductVariant
	{
		return Db::get()->getRepository(ProductVariant::class)
			->createQueryBuilder('productVariant')
			->where('productVariant.id = :skuId')
			->setParameter('skuId', $sku_id)
			->getQuery()
			->getOneOrNullResult();
	}

	public function chooseVariant(array $options, ?int $product_id = null): array
	{
		$count = count($options);

		return Db::get()->getRepository(ProductVariant::class)
			->createQueryBuilder('product_var')
			->join('product_var.variantOptions', 'varOpt', Join::WITH, 'varOpt.id IN (:options)')
			->where('product_var.price > 0')
			->setParameters([
				'options' => $options,
				// 'productId' => $product_id
			])
			->groupBy('product_var.id')
			->having('count(product_var.id) >= ' . $count)
			// ->setMaxResults(1)
			->getQuery()
			->getResult();
	}
}
