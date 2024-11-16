<?php
namespace App\Exception;

use Exception;

class ProfessorClasseUpdateException extends Exception{
    protected $message = "La mise à jour des classes du prof n'a pas abouti";
}