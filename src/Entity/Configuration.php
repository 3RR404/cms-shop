<?php

namespace Weblike\Cms\Shop\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_config")
 */
class Configuration
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    public function setName( string $string ) : void
    {
        $this->name = $string;
    }

    public function setValue( string $string ) : void
    {
        $this->value = $string;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue() : string
    {
        return $this->value;
    }
}