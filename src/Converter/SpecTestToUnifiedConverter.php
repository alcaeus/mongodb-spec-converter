<?php

namespace App\Converter;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

use function array_merge_recursive;
use function file_exists;
use function in_array;
use function is_subclass_of;
use function sprintf;

class SpecTestToUnifiedConverter
{
    /** @param class-string<TestSuiteConverterInterface> $converter */
    public function __construct(private string $converter)
    {
        if (!is_subclass_of($this->converter, TestSuiteConverterInterface::class)) {
            throw new RuntimeException(sprintf('Provided converter "%s" does not implement "%s".', $this->converter, TestSuiteConverterInterface::class));
        }
    }

    public function convert(): void
    {
        $baseDir = ($this->converter)::getInputDir();
        $finder = new Finder();
        $finder
            ->files()
            ->in($baseDir)
            ->name(($this->converter)::getMask());

        foreach ($finder as $file) {
            $this->convertFile($file);
        }
    }

    private function convertFile(SplFileInfo $file): void
    {
        $input = $file->getRealPath();
        $basename = $file->getBasename();
        $output = ($this->converter)::getOutputDir() . $basename;

        if (in_array($basename, ($this->converter)::getManuallyModifiedFiles()) && file_exists($output)) {
            return;
        }

        $inputData = Yaml::parseFile($input, Yaml::PARSE_REFERENCES_AS_OBJECTS);

        $initialOutputData = [];
        $rootConverter = ($this->converter)::getRootConverter();
        if ($rootConverter) {
            $initialOutputData = $rootConverter->convert($basename, $inputData);
        }

        $outputData = $this->applyItemConverters($inputData, $initialOutputData);

        $yaml = Yaml::dump($outputData, 12, 2);
        $yaml = <<<YAML
# This file was created automatically using mongodb-spec-converter.
# Please review the generated file, then remove this notice.

$yaml
YAML;

        (new Filesystem())->dumpFile($output, $yaml);
    }

    private function applyItemConverters(array $inputData, array $initialOutputData = []): array
    {
        $outputData = $initialOutputData;

        $itemConverters = ($this->converter)::getItemConverters();

        foreach ($itemConverters as $fieldName => $converter) {
            if (!$converter instanceof TestItemConverterInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Converter for field "%s" in "%s" does not implement interface "%s"',
                    $fieldName,
                    $this->converter,
                    TestItemConverterInterface::class,
                ));
            }

            $convertedData = $converter->convert($fieldName, $inputData[$fieldName] ?? null);
            if ($convertedData === null) {
                continue;
            }

            $outputData = array_merge_recursive($outputData, $convertedData);
        }

        return $outputData;
    }
}
