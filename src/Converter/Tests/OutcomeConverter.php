<?php

namespace App\Converter\Tests;

use App\Converter\TestItemConverterInterface;
use Symfony\Component\Yaml\Reference\Anchor;
use Symfony\Component\Yaml\Reference\Reference;

class OutcomeConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): mixed
    {
        if ($data === null || $data instanceof Reference) {
            return $data;
        }

        if ($data instanceof Anchor) {
            $anchorName = $data->getName();
            $data = $data->getValue();
        }

        $result = [[
            'collectionName' => $data['collection']['name'] ?? new Reference('collection_name'),
            'databaseName' => new Reference('database_name'),
            'documents' => $data['collection']['data'],
        ]];

        return isset($anchorName) ? new Anchor($anchorName, $result) : $result;
    }
}
