<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_group = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_group = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_createur = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_creation_group = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_meet = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'groups')]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdGroup(): ?int
    {
        return $this->id_group;
    }

    public function setIdGroup(int $id_group): static
    {
        $this->id_group = $id_group;

        return $this;
    }

    public function getDescriptionGroup(): ?string
    {
        return $this->description_group;
    }

    public function setDescriptionGroup(?string $description_group): static
    {
        $this->description_group = $description_group;

        return $this;
    }

    public function getNomCreateur(): ?string
    {
        return $this->nom_createur;
    }

    public function setNomCreateur(string $nom_createur): static
    {
        $this->nom_createur = $nom_createur;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateCreationGroup(): ?\DateTimeInterface
    {
        return $this->date_creation_group;
    }

    public function setDateCreationGroup(\DateTimeInterface $date_creation_group): static
    {
        $this->date_creation_group = $date_creation_group;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDateMeet(): ?\DateTimeInterface
    {
        return $this->date_meet;
    }

    public function setDateMeet(?\DateTimeInterface $date_meet): static
    {
        $this->date_meet = $date_meet;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addGroup($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeGroup($this);
        }

        return $this;
    }
}
