<?php

namespace App\Converter\Crud\V2;

use App\Converter\ListConverter;
use App\Converter\Operation\LegacyOperationConverter;
use App\Converter\TestItemConverterInterface;
use Symfony\Component\Yaml\Reference\Anchor;
use Symfony\Component\Yaml\Reference\Reference;
use function App\array_filter_null;
use function App\array_map_recursive;
use function array_filter;
use function array_map;
use function array_unshift;

final class CrudV2TestConverter implements TestItemConverterInterface
{
    private const DEFAULT_UPDATE_CMD_OPTIONS = [
        'multi' => ['$$unsetOrMatches' => false],
        'upsert' => ['$$unsetOrMatches' => false],
    ];

    public function convert(string $fieldName, mixed $data): mixed
    {
        if (isset($data['failPoint'])) {
            array_unshift($data['operations'], [
                'name' => 'failPoint',
                'object' => 'testRunner',
                'arguments' => [
                    'client' => new Reference('client0'),
                    'failPoint' => $data['failPoint'],
                ],
            ]);
        }

        $operations = (new ListConverter(new LegacyOperationConverter(), false))
            ->convert('', $data['operations']);

        return array_filter_null([
            'description' => $data['description'],
            'skipReason' => $data['skipReason'] ?? null,
            // failPoint handled above
            // clientOptions not supported, will cause errors to point out manual work
            'clientOptions' => $data['clientOptions'] ?? null,
            'operations' => $operations,
            'expectEvents' => $this->convertExpectedEvents($data['expectations'] ?? null),
            'outcome' => $this->convertOutcome($data['outcome'] ?? null),
        ]);
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

    private function convertCommandExpectations(array $command): array
    {
        $command = $this->addMissingUpdateCommandOptions($command);
        return $this->convertExpectedNullValues($command);
    }

    private function addMissingUpdateCommandOptions(array $command): array
    {
        $commandName = array_keys($command)[0];
        if ($commandName !== 'update') {
            return $command;
        }

        $command['updates'] = array_map(
            fn (array $update): array => $update + self::DEFAULT_UPDATE_CMD_OPTIONS,
            $command['updates'],
        );

        return $command;
    }

    private function convertExpectedNullValues(array $array): array
    {
        return array_map_recursive(
            fn ($value) => $value !== null ? $value : ['$$exists' => false],
            $array,
        );
    }
}
