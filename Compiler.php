<?php


class Compiler
{
    private array $grammar;
    private array $rules;
    private array $tokens;
    private array $input;

    public function __construct(string $grammar, string $input, string $dictionary)
    {
        $this->grammar = self::parseGrammar($grammar);
        $dictionaryArray = self::parseDictionary($dictionary);
        $this->tokens = $dictionaryArray['tokens'];
        $this->rules = $dictionaryArray['rules'];

        $this->input = self::parseInput($input, $this->tokens);
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

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
                        $inputArray[] = (is_numeric($val)?'nb':'id')."($val)";
                    }
                    $inputArray[] = $currentToken;
                    $input = substr($input, $i + strlen($currentToken));
                    break;
                }
                if($i == strlen($input)) {
                    $val = substr($input, 0, $i);
                    if(is_numeric($val)) {
                        $inputArray[] = 'nb('.$val.')';
                    }
                    else {
                        $inputArray[] = 'id('.$val.')';
                    }
                    $input = '';
                }
            }
        } while(!empty($input) and $input != '');
        return $inputArray;
    }

    public static function parseGrammar(string $grammar): array {
        $grammarArray = [];
        foreach (explode("\n", $grammar) as $line) {
            $data = explode("\t",trim($line),3);
            if(!is_int($data[0])) continue;
            $grammarArray[$data[1]] = [
                'id' => $data[0]
            ];
            foreach(explode(' ',$data[2]) as $output) {
                $grammarArray[$data[1]]['outputs'][] = $output;
            }
        }
        return $grammarArray;
    }

    public static function parseDictionary(string $dictionary) {
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
