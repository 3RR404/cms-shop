<?php

declare(strict_types=1);

namespace Weblike\Cms\Shop;

use Weblike\Cms\Core\Db;
use Weblike\Cms\Shop\Entity\Wishlist;
use Weblike\Plugins\User;

class WishlistManager
{

	protected $user;

	protected $productManager;

	protected $product;

	function __construct()
	{
		$this->user = new User;

		$this->productManager = new ProductManager;
	}

	public function getUserWishlist()
	{
		return Db::get()->getRepository(Wishlist::class)
			->createQueryBuilder('wish')
			->where('wish.user = :uid')
			->setParameter('uid', $this->user->id)
			->getQuery()
			->getOneOrNullResult();
	}

	public function addProductToUserWishlist(int $product_id): void
	{
		$product = $this->productManager->getOne($product_id);

		$repository = new Wishlist;

		if ($this->getUserWishlist() && $userWishListId = $this->getUserWishlist()->getId())
			$repository = Db::get()->getReference(Wishlist::class, $userWishListId);

		$repository->setUser($this->user->getUsr())
			->setProduct($product);

		Db::get()->persist($repository);
		Db::get()->flush();
	}

	public function removeProductFromUserWishlist(int $product_id): void
	{
		$product = $this->productManager->getOne($product_id);

		$userWishListId = $this->getUserWishlist()->getId();

		$repository = Db::get()->getReference(Wishlist::class, $userWishListId);

		$repository->unsetProduct($product);

		Db::get()->persist($repository);
		Db::get()->flush();
	}
}
