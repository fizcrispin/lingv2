<?php
require 'vendor/autoload.php';

$classes = [
    'Filament\Tables\Enums\RecordActionsPosition',
    'Filament\Tables\Enums\ActionsPosition',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "$class exists\n";
    }
}
