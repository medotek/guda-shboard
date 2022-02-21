<?php

namespace App\Entity;

use App\Repository\HoyolabPostDiscordNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HoyolabPostDiscordNotificationRepository::class)
 */
class HoyolabPostDiscordNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $messageId;

    /**
     * @ORM\OneToOne(targetEntity=HoyolabPost::class, inversedBy="hoyolabPostDiscordNotification", cascade={"persist", "remove"})
     */
    private $hoyolabPost;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processDate;

    /**
     * @ORM\OneToOne(targetEntity=HoyolabPostStats::class, cascade={"persist", "remove"})
     */
    private $processStats;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $channelId;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $guildId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(?string $messageId): self
    {
        $this->messageId = $messageId;

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

    public function getProcessDate(): ?\DateTimeInterface
    {
        return $this->processDate;
    }

    public function setProcessDate(?\DateTimeInterface $processDate): self
    {
        $this->processDate = $processDate;

        return $this;
    }

    public function getProcessStats(): ?HoyolabPostStats
    {
        return $this->processStats;
    }

    public function setProcessStats(?HoyolabPostStats $processStats): self
    {
        $this->processStats = $processStats;

        return $this;
    }

    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    public function setChannelId(?string $channelId): self
    {
        $this->channelId = $channelId;

        return $this;
    }

    public function getGuildId(): ?string
    {
        return $this->guildId;
    }

    public function setGuildId(?string $guildId): self
    {
        $this->guildId = $guildId;

        return $this;
    }
}
