<?php

namespace App\Converter\Crud\V1;

use App\Converter\Operation\LegacyOperationConverter;
use App\Converter\TestItemConverterInterface;
use App\Converter\Tests\OutcomeConverter;
use function App\array_filter_null;

final class CrudV1TestConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): mixed
    {
        // Legacy CRUD v1 tests have a single operation, so build a new array
        $operations = [];

        // Move outcome error/result fields to operation
        if (isset($data->outcome->error)) {
            $data->operation->error = $data->outcome->error;
            unset($data->outcome->error);
        }

        // TODO: Handle null result
        if (isset($data->outcome->result)) {
            $data->operation->result = $data->outcome->result;
            unset($data->outcome->result);
        }

        $operations[] = (new LegacyOperationConverter())->convert('', $data->operation);

        // Some tests do not assert collection output
        if (isset($data->outcome->collection)) {
            $outcome = (new OutcomeConverter())->convert('', $data->outcome ?? null);
        }

        return array_filter_null([
            'description' => $data->description,
            'operations' => $operations,
            'outcome' => $outcome ?? null,
        ]);
    }
}
