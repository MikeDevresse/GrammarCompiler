<?php


class UnknownRuleException extends Exception
{
    public function __construct($stack, $input)
    {
        parent::__construct("Règle inconnue pour la pile \"$stack\" et l'entrée \"$input\".");
    }
}
