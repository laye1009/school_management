<?php
namespace App\Service;


class StudentService {

    public function filterMatieres(string $matiere, array $notes) {
        $filteredNotes = array_filter($notes, function($note) use ($matiere) {
            return $note->getMatiere()->getLibelle() == $matiere;
        });
        return $filteredNotes;
    }

    public function getMoyenneControles(array $notes) {
        $somme = 0;
        foreach($notes as $note) {
            if (in_array($note->getType(), ['c1, c2'])) {
                $somme += $note->getNote();
            }
        }
        return $somme / 2;
    }

    public function getFinalNote(array $notes) {
        $finalNote = array_filter($notes, function($note) use ($notes) {
            return $note->getType() == 'final';
        });

        return count($finalNote) > 0 ? $finalNote[0]->getNote() > 0 : null;
    }

    public function getMoyenneFinal(?int $moyenneControle, ?int $noteFinal) {
        if(!is_null($moyenneControle) && !is_null($noteFinal)) {
            return ($moyenneControle + $noteFinal) / 2;
        }
        return "";
    }

    public function getContoleTypeFromNotes($notes) {
        return array_map(function($note) {
            return $note->getType();
        }, $notes);
    }

    public function hasControleMark(string $controle, array $notes) {
        foreach($notes as $note) {
            $controlesToAdd[] = $note->getType();
            if (!in_array($controle, $note->getType())) {
                return True;
            } 
        }
        return false;
    }
}