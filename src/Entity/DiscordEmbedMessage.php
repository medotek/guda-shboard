<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DiscordEmbedMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"discord_embed_message:read"}},
 *     denormalizationContext={"groups"={"discord_embed_message:write"}}
 * )
 * @ORM\Entity(repositoryClass=DiscordEmbedMessageRepository::class)
 */
class DiscordEmbedMessage
{
    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="array")
     */
    private $embeds = [];

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="string", length=255)
     */
    private $messageId;

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $message;

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="discordEmbedMessages")
     */
    private $user;

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $channelName;

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $channelId;

    /**
     * @ORM\ManyToOne(targetEntity=DiscordGroupedMessages::class, inversedBy="discordMessage")
     * @ORM\JoinColumn(nullable=false)
     */
    private $discordGroupedMessages;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmbeds(): ?array
    {
        return $this->embeds;
    }

    public function setEmbeds(array $embeds): self
    {
        $this->embeds = $embeds;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

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

    public function getChannelName(): ?string
    {
        return $this->channelName;
    }

    public function setChannelName(?string $channelName): self
    {
        $this->channelName = $channelName;

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

    public function getDiscordGroupedMessages(): ?DiscordGroupedMessages
    {
        return $this->discordGroupedMessages;
    }

    public function setDiscordGroupedMessages(?DiscordGroupedMessages $discordGroupedMessages): self
    {
        $this->discordGroupedMessages = $discordGroupedMessages;

        return $this;
    }
}
