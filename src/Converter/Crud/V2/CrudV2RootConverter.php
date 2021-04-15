<?php

namespace App\Converter\Crud\V2;

use App\Converter\RootConverterInterface;
use Symfony\Component\Yaml\Reference\Anchor;
use function basename;

final class CrudV2RootConverter implements RootConverterInterface
{
    public function convert(string $filename, array $inputData): array
    {
        return [
            'description' => basename($filename, '.yml'),
            'schemaVersion' => '1.1',
            'createEntities' => [
                ['client' => [
                    'id' => new Anchor('client0', 'client0'),
                    'useMultipleMongoses' => false,
                    'uriOptions' => ['retryReads' => false],
                    'observeEvents' => ['commandStartedEvent'],
                ]],
                ['database' => [
                    'id' => new Anchor('database0', 'database0'),
                    'client' => 'client0',
                    'databaseName' => $inputData['database_name'] ?? new Anchor('database_name', 'crud-v2'),
                ]],
                ['collection' => [
                    'id' => new Anchor('collection0', 'collection0'),
                    'database' => 'database0',
                    'collectionName' => $inputData['collection_name'] ?? new Anchor('collection_name', 'crud-v2'),
                ]],
            ]
        ];
    }
}
