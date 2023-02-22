<?php

namespace Rx0\Compiler;

class Parser {

  public function __construct(
    private array $operators,
    private array $groupStart,
    private array $groupEnd,
  ){}

  /**
   * @param array $tokens
   * @return void
   */
  public function parse(array $tokens){

    // poor man's endless loop prevention (super risky, yikes)
    if(count($tokens) <= 1){
      return null;
    }
  
    $op = null;
    $left = [];
    $right = [];
  
    foreach($this->operators as $operator){
      list($operatorType, $operatorLabel) = $operator;

      $op = null;
      $left = [];
      $right = [];

      $tokensBuffer = [...$tokens];
      while($token = array_shift($tokensBuffer)){

        list($groupStartType, $groupStartLabel) = $this->groupStart;
        if($token->type === $groupStartType && $token->value === $groupStartLabel){
          list($groupLeft, $groupRight) = $this->extractGroup($tokensBuffer, $this->groupStart, $this->groupEnd);
          $left = $groupLeft;
          $tokensBuffer = $groupRight;
          continue;
        }
  
        if(!$op){
          if($token->type === $operatorType && $token->value === $operatorLabel){
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
        $this->parse($left) ?? $left,
        $this->parse($right) ?? $right,
      ],
    ];
  }

  /**
   * @param array $tokens
   * @param array $groupStart
   * @param array $groupEnd
   * @return int|null
   */
  private function findEndOfGroupIndex(array $tokens, array $groupStart, array $groupEnd): int | null {
    $depth = 0;
    foreach($tokens as $index => $token){
      if($token->type === $groupStart[0] && $token->value === $groupStart[1]){
        $depth++;
      }
      if($token->type === $groupEnd[0] && $token->value === $groupEnd[1]){
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
   * @param array $groupStart
   * @param array $groupEnd
   * @return array
   */
  private function extractGroup(array $tokens, array $groupStart, array $groupEnd): array {
    $lastIndex = $this->findEndOfGroupIndex($tokens, $groupStart, $groupEnd);
    if($lastIndex === null){
      return [ [], $tokens ];
    }
    $head = array_slice($tokens, 0, $lastIndex);
    $tail = array_slice($tokens, $lastIndex + 1);
    return [ $head, $tail ];
  }

}