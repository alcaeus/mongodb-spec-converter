<?php

namespace App\Converter;

final class PassThroughConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): array
    {
        return [$fieldName => $data];
    }
}
