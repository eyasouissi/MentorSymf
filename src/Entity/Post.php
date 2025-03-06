<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Forum $forum = null;

    #[ORM\Column(length: 2000)]
    #[Assert\NotBlank(message: "Content cannot be empty.")]
    #[Assert\Length(
        max: 2000,
        maxMessage: "Content cannot exceed 2000 characters."
    )]
    #[Assert\Regex(
        pattern: '/\b(admin|root|sudo)\b/i',
        match: false,
        message: "Content contains prohibited terms"
    )] // Temporary simple filter
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(
        targetEntity: Comment::class, 
        mappedBy: 'post', 
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $comments;

    #[ORM\Column]
    private ?int $likes = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Assert\Count(
        max: 10,
        maxMessage: "You can upload up to 10 photos maximum."
    )]
    #[Assert\All([
        new Assert\File([
            'maxSize' => '5M',
            'mimeTypes' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
            ],
            'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF, or WEBP)',
        ])
    ])]
    private ?array $photos = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(
        protocols: ['http', 'https'],
        message: 'Please enter a valid GIF URL'
    )]
    private ?string $gifUrl = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_likes')]
    private Collection $likedByUsers;

    #[ORM\OneToMany(
        targetEntity: Notif::class, 
        mappedBy: 'post', 
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $notifications;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likedByUsers = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->notifications = new ArrayCollection();

    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): static
    {
        $this->forum = $forum;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        $this->updatedAt = new \DateTime();
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

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;
        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): static
    {
        $this->photos = $photos;
        return $this;
    }

    public function getGifUrl(): ?string
    {
        return $this->gifUrl;
    }

    public function setGifUrl(?string $gifUrl): static
    {
        $this->gifUrl = $gifUrl;
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

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }
        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
        return $this;
    }

    public function getLikedByUsers(): Collection
    {
        return $this->likedByUsers;
    }

    public function addLikedByUser(User $user): static
    {
        if (!$this->likedByUsers->contains($user)) {
            $this->likedByUsers->add($user);
        }
        return $this;
    }

    public function removeLikedByUser(User $user): static
    {
        $this->likedByUsers->removeElement($user);
        return $this;
    }

    public function isLikedByUser(User $user): bool
    {
        return $this->likedByUsers->contains($user);
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notif $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setPost($this);
        }
        return $this;
    }

    public function removeNotification(Notif $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPost() === $this) {
                $notification->setPost(null);
            }
        }
        return $this;
    }
}