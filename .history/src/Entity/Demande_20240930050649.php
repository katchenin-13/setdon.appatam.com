<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\DBAL\Types\Types;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length:300 , type: Types::TEXT)]
    #[Group(["group1"])]
    #[Assert\NotBlank(message: 'Veuillez renseigner le motif ')]
    private ?string $motif = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Group(["group1"])]
    #[Assert\NotBlank(message: 'Veuillez renseigner la date de la rencontre')]
    #[Assert\Expression("this.getCreatedAt() <= this.getDaterencontre()", message: "la date de la rencontre doit être superirieure ou égale à la date de création")]

    private ?\DateTimeInterface $daterencontre = null;
    #[Group(["group1"])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    
    #[Assert\NotBlank(message: "Veuillez renseigner le lieu d'habitation")]
    private ?string $lieu_habitation = null;

    #[ORM\Column(length: 16)]
    #[Assert\NotBlank(message: 'Veuillez renseigner le numéro')]
    #[Assert\Length(
        min: 10,
        max: 16,
        minMessage: 'Le numéro de téléphone doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le numéro de téléphone ne doit pas faire plus de {{ limit }} caractères'
    )]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez selectionner des la communaute')]
    private ?Communaute $communaute = null;

    #[ORM\Column(nullable: true, type: Types::TEXT)]
    private ?string $justification = null;
    
    #[ORM\Column(length: 255)]
    private ?string $etat = 'demande_initie' ;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[Gedmo\Blameable(on: 'create')]
    private ?Utilisateur $utilisateur = null;



    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $UpdatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $CreatedAt = null;
 
    public function getDaterencontre(): ?\DateTimeInterface
    {
        return $this->daterencontre;
    }

    public function setDaterencontre(\DateTimeInterface $daterencontre): self
    {
        $this->daterencontre = $daterencontre;

        return $this;
    }



    public function getId(): ?int
    {
        return $this->id;
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


    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    
    public function getLieuHabitation(): ?string
    {
        return $this->lieu_habitation;
    }

    public function setLieuHabitation(string $lieu_habitation): self
    {
        $this->lieu_habitation = $lieu_habitation;

        return $this;
    }
    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getCommunaute(): ?Communaute
    {
        return $this->communaute;
    }

    public function setCommunaute(?Communaute $communaute): self
    {
        $this->communaute = $communaute;

        return $this;
    }
    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(string $justification): self
    {
        $this->justification = $justification;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

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

    public function __toString()
    {
        if (is_null($this->communaute)) {
            return 'NULL';
        }
        return $this->communaute;
    }

    public function getDate()
    {
        // Créer un objet DateTime à partir de la chaîne de date fournie
        $date = $this->daterencontre;
        $format = 'Y-m-d';

        // Formater la date selon le format spécifié
        $formattedDate = $date->format($format);

        return $formattedDate;
    }
}
