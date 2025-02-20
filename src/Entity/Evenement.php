<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(min: 3, minMessage: "Le titre doit contenir au moins 3 caractères.")]
    private ?string $titreE = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(min: 10, minMessage: "La description doit contenir au moins 10 caractères.")]
    private ?string $descriptionE = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de début doit être une date valide.")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de fin doit être une date valide.")]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être postérieure à la date de début.")]
    private ?\DateTimeInterface $dateFin = null;

    //#[ORM\ManyToOne(targetEntity: User::class)]
    //#[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false)]
    //#[Assert\NotNull(message: "L'utilisateur est obligatoire.")]
    //private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Annonce::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(name: "id_annonce", referencedColumnName: "id", nullable: false)]
    private ?Annonce $annonce = null;
    

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $imageE = null; // Image field

    // Getters & Setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreE(): ?string
    {
        return $this->titreE;
    }

    public function setTitreE(string $titreE): static
    {
        $this->titreE = $titreE;
        return $this;
    }

    public function getDescriptionE(): ?string
    {
        return $this->descriptionE;
    }

    public function setDescriptionE(string $descriptionE): static
    {
        $this->descriptionE = $descriptionE;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
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

    public function getAnnonce(): ?Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(Annonce $annonce): static
    {
        $this->annonce = $annonce;
        return $this;
    }

    public function getImageE(): ?string // Getter for the image
    {
        return $this->imageE;
    }

    public function setImageE(?string $imageE): static // Setter for the image
    {
        $this->imageE = $imageE;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imageE ? '/uploads/' . $this->imageE : null;
    }
}
