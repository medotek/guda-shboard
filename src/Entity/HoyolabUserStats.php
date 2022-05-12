<?php

namespace App\Entity;

use App\Repository\HoyolabUserStatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HoyolabUserStatsRepository::class)
 */
class HoyolabUserStats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $likes;

    /**
     * @ORM\Column(type="integer")
     */
    private $posts;

    /**
     * @ORM\Column(type="integer")
     */
    private $replyposts;

    /**
     * @ORM\Column(type="integer")
     */
    private $followed;

    /**
     * @ORM\Column(type="integer")
     */
    private $newFollowers;

    /**
     * @ORM\ManyToOne(targetEntity=HoyolabPostUser::class, inversedBy="stats")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    public function getPosts(): ?int
    {
        return $this->posts;
    }

    public function setPosts(int $posts): self
    {
        $this->posts = $posts;

        return $this;
    }

    public function getReplyposts(): ?int
    {
        return $this->replyposts;
    }

    public function setReplyposts(int $replyposts): self
    {
        $this->replyposts = $replyposts;

        return $this;
    }

    public function getFollowed(): ?int
    {
        return $this->followed;
    }

    public function setFollowed(int $followed): self
    {
        $this->followed = $followed;

        return $this;
    }

    public function getNewFollowers(): ?int
    {
        return $this->newFollowers;
    }

    public function setNewFollowers(int $newFollowers): self
    {
        $this->newFollowers = $newFollowers;

        return $this;
    }

    public function getUser(): ?HoyolabPostUser
    {
        return $this->user;
    }

    public function setUser(?HoyolabPostUser $user): self
    {
        $this->user = $user;

        return $this;
    }
}
