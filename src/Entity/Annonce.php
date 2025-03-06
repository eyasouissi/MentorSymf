<?php

namespace App\Entity;

use App\Repository\AnnonceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: AnnonceRepository::class)]


class Annonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $image_a = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $titre_a = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $description_a = null;

    #[ORM\Column(type: "datetime")]
    private \DateTime $date_a;

    //#[ORM\ManyToOne(targetEntity: User::class)]
    //#[ORM\JoinColumn(name: "id_user", referencedColumnName: "id")]
    //private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getImageA(): ?string
{
    return $this->image_a;
}


    public function setImageA(string $image_a): static
    {
        $this->image_a = $image_a;
        return $this;
    }

    public function getTitreA(): ?string
    {
        return $this->titre_a;
    }

    public function setTitreA(string $titre_a): static
    {
        $this->titre_a = $titre_a;
        return $this;
    }

    public function getDescriptionA(): ?string
    {
        return $this->description_a;
    }

    public function setDescriptionA(string $description_a): static
    {
        $this->description_a = $description_a;
        return $this;
    }

    public function getDateA(): \DateTime
    {
        return $this->date_a;
    }

    public function setDateA(\DateTime $date_a): static
    {
        $this->date_a = $date_a;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
