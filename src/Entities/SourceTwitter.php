<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="sources_twitter",options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 * @ORM\Entity
 */
class SourceTwitter extends Source
{
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $method;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $consumerKey;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $consumerSecret;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $accessTokenKey;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $accessTokenSecret;

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'method' => $this->method,
            'consumerKey' => $this->consumerKey,
            'consumerSecret' => $this->consumerSecret,
            'accessTokenKey' => $this->accessTokenKey,
            'accessTokenSecret' => $this->accessTokenSecret,
        ];
    }
}
