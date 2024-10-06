<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\NoteRepository;

class DefaultController extends AbstractController {
    #[Route('/', name:'home_page')]
    public function homePage()  {
        return $this->render('home.html.twig');
    }

    #[Route('user/ui', name:'redirect_to_user_ui')]
    public function redirectToUserUi(NoteRepository $noteRepo){
        /**@var User $user */
        $user = $this->getUser();
        if (in_array('ROLE_STUDENT', $user->getRoles())) {
            $matieres = $user->getClasse()->getMatieres();
            $notes = $noteRepo->findBy(['student' => $user->getId()]);
            return $this->render('uis/student.html.twig', ['matieres' => $matieres, 'notes' => $notes]);
        } else {
            return $this->render('uis/commonUserUi.html.twig');
        }
        
    }


}



