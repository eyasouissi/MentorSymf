<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupeRepository::class)]
#[ORM\Table(name: 'groupe')]
class Groupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le nombre de membres ne peut pas être vide.")]
    private ?int $nbrMembers = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    private ?string $descriptionGroup = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du groupe ne peut pas être vide.")]
    private ?string $nomGroup = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ des membres ne peut pas être vide.")]
    private ?string $members = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreationGroup = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual("today", message: "La date de rencontre doit être aujourd'hui ou plus tard.")]
    private ?\DateTimeInterface $dateMeet = null;

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

    public function getNbrMembers(): ?int
    {
        return $this->nbrMembers;
    }

    public function setNbrMembers(int $nbrMembers): static
    {
        $this->nbrMembers = $nbrMembers;

        return $this;
    }

    public function getDescriptionGroup(): ?string
    {
        return $this->descriptionGroup;
    }

    public function setDescriptionGroup(?string $descriptionGroup): static
    {
        $this->descriptionGroup = $descriptionGroup;

        return $this;
    }

    public function getNomGroup(): ?string
    {
        return $this->nomGroup;
    }

    public function setNomGroup(string $nomGroup): static
    {
        $this->nomGroup = $nomGroup;

        return $this;
    }

    public function getMembers(): ?string
    {
        return $this->members;
    }

    public function setMembers(string $members): static
    {
        $this->members = $members;

        return $this;
    }

    public function getDateCreationGroup(): ?\DateTimeInterface
    {
        return $this->dateCreationGroup;
    }

    public function setDateCreationGroup(?\DateTimeInterface $dateCreationGroup): static
    {
        $this->dateCreationGroup = $dateCreationGroup;

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
        return $this->dateMeet;
    }

    public function setDateMeet(?\DateTimeInterface $dateMeet): static
    {
        $this->dateMeet = $dateMeet;

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
