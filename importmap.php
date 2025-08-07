<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    "app" => [
        "path" => "./assets/app.js",
        "entrypoint" => true,
    ],
    "@symfony/stimulus-bundle" => [
        "path" => "./vendor/symfony/stimulus-bundle/assets/dist/loader.js",
    ],
    "@hotwired/stimulus" => [
        "version" => "3.2.2",
    ],
    "daisyui" => [
        "version" => "5.0.50",
    ],
    "daisyui/daisyui.min.css" => [
        "version" => "5.0.50",
        "type" => "css",
    ],
    "@orchidjs/sifter" => [
        "version" => "1.1.0",
    ],
    "@orchidjs/unicode-variants" => [
        "version" => "1.1.2",
    ],
    "tom-select" => [
        "version" => "2.4.3",
    ],
    "tom-select/dist/css/tom-select.default.min.css" => [
        "version" => "2.4.3",
        "type" => "css",
    ],
    "@hotwired/turbo" => [
        "version" => "7.3.0",
    ],
];
