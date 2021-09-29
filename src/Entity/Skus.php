<?php declare(strict_types=1);

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Weblike\Cms\Shop\Utilities\Price;

/**
 * ## Storage Keeping Unit
 * @ORM\Entity
 * @ORM\Table(name="es__skus")
 */
class Skus
{
	/** 
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;

	// /**
	//  * @ORM\OneToMany(targetEntity="Weblike\Cms\Shop\Entity\VariantSkus", mappedBy="skus", cascade={"all"})
	//  * @var Collection
	//  */
	// protected $variantSkus;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\Variant", inversedBy="skus")
	 * @ORM\JoinColumn(name="variant_id", referencedColumnName="id")
	 * @ORM\OrderBy({"position" = "ASC"})
	 * @var Variant[]|Collection
	 */
	protected $variants;

	/**
	 * @ORM\ManyToMany(targetEntity="Weblike\Cms\Shop\Entity\VariantOption", inversedBy="skus")
	 * @ORM\JoinColumn(name="option_id", referencedColumnName="id")
	 * @ORM\OrderBy({"variant" = "ASC"})
	 * @var VariantOption[]|Collection
	 */
	protected $options;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $price;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $stock;

	/**
	 * @ORM\ManyToOne(targetEntity="Weblike\Cms\Shop\Entity\Product", inversedBy="skus")
	 * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
	 * @var Product
	 */
	protected $product;

	function __construct()
	{
		$this->variants = new ArrayCollection();
		$this->options = new ArrayCollection();
	}

	public function __get( $name )
	{
		return $this->{$name};
	}

	/**
	 * Set new ID
	 *
	 * @param string $id
	 * @return self
	 */
	public function setId( string $id ): self
	{
		$this->id = $id;

		return $this;
	}
	
	/**
	 * Skus Id
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Set price without tax
	 *
	 * @param float $price
	 * @return self
	 */
	public function setPrice( float $price ): self
	{
		$this->price = $price;
		
		return $this;
	}

	/**
     * Price without tax
     *
     * @param boolean|null $formated
     * @return string|float
     */
    public function getPrice( ?bool $formated = false )
    {
        if ( $formated ) return Price::format( $this->price );

        return Price::whoolPrice( $this->price, 3, false );
    }


	/**
	 * Set up how much on stock is
	 *
	 * @param integer $stock
	 * @return self
	 */
	public function setStock( int $stock ): self
	{
		$this->stock = $stock;

		return $this;
	}

	/**
	 * Return amount of stock
	 *
	 * @return integer|null
	 */
	public function getStock(): ?int
	{
		return $this->stock;
	}

	/**
	 * Reference to product
	 *
	 * @param Product $product
	 * @return self
	 */
	public function setProduct( Product $product ): self
	{
		$this->product = $product;

		return $this;
	}

	/**
	 * Return Product entity
	 *
	 * @return Product
	 */
	public function getProduct(): Product
	{
		return $this->product;
	}

	public function addVariants( Variant $variant ): void
    {
        if ( $this->variants->contains( $variant ) )
            return;

        $this->variants->add($variant);

        $variant->setSkus( $this );
    }

	public function addOptions( VariantOption $option ): void
	{
		if ( $this->options->contains( $option ) )
            return;

		$this->options->add( $option );

        $option->setSkus( $this );

	}
}