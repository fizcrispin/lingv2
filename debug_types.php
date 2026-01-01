<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

function checkResource($className) {
    echo "Checking $className:\n";
    $reflection = new ReflectionClass($className);
    try {
        $prop = $reflection->getProperty('navigationIcon');
        echo "  Type: " . $prop->getType() . "\n";
        echo "  Declaring Class: " . $prop->getDeclaringClass()->getName() . "\n";
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    
    $parent = $reflection->getParentClass();
    if ($parent) {
        echo "Parent: " . $parent->getName() . "\n";
        try {
            $parentProp = $parent->getProperty('navigationIcon');
            echo "  Parent Type: " . $parentProp->getType() . "\n";
        } catch (Exception $e) {
            echo "  Parent Error: " . $e->getMessage() . "\n";
        }
    }
}

checkResource('App\Filament\Resources\PendaftarLingkungans\PendaftarLingkunganResource');
echo "\n";
checkResource('App\Filament\Resources\JenisSampels\JenisSampelResource');
