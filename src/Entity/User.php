<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email(message: "L'adresse email est invalide.")]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins 8 caractères.")]
    #[Assert\Regex(pattern: "/[A-Z]/", message: "Le mot de passe doit contenir au moins une majuscule.")]
    #[Assert\Regex(pattern: "/\d/", message: "Le mot de passe doit contenir au moins un chiffre.")]
    #[Assert\Regex(pattern: "/[^A-Za-z0-9]/", message: "Le mot de passe doit contenir un caractère spécial.")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: "/^0[1-9](\d{8})$/",
        message: "Le numéro de téléphone doit être valide (format FR)."
    )]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La date doit être valide.")]
    #[Assert\LessThanOrEqual("-18 years", message: "Vous devez avoir au moins 18 ans.")]
    private ?\DateTime $date_of_birth = null;

    #[ORM\Column(length: 255)]
    private ?string $picture = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(nullable: true)]
    private ?bool $music = null;

    #[ORM\Column(nullable: true)]
    private ?bool $animal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $discussion = null;

    #[ORM\Column(nullable: true)]
    private ?bool $cigarette = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reset_token = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remember_token = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $car = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isVerified = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isLicenseVerified = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isStripeActive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(targetEntity: Trip::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $trips;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reservations;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'envoyeur', orphanRemoval: true)]
    private Collection $messages;

    /**
     * @var Collection<int, DriverReview>
     */
    #[ORM\OneToMany(targetEntity: DriverReview::class, mappedBy: 'target')]
    private Collection $driverReviewsReceived;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->isVerified = false;
        $this->isLicenseVerified = false;
        $this->isStripeActive = false;
        $this->trips = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->driverReviewsReceived = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTime
    {
        return $this->date_of_birth;
    }

    public function setDateOfBirth(\DateTime $date_of_birth): static
    {
        $this->date_of_birth = $date_of_birth;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function isMusic(): ?bool
    {
        return $this->music;
    }

    public function setMusic(?bool $music): static
    {
        $this->music = $music;

        return $this;
    }

    public function isAnimal(): ?bool
    {
        return $this->animal;
    }

    public function setAnimal(?bool $animal): static
    {
        $this->animal = $animal;

        return $this;
    }

    public function isDiscussion(): ?bool
    {
        return $this->discussion;
    }

    public function setDiscussion(?bool $discussion): static
    {
        $this->discussion = $discussion;

        return $this;
    }

    public function isCigarette(): ?bool
    {
        return $this->cigarette;
    }

    public function setCigarette(?bool $cigarette): static
    {
        $this->cigarette = $cigarette;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): static
    {
        $this->reset_token = $reset_token;

        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $expiresAt): static
    {
        $this->resetTokenExpiresAt = $expiresAt;
        return $this;
    }


    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken(?string $remember_token): static
    {
        $this->remember_token = $remember_token;

        return $this;
    }

    public function getCar(): ?string
    {
        return $this->car;
    }

    public function setCar(?string $car): static
    {
        $this->car = $car;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(?bool $is_verified): static
    {
        $this->isVerified = $is_verified;

        return $this;
    }

    public function isLicenseVerified(): ?bool
    {
        return $this->isLicenseVerified;
    }

    public function setIsLicenseVerified(?bool $is_license_verified): static
    {
        $this->isLicenseVerified = $is_license_verified;

        return $this;
    }

    public function isStripeActive(): ?bool
    {
        return $this->isStripeActive;
    }

    public function setIsStripeActive(?bool $is_stripe_active): static
    {
        $this->isStripeActive = $is_stripe_active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getTrips(): Collection
    {
        return $this->trips;
    }

    public function addTrip(Trip $trip): static
    {
        if (!$this->trips->contains($trip)) {
            $this->trips->add($trip);
            $trip->setUser($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): static
    {
        if ($this->trips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getUser() === $this) {
                $trip->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DriverReview>
     */
    public function getDriverReviewsReceived(): Collection
    {
        return $this->driverReviewsReceived;
    }

    public function addDriverReviewReceived(DriverReview $driverReview): static
    {
        if (!$this->driverReviewsReceived->contains($driverReview)) {
            $this->driverReviewsReceived->add($driverReview);
            $driverReview->setTarget($this);
        }
        return $this;
    }

    public function removeDriverReviewReceived(DriverReview $driverReview): static
    {
        if ($this->driverReviewsReceived->removeElement($driverReview)) {
            if ($driverReview->getTarget() === $this) {
                $driverReview->setTarget(null);
            }
        }
        return $this;
    }
}
