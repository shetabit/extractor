<?php

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkip([
        // The values of a remote response are passed around as they come in,
        // where a numeric string standing in for a number is normal.
        SafeDeclareStrictTypesRector::class,

        // Turns `$value !== null` into `$value instanceof Something` where the
        // parameter already declares the type, which reads worse.
        FlipTypeControlToUseExclusiveTypeRector::class,

        // The stub server is a script that PHP's built in server runs.
        __DIR__.'/tests/Support/server',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withPhpSets();
