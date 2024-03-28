<?php

namespace App\Entity;

use App\Repository\FielpromesseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: FielpromesseRepository::class)]
class Fielpromesse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
   

    
    #[ORM\Column(length: 3, type: 'integer',nullable:true)]
    #[Assert\NotBlank(message: 'Veuillez renseigner la quantité')]
    #[Assert\PositiveOrZero(message: 'La quantité ne peut pas être négatif')]
    private ?int $qte = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nature = null;
   

    #[ORM\Column(length: 255)]
    private ?string $motif = null;

    
    #[ORM\Column(length: 3, type: 'integer')]
    #[Assert\NotBlank(message: 'Veuillez renseigner le montant')]
    #[Assert\PositiveOrZero(message: 'Le montant ne peut pas être négatif')]
    private ?float $montant = null;

    #[ORM\ManyToOne(inversedBy: 'fielpromesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Promesse $promesse = null;

    #[ORM\ManyToOne(inversedBy: 'fielpromesses')]
    #[Gedmo\Blameable(on: 'create')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $UpdatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $CreatedAt = null;
    #[ORM\Column(length: 255)]
    private ?string $etat = 'fielpromesse_initie';

    #[ORM\Column(length: 255)]
    private ?string $typepromesse = null;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getQte(): ?int
    {
        return $this->qte;
    }

    public function setQte(int $qte): self
    {
        $this->qte = $qte;

        return $this;
    }

    public function getNature(): ?string
    {
        return $this->nature;
    }

    public function setNature(string $nature): self
    {
        $this->nature = $nature;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getPromesse(): ?Promesse
    {
        return $this->promesse;
    }

    public function setPromesse(?Promesse $promesse): self
    {
        $this->promesse = $promesse;

        return $this;
    }


    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;
        return $this;
    }

    public function getTypepromesse(): ?string
    {
        return $this->typepromesse;
    }

    public function setTypepromesse(string $typepromesse): self
    {
        $this->typepromesse = $typepromesse;

        return $this;
    }

   
}
