<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DiscordWebhookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get","custom_discord_webhooks_list"={
 *              "route_name"="discord_webhook_list"
 *              }
 *      },
 *     itemOperations={
 *          "get","custom_discord_webhooks_item"={
 *              "route_name"="discord_webhook_item"
 *              }
 *      },
 *     normalizationContext={"groups"={"discord_webhook:read"}},
 *     denormalizationContext={"groups"={"discord_webhook:write"}}
 * )
 *
 * @ORM\Entity(repositoryClass=DiscordWebhookRepository::class)
 * @UniqueEntity(fields="webhookId", message="Ce webhook existe déjà.")
 */
class DiscordWebhook
{
    private $encoderFactory;

    /**
     * @Groups("discord_webhook:read")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups("discord_webhook:read")
     * @ORM\Column(type="string", length=255)
     */
    private $name;


    /**
     * @Groups("discord_webhook:read")
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $avatarId;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="discordWebhooks")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="discordOwnedWehbooks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $token;


    /**
     * @Groups("discord_webhook:read")
     * @ORM\Column(type="string", length=100)
     */
    private $webhookId;

    /**
     * @Groups("discord_webhook:read")
     * @ORM\Column(type="string", length=100)
     */
    private $channelId;

    /**
     * @Groups("discord_webhook:read")
     * @ORM\Column(type="string", length=100)
     */
    private $guildId;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAvatarId(): ?string
    {
        return $this->avatarId;
    }

    public function setAvatarId(?string $avatarId): self
    {
        $this->avatarId = $avatarId;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getWebhookId(): ?int
    {
        return $this->webhookId;
    }

    public function setWebhookId(int $webhookId): self
    {
        $this->webhookId = $webhookId;

        return $this;
    }

    public function getChannelId(): ?int
    {
        return $this->channelId;
    }

    public function setChannelId(int $channelId): self
    {
        $this->channelId = $channelId;

        return $this;
    }

    public function getGuildId(): ?int
    {
        return $this->guildId;
    }

    public function setGuildId(int $guildId): self
    {
        $this->guildId = $guildId;

        return $this;
    }
}
