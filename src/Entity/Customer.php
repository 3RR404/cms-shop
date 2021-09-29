<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_customer")
 */
class Customer
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Core\Entity\UserManager")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 * @var \Weblike\Cms\Core\Entity\UserManager[]|Collection
	 */
	protected $user;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $buy_as_company;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $ship_address;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $address_line_1;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $address_line_2;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $city;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $phone;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $country;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $state;

	/**
	 * @ORM\Column(type="integer", columnDefinition="MEDIUMINT(6)", nullable=true)
	 */
	protected $zip;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ship_address_line_1;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ship_address_line_2;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ship_city;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ship_country;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ship_state;

	/**
	 * @ORM\Column(type="integer", columnDefinition="MEDIUMINT(6)", nullable=true)
	 */
	protected $ship_zip;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ship_name;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $company_name;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ic_dph;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $ico;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $dic;

	public function __construct()
	{
		$this->user = new ArrayCollection();
	}

	/**
	 * Set id.
	 *
	 * @param string $id
	 *
	 * @return Customer
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Get id.
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set buyAsCompany.
	 *
	 * @param int|null $buyAsCompany
	 *
	 * @return Customer
	 */
	public function setBuyAsCompany($buyAsCompany = null)
	{
		$this->buy_as_company = $buyAsCompany;

		return $this;
	}

	/**
	 * Get buyAsCompany.
	 *
	 * @return int|null
	 */
	public function getBuyAsCompany()
	{
		return $this->buy_as_company;
	}

	public function isBusiness(): bool
	{
		return $this->getBuyAsCompany() == 1 ? true : false;
	}

	/**
	 * Set shipAddress.
	 *
	 * @param int|null $shipAddress
	 *
	 * @return Customer
	 */
	public function setShipAddress($shipAddress = null)
	{
		$this->ship_address = $shipAddress;

		return $this;
	}

	/**
	 * Get shipAddress.
	 *
	 * @return int|null
	 */
	public function getShipAddress()
	{
		return $this->ship_address;
	}

	public function sameShippingAsBilling(): bool
	{
		return $this->getShipAddress() == 1 ? true : false;
	}

	/**
	 * Set addressLine1.
	 *
	 * @param string|null $addressLine1
	 *
	 * @return Customer
	 */
	public function setAddressLine1($addressLine1 = null)
	{
		$this->address_line_1 = $addressLine1;

		return $this;
	}

	/**
	 * Get addressLine1.
	 *
	 * @return string|null
	 */
	public function getAddressLine1()
	{
		return $this->address_line_1;
	}

	/**
	 * Set addressLine2.
	 *
	 * @param string|null $addressLine2
	 *
	 * @return Customer
	 */
	public function setAddressLine2($addressLine2 = null)
	{
		$this->address_line_2 = $addressLine2;

		return $this;
	}

	/**
	 * Get addressLine2.
	 *
	 * @return string|null
	 */
	public function getAddressLine2()
	{
		return $this->address_line_2;
	}

	/**
	 * Set city.
	 *
	 * @param string|null $city
	 *
	 * @return Customer
	 */
	public function setCity($city = null)
	{
		$this->city = $city;

		return $this;
	}

	/**
	 * Get city.
	 *
	 * @return string|null
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * Set phone.
	 *
	 * @param string|null $phone
	 *
	 * @return Customer
	 */
	public function setPhone($phone = null)
	{
		$this->phone = $phone;

		return $this;
	}

	/**
	 * Get phone.
	 *
	 * @return string|null
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * Set country.
	 *
	 * @param string|null $country
	 *
	 * @return Customer
	 */
	public function setCountry($country = null)
	{
		$this->country = $country;

		return $this;
	}

	/**
	 * Get country.
	 *
	 * @return string|null
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * Set state.
	 *
	 * @param string|null $state
	 *
	 * @return Customer
	 */
	public function setState($state = null)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * Get state.
	 *
	 * @return string|null
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Set zip.
	 *
	 * @param int|null $zip
	 *
	 * @return Customer
	 */
	public function setZip($zip = null)
	{
		$this->zip = $zip;

		return $this;
	}

	/**
	 * Get zip.
	 *
	 * @return int|null
	 */
	public function getZip()
	{
		return $this->zip;
	}

	/**
	 * Set shipAddressLine1.
	 *
	 * @param string|null $shipAddressLine1
	 *
	 * @return Customer
	 */
	public function setShipAddressLine1($shipAddressLine1 = null)
	{
		$this->ship_address_line_1 = $shipAddressLine1;

		return $this;
	}

	/**
	 * Get shipAddressLine1.
	 *
	 * @return string|null
	 */
	public function getShipAddressLine1()
	{
		return $this->ship_address_line_1;
	}

	/**
	 * Set shipAddressLine2.
	 *
	 * @param string|null $shipAddressLine2
	 *
	 * @return Customer
	 */
	public function setShipAddressLine2($shipAddressLine2 = null)
	{
		$this->ship_address_line_2 = $shipAddressLine2;

		return $this;
	}

	/**
	 * Get shipAddressLine2.
	 *
	 * @return string|null
	 */
	public function getShipAddressLine2()
	{
		return $this->ship_address_line_2;
	}

	/**
	 * Set shipCity.
	 *
	 * @param string|null $shipCity
	 *
	 * @return Customer
	 */
	public function setShipCity($shipCity = null)
	{
		$this->ship_city = $shipCity;

		return $this;
	}

	/**
	 * Get shipCity.
	 *
	 * @return string|null
	 */
	public function getShipCity()
	{
		return $this->ship_city;
	}

	/**
	 * Set shipCountry.
	 *
	 * @param string|null $shipCountry
	 *
	 * @return Customer
	 */
	public function setShipCountry($shipCountry = null)
	{
		$this->ship_country = $shipCountry;

		return $this;
	}

	/**
	 * Get shipCountry.
	 *
	 * @return string|null
	 */
	public function getShipCountry()
	{
		return $this->ship_country;
	}

	/**
	 * Set shipState.
	 *
	 * @param string|null $shipState
	 *
	 * @return Customer
	 */
	public function setShipState($shipState = null)
	{
		$this->ship_state = $shipState;

		return $this;
	}

	/**
	 * Get shipState.
	 *
	 * @return string|null
	 */
	public function getShipState()
	{
		return $this->ship_state;
	}

	/**
	 * Set shipZip.
	 *
	 * @param int|null $shipZip
	 *
	 * @return Customer
	 */
	public function setShipZip($shipZip = null)
	{
		$this->ship_zip = $shipZip;

		return $this;
	}

	/**
	 * Get shipZip.
	 *
	 * @return int|null
	 */
	public function getShipZip()
	{
		return $this->ship_zip;
	}

	/**
	 * Set shipName.
	 *
	 * @param string|null $shipName
	 *
	 * @return Customer
	 */
	public function setShipName($shipName = null)
	{
		$this->ship_name = $shipName;

		return $this;
	}

	/**
	 * Get shipName.
	 *
	 * @return string|null
	 */
	public function getShipName()
	{
		return $this->ship_name;
	}

	/**
	 * Set companyName.
	 *
	 * @param string|null $companyName
	 *
	 * @return Customer
	 */
	public function setCompanyName($companyName = null)
	{
		$this->company_name = $companyName;

		return $this;
	}

	/**
	 * Get companyName.
	 *
	 * @return string|null
	 */
	public function getCompanyName()
	{
		return $this->company_name;
	}

	/**
	 * Set icDph.
	 *
	 * @param string|null $icDph
	 *
	 * @return Customer
	 */
	public function setIcDph($icDph = null)
	{
		$this->ic_dph = $icDph;

		return $this;
	}

	/**
	 * Get icDph.
	 *
	 * @return string|null
	 */
	public function getIcDph()
	{
		return $this->ic_dph;
	}

	/**
	 * Set ico.
	 *
	 * @param string|null $ico
	 *
	 * @return Customer
	 */
	public function setIco($ico = null)
	{
		$this->ico = $ico;

		return $this;
	}

	/**
	 * Get ico.
	 *
	 * @return string|null
	 */
	public function getIco()
	{
		return $this->ico;
	}

	/**
	 * Set dic.
	 *
	 * @param string|null $dic
	 *
	 * @return Customer
	 */
	public function setDic($dic = null)
	{
		$this->dic = $dic;

		return $this;
	}

	/**
	 * Get dic.
	 *
	 * @return string|null
	 */
	public function getDic()
	{
		return $this->dic;
	}

	/**
	 * Set user.
	 *
	 * @param \Weblike\Cms\Core\Entity\UserManager $user
	 *
	 * @return Customer
	 */
	public function setUser(\Weblike\Cms\Core\Entity\UserManager $user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user.
	 *
	 * @return \Weblike\Cms\Core\Entity\UserManager
	 */
	public function getUser()
	{
		return $this->user;
	}
}
