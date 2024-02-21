<?php

namespace App\Converter;

final class ListConverter extends YamlAnchorAwareConverter
{
    public function __construct(
        private TestItemConverterInterface $converter,
        private bool $useFieldName = false,
    ) {}

    protected function doConvert(string $fieldName, mixed $data): mixed
    {
        $converted = array_map(
            fn (mixed $item) => $this->converter->convert($fieldName, $item),
            $data,
        );

        return $this->useFieldName ? [$fieldName => $converted] : $converted;
    }
}
