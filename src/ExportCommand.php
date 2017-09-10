<?php

namespace SemyaChecksExporter;

use SemyaChecksExporter\Data\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class ExportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('export')
            ->setDescription('Export Semya checks')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to config file', '~/.config/semya.ini')
            ->addOption('startDate', 's',InputOption::VALUE_REQUIRED,'begin date', '2001-01-01')
            ->addOption('endDate', 'e', InputOption::VALUE_REQUIRED, 'end date', '+1 day')
            ->addArgument('output', InputArgument::REQUIRED, 'output file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFileName = $input->getOption('config');

        if (strpos($configFileName, '~') !== false) {
            $info = posix_getpwuid(posix_getuid());
            $configFileName = str_replace('~', $info['dir'], $configFileName);
        }

        $iniConfig = @parse_ini_file($configFileName);

        if (!$iniConfig) {
            throw new \InvalidArgumentException("{$configFileName} was not found or invalid ini-format");
        }

        if (!isset(
            $iniConfig['card_id'],
            $iniConfig['name'],
            $iniConfig['secret']
        )) {
            throw new \InvalidArgumentException("'card_id', 'name', 'secret' options are missing or empty");
        }

        $config = new Config;
        $config->cardId = $iniConfig['card_id'];
        $config->name = $iniConfig['name'];
        $config->secret = $iniConfig['secret'];
        $config->udid = $iniConfig['udid'] ?? null;

        $startDate = null;
        $endDate = null;

        foreach ([['startDate', & $startDate], ['endDate', & $endDate]] as $date) {
            $dateBoundary = $input->getOption($date[0]);

            try {
                $date[1] = new \DateTime($dateBoundary);
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException("Invalid date boundary '{$dateBoundary}' passed for {$date[0]} option");
            }
        }

        $handle = @fopen($outputFileName = $input->getArgument('output'), 'wb');

        if (!$handle) {
            throw new \RuntimeException("Can't open {$outputFileName} for writing");
        }

        $exportStream = new StreamOutput($handle);
        $exportStream->write("[\n");

        $exportGenerator = (new Exporter($config))->export($startDate, $endDate);

        while (true) {
            if ($exportGenerator->valid()) {
                $check = $exportGenerator->current();

                if (isset(
                    $check['date'],
                    $check['checkSum']
                )) {
                    $output->writeln("Check from {$check['date']}, " . count($check['detailsInfo']) . " item(s), {$check['checkSum']}₽");
                }

                $exportStream->write(json_encode($exportGenerator->current(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            } else {
                $exportStream->write("\n]");

                break;
            }

            $exportGenerator->next();

            if ($exportGenerator->valid()) {
                $exportStream->write(",\n");
            } else {
                $exportStream->write("\n]");

                break;
            }
        }
    }
}