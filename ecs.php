<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpCsFixerSets(
        doctrineAnnotation: true,
        per: true,
        perCS: true,
        symfony: true,
    )
    ->withSkip([
        PhpdocToCommentFixer::class
    ]);
