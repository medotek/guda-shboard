<?php

namespace App\Entity;

use App\Repository\DiscordCredentialsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=DiscordCredentialsRepository::class)
 */
class DiscordCredentials
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="discordCredentials", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @Groups("discord_credentials")
     * @ORM\Column(type="string", length=255)
     */
    private $accessToken;

    /**
     * @Groups("discord_credentials")
     * @ORM\Column(type="string", length=255)
     */
    private $refreshToken;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }
}
