<?php

namespace App\Entity;

use App\Repository\HoyolabPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=15)
     */
    private $postId;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="datetime")
     */
    private $postCreationDate;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastReplyTime;

    /**
     * @ORM\OneToOne(targetEntity=DiscordEmbedMessage::class, cascade={"persist", "remove"})
     */
    private $discordMessage;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\OneToOne(targetEntity=HoyolabPostStats::class, mappedBy="hoyolabPost", cascade={"persist", "remove"})
     */
    private $hoyolabPostStats;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $webhookId;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity=HoyolabPostUser::class, inversedBy="hoyolabPosts")
     */
    private $hoyolabPostUser;


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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getHoyolabPostUser(): ?HoyolabPostUser
    {
        return $this->hoyolabPostUser;
    }

    public function setHoyolabPostUser(?HoyolabPostUser $hoyolabPostUser): self
    {
        $this->hoyolabPostUser = $hoyolabPostUser;

        return $this;
    }
}
