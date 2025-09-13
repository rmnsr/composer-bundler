<?php
/**
 * Bundler class for the Composer Bundler application.
 *
 * Handles:
 *  - Writing user-provided composer.json
 *  - Running `composer install`
 *  - Compressing the project folder into a ZIP
 *  - Serving the ZIP file for download
 *
 * @project ComposerBundler
 * @author Armin Mansouri
 */

declare(strict_types=1);

namespace ComposerBundler;

class Bundler
{
    private array $data;
    private string $tmpDir;
    private string $composerBin;

    public function __construct(array $data)
    {
        putenv('COMPOSER_HOME=/tmp');

        $this->data = $data;
        $this->tmpDir = '/tmp';
        $this->composerBin = $this->detectComposer();

        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }
    }

    public function make(): void
    {
        $dirName = 'composer_' . uniqid();
        $destination = $this->tmpDir . '/' . $dirName;
        
        if (!mkdir($destination, 0777, true) && !is_dir($destination)) {
            throw new \RuntimeException("Failed to create directory $destination");
        }

        $json = $this->data['json'] ?? '';
        if (!is_string($json) || !@json_decode($json)) {
            throw new \RuntimeException("Invalid JSON input.");
        }
        file_put_contents($destination . '/composer.json', $json);

        $cmd = escapeshellcmd($this->composerBin) . ' install -d ' . escapeshellarg($destination) . ' 2>&1';
        shell_exec($cmd);

        $zipPath = $destination . '.zip';
        if ($this->compress($destination, $zipPath)) {
            echo json_encode(['file' => basename($zipPath)]);
        }
    }

    public function compress(string $source, string $target): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($target, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $source = realpath($source);
        if ($source === false) {
            return false;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            if ($filePath === false) continue;
            $relativePath = substr($filePath, strlen($source) + 1);
            $zip->addFile($filePath, $relativePath);
        }

        return $zip->close();
    }

    public function download(string $file): void
    {
        if (basename($file) !== $file) {
            throw new \RuntimeException("Invalid file name.");
        }

        $path = $this->tmpDir . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($path)) {
            throw new \RuntimeException("ZIP file $file not found.");
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== 'zip') {
            throw new \RuntimeException("Invalid file type.");
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    /**
     * Detect Composer binary in the system.
     *
     * @return string
     * @throws \RuntimeException
     */
    private function detectComposer(): string
    {
        $which = trim(shell_exec('which composer 2>/dev/null'));
        if ($which && is_executable($which)) {
            return $which;
        }

        throw new \RuntimeException(
            "Composer binary not found. Please install Composer or provide its path."
        );
    }
}