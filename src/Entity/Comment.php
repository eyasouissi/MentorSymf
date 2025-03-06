<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\CommentRepository; // Ensure this use statement exists
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;



#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(length: 2000)]
    #[Assert\NotBlank(message: "Content cannot be empty.")]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: Reply::class, orphanRemoval: true)]
    private Collection $replies;

    // For handling file uploads:
    private ?File $file = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->replies = new ArrayCollection();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): static
    {
        $this->file = $file;
        return $this;
    }

        public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(Reply $reply): self
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setComment($this);
        }
        return $this;
    }

    public function removeReply(Reply $reply): self
    {
        if ($this->replies->removeElement($reply)) {
            if ($reply->getComment() === $this) {
                $reply->setComment(null);
            }
        }
        return $this;
    }

    // Method to handle file upload after persisting
    public function uploadPhoto(): void
    {
        if (null === $this->file) {
            return;
        }

        // Set the file path where the image will be saved (e.g., 'uploads/comments')
        $fileName = uniqid() . '.' . $this->file->guessExtension();
        $this->file->move(
            __DIR__ . '/../../public/uploads/comments',
            $fileName
        );

        $this->photo = $fileName;
        $this->file = null; // Clear the file property after upload
    }

    // Custom validation logic for content (optional)
    public static function validateContent($value, ExecutionContextInterface $context)
    {
        // Ensure content is not empty
        if (empty($value)) {
            $context->buildViolation('Content cannot be empty.')
                ->atPath('content')
                ->addViolation();
        }

        // Check word count (limit to 200 words)
        $wordCount = str_word_count($value);
        if ($wordCount > 200) {
            $context->buildViolation('Content must be limited to 200 words.')
                ->atPath('content')
                ->addViolation();
        }
    }
}