<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\HoyolabPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HoyolabPostRepository::class)
 * @UniqueEntity(fields="postId", message="The post already exists")
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
     * @ORM\Column(type="string", length=15, unique=true)
     */
    public $postId;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=255)
     */
    public $subject;

    /**
     * @ORM\Column(type="datetime")
     */
    public $creationDate;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="datetime")
     */
    public $postCreationDate;

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
    public $hoyolabPostStats;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $webhookId;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    public $image;

    /**
     * @ORM\ManyToOne(targetEntity=HoyolabPostUser::class, inversedBy="hoyolabPosts")
     */
    private $hoyolabPostUser;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\OneToOne(targetEntity=HoyolabPostDiscordNotification::class, mappedBy="hoyolabPost", cascade={"persist", "remove"})
     */
    private $hoyolabPostDiscordNotification;

    /**
     * @ORM\OneToMany(targetEntity=HoyolabStats::class, mappedBy="hoyolabPost")
     */
    private $hoyolabStats;

    public function __construct()
    {
        $this->hoyolabStats = new ArrayCollection();
    }


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

    public function getHoyolabPostDiscordNotification(): ?HoyolabPostDiscordNotification
    {
        return $this->hoyolabPostDiscordNotification;
    }

    public function setHoyolabPostDiscordNotification(?HoyolabPostDiscordNotification $hoyolabPostDiscordNotification): self
    {
        // unset the owning side of the relation if necessary
        if ($hoyolabPostDiscordNotification === null && $this->hoyolabPostDiscordNotification !== null) {
            $this->hoyolabPostDiscordNotification->setHoyolabPost(null);
        }

        // set the owning side of the relation if necessary
        if ($hoyolabPostDiscordNotification !== null && $hoyolabPostDiscordNotification->getHoyolabPost() !== $this) {
            $hoyolabPostDiscordNotification->setHoyolabPost($this);
        }

        $this->hoyolabPostDiscordNotification = $hoyolabPostDiscordNotification;

        return $this;
    }

    /**
     * @return Collection|HoyolabStats[]
     */
    public function getHoyolabStats(): Collection
    {
        return $this->hoyolabStats;
    }

    public function addHoyolabStat(HoyolabStats $hoyolabStat): self
    {
        if (!$this->hoyolabStats->contains($hoyolabStat)) {
            $this->hoyolabStats[] = $hoyolabStat;
            $hoyolabStat->setHoyolabPost($this);
        }

        return $this;
    }

    public function removeHoyolabStat(HoyolabStats $hoyolabStat): self
    {
        if ($this->hoyolabStats->removeElement($hoyolabStat)) {
            // set the owning side to null (unless already changed)
            if ($hoyolabStat->getHoyolabPost() === $this) {
                $hoyolabStat->setHoyolabPost(null);
            }
        }

        return $this;
    }
}
