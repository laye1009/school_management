<?php

namespace App\Tests\Unit;

use App\Entity\Professor;
use App\Repository\ClasseRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Classe;
use App\Service\ProfessorService;
use App\Repository\ClasseRepositoty;
class ProfessorServiceTest extends KernelTestCase{
    private $professorService;
    private $classeRepo;
    private $classe1;
    private $classe2;
    private $professor;

    protected function setUp():void {
        self::bootKernel();
        $this->professorService = (static::getContainer())->get(ProfessorService::class);
        $this->classeRepo = (static::getContainer())->get(ClasseRepository::class);
        $this->classe1 = (new Classe())
            ->setLibelle('T1B');
        $this->classe2 = (new Classe())
            ->setLibelle('T2B');

        $this->professor = (new Professor())
            ->addClass($this->classe1)
            ->addClass($this->classe2)
            ;
    }

    public function testFilterOldClassesWhenSuppression() {

        $oldClasses = [$this->classe1, $this->classe2];

        $newClasses = [3 => '1SC'];
        $this->assertNotContains('1SC', array_map(function($element) {
            return $element->getLibelle();
        }, $this->professor->getClasses()));
        $newProf = $this->professorService->filterOldClasses($oldClasses, $newClasses, $this->professor, $this->classeRepo);
        $finalClasses = $newProf->getClasses();
        $finalClassesLibelles =  array_map(function($element) {
            return $element->getLibelle();
        }, $finalClasses);
        $this->assertContains('1SC', $finalClassesLibelles);
        $this->assertNotContains('T1B', $finalClassesLibelles);
        $this->assertNotContains('T2B', $finalClassesLibelles);
        $this->assertCount(1, $finalClassesLibelles);
    }

    public function testFilterOldClassesWhenAdd() {

        $oldClasses = [$this->classe1, $this->classe2];

        $newClasses = ['T1B','T2B','1SC'];
        $this->assertNotContains('1SC', array_map(function($element) {
            return $element->getLibelle();
        }, $this->professor->getClasses()));
        $newProf = $this->professorService->filterOldClasses($oldClasses, $newClasses, $this->professor, $this->classeRepo);
        $finalClasses = $newProf->getClasses();
        $finalClassesLibelles =  array_map(function($element) {
            return $element->getLibelle();
        }, $finalClasses);
        $this->assertContains('1SC', $finalClassesLibelles);
        $this->assertContains('T1B', $finalClassesLibelles);
        $this->assertContains('T2B', $finalClassesLibelles);
        $this->assertCount(3, $finalClassesLibelles);
    }

    // Améliorer avec les dataprovider

}