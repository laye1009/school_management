<?php
namespace App\Service;

use App\Entity\Professor;
use App\Repository\ClasseRepository;

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
                //unset($oldClasses[$i]); //problème détecté grâce au tests-> tuto
            }

        }
        
        return $this->addNewClassesToOld($oldClasses, $newClasses, $professor, $classeRepo); // pas besoin de passer $oldClasses en param
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
                //$filteredOldClasses[] = $newClass;
            }
        }
        //return $this->updateProfessorClasses($filteredOldClasses, $professor);
        return $professor;

    }

    public function updateProfessorClasses(array $updatedClasses, Professor $professor): Professor {
        foreach($updatedClasses as $updatedClass) {
            $professor->removeClass($updatedClass);
            $professor->addClass($updatedClass);
        }
         return $professor;
    }
}