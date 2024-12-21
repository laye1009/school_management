<?php
namespace App\Service;


class StudentService {

    public function filterMatieres(string $matiere, array $notes): array {
        $filteredNotes = array_filter($notes, function($note) use ($matiere) {
            return $note->getMatiere()->getLibelle() == $matiere;
        });
        return $filteredNotes;
    }

    public function getMoyenneControles(array $notes): ?float {
        $somme = 0;
        foreach($notes as $note) {
            if (in_array($note->getType(), ['c1', 'c2'])) { // corrigé grâce au test erreur ['c1,c2']
                $somme += $note->getNote();
            }
        }
        return $somme / 2;
    }

    public function getFinalNote(array $notes) {
        /*$finalNote = array_filter($notes, function($note) use ($notes) {
            return $note->getType() == 'final';
        }); 
        return count($finalNote) > 0 ? $finalNote[0]->getNote() > 0 : null;*/ // nonesence qui génère une erreur du test testGetFianalNote
        // ajouter une méthode pour tester qu'il y a qu'une seule note de type final
        foreach($notes as $note) {
            if ($note->getType() === 'final') {
                return $note->getNote();
            }
        }
        return null;
    }

    public function getMoyenneFinal(?int $moyenneControle, ?int $noteFinal): ?float {
        if(!is_null($moyenneControle) && !is_null($noteFinal)) {
            return ($moyenneControle + $noteFinal) / 2;
        }
        return "";
    }

    public function getContoleTypeFromNotes($notes): array { // not used
        return array_map(function($note) {
            return $note->getType();
        }, $notes);
    }

    public function hasControleMark(string $controle, array $notes) { // not used
        foreach($notes as $note) {
            $controlesToAdd[] = $note->getType();
            if (!in_array($controle, $note->getType())) {
                return True;
            } 
        }
        return false;
    }
}