<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="articles_users",options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"},
 * uniqueConstraints={@ORM\UniqueConstraint(name="articles_users", columns={"article_id", "user_id"})})
 * @ORM\Entity
 */
class ArticleUser extends EntityWithId
{
    /**
     * @var Article
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="articlesUsers")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    public $article;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="articlesUsers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    public $user;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    public $status = 0;

    public function jsonSerialize()
    {
        return [
            'article' => $this->article,
            'user' => $this->user,
            'status' => $this->status,
        ];
    }
}
