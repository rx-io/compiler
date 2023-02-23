<?php

namespace Rx0\Compiler;

class Parser {

  public function __construct(
    private array $operators,
    private array $groupLimiters,
  ){}

  /**
   * @param array $tokens
   * @return \stdClass
   */
  public function parse(array $tokens){

    // poor man's endless recursive loop prevention (super risky, yikes)
    if(count($tokens) <= 1){
      return null;
    }
  
    $op = null;
    $left = [];
    $right = [];
  
    foreach($this->operators as list($operatorType, $operatorSymbol)){

      $op = null;
      $left = [];
      $right = [];

      $tokensBuffer = [...$tokens];
      while($token = array_shift($tokensBuffer)){

        foreach($this->groupLimiters as list($groupStart, $groupEnd)){
          if($token->type === $groupStart){
            list($groupLeft, $groupRight) = $this->extractGroup($tokensBuffer, $groupStart, $groupEnd);
            $left = $groupLeft;
            $tokensBuffer = $groupRight;
            continue 2;
          }
        }
  
        if(!$op){
          if($token->type === $operatorType && $token->value === $operatorSymbol){
            $op = $token;
          } else {
            $left[] = $token;
          }
        } else {
          $right[] = $token;
        }
      }

      if($op){
        break;
      }

    }
  
    return (object)[
      'token' => $op,
      'nodes' => [
        $this->parse($left) ?? (object)[ 'token' => $left[0] ?? null, 'nodes' => [] ],
        $this->parse($right) ?? (object)[ 'token' => $right[0] ?? null, 'nodes' => [] ],
      ],
    ];
  }

  /**
   * @param array $tokens
   * @param array $groupStart
   * @param array $groupEnd
   * @return int|null
   */
  private function findEndOfGroupIndex(array $tokens, string $groupStart, string $groupEnd): int | null {
    $depth = 0;
    foreach($tokens as $index => $token){
      if($token->type === $groupStart){
        $depth++;
      }
      if($token->type === $groupEnd){
        if($depth !== 0){
          $depth--;
        } else {
          return $index;
        }
      }
    }
    return null;
  }

  /**
   * @param array $tokens
   * @param string $groupStart
   * @param string $groupEnd
   * @return array
   */
  private function extractGroup(array $tokens, string $groupStart, string $groupEnd): array {
    $lastIndex = $this->findEndOfGroupIndex($tokens, $groupStart, $groupEnd);
    if($lastIndex === null){
      return [ [], $tokens ];
    }
    $head = array_slice($tokens, 0, $lastIndex);
    $tail = array_slice($tokens, $lastIndex + 1);
    return [ $head, $tail ];
  }

}
