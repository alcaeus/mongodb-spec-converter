<?php

namespace App\Converter;

final class RenameConverter implements TestItemConverterInterface
{
    public function __construct(private string $newFieldName) {}

    public function convert(string $fieldName, mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return [$this->newFieldName => $data];
    }
}
