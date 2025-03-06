<?php

namespace App\Entity;

use App\Repository\CoursesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursesRepository::class)]
class Courses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        min: 3, 
        max: 255, 
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isPublished = true;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    #[Assert\PositiveOrZero(message: "Les points de progression requis doivent être un nombre positif ou nul.")]
    private ?int $progressPointsRequired = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Assert\NotNull(message: "La date de création est obligatoire.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "La catégorie est obligatoire.")]
    private ?Category $category = null;

    /**
     * @var Collection<int, Level>
     */
    #[ORM\OneToMany(targetEntity: Level::class, mappedBy: 'course', cascade: ['remove'])]
    private Collection $levels;

      /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'course', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ratings;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPremium = false;
    
    #[ORM\Column(length: 255, nullable: true)]
private ?string $tutorName = null;

public function getTutorName(): ?string
{
    return $this->tutorName;
}

public function setTutorName(?string $tutorName): self
{
    $this->tutorName = $tutorName;
    return $this;
}


        // ⚡ Ajoute cette méthode pour calculer la moyenne des ratings
        public function getAverageRating(): float
        {
            if ($this->ratings->isEmpty()) {
                return 0; // Aucun avis, retourne 0
            }
    
            $total = 0;
            foreach ($this->ratings as $rating) {
                $total += $rating->getRating(); // Utilise la méthode getRating() de l'entité Rating
            }
    
            return $total / count($this->ratings);
        }
    public function isPremium(): bool
    {
        return $this->isPremium;
    }
    
    public function setIsPremium(bool $isPremium): static
    {
        $this->isPremium = $isPremium;
        return $this;
    }
    
    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setCourse($this);
        }
        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getCourse() === $this) {
                $rating->setCourse(null);
            }
        }
        return $this;
    }
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // Initialise createdAt à l'instanciation
        $this->levels = new ArrayCollection();
        $this->ratings = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;
        return $this;
    }

    public function getProgressPointsRequired(): ?int
    {
        return $this->progressPointsRequired;
    }

    public function setProgressPointsRequired(?int $progressPointsRequired): static
    {
        $this->progressPointsRequired = $progressPointsRequired;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, Level>
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): static
    {
        if (!$this->levels->contains($level)) {
            $this->levels->add($level);
            $level->setCourse($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): static
    {
        if ($this->levels->removeElement($level)) {
            if ($level->getCourse() === $this) {
                $level->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * Get the number of levels associated with the course.
     * 
     * @return int
     */
    public function getNumberOfLevels(): int
    {
        return count($this->levels);
    }
}
