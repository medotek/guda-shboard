<?php

namespace App\Entity;

use App\Repository\HoyolabPostDiscordNotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processDate;

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
}
