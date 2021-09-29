<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_cart")
 */
class CartEntity
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
     * @var Collection|\Weblike\Cms\Core\Entity\UserManager
     */
    protected $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $cart;


    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function setUser( \Weblike\Cms\Core\Entity\UserManager $user )
    {
        $this->user = $user;

        return $this;
    }

    public function setCart( ?string $cart_data = null )
    {
        $this->cart = $cart_data;

        return $this;
    }

    public function getId() : int { return $this->id; }

    public function getCart() : string { return $this->cart; }

    public function getUser() : \Weblike\Cms\Core\Entity\UserManager { return $this->user; }
}