<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Article extends Entity
{
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $url;

    /**
     * @var string
     * @ORM\Column(type="string",length=32)
     */
    public $urlHash;

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'url' => $this->url,
            'urlHash' => $this->urlHash,
            'title' => $this->title,
        ];
    }
}
