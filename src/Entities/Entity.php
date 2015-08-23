<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;

abstract class Entity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'id' => $this->id,
        ];
    }
}
