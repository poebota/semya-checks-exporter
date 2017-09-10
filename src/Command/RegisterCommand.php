<?php

namespace SemyaChecksExporter\Command;

use SemyaChecksExporter\ConfigLoader;
use SemyaChecksExporter\Exporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('register existing udid')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to config file', '~/.config/semya.ini');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = (new ConfigLoader)->load($input->getOption('config'));
        $exporter = new Exporter($config);
        $udid = $exporter->register();
        $output->writeln("New udid: {$udid}\nstore it in config file");
    }

}