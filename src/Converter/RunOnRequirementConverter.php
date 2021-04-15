<?php

namespace App\Converter;

final class RunOnRequirementConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return [
            'runOnRequirements' => array_map(
                static function ($runOnRequirement) {
                    if (isset($runOnRequirement['topology'])) {
                        $runOnRequirement['topologies'] = $runOnRequirement['topology'];
                        unset($runOnRequirement['topology']);
                    }

                    return $runOnRequirement;
                },
                $data,
            ),
        ];
    }
}
