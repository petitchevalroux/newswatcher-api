<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * @ORM\Table(name="sources",options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"sources" = "Source", "twitter" = "SourceTwitter"})
 */
abstract class Source extends EntityWithId
{
    /**
     * @ORM\ManyToMany(targetEntity="Article")
     * @ORM\JoinTable(
     *  name="sources_articles",
     *  joinColumns={@ORM\JoinColumn(name="source_id", referencedColumnName="id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")}
     * )
     */
    public $articles;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="sources_users",
     *      joinColumns={@ORM\JoinColumn(name="source_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    public $users;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->users = new ArrayCollection();
    }
}
