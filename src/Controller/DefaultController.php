<?php


namespace App\Controller;

use Exception;
use App\Entity\Note;
use App\Entity\Student;
use App\Entity\Professor;
use App\Form\StudentType;
use App\Form\ProfessorType;
use App\Service\StudentService;
use App\Service\ProfessorService;
use App\Repository\NoteRepository;
use App\Repository\ClasseRepository;
use App\Repository\MatiereRepository;
use App\Repository\StudentRepository;
use App\Repository\ProfessorRepository;
use Doctrine\Persistence\ObjectManager;
use App\Service\UserIdentifierGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ProfessorClasseUpdateException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
class DefaultController extends AbstractController {

    public function __construct(public ProfessorService $profService, 
        public UserIdentifierGenerator $userService,
        public StudentService $studentService
    ) {
        $this->profService = $profService;

    }
    #[Route('/', name:'home_page')]
    public function homePage()  {
        return $this->render('home.html.twig');
    }

    #[Route('user/ui', name:'redirect_to_user_ui')]
    public function redirectToUserUi(){
        if(!$this->getUser()) {
            return $this->redirectToRoute("app_login");
        }
        if (in_array('ROLE_STUDENT', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('student_ui');

        } elseif (in_array('ROLE_PROF', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('professor_ui');
        } else {
            return $this->redirectToRoute('admin_ui');
        }
    }

    #[Route('/professor/ui', name: "professor_ui")]
    #[IsGranted('ROLE_PROF')]
    public function profUi() {
        /**
         * @var Professor $user 
         */
        $user = $this->getUser();
        $classes = $user->getClasses();
        //dump($classes);
        return $this->render('uis/professorUi.html.twig', ['classes' => $classes, 'prof' => $user]);
    }
    
    #[Route('/student/ui', name: "student_ui")]
    #[IsGranted('ROLE_STUDENT')]
    public function studentUi(NoteRepository $noteRepo) {
        /**
         *@var Student $user 
        */
        $user = $this->getUser();
        $classe = $user->getClasse();
        $matieres = $classe->getMatieres();
        $notes = $noteRepo->findBy(['student' => $user->getId()]);
        return $this->render('uis/student.html.twig', ['matieres' => $matieres, 'notes' => $notes]);
    }

    #[Route('/admin/ui', name: "admin_ui")]
    #[IsGranted('ROLE_ADMIN')]
    public function adminUi(StudentRepository $studentRepo, ProfessorRepository $profRepo, ClasseRepository $classeRepo) {
        $user = $this->getUser();
        //dump($classes);
        $eleves = $studentRepo->findAll();
        $professors = $profRepo->findAll();
        $classes = $classeRepo->findAll();
        return $this->render('uis/adminUi.html.twig', [
            'admin' => $user,
            'students' => $eleves,
            'profs' => $professors,
            'classes' => $classes
        ]);
    }

    #[Route('/professor/marks/management', name:'professor_marks_management')]
    #[IsGranted('ROLE_PROF')]
    public function getClassStudentList(Request $request, StudentRepository $studentRepo, 
        NoteRepository $noteRepo, MatiereRepository $matiereRepo
    )  {
        $classe = $request->request->get('classe');
        /**
         * @var Professor $professor 
         */
        $professor = $this->getUser();
        $students = $studentRepo->findBy(['classe' => $classe]);
        $matiere = $matiereRepo->findOneBy(['libelle' => $professor->getMatiereEnseigne()->getLibelle()]);
        
        $table = [];
        foreach($students as $student) {
            $note = $noteRepo->findOneBy(['matiere' => $matiere->getId(),"student" => $student->getId()]);
            $appreciation = $note?->getAppreciation();
            $line = [];
            $line[] = $student?->getNom();
            $line[] = $student->getPrenom();
            $line[] = $note?->getNote();
            $line['matiere'] = $professor->getMatiereEnseigne()->getId();
            $line['studentId'] = $student->getId();
            $line['appreciation'] = strlen($appreciation) > 25 ? substr($appreciation, 0, 20) : $appreciation;
            $line['nomPrenom'] = $student->getNom()." ".$student->getPrenom();
            $table[] = $line;
        }
        return new JsonResponse(['data' => $table]);
    }
    #[Route('/edit/note', name:'edit_note')]
    #[IsGranted('ROLE_PROF')]
    public function confirmNoteEdition(Request $request, ObjectManager $manager, MatiereRepository $matRepo, StudentRepository $studentRepo) {
        $studentId = $request->request->get('studentId');
        $newNote = $request->request->get('newNote');
        $matiereId = $request->get('matiere');
        $controle = $request->request->get('controle');
        $appreciation = $request->request->get('appreciation');
        $student = $studentRepo->findOneBy(['id' => $studentId]);
        
        $matiere = $matRepo->findOneBy(['id' => $matiereId]);
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
        
        $note->setNote((int)$newNote);
        $note->setAppreciation($appreciation);
        try {
            $manager->persist($note); 
            $manager->flush();
        } catch (Exception $e) {
            throw new Exception('Certains champs doivent être renseignés');
        }

        return new JsonResponse(['data' => 'La note est modifiée']);
    }

    #[Route('/controle/a_ajouter', name:'controle_a_ajouter')]
    #[IsGranted('ROLE_PROF')]
    public function ajouterNote(Request $request, StudentRepository $studentRepo) {
        $studentId = $request->get('studentId');

        $student = $studentRepo->findOneBy(['id' => $studentId]);
        $notes = $student->getNotes();
        $controles = ['c1', 'c2','final'];
        $controlesToAdd = [];

        $notesTypes = $this->studentService->getContoleTypeFromNotes($notes);
        foreach($controles as $controle) {
            if (!in_array($controle, $notesTypes)) {
                $controlesToAdd[] = $controle;
            } 
        }
        return new JsonResponse($controlesToAdd);

    }

    #[Route('/student/notes/list', name:'student_notes_list')]
    #[IsGranted('ROLE_PROF')]
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

    #[Route('/admin/edit/student', name:'admin_edit_student', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEditStudent(ObjectManager $manager, Request $request, StudentRepository $studentRepo, ClasseRepository $classeRepo) {
        $submittedToken = $request->request->get('_csrf_token');
        if ($submittedToken == 'admin_editing_student') {
            $student = $studentRepo->findOneBy([
                'nom'=>$request->request->get('nom'), 'prenom' =>$request->request->get('prenom')
            ]);
            $student->setNom($request->request->get('nom'));
            $student->setPrenom($request->request->get('prenom'));
            if (!is_null($request->get('classe'))) {
                $eClasse = $classeRepo->findOneBy(['libelle' => $request->get('classe')]);
                $student->setClasse($eClasse);
            }
            $manager->persist($student);
            $manager->flush();
        }
        return $this->redirectToRoute('admin_ui');
    }
    #[Route('/admin/edit/professor', name:'admin_edit_professor', methods:['POST'])]
    public function adminEditProfessor(
            ObjectManager $manager, 
            Request $request, 
            ProfessorRepository $profRepo, ClasseRepository $classeRepo
        ) {
        $submittedToken = $request->request->get('_csrf_token');
        if ($submittedToken == 'admin_editing_professor') {
            $prof = $profRepo->findOneBy([
                'nom'=>$request->request->get('profNom'), 'prenom' =>$request->request->get('profPrenom')
            ]);
            if (is_null($prof)) {
                $prof = new Professor();
            }
            $prof->setNom($request->request->get('profNom'));
            $prof->setPrenom($request->request->get('profPrenom'));
            $oldClasses = $prof->getClasses();
            $newClasses = $request->request->all('profClasses');

            $prof = $this->profService->filterOldClasses($oldClasses, $newClasses, $prof, $classeRepo);
            $manager->persist($prof);
            $manager->flush();
            //$this->addFlash('teal', 'le professeur a été bien à jour');
            return $this->redirectToRoute('admin_ui');
        }
    }
    #[Route('/admin/delete/prof', name:'admin_delete_prof')]
    //#[Security("request.isXmlHttpRequest()", message:"Accès interdit")]
    public function deleteProf(Request $request, ProfessorRepository $profRepo, ObjectManager $manager) {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException('Accès impossible');
        }
        $profId = $request->get('profId');
        $professor = $profRepo->findOneBy(['id' => $profId]);

        //$manager->remove($professor);
        //$manager->flush();
        return new JsonResponse(['data' => 'Suppression réussie!']);
    }
    #[Route('new/professor', name:'new_professor')]
    public function newProfessor(Request $request, ObjectManager $manager) {
        $form = $this->createForm(ProfessorType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $professor = $form->getData();

            $professor->setIdentifiant('PRF_'.$this->userService->generate());
            $professor->setRoles(['ROLE_PROF']);
            $professor->addClasseEnseigne($form->get('classeEnseigne')->getData());

            $manager->persist($professor);
            $manager->flush();
            $this->addFlash('teal', "Le professeur ".$professor->getNom()." ".$professor->getPrenom()." a été bien ajouté!");
            return $this->redirectToRoute('new_professor');
        }
        return $this->render('uis/newProfessor.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('new/student', name:'new_student')]
    public function newStudent(Request $request, ObjectManager $manager) {
        $form = $this->createForm(StudentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $student = $form->getData();
            $student->setIdentifiant('STD_'.$this->userService->generate());
            $student->setRoles(['ROLE_STUDENT']);
            $this->addFlash('teal', "L'élève ".$student->getNom()." ".$student->getPrenom()." a été bien ajouté!");
            $manager->persist($student);
            $manager->flush();
            return $this->redirectToRoute('new_student');
        }
        return $this->render('uis/newStudent.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/display/report', name:'display_reports')]
    public function displayReport(Request $request, StudentRepository $studentRepo) {
        $studentId = $request->get('studentId');
        $student = $studentRepo->findOneBy(['id' => $studentId]);
        $matieres = $student->getClasse()->getMatieres()->toArray();
        $notes = $student->getNotes();
        $table = [];
        foreach($matieres as $matiere) {
            $line = [];
            $line[] = $matiere->getLibelle();
            $matiereNotes = $this->studentService->filterMatieres($matiere->getLibelle(), $notes);
            $moyenneControle = $this->studentService->getMoyenneControles($matiereNotes);
            $line[] = $moyenneControle;
            $noteFinal = $this->studentService->getFinalNote($notes);
            
            $line[] = $noteFinal;
            $line[] = $this->studentService->getMoyenneFinal($moyenneControle, $noteFinal);
            $line[] = "Appréciation";
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



