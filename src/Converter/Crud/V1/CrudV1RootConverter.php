<?php

namespace App\Converter\Crud\V1;

use App\Converter\RootConverterInterface;
use Symfony\Component\Yaml\Reference\Anchor;
use function App\array_filter_null;
use function basename;

final class CrudV1RootConverter implements RootConverterInterface
{
    public function convert(string $filename, object $inputData): array
    {
        $runOn = array_filter_null([
            'minServerVersion' => $inputData->minServerVersion ?? null,
            'maxServerVersion' => $inputData->maxServerVersion ?? null,
            'serverless' => $inputData->serverless ?? null,
        ]);

        return [
            'description' => basename($filename, '.yml'),
            'schemaVersion' => isset($inputData->serverless) ? '1.4' : '1.0',
            'runOnRequirements' => empty($runOn) ? [] : [$runOn],
            'createEntities' => [
                ['client' => [
                    'id' => new Anchor('client0', 'client0'),
                ]],
                ['database' => [
                    'id' => new Anchor('database0', 'database0'),
                    'client' => 'client0',
                    'databaseName' => new Anchor('database_name', 'crud-v1'),
                ]],
                ['collection' => [
                    'id' => new Anchor('collection0', 'collection0'),
                    'database' => 'database0',
                    'collectionName' => new Anchor('collection_name', 'coll'),
                ]],
            ],
            'initialData' => [],
            'tests' => [],
        ];
    }
}
