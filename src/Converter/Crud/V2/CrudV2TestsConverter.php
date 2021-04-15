<?php

namespace App\Converter\Crud\V2;

use App\Converter\TestItemConverterInterface;
use Symfony\Component\Yaml\Reference\Anchor;
use Symfony\Component\Yaml\Reference\Reference;
use function array_filter;
use function array_map;
use function array_unshift;

final class CrudV2TestsConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): mixed
    {
        $operations = array_map(
            fn ($operation): array => $this->convertOperation($operation),
            $data['operations'],
        );

        if (isset($operation['failPoint'])) {
            array_unshift($operations, [
                'name' => 'failPoint',
                'object' => 'testRunner',
                'arguments' => [
                    'client' => new Reference('client0'),
                    'failPoint' => $operation['failPoint'],
                ],
            ]);
        }

        return array_filter(
            [
                'description' => $data['description'],
                'skipReason' => $data['skipReason'] ?? null,
                // failPoint handled above
                // clientOptions not supported, will cause errors to point out manual work
                'clientOptions' => $data['clientOptions'] ?? null,
                'operations' => $operations,
                'expectEvents' => $this->convertExpectedEvents($data['expectations'] ?? null),
                'outcome' => $this->convertOutcome($data['outcome'] ?? null),
            ],
            fn ($data): bool => $data !== null,
        );
    }

    private function convertOperation(array $operation): array
    {
        if ($operation['name'] === 'bulkWrite') {
            $operation['arguments']['requests'] = array_map(
                fn ($request) => [
                    $request['name'] => $request['arguments'],
                ],
                $operation['arguments']['requests'],
            );
        }

        $unifiedOperation = [
            'object' => new Reference($this->getOperationObjectName($operation['object'] ?? 'collection')),
            // collectionOptions not supported, will cause errors to point out manual work
            'collectionOptions' => $data['collectionOptions'] ?? null,
            'name' => $operation['name'],
            'arguments' => $operation['arguments'],
            // error handled below
            // result handled below
        ];

        if (isset($operation['error'])) {
            $unifiedOperation['expectError'] = ['isError' => $operation['error']];
        } elseif (isset($operation['result'])) {
            $unifiedOperation['expectResult'] = $operation['result'];
        }

        return $unifiedOperation;
    }

    private function convertExpectedEvents(?array $expectations): ?array
    {
        if ($expectations === null) {
            return null;
        }

        return [[
            'client' => new Reference('client0'),
            'events' => array_map(
                fn ($expectation) => ['commandStartedEvent' => array_filter([
                    'command' => $expectation['command_started_event']['command'],
                    'commandName' => $expectation['command_started_event']['command_name'] ?? false,
                    'databaseName' => $expectation['command_started_event']['database_name'] ?? false,
                ])],
                $expectations,
            ),
        ]];
    }

    private function convertOutcome($outcome): array | Anchor | Reference | null
    {
        if ($outcome === null || $outcome instanceof Reference) {
            return $outcome;
        }

        if ($outcome instanceof Anchor) {
            $anchorName = $outcome->getName();
            $outcome = $outcome->getValue();
        }

        $result = [[
            'collectionName' => $outcome['collection']['name'] ?? new Reference('collection_name'),
            'databaseName' => new Reference('database_name'),
            'documents' => $outcome['collection']['data'],
        ]];

        return isset($anchorName) ? new Anchor($anchorName, $result) : $result;
    }

    private function getOperationObjectName(string $object): string
    {
        return $object === 'collection' ? 'collection0' : 'database0';
    }
}
