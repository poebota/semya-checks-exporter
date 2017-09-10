<?php

namespace SemyaChecksExporter\Command;

use SemyaChecksExporter\ConfigLoader;
use SemyaChecksExporter\Exporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnregisterCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('unregister existing udid')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to config file', '~/.config/semya.ini');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = (new ConfigLoader)->load($input->getOption('config'));
        $exporter = new Exporter($config);
        $exporter->unregister($config->udid);
        $output->writeln("Udid unregistered. Create a new one with register command");
    }
}