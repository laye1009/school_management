<?php
namespace App\Service;

class UserIdentifierGenerator
{
    public function generate(): string
    {
        return str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}