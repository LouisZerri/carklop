<?php

namespace App\Entity;

use App\Repository\DriverReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DriverReviewRepository::class)]
#[ORM\Table(name: "driver_review")]
#[ORM\UniqueConstraint(name: "unique_driver_review", columns: ["author_id", "target_id", "trip_id"])]
class DriverReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:details'])]
    private ?int $id = null;

    // Le passager qui laisse l'avis
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:details'])]
    private ?User $author = null;

    // Le conducteur qui reçoit l'avis
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'driverReviewsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:details'])]
    private ?User $target = null;

    // Le trajet concerné
    #[ORM\ManyToOne(targetEntity: Trip::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:details'])]
    private ?Trip $trip = null;

    #[ORM\Column(type: "smallint")]
    #[Groups(['user:details'])]
    private int $rating = 5;

    #[ORM\Column(type: "text", nullable: true)]
    #[Groups(['user:details'])]
    private ?string $comment = null;

    #[ORM\Column(type: "datetime_immutable")]
    #[Groups(['user:details'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getTarget(): ?User
    {
        return $this->target;
    }

    public function setTarget(?User $target): static
    {
        $this->target = $target;
        return $this;
    }

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): static
    {
        $this->trip = $trip;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
