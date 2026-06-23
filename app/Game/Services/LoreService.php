<?php

namespace App\Game\Services;

use App\Game\Config\LevelLoreConfig;

class LoreService
{
    /**
     * @return array<int, array{title: string, subtitle: string, content: string, source: string, images: array<int, array{path: string, label: string}>}>
     */
    public function entriesForLevel(int $level): array
    {
        $entries = [];

        foreach (LevelLoreConfig::forLevel($level) as $entry) {
            $contentData = $this->readFirstExistingFile($entry['files'] ?? []);
            $images = $this->readExistingImages($entry['images'] ?? []);

            if ($contentData === null && $images === []) {
                continue;
            }

            $entries[] = [
                'title' => (string) ($entry['title'] ?? 'Dossie'),
                'subtitle' => (string) ($entry['subtitle'] ?? 'Arquivo de campo'),
                'content' => $contentData['content'] ?? '',
                'source' => $contentData['source'] ?? '',
                'images' => $images,
            ];
        }

        return $entries;
    }

    /**
     * @param  string[]  $relativePaths
     * @return array{content: string, source: string}|null
     */
    private function readFirstExistingFile(array $relativePaths): ?array
    {
        foreach ($relativePaths as $relativePath) {
            $safePath = $this->resolvePublicPath($relativePath);

            if ($safePath === null || ! is_file($safePath) || ! is_readable($safePath)) {
                continue;
            }

            $content = file_get_contents($safePath);

            if ($content === false) {
                continue;
            }

            $content = trim(str_replace(["\r\n", "\r"], "\n", $content));

            if ($content === '') {
                continue;
            }

            return [
                'content' => $content,
                'source' => str_replace('\\', '/', trim($relativePath, "/\\")),
            ];
        }

        return null;
    }

    /**
     * @param  array<int, array{path?: string, label?: string}>  $images
     * @return array<int, array{path: string, label: string}>
     */
    private function readExistingImages(array $images): array
    {
        $existingImages = [];

        foreach ($images as $image) {
            $relativePath = (string) ($image['path'] ?? '');
            $safePath = $this->resolvePublicPath($relativePath);

            if ($safePath === null || ! is_file($safePath) || ! is_readable($safePath)) {
                continue;
            }

            $extension = strtolower(pathinfo($safePath, PATHINFO_EXTENSION));

            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'svg', 'webp'], true)) {
                continue;
            }

            $existingImages[] = [
                'path' => str_replace('\\', '/', trim($relativePath, "/\\")),
                'label' => (string) ($image['label'] ?? pathinfo($safePath, PATHINFO_FILENAME)),
            ];
        }

        return $existingImages;
    }

    private function resolvePublicPath(string $relativePath): ?string
    {
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($relativePath, "/\\"));
        $candidate = public_path($normalizedPath);
        $publicRoot = realpath(public_path());
        $resolved = realpath($candidate);

        if ($publicRoot === false || $resolved === false) {
            return null;
        }

        $publicRoot = rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (! str_starts_with($resolved, $publicRoot)) {
            return null;
        }

        return $resolved;
    }
}
