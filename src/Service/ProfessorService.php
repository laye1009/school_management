<?php
namespace App\Service;

use App\Entity\Classe;
use App\Entity\Professor;
use App\Repository\ClasseRepository;
use App\Exception\ProfessorClasseUpdateException;

class ProfessorService {
    public function filterOldClasses(array $oldClasses, array $newClasses, 
            Professor $professor,  ClasseRepository $classeRepo
        ): Professor {
        /*foreach($oldClasses as $oldClass) {
            if (!in_array($oldClass->getLibelle(), array_values($newClasses))) {
                unset($oldClass);
            }
        }*/
        for ($i=0; $i < count($oldClasses); $i++) {
            if (!in_array($oldClasses[$i]->getLibelle(), array_values($newClasses))) {
                $professor->removeClass($oldClasses[$i]);
                unset($oldClasses[$i]);
                
            }

        }

        return $this->addNewClassesToOld($oldClasses, $newClasses, $professor, $classeRepo);
    }

    public function addNewClassesToOld(
            array $filteredOldClasses, array $newClasses, 
            Professor $professor, ClasseRepository $classeRepo
        ): Professor {
        $oldClassesLibelles = [];
        foreach($filteredOldClasses as $oldClass) {
            $oldClassesLibelles[] = $oldClass->getLibelle();
        }
        foreach($newClasses as $newClassLibelle) {
            if (!in_array($newClassLibelle, $oldClassesLibelles)) {
                $newClass = $classeRepo->findOneBy(['libelle' => $newClassLibelle]);
                $professor->addClass($newClass);
                $filteredOldClasses[] = $newClass;
            }
        }
        return $this->updateProfessorClasses($filteredOldClasses, $professor);

    }

    public function updateProfessorClasses(array $updatedClasses, Professor $professor): Professor {
        foreach($updatedClasses as $updatedClass) {
            $professor->removeClass($updatedClass);
            $professor->addClass($updatedClass);
        }
         return $professor;
    }
}