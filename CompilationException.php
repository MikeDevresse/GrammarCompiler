<?php


class CompilationException extends Exception
{
    public function __construct()
    {
        parent::__construct('Une erreur est apparu lors de la compilation');
    }
}
