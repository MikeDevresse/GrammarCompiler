<?php
require 'CompilationException.php';
require 'UnknownRuleException.php';
require 'WrongInputException.php';

class Compiler
{
    private array $grammar;
    private array $rules;
    private array $tokens;
    private array $input;
    private array $stack;
    private array $output;
    private array $inputIndexes;

    /**
     * @throws WrongInputException
     */
    public function __construct(string $grammar, string $input, string $dictionary)
    {
        $this->grammar = self::parseGrammar($grammar);
        $dictionaryArray = self::parseDictionary($dictionary);
        $this->tokens = $dictionaryArray['tokens'];
        $this->rules = $dictionaryArray['rules'];

        $this->stack = [];
        $this->stack[] = ['$','P'];
        $this->output = [];

        $this->input = self::parseInput($input, $this->tokens);
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function getInputIndexes(): array
    {
        return $this->inputIndexes;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function getStack(): array
    {
        return $this->stack;
    }

    public function getOutput(): array
    {
        return $this->output;
    }

    public function compile() {
        $currentStack = $this->stack[0];
        foreach($this->input as $k => $in) {
            do {
                $popped = array_pop($currentStack);
                if(substr($in,0,3) == 'id(') $in = 'id';
                if(substr($in,0,3) == 'nb(') $in = 'nb';
                if(!isset($this->rules[$popped][$in])) {
                    throw new UnknownRuleException($popped,$in);
                }
                $this->output[] = $this->rules[$popped][$in];
                if(isset($this->grammar[end($this->output)][$popped])) {
                    foreach(array_reverse($this->grammar[end($this->output)][$popped]) as $item) {
                        if($item != 'epsilon') $currentStack[] = $item;
                    }
                }
                $this->stack[] = $currentStack;
                $this->inputIndexes[] = $k;
            } while(end($this->output) != 'pop' and end($this->output) != 'acc');
        }
    }

    /**
     * @throws WrongInputException
     */
    public static function parseInput(string $input, array $tokens): array {
        $inputArray = [];
        $input = str_replace(' ', '', str_replace("\n", '', $input));
        do {
            for($i = 0 ; $i < strlen($input) ; $i++) {
                $currentToken = null;
                foreach ($tokens as $token) {
                    if(substr($input, $i, strlen($token)) == $token) {
                        $currentToken = $token;
                        break;
                    }
                }
                if($currentToken) {
                    if($i != 0) {
                        $val = substr($input, 0, $i);
                        if(trim($val) != '') {
                            $inputArray[] = (is_numeric($val)?'nb':'id')."($val)";
                        }
                    }
                    $inputArray[] = $currentToken;
                    $input = substr($input, $i + strlen($currentToken));
                    break;
                }
                if($i == strlen($input)-1) {
                    $val = substr($input, 0, $i);
                    if(trim($val) != '') {
                        if (is_numeric($val)) {
                            $inputArray[] = 'nb(' . $val . ')';
                        } else {
                            $inputArray[] = 'id(' . $val . ')';
                        }
                    }
                    $input = '';
                }
            }
        } while(!empty($input) and $input != '');
        if($inputArray[0] != 'debut') throw new WrongInputException($inputArray[0], 'debut', 0);
        if(end($inputArray) != 'fin') throw new WrongInputException(end($inputArray), 'fin', count($inputArray)-1);
        return $inputArray;
    }

    public static function parseGrammar(string $grammar): array {
        $grammarArray = [];
        foreach (explode("\n", $grammar) as $line) {
            $data = explode("\t",trim($line),3);
            if(!is_numeric($data[0])) continue;
            $grammarArray[$data[0]][$data[1]] = explode(' ',$data[2]);
        }
        return $grammarArray;
    }

    public static function parseDictionary(string $dictionary): array {
        $dictionaryArray = ['tokens'=>[],'rules'=>[]];
        foreach (explode("\n", $dictionary) as $line) {
            $data = explode("\t",trim($line),3);
            if(count($data) != 3) continue;
            if(!is_numeric($data[2]) and strtolower($data[2]) != 'acc' and strtolower($data[2]) != 'pop') continue;
            if(!in_array($data[1], $dictionaryArray['tokens'])) $dictionaryArray['tokens'][] = $data[1];
            $dictionaryArray['rules'][$data[0]][$data[1]] = $data[2];
        }
        return $dictionaryArray;
    }
}
