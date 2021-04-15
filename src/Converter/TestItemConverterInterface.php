<?php

namespace App\Converter;

interface TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): mixed;
}
