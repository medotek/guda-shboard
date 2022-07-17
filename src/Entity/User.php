<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields="name", message="Ce nom d'utilisateur a déjà été pris.")
 * @UniqueEntity(fields="email", message="Cet email est déjà utilisé. Si vous n'avez jamais mis votre email sur le site, veuilez contacter un administrateur.")
 */
class User implements UserInterface, \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface
{
    private $encoderFactory;

    /**
     * @Groups("user")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @Groups("user")
     * @Groups({"discord_embed_message:read", "discord_grouped_messages:read"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationDate;


    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @Groups("user")
     * @ORM\OneToMany(targetEntity=DiscordEmbedMessage::class, mappedBy="user")
     */
    private $discordEmbedMessages;

    /**
     * @ORM\OneToMany(targetEntity=DiscordGroupedMessages::class, mappedBy="author")
     */
    private $discordGroupedMessages;

    /**
     * @ORM\ManyToMany(targetEntity=Team::class, mappedBy="members")
     */
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="creator")
     */
    private $teamsCreated;

    /**
     * @Groups("user")
     * @ORM\OneToOne(targetEntity=DiscordCredentials::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $discordCredentials;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastConnectionDate;

    /**
     * @ORM\ManyToMany(targetEntity=DiscordWebhook::class, mappedBy="users")
     */
    private $discordWebhooks;

    /**
     *
     * @ORM\OneToMany(targetEntity=DiscordWebhook::class, mappedBy="owner", cascade={"persist"}, orphanRemoval=true)
     */
    private $discordOwnedWehbooks;

    /**
     * @MaxDepth(2)
     * @Groups("user")
     * @ORM\OneToMany(targetEntity=HoyolabPostUser::class, mappedBy="user")
     */
    private $hoyolabPostUsers;

    public function __construct()
    {
        $this->discordEmbedMessages = new ArrayCollection();
        $this->discordGroupedMessages = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->teamsCreated = new ArrayCollection();
        $this->discordWebhooks = new ArrayCollection();
        $this->discordOwnedWehbooks = new ArrayCollection();
        $this->hoyolabPosts = new ArrayCollection();
        $this->hoyolabPostUsers = new ArrayCollection();
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

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->addMember($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            $team->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeamsCreated(): Collection
    {
        return $this->teamsCreated;
    }

    public function addTeamsCreated(Team $teamsCreated): self
    {
        if (!$this->teamsCreated->contains($teamsCreated)) {
            $this->teamsCreated[] = $teamsCreated;
            $teamsCreated->setCreator($this);
        }

        return $this;
    }

    public function removeTeamsCreated(Team $teamsCreated): self
    {
        if ($this->teamsCreated->removeElement($teamsCreated)) {
            // set the owning side to null (unless already changed)
            if ($teamsCreated->getCreator() === $this) {
                $teamsCreated->setCreator(null);
            }
        }

        return $this;
    }

    public function getDiscordCredentials(): ?DiscordCredentials
    {
        return $this->discordCredentials;
    }

    public function setDiscordCredentials(?DiscordCredentials $discordCredentials): self
    {
        // unset the owning side of the relation if necessary
        if ($discordCredentials === null && $this->discordCredentials !== null) {
            $this->discordCredentials->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($discordCredentials !== null && $discordCredentials->getUser() !== $this) {
            $discordCredentials->setUser($this);
        }

        $this->discordCredentials = $discordCredentials;

        return $this;
    }

    public function getLastConnectionDate(): ?\DateTimeInterface
    {
        return $this->lastConnectionDate;
    }

    public function setLastConnectionDate(?\DateTimeInterface $lastConnectionDate): self
    {
        $this->lastConnectionDate = $lastConnectionDate;

        return $this;
    }

    /**
     * @return Collection|DiscordWebhook[]
     */
    public function getDiscordWebhooks(): Collection
    {
        return $this->discordWebhooks;
    }

    public function addDiscordWebhook(DiscordWebhook $discordWebhook): self
    {
        if (!$this->discordWebhooks->contains($discordWebhook)) {
            $this->discordWebhooks[] = $discordWebhook;
            $discordWebhook->addUser($this);
        }

        return $this;
    }

    public function removeDiscordWebhook(DiscordWebhook $discordWebhook): self
    {
        if ($this->discordWebhooks->removeElement($discordWebhook)) {
            $discordWebhook->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|DiscordWebhook[]
     */
    public function getDiscordOwnedWehbooks(): Collection
    {
        return $this->discordOwnedWehbooks;
    }

    public function addDiscordOwnedWehbook(DiscordWebhook $discordOwnedWehbook): self
    {
        if (!$this->discordOwnedWehbooks->contains($discordOwnedWehbook)) {
            $this->discordOwnedWehbooks[] = $discordOwnedWehbook;
            $discordOwnedWehbook->setOwner($this);
        }

        return $this;
    }

    public function removeDiscordOwnedWehbook(DiscordWebhook $discordOwnedWehbook): self
    {
        if ($this->discordOwnedWehbooks->removeElement($discordOwnedWehbook)) {
            // set the owning side to null (unless already changed)
            if ($discordOwnedWehbook->getOwner() === $this) {
                $discordOwnedWehbook->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|HoyolabPostUser[]
     */
    public function getHoyolabPostUsers(): Collection
    {
        return $this->hoyolabPostUsers;
    }

    public function addHoyolabPostUser(HoyolabPostUser $hoyolabPostUser): self
    {
        if (!$this->hoyolabPostUsers->contains($hoyolabPostUser)) {
            $this->hoyolabPostUsers[] = $hoyolabPostUser;
            $hoyolabPostUser->setUser($this);
        }

        return $this;
    }

    public function removeHoyolabPostUser(HoyolabPostUser $hoyolabPostUser): self
    {
        if ($this->hoyolabPostUsers->removeElement($hoyolabPostUser)) {
            // set the owning side to null (unless already changed)
            if ($hoyolabPostUser->getUser() === $this) {
                $hoyolabPostUser->setUser(null);
            }
        }

        return $this;
    }
}
