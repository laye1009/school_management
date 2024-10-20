<?php

namespace App\Entity;

use App\Repository\PrincipalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrincipalRepository::class)]
class Principal extends User
{
    #[ORM\Column(length: 255)]
    private ?string $school = null;

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(string $school): static
    {
        $this->school = $school;

        return $this;
    }
}
