<?php

namespace App\Tests\Unit;

use App\Entity\Matiere;
use App\Entity\Note;
use App\Service\StudentService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StudentServiceTest extends KernelTestCase {

    private $studentService;
    private $note1;
    private $note2;
    private $note3;

    protected function setUp(): void {
        self::bootKernel();
        $this->studentService = (static::getContainer())->get(StudentService::class);
        $matiere1 = (new Matiere())
                ->setLibelle("Mathematiques")
        ;
        $matiere2 = (new Matiere())
        ->setLibelle("Anglais")
        ;
        
        $this->note1 = (new Note())
                ->setNote(7)
                ->setType('c1')
                ->setMatiere($matiere1)
        ;
        $this->note2 = (new Note())
            ->setNote(10)
            ->setType('c2')
            ->setMatiere($matiere2)
        ;
        $this->note3 = (new Note())
            ->setNote(12)
            ->setType('final')
            ->setMatiere($matiere1)
        ;

        
    }

    public function testFilterMatiere() {
        $matLibelle = "Mathematiques";
        $filtered = $this->studentService->filterMatieres($matLibelle, [$this->note1, $this->note2, $this->note3]);
        $this->assertCount(2, $filtered);
        return $filtered;
    }

    /**
     * @depends testFilterMatiere
     */
    public function testGetMoyenneControles($notes) {
        $moyenne = $this->studentService->getMoyenneControles($notes);
        $this->assertEquals(3.5, $moyenne);
        return $moyenne;
    }


    public function testGetFinalNote() {
        $noteFinal = $this->studentService->getFinalNote([$this->note1, $this->note2, $this->note3]);
        $this->assertEquals(12, $noteFinal);
        return $noteFinal;
    }

    /**
     * @depends testGetMoyenneControles
     * @depends testGetFinalNote
     */
    public function testGetMoyenneFinal(float $moyenneControle, float $noteFinal) {
        $this->assertEquals(7.75, ($moyenneControle+$noteFinal)/2);
    }
}