<?php

namespace App\Converter\Tests;

use App\Converter\TestItemConverterInterface;
use Symfony\Component\Yaml\Reference\Reference;
use function App\array_map_recursive;
use function array_filter;
use function array_keys;
use function array_map;

final class ExpectationsConverter implements TestItemConverterInterface
{
    private const DEFAULT_UPDATE_CMD_OPTIONS = [
        'multi' => ['$$unsetOrMatches' => false],
        'upsert' => ['$$unsetOrMatches' => false],
    ];

    private const EVENT_NAME_MAP = [
        'command_started_event' => 'commandStartedEvent',
    ];

    public function convert(string $fieldName, mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return [[
            'client' => new Reference('client0'),
            'events' => array_map(
                function (array $expectation): array {
                    reset($expectation);
                    $event = key($expectation);
                    $eventData = current($expectation);

                    return [self::EVENT_NAME_MAP[$event] ?? $event => array_filter([
                        'command' => $this->convertCommandExpectations($eventData['command']),
                        'commandName' => $eventData['command_name'] ?? false,
                        'databaseName' => $eventData['database_name'] ?? false,
                    ])];
                },
                $data,
            ),
        ]];
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
