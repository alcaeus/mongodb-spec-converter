<?php

namespace App\Converter;

interface RootConverterInterface
{
    public function convert(string $filename, array $inputData): array;
}
