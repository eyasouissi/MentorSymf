<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Name cannot be blank.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Lastname cannot be blank.")]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Profile image cannot be blank.")]
    private ?string $profileimage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Email cannot be blank.")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Password cannot be blank.")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $accountname = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $datecreation = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column(length: 255)]
    private ?string $usertype = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastlogin = null;

    #[ORM\Column(length: 255)]
    private ?string $genre = null;

    #[ORM\Column(length: 255)]
    private ?string $sessiontocken = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sessionexpiration = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    // Getter and Setter for ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter and Setter for Name
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    // Getter and Setter for Lastname
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    // Getter and Setter for Profile Image
    public function getProfileimage(): ?string
    {
        return $this->profileimage;
    }

    public function setProfileimage(string $profileimage): self
    {
        $this->profileimage = $profileimage;
        return $this;
    }

    // Getter and Setter for Bio
    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }

    // Getter and Setter for Email
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    // Getter and Setter for Password
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    // Getter and Setter for Account Name
    public function getAccountname(): ?string
    {
        return $this->accountname;
    }

    public function setAccountname(string $accountname): self
    {
        $this->accountname = $accountname;
        return $this;
    }

    // Getter and Setter for Date Creation
    public function getDatecreation(): ?\DateTimeImmutable
    {
        return $this->datecreation;
    }

    public function setDatecreation(\DateTimeImmutable $datecreation): self
    {
        $this->datecreation = $datecreation;
        return $this;
    }

    // Getter and Setter for Status
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    // Getter and Setter for Role
    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    // Getter and Setter for User Type
    public function getUsertype(): ?string
    {
        return $this->usertype;
    }

    public function setUsertype(string $usertype): self
    {
        $this->usertype = $usertype;
        return $this;
    }

    // Getter and Setter for Last Login
    public function getLastlogin(): ?\DateTimeImmutable
    {
        return $this->lastlogin;
    }

    public function setLastlogin(\DateTimeImmutable $lastlogin): self
    {
        $this->lastlogin = $lastlogin;
        return $this;
    }

    // Getter and Setter for Genre
    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    // Getter and Setter for Session Token
    public function getSessiontocken(): ?string
    {
        return $this->sessiontocken;
    }

    public function setSessiontocken(string $sessiontocken): self
    {
        $this->sessiontocken = $sessiontocken;
        return $this;
    }

    // Getter and Setter for Session Expiration
    public function getSessionexpiration(): ?\DateTimeImmutable
    {
        return $this->sessionexpiration;
    }

    public function setSessionexpiration(\DateTimeImmutable $sessionexpiration): self
    {
        $this->sessionexpiration = $sessionexpiration;
        return $this;
    }

    // Getter and Setter for Image
    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
