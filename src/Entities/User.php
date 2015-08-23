<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users",options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 * @ORM\Entity
 */
class User extends Entity
{
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $name;

    /**
     * @var string
     * @ORM\Column(type="string",length=20)
     * Maximum length correspond to 2^64 according to
     * https://dev.twitter.com/overview/api/twitter-ids-json-and-snowflake
     */
    public $twitterId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $twitterToken;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $twitterTokenSecret;

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'name' => $this->name,
            'twitterId' => $this->twitterId,
            'twitterToken' => $this->twitterToken,
            'twitterTokenSecret' => $this->twitterTokenSecret,
        ];
    }
}
