<?php

namespace App\Entity;

use App\Repository\HoyolabPostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HoyolabPostRepository::class)
 */
class HoyolabPost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $postId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="hoyolabPosts")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $postCreationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastReplyTime;

    /**
     * @ORM\OneToOne(targetEntity=DiscordEmbedMessage::class, cascade={"persist", "remove"})
     */
    private $discordMessage;

    /**
     * @ORM\OneToOne(targetEntity=HoyolabPostStats::class, mappedBy="hoyolabPost", cascade={"persist", "remove"})
     */
    private $hoyolabPostStats;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $webhookId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostId(): ?string
    {
        return $this->postId;
    }

    public function setPostId(string $postId): self
    {
        $this->postId = $postId;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getPostCreationDate(): ?\DateTimeInterface
    {
        return $this->postCreationDate;
    }

    public function setPostCreationDate(\DateTimeInterface $postCreationDate): self
    {
        $this->postCreationDate = $postCreationDate;

        return $this;
    }

    public function getLastReplyTime(): ?\DateTimeInterface
    {
        return $this->lastReplyTime;
    }

    public function setLastReplyTime(?\DateTimeInterface $lastReplyTime): self
    {
        $this->lastReplyTime = $lastReplyTime;

        return $this;
    }

    public function getDiscordMessage(): ?DiscordEmbedMessage
    {
        return $this->discordMessage;
    }

    public function setDiscordMessage(?DiscordEmbedMessage $discordMessage): self
    {
        $this->discordMessage = $discordMessage;

        return $this;
    }

    public function getHoyolabPostStats(): ?HoyolabPostStats
    {
        return $this->hoyolabPostStats;
    }

    public function setHoyolabPostStats(?HoyolabPostStats $hoyolabPostStats): self
    {
        // unset the owning side of the relation if necessary
        if ($hoyolabPostStats === null && $this->hoyolabPostStats !== null) {
            $this->hoyolabPostStats->setHoyolabPost(null);
        }

        // set the owning side of the relation if necessary
        if ($hoyolabPostStats !== null && $hoyolabPostStats->getHoyolabPost() !== $this) {
            $hoyolabPostStats->setHoyolabPost($this);
        }

        $this->hoyolabPostStats = $hoyolabPostStats;

        return $this;
    }

    public function getWebhookId(): ?string
    {
        return $this->webhookId;
    }

    public function setWebhookId(?string $webhookId): self
    {
        $this->webhookId = $webhookId;

        return $this;
    }
}
