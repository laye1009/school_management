<?php


namespace App\Controller;

use App\Entity\Note;
use App\Entity\Student;
use App\Repository\NoteRepository;
use App\Repository\ClasseRepository;
use App\Repository\MatiereRepository;
use App\Repository\StudentRepository;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController {
    #[Route('/', name:'home_page')]
    public function homePage()  {
        return $this->render('home.html.twig');
    }

    #[Route('user/ui', name:'redirect_to_user_ui')]
    public function redirectToUserUi(NoteRepository $noteRepo, ClasseRepository $classeRepo){
        if (in_array('ROLE_STUDENT', $this->getUser()->getRoles())) {
            /**@var Student $user */
            $user = $this->getUser();
            $classe = $classeRepo->fincdOneBy(['student' => $user->getId()]);
            $matieres = $classe->getMatieres();
            $notes = $noteRepo->findBy(['student' => $user->getId()]);
            return $this->render('uis/student.html.twig', ['matieres' => $matieres, 'notes' => $notes]);
        } elseif (in_array('ROLE_PROF', $this->getUser()->getRoles())) {
            /**@var Professor $user */
            $user = $this->getUser();
            $classes = $user->getClasses();
            //dump($classes);
            return $this->render('uis/professorUi.html.twig', ['classes' => $classes, 'prof' => $user]);
        }
    }

    /*#[Route('/matiere/list', name: "selected_classe_matiere")] // matiere d'une nouvelle note est celle du prof
    public function getSelectedClasseMatieres(Request $request, ClasseRepository $classeRepo) {
        $selectedClasseId = $request->request->get('classeId');
        $classe = $classeRepo->findOneBy(['id' => $selectedClasseId]);
        $matieres = $classe->getMatieres();
        $table = [];
        foreach($matieres as $matiere) {
            $line = [];
            $line[] = $matiere->getLibelle();
            $table[] = $line;
        }
        return new JsonResponse(['data' => $table]);
    }*/

    #[Route('/professor/marks/management', name:'professor_marks_management')]
    public function getClassStudentList(Request $request, StudentRepository $studentRepo, 
        NoteRepository $noteRepo, MatiereRepository $matiereRepo
    )  {
        $classe = $request->request->get('classe');
        /**@var Professor $professor */
        $professor = $this->getUser();
        $students = $studentRepo->findBy(['classe' => $classe]);
        $matiere = $matiereRepo->findOneBy(['libelle' => $professor->getMatiereEnseigne()->getLibelle()]);
        
        $table = [];
        foreach($students as $student) {
            $note = $noteRepo->findOneBy(['matiere' => $matiere->getId(),"student" => $student->getId()]);
            $appreciation = $note->getAppreciation();
            $line = [];
            $line[] = $student->getNom();
            $line[] = $student->getPrenom();
            $line[] = $note->getNote();
            $line['matiere'] = $professor->getMatiereEnseigne()->getId();
            $line['studentId'] = $student->getId();
            $line['appreciation'] = strlen($appreciation) > 25 ? substr($appreciation, 0, 20) : $appreciation;
            $line['nomPrenom'] = $student->getNom()." ".$student->getPrenom();
            $table[] = $line;
        }
        return new JsonResponse(['data' => $table]);
    }
    #[Route('/edit/note', name:'edit_note')]
    public function confirmNoteEdition(Request $request, ObjectManager $manager, MatiereRepository $matRepo, StudentRepository $studentRepo) {
        $studentId = $request->request->get('studentId');
        $newNote = $request->request->get('newNote');
        $matiereId = $request->request->get('matiere');
        $controle = $request->request->get('controle');
        $appreciation = $request->request->get('appreciation');
        $student = $studentRepo->findOneBy(['id' => $studentId]);

        $matiere = $matRepo->findOneBy(['id' => $matiereId]);

        $matiereId = $matiere->getId();
        
        
        //$note = $noteRepo->findOneBy(['matiere' => $matiere->getId(), 'student' => $studentId]);
        $notes = $student->getNotes();
        $matiereNotes = array_filter($notes, function ($note) use($matiereId, $controle){
            return $note->getMatiere()->getId() == $matiereId && $note->getType() == $controle;
        });
        if (count($matiereNotes) > 0) {
            $note = reset($matiereNotes);
        } else {
            $note = new Note();
            $note->setMatiere($matiere);
            $note->setStudent($student);
            $note->setType($controle);
        }
        $note->setNote($newNote);
        $note->setAppreciation($appreciation);
        try {
            $manager->persist($note); 
            $manager->flush();
        } catch (Exception $e) {
            throw new Exception('Certains champs doivent être renseignés');
        }

        return new JsonResponse(['data' => 'La note est modifiée']);
    }

    #[Route('/student/notes/list', name:'student_notes_list')]
    public function lsiteNoteStudent(Request $request, StudentRepository $studentRepo, NoteRepository $noteRepo) {
        $studentId = $request->request->get('student');
        $matiereId = $request->request->get('matiere');
        $student = $studentRepo->findOneBy(['id' => $studentId]);
        //$studentNotes = $noteRepo->getNotesForMatiere($matiereId, $studentId);
        $notes = $student->getNotes();
        $matiereNotes = array_filter($notes, function ($note) use($matiereId){
            return $note->getMatiere()->getId() == $matiereId;
        });
        $table = [];
        foreach($matiereNotes as $note) {
            $appreciation = $note->getAppreciation();
            $line = [];
            $line[] = $note->getMatiere()->getLibelle();
            $line[] = $note->getType();
            $line[] = $note->getNote();
            $line[] = strlen($appreciation)> 20 ? substr($appreciation, 0, 20).'...' : $appreciation;
            $line['studentId'] = $note->getStudent()->getId();
            $line['matiere'] = $note->getMatiere()->getId();
            $line['note'] = $note->getNote();
            
            //$line['appreciation'] = $note->getNote();
            $table[] = $line;

        }
 
        return new JsonResponse(['data' => $table]);
    }

    //#[Route('/professor/edit/student/{studentId}', name:'professor_edit_student')]
    /*public function editStudentByProfessor(int $studentId, StudentRepository $studentRepo, ClasseRepository $classeRepo, NoteRepository $noteRepo) {
        /**@var Professor $professor 
        $professor = $this->getUser();
        $student = $studentRepo->findOneBy(["id" => $studentId]);
        //$matieres = $student->getClasse()->getMatieres();
        $matiere = $classeRepo->findOneBy(['id' => $professor->getMatiereEnseigne()]);
        $note = $noteRepo->findBy(['matiere' => $matiere->getId()]);
        return $this->render('uis/studentEdit.html.twig', ['notes' => $note]);
    }*/


}



