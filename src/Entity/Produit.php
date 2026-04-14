<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
    #[Assert\Length(max: 150)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 5000)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Categorie $categorie = null;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'produit')]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->ligneCommandes = new ArrayCollection();
    }

    #[Assert\Callback]
    public function validerPrixEtImage(ExecutionContextInterface $context): void
    {
        if (null === $this->prix || '' === trim((string) $this->prix)) {
            $context->buildViolation('Indiquez un prix.')
                ->atPath('prix')
                ->addViolation();
        } elseif (!is_numeric($this->prix)) {
            $context->buildViolation('Le prix doit être un nombre (ex. 99 ou 99.99).')
                ->atPath('prix')
                ->addViolation();
        } elseif ((float) $this->prix <= 0) {
            $context->buildViolation('Le prix doit être strictement supérieur à 0.')
                ->atPath('prix')
                ->addViolation();
        }

        if (null === $this->image || '' === trim($this->image)) {
            return;
        }
        $v = trim($this->image);
        if (str_starts_with($v, 'http://') || str_starts_with($v, 'https://')) {
            return;
        }
        if (str_contains($v, '..')) {
            $context->buildViolation('Chemin d’image invalide.')
                ->atPath('image')
                ->addViolation();

            return;
        }
        if (!preg_match('/\\.(jpe?g|png|gif|webp|svg)$/i', $v)) {
            $context->buildViolation('Extension attendue : jpg, png, gif, webp ou svg.')
                ->atPath('image')
                ->addViolation();

            return;
        }
        if (!preg_match('#^[a-zA-Z0-9_./-]+$#', $v)) {
            $context->buildViolation('Utilisez uniquement lettres, chiffres, tirets, points et slash (ex. images/mon-produit.jpg).')
                ->atPath('image')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPrixFloat(): float
    {
        return (float) $this->prix;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setProduit($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            if ($ligneCommande->getProduit() === $this) {
                $ligneCommande->setProduit(null);
            }
        }

        return $this;
    }
}
