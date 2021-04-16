<?php

namespace App\Converter\Operation;

use App\Converter\TestItemConverterInterface;
use Symfony\Component\Yaml\Reference\Reference;
use function App\array_filter_null;
use function array_map;

final class LegacyOperationConverter implements TestItemConverterInterface
{
    private const DEFAULT_OBJECT_NAME_MAPPINGS = [
        'collection' => 'collection0',
        'database' => 'database0',
    ];

    public function __construct(private array $objectNameMappings = self::DEFAULT_OBJECT_NAME_MAPPINGS) {}

    public function convert(string $fieldName, mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        if ($data['name'] === 'bulkWrite') {
            $data['arguments']['requests'] = array_map(
                fn ($request) => [
                    $request['name'] => $request['arguments'],
                ],
                $data['arguments']['requests'],
            );
        }

        if (is_array($data['arguments']) && isset($data['arguments']['options'])) {
            $options = $data['arguments']['options'];
            unset($data['arguments']['options']);
            $data['arguments'] = array_merge($data['arguments'], $options);
        }

        $unifiedOperation = [
            'object' => new Reference($this->getOperationObjectName($data['object'] ?? 'collection')),
            // collectionOptions not supported, will cause validation errors to point out manual work
            'collectionOptions' => $data['collectionOptions'] ?? null,
            'name' => $data['name'],
            'arguments' => $data['arguments'],
            // error handled below
            // result handled below
        ];

        if (isset($data['error'])) {
            $unifiedOperation['expectError'] = ['isError' => $data['error']];
        } elseif (isset($data['result'])) {
            $unifiedOperation['expectResult'] = $data['result'];
        }

        return array_filter_null($unifiedOperation);
    }

    private function getOperationObjectName(string $object): string
    {
        return $this->objectNameMappings[$object] ?? $object;
    }
}
