<?php

namespace App\Entity;

use App\Repository\HoyolabStatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HoyolabStatsRepository::class)
 */
class HoyolabStats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=HoyolabStatType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $statType;

    /**
     * @ORM\ManyToOne(targetEntity=HoyolabPost::class, inversedBy="hoyolabStats")
     */
    private $hoyolabPost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

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

    public function getStatType(): ?HoyolabStatType
    {
        return $this->statType;
    }

    public function setStatType(?HoyolabStatType $statType): self
    {
        $this->statType = $statType;

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
}
