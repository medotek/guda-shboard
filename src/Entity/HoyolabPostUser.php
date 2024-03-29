<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\HoyolabPostUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"={
 *              "security"="object.user == user"
 *              }
 *          },
 *     normalizationContext={"groups"={"hoyolab_post_user"}},
 *     denormalizationContext={}
 * )
 * @ORM\Entity(repositoryClass=HoyolabPostUserRepository::class)
 */
class HoyolabPostUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ApiProperty(identifier=true)
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=15, unique=true)
     */
    private $uid;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=100)
     */
    private $nickname;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $avatarUrl;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="hoyolabPostUsers")
     */
    public $user;

    /**
     * @ORM\OneToMany(targetEntity=HoyolabPost::class, mappedBy="hoyolabPostUser")
     */
    public $hoyolabPosts;

    /**
     * @Groups("hoyolab_post_user")
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $pendant;

    /**
     * @Groups("hoyolab_post_user_detail")
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $webhookUrl;

    /**
     * @ORM\OneToMany(targetEntity=HoyolabUserStats::class, mappedBy="user")
     */
    private $stats;

    public function __construct()
    {
        $this->hoyolabPosts = new ArrayCollection();
        $this->stats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

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

    /**
     * @return Collection|HoyolabPost[]
     */
    public function getHoyolabPosts(): Collection
    {
        return $this->hoyolabPosts;
    }

    public function addHoyolabPost(HoyolabPost $hoyolabPost): self
    {
        if (!$this->hoyolabPosts->contains($hoyolabPost)) {
            $this->hoyolabPosts[] = $hoyolabPost;
            $hoyolabPost->setHoyolabPostUser($this);
        }

        return $this;
    }

    public function removeHoyolabPost(HoyolabPost $hoyolabPost): self
    {
        if ($this->hoyolabPosts->removeElement($hoyolabPost)) {
            // set the owning side to null (unless already changed)
            if ($hoyolabPost->getHoyolabPostUser() === $this) {
                $hoyolabPost->setHoyolabPostUser(null);
            }
        }

        return $this;
    }

    public function getPendant(): ?string
    {
        return $this->pendant;
    }

    public function setPendant(?string $pendant): self
    {
        $this->pendant = $pendant;

        return $this;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl): self
    {
        $this->webhookUrl = $webhookUrl;

        return $this;
    }

    /**
     * @return Collection<int, HoyolabUserStats>
     */
    public function getStats(): Collection
    {
        return $this->stats;
    }

    public function addStat(HoyolabUserStats $stat): self
    {
        if (!$this->stats->contains($stat)) {
            $this->stats[] = $stat;
            $stat->setUser($this);
        }

        return $this;
    }

    public function removeStat(HoyolabUserStats $stat): self
    {
        if ($this->stats->removeElement($stat)) {
            // set the owning side to null (unless already changed)
            if ($stat->getUser() === $this) {
                $stat->setUser(null);
            }
        }

        return $this;
    }
}
