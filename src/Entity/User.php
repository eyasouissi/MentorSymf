<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Email cannot be blank', groups: ['Registration'])]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column] // Change to json to handle the array properly
    private array $roles = [];


    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bio = null;

    
    #[Assert\NotBlank(message: 'Name cannot be blank', groups: ['Registration'])]
    #[Assert\Length(min: 6, minMessage: 'Name should be at least 2 characters long')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date_creation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_verified = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password_reset_token = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $password_reset_requested_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $speciality = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    /**
     * Temporary field for plain password during registration.
     */
    #[Assert\NotBlank(message: 'Password cannot be blank.', groups: ['Registration'])]
    #[Assert\Length(min: 6, minMessage: 'Password must be at least 6 characters long.')]
    private ?string $plainPassword = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $pfp = null;
  
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $bg = null;

    // Add the inverse relationship with Post
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class)]
    private Collection $posts;

    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'likedByUsers')]
    private Collection $likedPosts;


 /**
     * OAuth2 fields
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $oauthId = null; // Unique identifier from the OAuth provider

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $oauthType = null; // Type of OAuth provider (e.g., 'google', 'facebook')

    #[ORM\Column(type: 'boolean')]
    private bool $isRestricted = false;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $googleId = null; // Unique identifier from Google

////////////added by eya ////////////
#[ORM\ManyToMany(targetEntity: Level::class)]
#[ORM\JoinTable(name: 'user_completed_levels')]
private Collection $completedLevels;

#[ORM\Column(type: "integer")]
private int $karmaPoints = 0;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $ratings;

public function getKarmaPoints(): int
{
    return $this->karmaPoints;
}

public function addKarmaPoints(int $points): self
{
    $this->karmaPoints += $points;
    return $this;
}
public function calculateKarmaPoints(): void
{
    // Calculer le karma en fonction du nombre de niveaux complétés
    $this->karmaPoints = $this->completedLevels->count();
}


public function getCompletedLevels(): Collection
{
    return $this->completedLevels;
}

public function completeLevel(Level $level): self
{
    if (!$this->completedLevels->contains($level)) {
        $this->completedLevels->add($level);
                // Ajoute 1 karma point pour chaque niveau complété
         $this->addKarmaPoints(1); 

                // Recalculer les karma points (bien que ce soit redondant ici car on les calcule déjà dans `addKarmaPoints()`)
        $this->calculateKarmaPoints();  
    }
    return $this;
}

public function hasCompletedLevel(Level $level): bool
{
    return in_array($level, $this->completedLevels->toArray(), true);
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
            $rating->setUser($this);
        }
        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
        }
        return $this;
    }
////////////////////////////////////////////////////////////////////////////////////

    
    public function getGoogleId(): ?string
{
    return $this->googleId;
}

public function setGoogleId(?string $googleId): self
{
    $this->googleId = $googleId;
    return $this;
}

    public function getIsRestricted(): ?bool
    {
        return $this->isRestricted;
    }

    public function setIsRestricted(bool $isRestricted): self
    {
        $this->isRestricted = $isRestricted;

        return $this;
    }

    public function getOauthId(): ?string
    {
        return $this->oauthId;
    }

    public function setOauthId(?string $oauthId): self
    {
        $this->oauthId = $oauthId;
        return $this;
    }

    public function getOauthType(): ?string
    {
        return $this->oauthType;
    }

    public function setOauthType(?string $oauthType): self
    {
        $this->oauthType = $oauthType;
        return $this;
    }



    #[ORM\Column(length: 5, options: ["default" => "en"])] // Changed from nullable
    private string $locale = 'en';

    public function getPfp(): ?string
    {
        return $this->pfp;
    }

    public function setPfp(?string $pfp): self
    {
        $this->pfp = $pfp;

        return $this;
    }

 public function getBg(): ?string
    {
        return $this->bg;
    }
    
    public function setBg(?string $bg): self
    {
        $this->bg = $bg;
        return $this;
    }
    


    public function __construct()
    {
        $this->roles = [];
        $this->date_creation = new \DateTimeImmutable(); // Use DateTimeImmutable for consistency
        $this->is_verified = false;
        $this->posts = new ArrayCollection();
        $this->likedPosts = new ArrayCollection();
        $this->locale = 'en';
        $this->groups = new ArrayCollection();
        $this->googleId = null;
        $this->completedLevels = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear sensitive data if needed
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeImmutable $date_creation): static
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(?bool $is_verified): static
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->password_reset_token;
    }

    public function setPasswordResetToken(?string $password_reset_token): static
    {
        $this->password_reset_token = $password_reset_token;
        return $this;
    }

    public function getPasswordResetRequestedAt(): ?\DateTimeImmutable
    {
        return $this->password_reset_requested_at;
    }

    public function setPasswordResetRequestedAt(?\DateTimeImmutable $password_reset_requested_at): static
    {
        $this->password_reset_requested_at = $password_reset_requested_at;
        return $this;
    }

    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(?string $diplome): static
    {
        $this->diplome = $diplome;
        return $this;
    }

    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    public function setSpeciality(?string $speciality): static
    {
        $this->speciality = $speciality;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): self
    {
        $this->verificationToken = $verificationToken;
        return $this;
    }

     // Getter for posts
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }


     /**
     * @return Collection<int, Post>
     */
    public function getLikedPosts(): Collection
    {
        return $this->likedPosts;
    }

    public function addLikedPost(Post $post): self
    {
        if (!$this->likedPosts->contains($post)) {
            $this->likedPosts[] = $post;
            $post->addLikedByUser($this);
        }

        return $this;
    }

    public function removeLikedPost(Post $post): self
    {
        if ($this->likedPosts->removeElement($post)) {
            $post->removeLikedByUser($this);
        }

        return $this;
    }

    public function getLocale(): ?string
{
    return $this->locale;
}

public function setLocale(string $locale): self
{
    $locale = strtolower(substr($locale, 0, 2));
    $this->locale = in_array($locale, ['en', 'fr', 'es', 'de', 'it', 'pt']) ? $locale : 'en';
    return $this;
}


    /**
 * @ORM\ManyToMany(targetEntity=GroupStudent::class, mappedBy="members")
 * @var Collection<int, GroupStudent>
 */
#[ORM\ManyToMany(targetEntity: GroupStudent::class, mappedBy: 'members')]
private Collection $groups;
public function getGroups(): Collection
{
    return $this->groups;
}

public function addGroup(GroupStudent $group): self
{
    if (!$this->groups->contains($group)) {
        $this->groups[] = $group;
        $group->addMember($this);
    }

    return $this;
}

public function removeGroup(GroupStudent $group): self
{
    if ($this->groups->removeElement($group)) {
        $group->removeMember($this);
    }

    return $this;
}


}