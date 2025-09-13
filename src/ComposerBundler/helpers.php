<?php
/**
 * Helper functions for the Composer Bundler application.
 */

declare(strict_types=1);

/**
 * Renders a Mustache template with data
 *
 * @param string $templateName
 * @param array<string, mixed> $data
 * @return string
 */
function renderTemplate(string $template, array $data = []): string
{
    static $mustache = null;

    if ($mustache === null) {
        $mustache = new \Mustache\Engine([
            'loader' => new \Mustache\Loader\FilesystemLoader(__DIR__ . '/templates'),
        ]);
    }

    return $mustache->loadTemplate($template)->render($data);
}
