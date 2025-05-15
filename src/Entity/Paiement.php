<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use App\Entity\Offre;
use App\Repository\PaiementRepository;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_paiement = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(name: "id_offre", referencedColumnName: "id_offre", nullable: false, onDelete: "CASCADE")]
    private ?Offre $offre = null;

    
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $datePaiement = null;

    public function __construct() {
        $this->datePaiement = new \DateTime();  // Définit la date actuelle par défaut
    }

    public function getId(): ?int
    {
        return $this->id_paiement;
    }

    public function setIdPaiement(int $id_paiement): static
    {
        $this->id_paiement = $id_paiement;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(?Offre $offre): static
    {
        $this->offre = $offre;
        return $this;
    }

   

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }
}
