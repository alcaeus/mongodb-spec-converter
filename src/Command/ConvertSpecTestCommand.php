<?php

namespace App\Command;

use App\Converter\Crud\V2\CrudV2SuiteConverter;
use App\Converter\SpecTestToUnifiedConverter;
use App\Converter\TestSuiteConverterInterface;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException as InvalidConsoleArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function is_subclass_of;
use function sprintf;

class ConvertSpecTestCommand extends Command
{
    private const SUITES = [
        'crud-v2' => CrudV2SuiteConverter::class,
    ];

    protected static $defaultName = 'app:convert-spec-tests';

    protected function configure()
    {
        $this->checkSuites();

        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'Test suite to convert')
            ->addArgument('mask', InputArgument::OPTIONAL, 'Optional file mask for tests', '**.yml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');

        if (!isset(self::SUITES[$suite])) {
            throw new InvalidConsoleArgumentException(sprintf('No converter found for test suite "%s".', $suite));
        }

        $converter = self::SUITES[$suite];

        (new SpecTestToUnifiedConverter($converter))->convert();

        return Command::SUCCESS;
    }

    private function checkSuites(): void
    {
        foreach (self::SUITES as $suite => $class) {
            if (is_subclass_of($class, TestSuiteConverterInterface::class)) {
                continue;
            }

            throw new LogicException(sprintf('Converter for suite "%s" does not implement the correct interface.', $suite));
        }
    }
}
