<?php

namespace App\Entity;

use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Courses::class, inversedBy: 'levels')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Courses $course = null;
    

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'level', cascade: ['persist', 'remove'])]
    private Collection $files;


    #[ORM\ManyToOne(targetEntity: Level::class)]
    #[ORM\JoinColumn(name: 'previous_level_id', referencedColumnName: 'id', nullable: true)]
    private ?Level $previousLevel = null;


    public function isUnlocked(User $user): bool
    {
        // Le niveau 1 est toujours déverrouillé
        if ($this->previousLevel === null) {
            return true;
        }
    
        // Vérifiez si l'utilisateur a complété le niveau précédent
        return $user->hasCompletedLevel($this->previousLevel);
    }

// Level.php
#[ORM\Column(type: 'boolean')]
private bool $isComplete = false;

public function getIsComplete(): bool
{
    return $this->isComplete;
}

public function setIsComplete(bool $isComplete): self
{
    $this->isComplete = $isComplete;
    return $this;
}
    
    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCourse(): ?Courses
    {
        return $this->course;
    }

    public function setCourse(?Courses $course): static
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setLevel($this);
        }

        return $this;
    }

    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getLevel() === $this) {
                $file->setLevel(null);
            }
        }

        return $this;
    }

    public function getPreviousLevel(): ?Level
    {
        return $this->previousLevel;
    }
    
    public function setPreviousLevel(?Level $previousLevel): static
    {
        $this->previousLevel = $previousLevel;
        return $this;
    }
    
 

}

