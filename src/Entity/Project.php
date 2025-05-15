<?php

namespace App\Entity;


use App\Repository\ProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title cannot be empty!")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Description cannot be empty!")]
    #[Assert\Length(
        min: 5,
        minMessage: "Description must contain at least 5 characters!"
    )]
    private ?string $description_project = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $fichier_pdf = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation_project = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Select one of these choices!")]
    private ?string $difficulte = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual("today", message: "Please enter a valid date!")]
    private ?\DateTimeInterface $date_limite = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: GroupStudent::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?GroupStudent $group = null;


    public function __construct()
    {
        $this->date_creation_project = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescriptionProject(): ?string
    {
        return $this->description_project;
    }

    public function setDescriptionProject(?string $description_project): self
    {
        $this->description_project = $description_project;
        return $this;
    }

    public function getDateCreationProject(): ?\DateTimeInterface
    {
        return $this->date_creation_project;
    }

    public function setDateCreationProject(\DateTimeInterface $date_creation_project): self
    {
        $this->date_creation_project = $date_creation_project;
        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): self
    {
        $this->difficulte = $difficulte;
        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->date_limite;
    }

    public function setDateLimite(?\DateTimeInterface $date_limite): self
    {
        $this->date_limite = $date_limite;
        return $this;
    }

    public function getFichierPdf(): ?string
    {
        return $this->fichier_pdf;
    }

    public function setFichierPdf(?string $fichier_pdf): self
    {
        $this->fichier_pdf = $fichier_pdf;
        return $this;
    }

    public function getGroup(): ?GroupStudent
    {
        return $this->group;
    }

    public function setGroup(?GroupStudent $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }
}

