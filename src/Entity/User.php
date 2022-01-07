<?php

namespace App\Entity;

use App\Repository\UserRepository;
use ContainerOcq7WVR\getSecurity_EncoderFactory_GenericService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use function Couchbase\defaultEncoder;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields="name", message="Ce nom d'utilisateur a déjà été pris.")
 * @UniqueEntity(fields="uid", message="Cet uid est déjà utilisé. Si vous n'avez jamais mis votre uid sur le site, veuilez contacter un administrateur.")
 */
class User implements UserInterface
{
    private $encoderFactory;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $creationDate;


    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity=DiscordEmbedMessage::class, mappedBy="user")
     */
    private $discordEmbedMessages;

    /**
     * @ORM\OneToMany(targetEntity=DiscordGroupedMessages::class, mappedBy="author")
     */
    private $discordGroupedMessages;

    public function __construct()
    {
        $this->discordEmbedMessages = new ArrayCollection();
        $this->discordGroupedMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;

    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    //Méthodes d'Authentification

    public function getUsername()
    {
        return $this->email;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|DiscordEmbedMessage[]
     */
    public function getDiscordEmbedMessages(): Collection
    {
        return $this->discordEmbedMessages;
    }

    public function addDiscordEmbedMessage(DiscordEmbedMessage $discordEmbedMessage): self
    {
        if (!$this->discordEmbedMessages->contains($discordEmbedMessage)) {
            $this->discordEmbedMessages[] = $discordEmbedMessage;
            $discordEmbedMessage->setUser($this);
        }

        return $this;
    }

    public function removeDiscordEmbedMessage(DiscordEmbedMessage $discordEmbedMessage): self
    {
        if ($this->discordEmbedMessages->removeElement($discordEmbedMessage)) {
            // set the owning side to null (unless already changed)
            if ($discordEmbedMessage->getUser() === $this) {
                $discordEmbedMessage->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DiscordGroupedMessages[]
     */
    public function getDiscordGroupedMessages(): Collection
    {
        return $this->discordGroupedMessages;
    }

    public function addDiscordGroupedMessage(DiscordGroupedMessages $discordGroupedMessage): self
    {
        if (!$this->discordGroupedMessages->contains($discordGroupedMessage)) {
            $this->discordGroupedMessages[] = $discordGroupedMessage;
            $discordGroupedMessage->setAuthor($this);
        }

        return $this;
    }

    public function removeDiscordGroupedMessage(DiscordGroupedMessages $discordGroupedMessage): self
    {
        if ($this->discordGroupedMessages->removeElement($discordGroupedMessage)) {
            // set the owning side to null (unless already changed)
            if ($discordGroupedMessage->getAuthor() === $this) {
                $discordGroupedMessage->setAuthor(null);
            }
        }

        return $this;
    }
}
