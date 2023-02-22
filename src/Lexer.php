<?php

namespace Rx0\Compiler;

class Lexer {

  public function __construct(
    private array $grammar = [],
  ){}

  /**
   * @param string $input
   * @throws LexerException
   * @return array
   */
  public function scan(string $input): array {
    $tokens = [];
    while(strlen($input)){
      $foundMatch = false;
      foreach($this->grammar as $pattern => $type){
        if(!preg_match($pattern, $input, $match)){
          continue;
        }
        $foundMatch = true;
        $value = $match[0];
        $input = substr($input, strlen($value));
        $tokens[] = (object)[
          'type' => $type,
          'value' => $value,
        ];
        break;
      }
      if(!$foundMatch){
        throw new LexerException(sprintf('Could not match any pattern at --> "%s"', $input));
      }
    }

    return $tokens;
  }

}