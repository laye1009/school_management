<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\Classe;
use App\Entity\Matiere;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public UserPasswordHasherInterface $passwrdHasher;
    public function __construct(UserPasswordHasherInterface $hasher, ) {
        $this->passwrdHasher = $hasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();

        $matieresLibelles = ['anglais','mathematiques','francais','arabe','espagnol','histoire','geographie'];
        $matieres = [];
        foreach($matieresLibelles as $mat) {
            $matiere = new Matiere();
            $matiere->setLibelle($mat);
            $matiere->setFacultative($faker->boolean());
            $matieres[] = $matiere;
            $manager->persist($matiere);
            $manager->flush();
        }

        $classesLibelles = ['2LA','2LB','2SC','1LA','1LB','1SC','TLA','TLB','TSC'];
        $classes = [];
        foreach($classesLibelles as $cls) {
            $classe = new Classe();
            $classe->setLibelle($cls);
            for($i = 0; $i < 4; $i++) {
                $classe->addMatiere($faker->randomElement($matieres));
            }
            $classes[] = $classe;
            $manager->persist($classe);
            $manager->flush();
        }

        $students = [];
        for ($i = 0; $i < 5; $i++) {
            $student = new User();
            $password = $this->passwrdHasher->hashPassword($student, 'password');
            $student->setNom($faker->firstname());
            $student->setPrenom($faker->lastname());
            $student->setEmail($faker->email());
            $student->setPassword($password);
            $student->setRoles(['ROLE_STUDENT']);
            $student->setIdentifiant($faker->regexify('[A-Za-z0-9]{5}'));
            $student->setClasse($faker->randomElement($classes));
            $students[] = $student;
            $manager->persist($student);
            $manager->flush();
        }

        $noteTypes = ['c1', 'c2', 'final'];
        for($i = 0; $i < 5; $i++) {
            $note = new Note();
            $sdt = $faker->randomElement($students);
            $mat = $sdt->getClasse()->getMatieres();
            $note->setStudent($sdt);
            $note->setMatiere($faker->randomElement($mat));
            $note->setNote($faker->numberBetween(0,20));
            $note->setType($faker->randomElement($noteTypes));
            $note->setAppreciation($faker->paragraph());
            $manager->persist($note);
            $manager->flush();
        }




        
    }
}
