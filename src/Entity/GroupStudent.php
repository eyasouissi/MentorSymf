<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: 'groupstudent')]
class GroupStudent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $nbr_members;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: "Description cannot be empty!")]
    #[Assert\Length(
        min: 5,
        minMessage: "Description must contain at least 5 characters!"
    )]
    private ?string $description_group = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Group name cannot be empty!")]
    private ?string $nom_group = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groups')]
    #[ORM\JoinTable(name: 'group_student_members')]
    private Collection $members;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $fichierPdf = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_creation_group = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "You should fix a meeting date!")]
    #[Assert\GreaterThanOrEqual("today", message: "Please, enter a valid date!")]
    private ?\DateTimeInterface $date_meet = null;

    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'group', orphanRemoval: true)]
    private Collection $projects;
    
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->date_creation_group = new \DateTime();
        $this->projects = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->nbr_members = 0; // Initialize properly
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbrMembers(): int
    {
        return $this->nbr_members;
    }

    public function setNbrMembers(int $nbr_members): self
    {
        $this->nbr_members = $nbr_members;
        return $this;
    }

    public function getDescriptionGroup(): ?string
    {
        return $this->description_group;
    }

    public function setDescriptionGroup(?string $description_group): self
    {
        $this->description_group = $description_group;
        return $this;
    }

    public function getNomGroup(): ?string
    {
        return $this->nom_group;
    }

    public function setNomGroup(string $nom_group): self
    {
        $this->nom_group = $nom_group;
        return $this;
    }

    public function getFichierPdf(): ?string
    {
        return $this->fichierPdf;
    }

    public function setFichierPdf(?string $fichierPdf): self
    {
        $this->fichierPdf = $fichierPdf;
        return $this;
    }

    public function getDateCreationGroup(): ?\DateTimeInterface
    {
        return $this->date_creation_group;
    }

    public function setDateCreationGroup(?\DateTimeInterface $date_creation_group): self
    {
        $this->date_creation_group = $date_creation_group;
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

    public function getDateMeet(): ?\DateTimeInterface
    {
        return $this->date_meet;
    }

    public function setDateMeet(?\DateTimeInterface $date_meet): self
    {
        $this->date_meet = $date_meet;
        return $this;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setGroup($this);
        }
        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            if ($project->getGroup() === $this) {
                $project->setGroup(null);
            }
        }
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->addGroup($this); // Ensure this method exists in the User entity
            $this->nbr_members = $this->members->count(); // Update the member count
        }
        return $this;
    }

    public function removeMember(User $member): self
    {
        if ($this->members->removeElement($member)) {
            $member->removeGroup($this);
            $this->nbr_members = $this->members->count();
        }
        return $this;
    }
}