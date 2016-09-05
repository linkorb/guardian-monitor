<?php
namespace Guardian\Monitor\Command;

use RuntimeException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Guardian\Monitor\Loader\YamlLoader;
use Guardian\Monitor\Monitor;

class RunCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run agent')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = __DIR__ . '/../../monitor.yml';
        
        $loader = new YamlLoader();
        $data = $loader->loadYaml($filename);
        $monitor = $loader->loadMonitor($data);
        
        $output->writeln('Running Guardian Monitor');
        $monitor->run();
    }
}
