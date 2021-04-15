<?php

namespace App\Converter;

use Symfony\Component\Yaml\Reference\Reference;

final class InitialDataConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }

        return [
            'initialData' => [[
                'collectionName' => new Reference('collection_name'),
                'databaseName' => new Reference('database_name'),
                'documents' => $data,
            ]],
        ];
    }
}
