<?php

namespace App\Entity;

use App\Repository\MissionrapportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MissionrapportRepository::class)]
class Missionrapport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un titre de mission')]
    private ?string $titre_mission = null;

    
    #[ORM\Column(length: 3, type: 'integer')]
    #[Assert\NotBlank(message: 'Veuillez renseigner le nombre de pqrticipants')]
    #[Assert\PositiveOrZero(message: 'Le nombre de pqrticipants ne peut pas être négatif')]
    private ?int $nombrepersonne = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Veuillez renseigner les Objectif (s) de la mission')]
    private ?string $objectifs = null;

    #[ORM\Column(type: Types::TEXT,nullable:true)]
    #[Assert\NotBlank(message: 'Veuillez renseigner les Action (s) réalisée (s')]
    private ?string $action = null;

    #[ORM\Column(type: Types::TEXT,nullable:true)]
    private ?string $opportunite = null;


    #[ORM\Column(length: 255,nullable:true)]
    private ?string $prochaineetat = null;

    #[ORM\ManyToOne(inversedBy: 'missionrapports')]
    private ?Communaute $communaute = null;

   

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $UpdatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $CreatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'missionrapports')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Blameable(on: 'create')]
    private ?utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::TEXT,nullable:true)]
    private ?string $difficulte = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = 'missionrapport_initie';

    #[ORM\ManyToOne(inversedBy: 'no')]
    private ?employe $employe = null;

    
    #[ORM\Column(nullable: true,type: Types::TEXT)]
    private ?string $justification = null;

  
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreMission(): ?string
    {
        return $this->titre_mission;
    }

    public function setTitreMission(string $titre_mission): self
    {
        $this->titre_mission = $titre_mission;

        return $this;
    }

    public function getNombrepersonne(): ?int
    {
        return $this->nombrepersonne;
    }

    public function setNombrepersonne(int $nombrepersonne): self
    {
        $this->nombrepersonne = $nombrepersonne;

        return $this;
    }

    public function getObjectifs(): ?string
    {
        return $this->objectifs;
    }

    public function setObjectifs(string $objectifs): self
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getOpportunite(): ?string
    {
        return $this->opportunite;
    }

    public function setOpportunite(string $opportunite): self
    {
        $this->opportunite = $opportunite;

        return $this;
    }

    public function getProchaineetat(): ?string
    {
        return $this->prochaineetat;
    }

    public function setProchaineetat(string $prochaineetat): self
    {
        $this->prochaineetat = $prochaineetat;

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

 
    public function getCreatedAd(): ?\DateTimeInterface
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

    public function getUtilisateur(): ?utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): self
    {
        $this->difficulte = $difficulte;

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

    public function getEmploye(): ?employe
    {
        return $this->employe;
    }

    public function setEmploye(?employe $employe): self
    {
        $this->employe = $employe;

        return $this;
    }
   
public function __toString()
{
    return (string) $this->getEmploye();
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
   

}
