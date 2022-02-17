<?php

namespace App\Entity;

use App\Repository\HoyolabPostStatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HoyolabPostStatsRepository::class)
 */
class HoyolabPostStats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $view;

    /**
     * @ORM\Column(type="integer")
     */
    private $reply;

    /**
     * @ORM\Column(type="integer")
     */
    private $likes;

    /**
     * @ORM\Column(type="integer")
     */
    private $bookmark;

    /**
     * @ORM\Column(type="integer")
     */
    private $share;

    /**
     * @ORM\OneToOne(targetEntity=HoyolabPost::class, inversedBy="hoyolabPostStats", cascade={"persist", "remove"})
     */
    private $hoyolabPost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getView(): ?int
    {
        return $this->view;
    }

    public function setView(int $view): self
    {
        $this->view = $view;

        return $this;
    }

    public function getReply(): ?int
    {
        return $this->reply;
    }

    public function setReply(int $reply): self
    {
        $this->reply = $reply;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    public function getBookmark(): ?int
    {
        return $this->bookmark;
    }

    public function setBookmark(int $bookmark): self
    {
        $this->bookmark = $bookmark;

        return $this;
    }

    public function getShare(): ?int
    {
        return $this->share;
    }

    public function setShare(int $share): self
    {
        $this->share = $share;

        return $this;
    }

    public function getHoyolabPost(): ?HoyolabPost
    {
        return $this->hoyolabPost;
    }

    public function setHoyolabPost(?HoyolabPost $hoyolabPost): self
    {
        $this->hoyolabPost = $hoyolabPost;

        return $this;
    }
}
