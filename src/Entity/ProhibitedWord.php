<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\ProhibitedWordRepository")]
#[ORM\Table(name: "prohibited_word")]
#[ORM\HasLifecycleCallbacks]
class ProhibitedWord
{
    public const CATEGORIES = [
        'profanity',
        'hate_speech',
        'personal_info',
        'other'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank]
    private $word;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\Choice(choices: self::CATEGORIES, message: "Invalid category")]
    private $category;

    #[ORM\Column(type: "smallint")]
    #[Assert\Range(min: 1, max: 5)]
    private $severity = 1;

    #[ORM\Column(type: "datetime")]
    private $createdAt;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getSeverity(): ?int
    {
        return $this->severity;
    }

    public function setSeverity(int $severity): self
    {
        $this->severity = $severity;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}