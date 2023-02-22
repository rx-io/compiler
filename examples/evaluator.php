<?php

use Rx0\Compiler\{ Evaluator };

require __DIR__ . '/../vendor/autoload.php';

$eval = new Evaluator(Evaluator::partialMap([
  'T_OPERATOR' => Evaluator::partialMap([
    'or'  => function(array $arguments){
      return array_reduce($arguments, fn($isTrue, $arg) => $isTrue || $arg, false);
    },
    'and' => function(array $arguments){
      return array_reduce($arguments, fn($isTrue, $arg) => $isTrue && $arg, true);
    },
    'in'  => function(array $arguments){
      $head = array_slice($arguments, 0, 1)[0] ?? null;
      $tail = array_slice($arguments, 1) ?? [];
      return in_array($head, $tail, true);
    },
    'eq'  => function(array $arguments){
      $head = array_slice($arguments, 0, 1)[0] ?? null;
      $tail = array_slice($arguments, 1, 1)[0] ?? null;
      return $head === $tail;
    },
  ]),
  'T_VAR' => function($name){
    return [
      '$foo'    => 'foo',
      '$bar'    => 'bar',
      '$foobar' => 'foobar',
    ][$name] ?? null;
  },
  'T_VAL' => function($value){
    return $value;
  },
]));

function token(string $type, $value, array $nodes = []){
  return (object)[
    'token' => (object)[
      'type' => $type,
      'value' => $value,
    ],
    'nodes' => $nodes,
  ];
}

$ast = token('T_OPERATOR', 'and', [
  token('T_OPERATOR', 'in', [
    // needle
    token('T_VAL', 1),
    // haystack
    token('T_VAL', 1),
    token('T_VAL', 2),
    token('T_VAL', 3),
  ]),
  token('T_OPERATOR', 'eq', [
    token('T_VAR', '$foo'),
    token('T_VAL', 'foo'),
  ]),
  token('T_OPERATOR', 'or', [
    token('T_OPERATOR', 'eq', [
      token('T_VAR', '$bar'),
      token('T_VAL', 'bar'),
    ]),
    token('T_OPERATOR', 'eq', [
      token('T_VAR', '$foobar'),
      token('T_VAL', 'foobar'),
    ]),
  ]),
]);

$res = $eval->evaluate($ast);
var_dump($res);
