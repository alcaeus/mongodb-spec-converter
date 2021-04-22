<?php

namespace App\Converter\Transactions;

use App\Converter\RootConverterInterface;
use Symfony\Component\Yaml\Reference\Anchor;
use Symfony\Component\Yaml\Reference\Reference;
use function basename;

final class TransactionsRootConverter implements RootConverterInterface
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
                    'observeEvents' => ['commandStartedEvent'],
                ]],
                ['database' => [
                    'id' => new Anchor('database0', 'database0'),
                    'client' => new Reference('client0'),
                    'databaseName' => $inputData['database_name'] ?? new Anchor('database_name', 'transactions'),
                ]],
                ['collection' => [
                    'id' => new Anchor('collection0', 'collection0'),
                    'database' => new Reference('database0'),
                    'collectionName' => $inputData['collection_name'] ?? new Anchor('collection_name', 'transactions'),
                ]],
                ['session' => [
                    'id' => new Anchor('session0', 'session0'),
                    'client' => new Reference('client0'),
                ]],
                ['session' => [
                    'id' => new Anchor('session1', 'session1'),
                    'client' => new Reference('client0'),
                ]],
            ]
        ];
    }
}
