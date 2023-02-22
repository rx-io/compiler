<?php

use Rx0\Compiler\{ Lexer, Parser };

require __DIR__ . '/../vendor/autoload.php';

$lexer = new Lexer([
  '/^\$([a-zA-Z])+/'               => 'T_VAR',
  '/^"(.+?)"/'                     => 'T_VAL_STR',
  '/^\d+/'                         => 'T_VAL_INT',
  '/^(true|false)/'                => 'T_VAL_BOOL',
  '/^(or|and)/'                    => 'T_OPERATOR',
  '/^(eq|neq|gt|gte|lt|lte|in)/'   => 'T_OPERATOR',
  '/^[a-zA-Z]+\(.*?\)/'            => 'T_CALL',
  '/^\[.*?\]/'                     => 'T_LIST',
  '/^\(/'                          => 'T_BRACE_START',
  '/^\)/'                          => 'T_BRACE_END',
  '/^ /'                           => 'T_SPACE',
]);

$tokens = $lexer->scan('($number in [1,"2",3] or $model eq 5678) and $name eq "foo"');
$tokens = array_values(array_filter($tokens, fn($token) => $token->type !== 'T_SPACE'));
printf("--- TOKENS ---\n\n%s\n\n", json_encode($tokens, JSON_PRETTY_PRINT));

$parser = new Parser([
  ...array_map(fn(string $op) => [ 'T_OPERATOR', $op ], explode('|', 'or|and|eq|neq|gt|gte|lt|lte|in')),
], [
  ['T_BRACE_START', 'T_BRACE_END'],
]);
$tree = $parser->parse($tokens);
printf("--- AST ---\n\n%s\n\n", json_encode($tree, JSON_PRETTY_PRINT));
