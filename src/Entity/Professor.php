<?php

namespace App\Entity;

use App\Repository\ProfessorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessorRepository::class)]
class Professor extends User
{
    /**
     * @var Collection<int, Classe>
     */
    #[ORM\OneToMany(targetEntity: Classe::class, mappedBy: 'professor')]
    private Collection $classes;

    #[ORM\ManyToOne(inversedBy: 'professors')]
    private ?Matiere $matiereEnseigne = null;

    /**
     * @var Collection<int, Classe>
     */
    #[ORM\OneToMany(targetEntity: Classe::class, mappedBy: 'professor')]
    private Collection $classeEnseigne;

    public function __construct()
    {
        $this->classes = new ArrayCollection();
        $this->classeEnseigne = new ArrayCollection();
    }

    /**
     * @return array<int, Classe>
     */
    public function getClasses(): array
    {
        return $this->classes->toArray();
    }

    public function addClass(Classe $class): static
    {
        if (!$this->classes->contains($class)) {
            $this->classes->add($class);
            $class->setProfessor($this);
        }

        return $this;
    }

    public function removeClass(Classe $class): static
    {
        if ($this->classes->removeElement($class)) {
            // set the owning side to null (unless already changed)
            if ($class->getProfessor() === $this) {
                $class->setProfessor(null);
            }
        }

        return $this;
    }

    public function getMatiereEnseigne(): ?Matiere
    {
        return $this->matiereEnseigne;
    }

    public function setMatiereEnseigne(?Matiere $matiereEnseigne): static
    {
        $this->matiereEnseigne = $matiereEnseigne;

        return $this;
    }

    /**
     * @return Collection<int, Classe>
     */
    public function getClasseEnseigne(): Collection
    {
        return $this->classeEnseigne;
    }

    public function addClasseEnseigne(Classe $classeEnseigne): static
    {
        if (!$this->classeEnseigne->contains($classeEnseigne)) {
            $this->classeEnseigne->add($classeEnseigne);
            $classeEnseigne->setProfessor($this);
        }

        return $this;
    }

    public function removeClasseEnseigne(Classe $classeEnseigne): static
    {
        if ($this->classeEnseigne->removeElement($classeEnseigne)) {
            // set the owning side to null (unless already changed)
            if ($classeEnseigne->getProfessor() === $this) {
                $classeEnseigne->setProfessor(null);
            }
        }

        return $this;
    }
}
