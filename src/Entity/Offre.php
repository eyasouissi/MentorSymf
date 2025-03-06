<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_offre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The name cannot be empty.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "The name should be at least {{ limit }} characters long.",
        maxMessage: "The name cannot be longer than {{ limit }} characters."
    )]
    private ?string $nom_offre = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Image(
        mimeTypes: ["image/jpeg", "image/png", "image/gif"],
        maxSize: "2M",
        mimeTypesMessage: "Veuillez télécharger une image valide (JPEG, PNG, GIF).",
        maxSizeMessage: "L'image ne doit pas dépasser 2 Mo."
    )]
    private ?string $image_offre = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "The price cannot be empty.")]
    #[Assert\GreaterThan(value: 0, message: "The price must be greater than 0.")]
    private ?float $prix = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotBlank(message: "Please provide a start date.")]
    #[Assert\GreaterThanOrEqual("today", message: "The start date must be today or in the future.")]
    private ?\DateTimeInterface $date_debut;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "Please provide an end date.")]
    #[Assert\GreaterThan(propertyPath: "date_debut", message: "The end date must be after the start date.")]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Description cannot be empty.")]
    #[Assert\Length(
        min: 5,
        max: 100,
        minMessage: "Description should be at least {{ limit }} characters long.",
        maxMessage: "Description cannot be longer than {{ limit }} characters."
    )]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'id_offre', targetEntity: Paiement::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $paiements;

    public function __construct()
    {
        $this->date_debut = new \DateTime();
        $this->paiements = new ArrayCollection();
    }

    public function getIdOffre(): ?int
    {
        return $this->id_offre;
    }

    public function getNomOffre(): ?string
    {
        return $this->nom_offre;
    }

    public function setNomOffre(string $nom_offre): self
    {
        $this->nom_offre = $nom_offre;
        return $this;
    }

    public function getImageOffre(): ?string
    {
        return $this->image_offre;
    }

    public function setImageOffre(?string $image_offre): static
    {
        $this->image_offre = $image_offre;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;
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

    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function __toString(): string
    {
        return $this->nom_offre ?? 'Offre';
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setIdOffre($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getIdOffre() === $this) {
                $paiement->setIdOffre(null);
            }
        }

        return $this;
    }
}
