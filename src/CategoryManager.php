<?php

namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Category;

class CategoryManager
{

	public function getCategories(?bool $as_array = false)
	{
		return Db::get()->getRepository(Category::class)
			->createQueryBuilder('category')
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getCategoriesTree(?int $level = 0)
	{

		return Db::get()->getRepository(Category::class)
			->createQueryBuilder('parent')
			->where('parent.parent IS NULL')
			->getQuery()
			->getResult();
	}

	public function getParents($child_id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(Category::class)
			->createQueryBuilder('category')
			->where('category.id <> :childId')
			->andWhere('category.parent IS NULL')
			->setParameters([
				'childId' => $child_id,
			])
			->getQuery()
			->getResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getCategory(string $id, ?bool $as_array = false)
	{
		return Db::get()->getRepository(Category::class)
			->createQueryBuilder('category')
			->where('category.id = :catId')
			->setParameter('catId', $id)
			->getQuery()
			->getOneOrNullResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	public function getBySlug(string $slug, ?string $lang = 'sk')
	{
		return Db::get()->getRepository(Category::class)
			->createQueryBuilder('category')
			->where('category.slug LIKE ?1')
			->setParameter(1, "{%\"$lang\":\"$slug\"%}")
			->getQuery()
			->getOneOrNullResult();
	}

	public function save(?string $product_id = null, array $data)
	{
		if ($product_id) $category = Db::get()->getReference(Category::class, $product_id);
		else $category = new Category;

		$parent = $data['parent'] ? Db::get()->getReference(Category::class, $data['parent']) : null;

		$category->setName($data['name'])
			->setSlug($data['slug'])
			->setImage($data['image'])
			->setIcon($data['icon']);

		$category->setParent($parent);

		Db::get()->persist($category);
		Db::get()->flush();
	}

	public function remove(string $product_id): void
	{
		$this->delete($product_id);
	}

	public function delete(string $product_id): void
	{
		$repository = Db::get()->getReference(Category::class, $product_id);

		Db::get()->remove($repository);
		Db::get()->flush();
	}
}
