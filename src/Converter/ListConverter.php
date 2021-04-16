<?php

namespace App\Converter;

final class ListConverter implements TestItemConverterInterface
{
    public function __construct(private TestItemConverterInterface $converter) {}

    public function convert(string $fieldName, mixed $data): mixed
    {
        return [$fieldName => array_map(
            fn (mixed $item) => $this->converter->convert($fieldName, $item),
            $data,
        )];
    }
}
