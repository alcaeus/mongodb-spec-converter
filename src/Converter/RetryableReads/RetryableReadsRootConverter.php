<?php

namespace App\Converter\RetryableReads;

use App\Converter\RootConverterInterface;
use Symfony\Component\Yaml\Reference\Anchor;
use Symfony\Component\Yaml\Reference\Reference;
use function basename;

final class RetryableReadsRootConverter implements RootConverterInterface
{
    public function convert(string $filename, object $inputData): array
    {
        return [
            'description' => basename($filename, '.yml'),
            // 1.9 is required for createEntities operation (per-test clientOptions)
            'schemaVersion' => '1.9',
            'createEntities' => [
                ['client' => [
                    'id' => new Anchor('client0', 'client0'),
                    'useMultipleMongoses' => false,
                    'observeEvents' => ['commandStartedEvent'],
                ]],
                ['database' => [
                    'id' => new Anchor('database0', 'database0'),
                    'client' => new Reference('client0'),
                    'databaseName' => $inputData->database_name ?? new Anchor('database_name', 'retryable-reads-tests'),
                ]],
                ['collection' => [
                    'id' => new Anchor('collection0', 'collection0'),
                    'database' => new Reference('database0'),
                    'collectionName' => $inputData->collection_name ?? new Anchor('collection_name', 'coll'),
                ]],
            ]
        ];
    }
}
