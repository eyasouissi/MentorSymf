<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_offre = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_offre = '';

    #[ORM\Column(length: 255)]
    private ?string $image_offre = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_debut;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]  
    private ?\DateTimeInterface $date_fin = null;
    

    #[ORM\Column(length: 255)]
    private ?string $description = null;

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

    public function setImageOffre(string $image_offre): static
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

    public function setDateFin(\DateTimeInterface $date_fin): static
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

    public function __toString(): string
    {
        return $this->nom_offre ?? 'Offre';
    }
    
    #[ORM\OneToMany(mappedBy: 'id_offre', targetEntity: Paiement::class, cascade: ['remove'], orphanRemoval: true)]
private Collection $paiements;

public function getPaiements(): Collection
{
    return $this->paiements;
}
}
