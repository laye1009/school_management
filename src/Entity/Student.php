<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\StudentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends User
{
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'student')]
    private Collection $notes;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?Classe $classe = null;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

        /**
     * @return array<int, Note>
     */
    public function getNotes(): array
    {
        return $this->notes->toArray();
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setStudent($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getMatiere() === $this) {
                $note->setMatiere(null);
            }
        }

        return $this;
    }

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): static
    {
        $this->classe = $classe;

        return $this;
    }
}
