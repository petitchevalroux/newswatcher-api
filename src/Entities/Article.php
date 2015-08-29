<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="articles",options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Article extends EntityWithId
{
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $url;

    /**
     * @var string
     * @ORM\Column(type="string",length=32, unique=true)
     */
    public $urlHash;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    public $title;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ArticleUser", mappedBy="article")
     **/
    private $articlesUsers;

    public function __construct()
    {
        $this->articlesUsers = new ArrayCollection();
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            'url' => $this->url,
            'urlHash' => $this->urlHash,
            'title' => $this->title,
        ];
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->urlHash = md5($this->url);
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->urlHash = md5($this->url);
    }
}
