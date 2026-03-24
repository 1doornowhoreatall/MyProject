<?php

$manifestPath = __DIR__ . '/public/build/manifest.json';
$manifest = json_decode(file_get_contents($manifestPath), true);

$langs = ['en', 'es', 'pt_BR'];

foreach ($langs as $lang) {
    $jsonPath = __DIR__ . "/lang/{$lang}.json";
    if (!file_exists($jsonPath)) continue;
    
    $jsonContent = file_get_contents($jsonPath);
    $dict = json_decode($jsonContent, true);
    
    // Fix Portuguese words in English dictionary
    if ($lang === 'en') {
        if (isset($dict['Reviews']) && $dict['Reviews'] === 'Avaliações') {
            $dict['Reviews'] = 'Reviews';
        }
        // Save the fixed json
        file_put_contents($jsonPath, json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    // Find the asset file
    $manifestKey = "lang/{$lang}.json";
    if (!isset($manifest[$manifestKey]['file'])) {
        echo "Manifest key missing for $lang\n";
        continue;
    }
    
    $assetPath = __DIR__ . '/public/build/' . $manifest[$manifestKey]['file'];
    if (!file_exists($assetPath)) {
        echo "Asset missing for $lang\n";
        continue;
    }
    
    $jsContent = file_get_contents($assetPath);
    
    // Extract the export block
    // export{Be as Accept,Le as Active,... Ue as default,...oe as draw};
    if (preg_match('/export\s*\{([^}]+)\}\s*;?$/', $jsContent, $matches)) {
        $exportBlock = $matches[1];
        $exports = explode(',', $exportBlock);
        
        $newExports = [];
        $newVariables = [];
        
        foreach ($exports as $export) {
            $export = trim($export);
            if (empty($export)) continue;
            
            if (preg_match('/([a-zA-Z0-9_$]+)\s+as\s+([a-zA-Z0-9_$]+)/', $export, $parts)) {
                $local = $parts[1];
                $exported = $parts[2];
                
                if ($exported === 'default') {
                    // Handled specially
                    $newExports[] = "Ue_new as default";
                } else {
                    // Look up the translation
                    // If the export name (e.g., Accept) is in the dictionary:
                    $val = isset($dict[$exported]) ? $dict[$exported] : $exported;
                    
                    // Create a clean local variable (using the exported name as local to avoid conflicts)
                    $cleanLocal = 'var_' . $exported;
                    $safeVal = json_encode($val, JSON_UNESCAPED_UNICODE);
                    $newVariables[] = "const {$cleanLocal} = {$safeVal};";
                    $newExports[] = "{$cleanLocal} as {$exported}";
                }
            }
        }
        
        // Build the new Ue_new object
        $ueString = json_encode($dict, JSON_UNESCAPED_UNICODE);
        $newVariables[] = "const Ue_new = {$ueString};";
        
        $newVariablesStr = implode("\n", $newVariables);
        $newExportsStr = "export {" . implode(", ", $newExports) . "};";
        
        // Remove the old export block
        $jsContent = preg_replace('/export\s*\{[^}]+\}\s*;?$/', '', $jsContent);
        
        // Append our clean variables and export
        $jsContent .= "\n" . $newVariablesStr . "\n" . $newExportsStr . "\n";
        
        file_put_contents($assetPath, $jsContent);
        echo "Successfully translated asset for $lang\n";
    } else {
        echo "Could not find export block in $lang\n";
    }
}
