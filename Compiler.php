<?php


class Compiler
{
    private array $grammar;
    
    public function __construct(string $grammar, string $input)
    {
        $this->grammar = self::parseGrammar($grammar);
    }

    public static function parseGrammar(string $grammar): array {
        $grammarArray = [];
        foreach (explode("\n", $grammar) as $line) {
            $data = explode("\t",$line,3);
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
}
