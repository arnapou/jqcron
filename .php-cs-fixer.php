<?php

declare(strict_types=1);

/*
 * This file is part of the Arnapou json-parser package.
 *
 * (c) Arnaud Buathier <me@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<HEADER
    This file is part of the Arnapou jqCron package.

    (c) Arnaud Buathier <me@arnapou.net>

    For the full copyright and license information, please view the LICENSE
    file that was distributed with this source code.
    HEADER;

$dirs = [
    __DIR__ . '/src',
    __DIR__ . '/test_php',
];

$rules = [
    '@PSR2' => true,
    '@PSR12' => true,
    '@Symfony' => true,
    '@DoctrineAnnotation' => true,
    '@PHP80Migration' => true,
    '@PHP81Migration' => true,
    '@PHP82Migration' => true,
    '@PHP83Migration' => true,
    '@PHP84Migration' => true,
    'new_expression_parentheses' => ['use_parentheses' => true],
    'declare_strict_types' => true,
    'concat_space' => ['spacing' => 'one'],
    'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
    'native_function_invocation' => ['include' => ['@compiler_optimized']],
    'combine_consecutive_issets' => true,
    'combine_consecutive_unsets' => true,
    'phpdoc_order' => true,
    'phpdoc_var_annotation_correct_order' => true,
    'global_namespace_import' => ['import_classes' => true, 'import_functions' => false, 'import_constants' => false],
    'header_comment' => ['location' => 'after_declare_strict', 'header' => $header],
    'trailing_comma_in_multiline' => ['elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters']],
    'phpdoc_line_span' => ['const' => 'single', 'method' => 'multi', 'property' => 'single'],
    'phpdoc_to_comment' => false,
];

return (new PhpCsFixer\Config())
    ->setUnsupportedPhpVersionAllowed(true)
    ->setCacheFile(sys_get_temp_dir() . '/.php-cs-fixer.cache')
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder((new PhpCsFixer\Finder())->in($dirs));
