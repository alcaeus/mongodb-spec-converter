<?php

namespace App\Converter\Tests;

use App\Converter\YamlAnchorAwareConverter;
use stdClass;
use Symfony\Component\Yaml\Reference\Reference;
use function App\array_map_recursive;
use function array_filter;
use function array_keys;
use function array_map;
use function current;
use function key;
use function reset;

final class ExpectationConverter extends YamlAnchorAwareConverter
{
    private const DEFAULT_UPDATE_CMD_OPTIONS = [
        'multi' => ['$$unsetOrMatches' => false],
        'upsert' => ['$$unsetOrMatches' => false],
    ];

    private const EVENT_NAME_MAP = [
        'command_started_event' => 'commandStartedEvent',
    ];

    protected function doConvert(string $fieldName, mixed $data): ?array
    {
        if (!is_object($data)) {
            return $data;
        }

        $data = (array) $data;

        reset($data);
        $event = key($data);
        $eventData = current($data);

        return [self::EVENT_NAME_MAP[$event] ?? $event => array_filter([
            'command' => $this->convertCommandExpectations($eventData->command),
            'commandName' => $eventData->command_name ?? false,
            'databaseName' => $eventData->database_name ?? false,
        ])];
    }

    private function convertCommandExpectations(stdClass $command): array
    {
        $command = (array) $command;

        $command = $this->addMissingUpdateCommandOptions($command);
        $command = $this->replaceSessionIdCheck($command);
        $command = $this->replaceMagicNumberExpectations($command);
        return $this->convertExpectedNullValues($command);
    }

    private function addMissingUpdateCommandOptions(array $command): array
    {
        $commandName = array_keys($command)[0];
        if ($commandName !== 'update') {
            return $command;
        }

        $command['updates'] = array_map(
            fn (stdClass $update): array => (array) $update + self::DEFAULT_UPDATE_CMD_OPTIONS,
            $command['updates'],
        );

        return $command;
    }

    private function replaceSessionIdCheck(array $command): array
    {
        if (isset($command['lsid'])) {
            $command['lsid'] = ['$$sessionLsid' => new Reference($command['lsid'])];
        }

        return $command;
    }

    private function replaceMagicNumberExpectations(array $command): array
    {
        if (isset($command['getMore'])) {
            $command['getMore'] = ['$$type' => 'long'];
        }

        if (isset($command['readConcern']->afterClusterTime) && $command['readConcern']->afterClusterTime === 42) {
            $command['readConcern']->afterClusterTime = ['$$exists' => true];
        }

        if (isset($command['recoveryToken']) && $command['recoveryToken'] === 42) {
            $command['recoveryToken'] = ['$$exists' => true];
        }

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
