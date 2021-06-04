<?php
require 'CompilationException.php';
require 'UnknownRuleException.php';
require 'WrongInputException.php';

class Compiler
{
    /**
     * @var array Grammaire renseignée par l'utilisateur
     */
    private array $grammar;

    /**
     * @var array|mixed Liste des règles
     */
    private array $rules;

    /**
     * @var array|mixed Liste des tokens, un token est un élément dont on connait la signification. On les stocks afin
     * de ne pas les confondre avec des variables
     */
    private array $tokens;

    /**
     * @var array Tableau contenant la liste des entrées saisies
     */
    private array $input;

    /**
     * @var array Liste des stack à chaque étape, on stock une liste de stack afin de pouvoir avancer/reculer comme on veut
     */
    private array $stack;

    /**
     * @var array Liste des sorties, chaque index de ce tableau correspond à une étape
     */
    private array $output;

    /**
     * @var array Liste de l'entrée courante à l'index de l'étape choisis
     */
    private array $inputIndexes;

    /**
     * Compiler constructor.
     * @param string $grammar Chaine de caractère contenant notre grammaire
     * @param string $input Chaine de caractère qui doit être interprété
     * @param string $dictionary Chaine de caractère contenant notre dictionnaire
     * @throws WrongInputException Exception levé si l'entrée n'est pas correcte (mauvaise entrée à un mauvais index ex:
     * l'index 0 doit toujours être début)
     */
    public function __construct(string $grammar, string $input, string $dictionary)
    {
        /* on initialise notre grammaire, nos règles et nos tokens */
        $this->grammar = self::parseGrammar($grammar);
        $dictionaryArray = self::parseDictionary($dictionary);
        $this->tokens = $dictionaryArray['tokens'];
        $this->rules = $dictionaryArray['rules'];

        /*
         * On initialise notre stack avec '$' et 'P' en premier état pour signifié que c'est le début et pour avoir
         * quelque chose à donnée pour la fin
         */
        $this->stack = [];
        $this->stack[] = ['$','P'];

        $this->output = [];
        $this->input = self::parseInput($input, $this->tokens);
    }

    /**
     * @return array Tableau contenant les entrées dans l'ordre
     */
    public function getInput(): array
    {
        return $this->input;
    }

    /**
     * @return array Tableau contenant l'index de l'entrée à l'index de chaque étape
     */
    public function getInputIndexes(): array
    {
        return $this->inputIndexes;
    }

    /**
     * @return array Tableau contenant un tableau représentant notre pile à l'index de chaque étape
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    /**
     * @return array Tableau contenant la liste des sorties du programme compilé
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * Méthode permettant de "compiler" notre entrée pour en générer la sortie et sauvegarde l'état à chaque étape du programme
     * @throws UnknownRuleException
     */
    public function compile() {
        /* On récupère le stack initiale */
        $currentStack = $this->stack[0];
        /* on boucle pour chaque entrée */
        foreach($this->input as $k => $in) {
            /* Tant que notre dernière sortie n'est pas pop ou acc on reste sur la même input */
            do {
                /* on récupère l'élément en haut de notre stack */
                $popped = array_pop($currentStack);
                /*
                 * Si on à une variable ou un nombre, alors on écrit simple id ou nb afin de correspondre à notre
                 * grammaire et à nos règles
                 */
                if(substr($in,0,3) == 'id(' || substr($in,0,3) == 'nb(') $in = substr($in,0,2);
                /* Si on ne trouve pas la règle alors on lève une exception: une erreur de compilation */
                if(!isset($this->rules[$popped][$in])) {
                    throw new UnknownRuleException($popped,$in);
                }
                /* on sauvegarde la règle */
                $this->output[] = $this->rules[$popped][$in];
                /*
                 * Si notre grammaire dit quelque chose concernant notre sortie et notre stack dépilé alors on ajoute
                 * à notre stack tous les éléments qui ne sont pas l'élément vide: epsilon
                 */
                if(isset($this->grammar[end($this->output)][$popped])) {
                    foreach(array_reverse($this->grammar[end($this->output)][$popped]) as $item) {
                        if($item != 'epsilon') $currentStack[] = $item;
                    }
                }
                /* On sauvegarde les état afin de pouvoir les afficher et faire du suivant/précédant */
                $this->stack[] = $currentStack;
                $this->inputIndexes[] = $k;
            } while(end($this->output) != 'pop' and end($this->output) != 'acc');
        }
    }

    /**
     * Méthode permettant grâce au tokens de transformer une entrée d'une chaîne de caractères à un tableau et de transformer
     * les variables en id(var) et les nombres en nb(var)
     * @param string $input Entrée du programme
     * @param array $tokens Liste des tokens
     * @return array Tableau contenant notre liste d'entrée
     * @throws WrongInputException
     */
    public static function parseInput(string $input, array $tokens): array {
        $inputArray = [];
        /* On enlève les sauts de lignes et les espaces car ils ne servent pas dans notre langage */
        $input = str_replace(' ', '', str_replace("\n", '', $input));
        /* tant que notre entrée n'est pas vide */
        do {
            /*
             * On cherche si ce qui est à la position i fait partit de nos tokens, il faut faire une boucle pour vérifier
             * jusqu'à ce qu'on trouve un token ou on arrive au bout car on ne peut pas détecter si la suite de l'entrée
             * est une variable un nombre ou un élément connue autrement
             */
            for($i = 0 ; $i < strlen($input) ; $i++) {
                /* on cherche parmi nos tokens si on trouve quelque chose qui correspond à l'index i */
                $currentToken = null;
                foreach ($tokens as $token) {
                    if(substr($input, $i, strlen($token)) == $token) {
                        $currentToken = $token;
                        break;
                    }
                }

                /* Si on a trouver un token alors on l'ajoute à notre liste d'entrée */
                if($currentToken) {
                    /*
                     * Mais si avant on n'est pas à l'index 0, cela veut dire qu'on à une variable ou un nombre avant
                     * alors on l'ajoute à notre liste d'entrées
                     */
                    if($i != 0) {
                        $val = substr($input, 0, $i);
                        /* on vérifie que notre valeur n'est pas vide */
                        if(trim($val) != '') { $inputArray[] = (is_numeric($val)?'nb':'id')."($val)"; }
                    }
                    $inputArray[] = $currentToken;
                    /* On oublie pas de retirer ce qu'on à analyser de l'entrée afin de ne pas dupliqué nos entrées */
                    $input = substr($input, $i + strlen($currentToken));
                    /* On break la boucle afin de recommencer avec i = 0 */
                    break;
                }
                /*
                 * Si on arrive à la fin de la chaine de caractère, cela veut dire que l'on à rencontrer un nombre ou
                 * une variable, alors on l'ajoute
                 */
                if($i == strlen($input)-1) {
                    $val = substr($input, 0, $i);
                    if(trim($val) != '') { $inputArray[] = (is_numeric($val)?'nb':'id')."($val)"; }
                    $input = '';
                }
            }
        } while(!empty($input) and $input != '');
        /* Si notre entrée ne commence pas par "debut" ou ne fini pas par "fin" alors on lève une exception */
        if($inputArray[0] != 'debut') throw new WrongInputException($inputArray[0], 'debut', 0);
        if(end($inputArray) != 'fin') throw new WrongInputException(end($inputArray), 'fin', count($inputArray)-1);
        return $inputArray;
    }

    /**
     * Permet d'avoir la liste des grammaires sous la forme d'un tableau ce qui rend plus facile son exploitation
     * @param string $grammar Grammaire sous forme de chaîne de caractère
     * @return array Tableau contenant notre grammaire
     */
    public static function parseGrammar(string $grammar): array {
        $grammarArray = [];
        foreach (explode("\n", $grammar) as $line) {
            /* On limite à 3 l'explode car au dela cela fait partie des règles */
            $data = explode("\t",trim($line),3);
            /* Si notre premier élément n'est pas un nombre alors ce n'est pas un numéro de règle et on l'ignore */
            if(!is_numeric($data[0])) continue;
            /* On explode notre sortie afin de pouvoir la manipuler plus facilement */
            $grammarArray[$data[0]][$data[1]] = explode(' ',$data[2]);
        }
        return $grammarArray;
    }

    /**
     * Analyse le fichier dictionnaire afin de pouvoir retourner une liste de tokens et de règles
     * @param string $dictionary Dictionnaire sous forme de chaîne de caractère
     * @return array|array[] Un tableau contenant deux tableau, un à l'index tokens et l'autre à l'index rules
     */
    public static function parseDictionary(string $dictionary): array {
        $dictionaryArray = ['tokens'=>[],'rules'=>[]];
        foreach (explode("\n", $dictionary) as $line) {
            $data = explode("\t",trim($line),3);
            /* si notre explode n'est pas de taille 3 alors l'entrée n'est pas corrècte */
            if(count($data) != 3) continue;
            /* Si notre sortie n'est pas un nombre (numéro de règle) pop ou acc alors on l'ignore */
            if(!is_numeric($data[2]) and strtolower($data[2]) != 'acc' and strtolower($data[2]) != 'pop') continue;
            /* Si le token est inconnue alors on le sauvegarde */
            if(!in_array($data[1], $dictionaryArray['tokens'])) $dictionaryArray['tokens'][] = $data[1];
            /* et on sauvegarde la règle */
            $dictionaryArray['rules'][$data[0]][$data[1]] = $data[2];
        }
        return $dictionaryArray;
    }
}
