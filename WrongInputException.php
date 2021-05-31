<?php


class WrongInputException extends Exception
{
    public function __construct($input,$expected,$pos)
    {
        parent::__construct("L'input à la position $pos est incorrect, reçu \"$input\" devrait être \"$expected\".");
    }
}
