<?php
/**
 * Router class for the Composer Bundler application.
 *
 * This class handles incoming HTTP requests and delegates actions
 * (such as generating or downloading bundles) to the Bundler class.
 *
 * @project ComposerBundler
 * @author Armin Mansouri
 */

declare(strict_types=1);

namespace ComposerBundler;

class Router
{
    private array $get;
    private array $post;
    private string|null $page;
    private string|null $action;
    private string|null $fileName;
    private array $data;

    public function __construct(array $get = [], array $post = [])
    {
        $this->get      = $get;
        $this->post     = $post;
        $this->page     = $get['p']      ?? null;
        $this->action   = $get['action'] ?? null;
        $this->fileName = $get['file']   ?? null;
        $this->data     = $post          ?? [];
    }

    public function handle(): void
    {
        $template = match ($this->page) {
            'ajax'   => null,
            default  => 'main',
        };

        if ($this->action !== null) {
            $bundler = new Bundler($this->data);

            match ($this->action) {
                'generate' => $bundler->make(),
                'download' => $bundler->download($this->fileName),
                default    => null,
            };
        }

        if ($template !== null) {
            echo \renderTemplate($template, [
                'name'          => 'Composer Bundler',
                'jquery'        => 'https://code.jquery.com/jquery-3.7.1.min.js',
                'js'            => '/assets/js/main.js',
                'css'           => '/assets/css/main.css',
                'loaderText'    => 'Generating your bundle…'
            ]);
        }
    }
}
