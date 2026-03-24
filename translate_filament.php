<?php

$dir = __DIR__ . '/app/Filament';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$methods = ['label', 'title', 'heading', 'modalHeading', 'modalButton', 'placeholder', 'button', 'tooltip', 'danger', 'success'];

$patternSingleQuotes = '/->(' . implode('|', $methods) . ')\(\s*\'([^\']+)\'\s*\)/';
$patternDoubleQuotes = '/->(' . implode('|', $methods) . ')\(\s*"([^"]+)"\s*\)/';

$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        $originalContent = $content;
        
        // Single Quotes
        $content = preg_replace_callback($patternSingleQuotes, function ($matches) {
            // Ignore if it looks like a variable, an env call or already translated
            // $matches[1] = method, $matches[2] = string
            if (strpos($matches[2], '__(') !== false || preg_match('/^[a-z0-9_]+$/', $matches[2]) && strlen($matches[2]) < 5 && $matches[2] !== 'USER') {
                return $matches[0];
            }
            return "->{$matches[1]}(__('{$matches[2]}'))";
        }, $content);
        
        // Double Quotes
        $content = preg_replace_callback($patternDoubleQuotes, function ($matches) {
            if (strpos($matches[2], '__(') !== false) {
                return $matches[0];
            }
            return "->{$matches[1]}(__(\"{$matches[2]}\"))";
        }, $content);
        
        if ($originalContent !== $content) {
            file_put_contents($path, $content);
            echo "Modified: " . $file->getFilename() . "\n";
            $count++;
        }
    }
}

echo "Total files modified: $count\n";
