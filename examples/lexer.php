<?php

use Rx0\Compiler\{ Lexer };

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

$tokens = $lexer->scan('($model in [1,2,3] or $model eq 5678 or get("prop") eq "crazy") and $make eq "VW"');
$tokens = array_values(array_filter($tokens, fn($token) => $token->type !== 'T_SPACE'));
print_r($tokens);
