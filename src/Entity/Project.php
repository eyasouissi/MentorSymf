<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        min: 20,
        minMessage: "La description doit contenir au moins 20 caractères."
    )]
    private ?string $description_project = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation_project = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Vous devez sélectionner un niveau de difficulté.")]
    private ?string $difficulte = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual("today", message: "La date limite doit être aujourd'hui ou plus tard.")]
    private ?\DateTimeInterface $date_limite = null;

    #[ORM\Column(type: "string", nullable: true)]
    #[Assert\NotBlank(message: "Un fichier PDF est requis.")]
    private ?string $fichier_pdf = null;

    // 🛠️ 🔥 Relation ManyToMany avec Groupe
    #[ORM\ManyToMany(targetEntity: Groupe::class, inversedBy: "projects")]
    #[ORM\JoinTable(name: "project_groupe")] // Updated the table name
    private Collection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
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

    // 🛠️ 🔥 Gestion des relations ManyToMany avec Groupe

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Groupe $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addProject($this); // Synchronisation avec Groupe
        }

        return $this;
    }

    public function removeGroup(Groupe $group): static
    {
        if ($this->groups->removeElement($group)) {
            $group->removeProject($this); // Synchronisation avec Groupe
        }

        return $this;
    }
}
