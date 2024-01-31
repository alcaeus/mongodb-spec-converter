<?php

namespace App\Converter\Operation;

use App\Converter\YamlAnchorAwareConverter;
use Symfony\Component\Yaml\Reference\Reference;
use function App\array_filter_null;
use function array_map;

final class LegacyOperationConverter extends YamlAnchorAwareConverter
{
    private const DEFAULT_OBJECT_NAME_MAPPINGS = [
        'collection' => 'collection0',
        'database' => 'database0',
    ];

    public function __construct(private array $objectNameMappings = self::DEFAULT_OBJECT_NAME_MAPPINGS) {}

    protected function doConvert(string $fieldName, mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        if ($data->name === 'bulkWrite') {
            $data->arguments->requests = array_map(
                fn ($request) => [
                    $request->name => $request->arguments,
                ],
                $data->arguments->requests,
            );
        }

        if ($data->name === 'runCommand' && isset($data->command_name)) {
            $data->arguments->commandName = $data->command_name;
            unset($data->command_name);
        }

        if (isset($data->arguments) && is_object($data->arguments)) {
            if (isset($data->arguments->options)) {
                $options = $data->arguments->options;
                unset($data->arguments->options);
                $data->arguments = (object) array_merge((array) $data->arguments, (array) $options);
            }

            if (isset($data->arguments->session) && !$data->arguments->session instanceof Reference) {
                $data->arguments->session = new Reference($data->arguments->session);
            }
        }

        $unifiedOperation = [
            'object' => $this->getOperationObject($data->object ?? 'collection'),
            // databaseOptions not supported, will cause validation errors to point out manual work
            'databaseOptions' => $data->databaseOptions ?? null,
            // collectionOptions not supported, will cause validation errors to point out manual work
            'collectionOptions' => $data->collectionOptions ?? null,
            'name' => $data->name,
            'arguments' => $data->arguments ?? null,
            'command_name' => $data->command_name ?? null,
            // error handled below
            // result handled below
        ];

        if (isset($data->error) && $data->error === true) {
            $unifiedOperation['expectError'] = ['isError' => $data->error];
            if (isset($data->result) && is_array($data->result)) {
                $unifiedOperation['expectError'] += $data->result;
            }
        } elseif (isset($data->result)) {
            $unifiedOperation['expectResult'] = $data->result;
        }

        return array_filter_null($unifiedOperation);
    }

    private function getOperationObject(string $object): string | Reference
    {
        if ($object === 'testRunner') {
            return $object;
        }

        return new Reference($this->objectNameMappings[$object] ?? $object);
    }
}
