<?php

namespace App\Converter\Crud\V2;

use App\Converter\Operation\LegacyOperationConverter;
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
        $operations = $this->convertOperations($data['operations']);

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

    private function convertExpectedEvents(?array $expectations): ?array
    {
        if ($expectations === null) {
            return null;
        }

        return [[
            'client' => new Reference('client0'),
            'events' => array_map(
                fn ($expectation) => ['commandStartedEvent' => array_filter([
                    'command' => $this->convertCommandExpectations($expectation['command_started_event']['command']),
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

    private function convertOperations(mixed $operations): array
    {
        $converter = new LegacyOperationConverter();

        return array_map(
            fn($operation): array => $converter->convert('', $operation),
            $operations,
        );
    }

    private function convertCommandExpectations(array $command): array
    {
        return $this->convertExpectedNullValues($command);
    }

    private function convertExpectedNullValues(array $array): array
    {
        return array_map(
            fn ($value) => match (true) {
                is_array($value) => $this->convertExpectedNullValues($value),
                is_null($value) => ['$$exists' => false],
                default => $value,
            },
            $array,
        );
    }
}
