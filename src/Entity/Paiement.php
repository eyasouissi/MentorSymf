<?php

namespace App\Entity;
use App\Entity\Offre;

use App\Repository\PaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    

    #[ORM\Column]
    private ?int $id_paiement = null;

    #[ORM\Column]
    private ?int $id_user = null;

    #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(name: "id_offre", referencedColumnName: "id_offre", nullable: false, onDelete: "CASCADE")]
    private ?Offre $id_offre = null;
    
    #[ORM\Column(length: 255)]
    private ?string $yourEmail = null;
    #[ORM\Column]
    #[Assert\NotBlank(['message' => 'Card number is required.'])]
    private ?string $card_num = null;
    
    
    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $Date_expiration = null;
    

    
    #[ORM\Column]
    private ?int $cvv = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPaiement(): ?int
    {
        return $this->id_paiement;
    }

    public function setIdPaiement(int $id_paiement): static
    {
        $this->id_paiement = $id_paiement;

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getIdOffre(): ?Offre
    {
        return $this->id_offre;
    }

    public function setIdOffre(?Offre $id_offre): static
    {
        $this->id_offre = $id_offre;

        return $this;
    }

    public function getYourEmail(): ?string
    {
        return $this->yourEmail;
    }

    public function setYourEmail(string $yourEmail): static
    {
        $this->yourEmail = $yourEmail;

        return $this;
    }

    public function getCardNum(): ?string
    {
        return $this->card_num;
    }

    public function setCardNum(string $cardNum): static
    {   
        $this->card_num = preg_replace('/\s+/', '', $cardNum);

        $this->card_num = openssl_encrypt($cardNum, 'aes-256-cbc', 'votre_cle_secrete', 0, '1234567812345678');

        return $this;
    }

    public function getMaskedCardNum(): string
    {
        return '**** **** **** ' . substr($this->getCardNumDecrypted(), -4);
    }

    public function getCardNumDecrypted(): ?string
    {
        return openssl_decrypt($this->card_num, 'aes-256-cbc', 'votre_cle_secrete', 0, '1234567812345678');
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->Date_expiration;
    }

    public function setDateExpiration(\DateTimeInterface $Date_expiration): static
    {
        $this->Date_expiration = $Date_expiration;

        return $this;
    }

    public function getCvv(): ?int
    {
        return $this->cvv;
    }

    public function setCvv(int $cvv): static
    {
        $this->cvv = $cvv;

        return $this;
    }
    public function __construct() {
        // Initialisation de Date_expiration avec la date actuelle si elle est nulle
        $this->Date_expiration = new \DateTime();  // Définit la date actuelle par défaut
    }
  }