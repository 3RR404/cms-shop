<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_product_tag")
 */
class Tag
{

    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(type="string") 
     */
    protected $name;

    /** 
     * @ORM\Column(type="string") 
     */
    protected $slug;

}