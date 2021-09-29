<?php

namespace Weblike\Cms\Shop;

use Weblike\Cms\Core\Application\Authorization;
use Weblike\Plugins\User;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Core\Entity\UserManager;
use Weblike\Cms\Shop\Entity\Customer;
use Weblike\Plugins\doHash;
use Weblike\Plugins\Entities\AuthToken;
use Weblike\Strings\Translate;

class CustomerManager extends User
{
	/** @var User */
	protected $user;

	/** @var Response|null */
	protected $response = null;

	function __construct()
	{
		$this->user = new User;
	}

	public function getCustomer()
	{
		return Db::get()->getRepository(Customer::class)
			->createQueryBuilder('c')
			->where('c.user = :userId')
			->setParameter('userId', $this->user->id)
			->getQuery()
			->getOneOrNullResult();
	}

	public function getResponse(): ?Response
	{
		return $this->response;
	}

	public function getJsonResponse(): ?string
	{
		return json_encode($this->response);
	}

	public function saveProfileData(array $data): self
	{

		$this->user->updateMe([
			'fname' => $data['fname'],
			'lname' => $data['lname'],
		]);

		if ($this->getCustomer()) $id = $this->getCustomer()->getId();

		$customer = new Customer();

		if ($id) $customer = Db::get()->getReference(Customer::class, $id);

		$user = Db::get()->getReference(UserManager::class, $this->user->id);

		$customer->setAddressLine1($data['address_line_1']);
		$customer->setAddressLine2($data['address_line_2']);
		$customer->setPhone($data['phone']);
		$customer->setCity($data['city']);
		$customer->setCountry($data['country']);
		// $customer->setState( $data['state'] );
		$customer->setZip($data['zip']);
		$customer->setUser($user)
			->setBuyAsCompany(0)
			->setShipAddress(1);

		if (@$data['buy_as_company'] && $data['buy_as_company'] === "on") {
			$customer->setBuyAsCompany(1)
				->setCompanyName($data['company_name'])
				->setIco($data['ico'])
				->setDic($data['dic'])
				->setIcDph($data['ic_dph']);
		}

		if (@$data['ship_address'] && $data['ship_address'] === "off") {
			$customer->setShipAddress(0)
				->setShipAddressLine1($data['ship_address_line_1'])
				->setShipAddressLine2($data['ship_address_line_2'])
				->setShipCity($data['ship_city'])
				->setShipCountry($data['ship_country'])
				->setShipZip($data['ship_zip']);
		}

		Db::get()->persist($customer);
		Db::get()->flush();

		$this->response = new Response(Translate::translate('DATA_BOLI_ULOZENE'), 'success');

		return $this;
	}

	public function signUp(array $user_data)
	{
		if (empty($user_data['email']))
			return new Response(Translate::translate('EMAIL_NESMIE_BYT_PRAZDNY'), 'error', 400);
		// throw new \Exception( 'Email can\'t be empty !' );

		if (empty($user_data['fname']))
			return new Response(Translate::translate('MENO_JE_POVINNE_POLE'), 'error', 400);
		// throw new \Exception( 'First name can\'t be empty !' );

		if (empty($user_data['lname']))
			return new Response(Translate::translate('PRIEZVISKO_JE_POVINNE_POLE'), 'error', 400);
		// throw new \Exception( 'Last name can\'t be empty !' );

		// if ( empty( $user_data['username'] ) )
		//     return new Response( 'Username can\'t be empty !', 'error', 400 );
		// throw new \Exception( 'Username can\'t be empty !' );

		if (empty($user_data['phone']))
			return new Response(Translate::translate('TELEFON_JE_POVINNE_POLE'), 'error', 400);

		if (empty($user_data['address_line_1']))
			return new Response(Translate::translate('VYPLNTE_PROSIM_FAKTURACNU_ADRESU_PRE_UCELY_SPRACOVANIA_OBJEDNAVKY_A_DODANIA_TOVARU'), 'error', 400);

		if (empty($user_data['country']))
			return new Response(Translate::translate('KRAJINA_JE_POVINNE_POLE'), 'error', 400);

		if (empty($user_data['city']))
			return new Response(Translate::translate('MESTO_JE_POVINNE_POLE'), 'error', 400);

		if (!isset($user_data['gdpr']) || $user_data['gdpr'] === 'off')
			return new Response(Translate::translate('PRECITAJTE_SI_PROSIM_SPRACOVANIE_OSOBNYCH_UDAJOV_BEZ_JEHO_SUHLASU_NEVIEME_SPRACOVAT_VASE_DATA'), 'error', 400);

		$user_email_exists = $this->dulicity($user_data['email'], 'email');
		$username_exists = $this->dulicity($user_data['username'], 'username');

		if ($user_email_exists) {
			throw new \Exception('User already exists with this primary key `email` ! Sign in, please !');
			return false;
		}
		if ($username_exists) {
			throw new \Exception('User already exists with this primary key `username` ! Sign in, please !');
			return false;
		}

		$password = doHash::getHash(6, true);
		$username = explode('@', $user_data['email']);

		$newUser = new UserManager;
		$newUser->setFname($user_data['fname']);
		$newUser->setLname($user_data['lname']);
		$newUser->setEmail($user_data['email']);
		$newUser->setPassword((new Authorization)->passHash($password));
		$newUser->setUsername($username[0]);
		$newUser->setActive(0);
		$newUser->setFlag('a0');

		$customer = new Customer;
		$customer->setUser($newUser);
		$customer->setPhone($user_data['phone']);
		$customer->setAddressLine1($user_data['address_line_1']);
		$customer->setAddressLine2($user_data['address_line_2']);
		$customer->setCity($user_data['city']);
		$customer->setCountry($user_data['country']);
		$customer->setZip($user_data['zip']);

		if (isset($user_data['buy_as_company']) && $user_data['buy_as_company'] === 'on') {
			$customer->setBuyAsCompany(1);
			$customer->setCompanyName($user_data['company_name']);
			$customer->setIcDph($user_data['ic_dph']);
			$customer->setIco($user_data['ico']);
			$customer->setDic($user_data['dic']);
		} else $customer->setBuyAsCompany(0);

		if (isset($user_data['ship_address']) && $user_data['ship_address'] === 'on') {
			$customer->setShipAddress(1);
			$customer->setShipAddressLine1($user_data['ship_address_line_1']);
			$customer->setShipAddressLine2($user_data['ship_address_line_2']);
			$customer->setShipCity($user_data['ship_city']);
			$customer->setShipCountry($user_data['ship_country']);
			$customer->setShipZip($user_data['ship_zip']);
		} else $customer->setShipAddress(0);


		Db::get()->persist($newUser);
		Db::get()->persist($customer);
		Db::get()->flush();

		$token = (new doHash)->accioUserToken($user_data, 32);
		$saveToken = (new Authorization)->saveToken($token, $user_data['email']);

		return new Response('All done !', 'success', 200, json_encode([
			$user_data['email'], $password
		]));
	}
}
