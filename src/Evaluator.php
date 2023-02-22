<?php

namespace Rx0\Compiler;

class Evaluator {

  public function __construct(
    private $eval
  ){}

  /**
   * @param \stdClass $tree
   * @return void
   */
  public function evaluate(\stdClass $node){
    return ($this->eval)(
      $node->token->type,
      $node->token->value,
      array_map(fn(\stdClass $node) => $this->evaluate($node), $node->nodes),
    );
  }

  public static function partialMap(array $map): callable {
    return function($head, ...$tail) use($map){
      return ($map[$head])(...$tail);
    };
  }

}