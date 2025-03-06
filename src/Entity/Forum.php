<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please fill this field')]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please fill this field')]
    #[Assert\Length(max: 255, maxMessage: 'Description cannot be longer than 255 characters')]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalposts = 0;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'forum', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $posts;
    
    #[ORM\Column(nullable: true)]
    private ?bool $isPublic = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Creation date should not be blank')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Update date should not be blank')]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'createdAt',
        message: 'The updated date cannot be before the created date.'
    )]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    private ?int $views = 0;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $topics = null;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTotalposts(): ?int
    {
        return $this->totalposts;
    }

    public function setTotalposts(?int $totalposts): static
    {
        $this->totalposts = $totalposts;
        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setForum($this);
            $this->totalposts = count($this->posts);
        }
        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getForum() === $this) {
                $post->setForum(null);
            }
            $this->totalposts = count($this->posts);
        }
        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(?bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;
        return $this;
    }

    public function getTopics(): ?string
    {
        return $this->topics;
    }

    public function setTopics(?string $topics): static
    {
        $this->topics = $topics;
        return $this;
    }
}
