<?php

namespace App\Converter;

final class ListConverter implements TestItemConverterInterface
{
    public function __construct(
        private TestItemConverterInterface $converter,
        private bool $useFieldName = false,
    ) {}

    public function convert(string $fieldName, mixed $data): mixed
    {
        $converted = array_map(
            fn(mixed $item) => $this->converter->convert($fieldName, $item),
            $data,
        );

        return $this->useFieldName ? [$fieldName => $converted] : $converted;
    }
}
