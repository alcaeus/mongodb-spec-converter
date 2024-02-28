<?php

namespace App\Converter\RetryableWrites;

use App\Converter\Operation\LegacyOperationConverter;
use App\Converter\TestItemConverterInterface;
use App\Converter\Tests\OutcomeConverter;
use Symfony\Component\Yaml\Reference\Reference;
use function App\array_filter_null;

final class RetryableWritesTestConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): mixed
    {
        /* Legacy retryable writes tests have a single operation, so build an
         * operations array from the failPoint (if any) and operation. */
        $operations = [];

        if (isset($data->failPoint)) {
            $operations[] = (object) [
                'name' => 'failPoint',
                'object' => 'testRunner',
                'arguments' => (object) [
                    'client' => new Reference('client0'),
                    'failPoint' => $data->failPoint,
                ],
            ];
        }

        // Move outcome error/result fields to operation
        if (isset($data->outcome->error)) {
            $data->operation->error = $data->outcome->error;
            unset($data->outcome->error);
        }

        if (isset($data->outcome->result)) {
            $data->operation->result = $data->outcome->result;
            unset($data->outcome->result);
        }

        $operations[] = (new LegacyOperationConverter())
            ->convert('', $data->operation);

        $outcome = (new OutcomeConverter())
            ->convert('', $data->outcome ?? null);

        return array_filter_null([
            'description' => $data->description,
            'skipReason' => $data->skipReason ?? null,
            // useMultipleMongoses not supported, will cause errors to point out manual work
            'useMultipleMongoses' => $data->useMultipleMongoses ?? null,
            // failPoint handled above
            // clientOptions not supported, will cause errors to point out manual work
            'clientOptions' => $data->clientOptions ?? null,
            'operations' => $operations,
            'outcome' => $outcome,
        ]);
    }
}
