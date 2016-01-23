<?php

namespace NwApi\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="articles_users",options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ArticleUser extends Entity
{
    /**
     * @var Article
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="articlesUsers")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    public $article;

    /**
     * @var User
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="User", inversedBy="articlesUsers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    public $user;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    public $isRead = false;

    /**
     * @var bool
     * @ORM\Column(type="integer")
     */
    public $twitterSeen = 0;

    /**
     * @var score
     * @ORM\Column(type="integer")
     */
    public $score = 0;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->computeScore();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->computeScore();
    }

    private function computeScore()
    {
        $this->score = $this->twitterSeen * 1;
    }

    public function jsonSerialize()
    {
        return [
            'article' => $this->article,
            'user' => $this->user,
            'twitterSeen' => $this->twitterSeen,
            'score' => $this->score,
        ];
    }
}
