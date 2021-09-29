<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\ORM\Mapping as ORM;
use Weblike\Cms\Shop\PromocodeManager;
use Weblike\Cms\Shop\Utilities\Price;

// use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_promocode")
 */
class Promocode
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", nullable=false)
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	protected $value;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $type;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $active;

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
	 * Set name.
	 *
	 * @param string|null $name
	 *
	 * @return Promocode
	 */
	public function setName($name = null)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set value.
	 *
	 * @param float|null $value
	 *
	 * @return Promocode
	 */
	public function setValue($value = null)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * Get value.
	 *
	 * @return float|string|null
	 */
	public function getValue(?bool $formated = false)
	{
		if ($formated) {
			switch ($this->type) {
				case PromocodeManager::CURRENCY:
					$value = Price::priceFormat($this->value);
					break;
				case PromocodeManager::PERCENTAGE:
					$value = "{$this->value} %";
					break;
			}

			return $value;
		}


		return $this->value;
	}

	/**
	 * Set type.
	 *
	 * @param int|null $type
	 *
	 * @return Promocode
	 */
	public function setType($type = null)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type.
	 *
	 * @return int|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set active.
	 *
	 * @param int|null $active
	 *
	 * @return Promocode
	 */
	public function setActive($active = null)
	{
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active.
	 *
	 * @return int|null
	 */
	public function getActive()
	{
		return $this->active;
	}

	public function isActive(): bool
	{
		if ($this->active === 1) return true;
		return false;
	}
}
