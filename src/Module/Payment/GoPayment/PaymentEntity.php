<?php

namespace Weblike\Cms\Shop\Module\Payment\GoPayment;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_payment")
 */
class PaymentEntity
{
	/** 
	 * @ORM\Id
	 * @ORM\Column(type="bigint")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="\Weblike\Cms\Shop\Entity\Order", cascade={"all"})
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
	 * @var \Weblike\Cms\Shop\Entity\Order
	 */
	protected $order;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $state;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $amount;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $currency;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $payer;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $target;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $additional_params;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $lang;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $gw_url;

	/**
	 * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP", nullable=true)
	 * @var DateTime
	 */
	protected $created_at;

	/**
	 * Set id.
	 *
	 * @param int $id
	 *
	 * @return PaymentEntity
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set state.
	 *
	 * @param string|null $state
	 *
	 * @return PaymentEntity
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
	 * Set amount.
	 *
	 * @param float|null $amount
	 *
	 * @return PaymentEntity
	 */
	public function setAmount($amount = null)
	{
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Get amount.
	 *
	 * @return float|null
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * Set currency.
	 *
	 * @param string|null $currency
	 *
	 * @return PaymentEntity
	 */
	public function setCurrency($currency = null)
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * Get currency.
	 *
	 * @return string|null
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * Set payer.
	 *
	 * @param string|null $payer
	 *
	 * @return PaymentEntity
	 */
	public function setPayer($payer = null)
	{
		$this->payer = $payer;

		return $this;
	}

	/**
	 * Get payer.
	 *
	 * @return string|null
	 */
	public function getPayer()
	{
		return $this->payer;
	}

	/**
	 * Set target.
	 *
	 * @param string|null $target
	 *
	 * @return PaymentEntity
	 */
	public function setTarget($target = null)
	{
		$this->target = $target;

		return $this;
	}

	/**
	 * Get target.
	 *
	 * @return string|null
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * Set additionalParams.
	 *
	 * @param string|null $additionalParams
	 *
	 * @return PaymentEntity
	 */
	public function setAdditionalParams($additionalParams = null)
	{
		$this->additional_params = $additionalParams;

		return $this;
	}

	/**
	 * Get additionalParams.
	 *
	 * @return string|null
	 */
	public function getAdditionalParams()
	{
		return $this->additional_params;
	}

	/**
	 * Set lang.
	 *
	 * @param string|null $lang
	 *
	 * @return PaymentEntity
	 */
	public function setLang($lang = null)
	{
		$this->lang = $lang;

		return $this;
	}

	/**
	 * Get lang.
	 *
	 * @return string|null
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * Set gwUrl.
	 *
	 * @param string|null $gwUrl
	 *
	 * @return PaymentEntity
	 */
	public function setGwUrl($gwUrl = null)
	{
		$this->gw_url = $gwUrl;

		return $this;
	}

	/**
	 * Get gwUrl.
	 *
	 * @return string|null
	 */
	public function getGwUrl()
	{
		return $this->gw_url;
	}

	/**
	 * Set createdAt.
	 *
	 * @param \DateTime|null $createdAt
	 *
	 * @return PaymentEntity
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
	public function getCreatedAt(): ?DateTime
	{
		return $this->created_at;
	}

	/**
	 * Set order.
	 *
	 * @param \Weblike\Cms\Shop\Entity\Order $order
	 *
	 * @return PaymentEntity
	 */
	public function setOrder(\Weblike\Cms\Shop\Entity\Order $order)
	{
		$this->order = $order;

		return $this;
	}

	/**
	 * Get order.
	 *
	 * @return \Weblike\Cms\Shop\Entity\Order
	 */
	public function getOrder()
	{
		return $this->order;
	}
}
