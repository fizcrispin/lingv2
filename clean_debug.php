<?php
include 'vendor/autoload.php';
// Bootstrapping NOT required for simple reflection of vendor class

try {
    $className = 'Filament\Resources\Resource';
    echo "Reflecting on $className:\n";
    $reflection = new ReflectionClass($className);
    $prop = $reflection->getProperty('navigationIcon');
    echo "Property: navigationIcon\n";
    echo "Type: " . (string) $prop->getType() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
