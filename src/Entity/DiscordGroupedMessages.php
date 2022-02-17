<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DiscordGroupedMessagesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"discord_grouped_messages:read"}},
 *     denormalizationContext={"groups"={"discord_embed_message:write"}}
 * )
 * @ORM\Entity(repositoryClass=DiscordGroupedMessagesRepository::class)
 */
class DiscordGroupedMessages
{
    /**
     * @Groups({"discord_grouped_messages:read"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"discord_grouped_messages:read"})
     * @ORM\OneToMany(targetEntity=DiscordEmbedMessage::class, mappedBy="discordGroupedMessages")
     */
    private $discordMessage;

    /**
     * @Groups({"discord_grouped_messages:read"})
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="discordGroupedMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    public function __construct()
    {
        $this->discordMessage = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|DiscordEmbedMessage[]
     */
    public function getDiscordMessage(): Collection
    {
        return $this->discordMessage;
    }

    public function addDiscordMessage(DiscordEmbedMessage $discordMessage): self
    {
        if (!$this->discordMessage->contains($discordMessage)) {
            $this->discordMessage[] = $discordMessage;
            $discordMessage->setDiscordGroupedMessages($this);
        }

        return $this;
    }

    public function removeDiscordMessage(DiscordEmbedMessage $discordMessage): self
    {
        if ($this->discordMessage->removeElement($discordMessage)) {
            // set the owning side to null (unless already changed)
            if ($discordMessage->getDiscordGroupedMessages() === $this) {
                $discordMessage->setDiscordGroupedMessages(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
