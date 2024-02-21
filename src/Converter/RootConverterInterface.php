<?php

namespace App\Converter;

interface RootConverterInterface
{
    public function convert(string $filename, object $inputData): array;
}
