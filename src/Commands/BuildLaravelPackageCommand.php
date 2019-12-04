<?php

namespace Huangdijia\ComposerPackageBuilder\Commands;

use Composer\Command\BaseCommand;
use Huangdijia\ComposerPackageBuilder\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildLaravelPackageCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('build:laravel-package');
        $this->setDescription('Build a laravel package.')
            ->setDefinition([
                new InputOption('vendor', null, InputOption::VALUE_OPTIONAL, 'Vendor Name', 'my-vendor'),
                new InputOption('package', null, InputOption::VALUE_OPTIONAL, 'Package Name', 'my-package'),
                new InputOption('author', null, InputOption::VALUE_OPTIONAL, 'Author Name', 'author'),
                new InputOption('email', null, InputOption::VALUE_OPTIONAL, 'Author Email', 'author@domain.com'),
                new InputOption('description', null, InputOption::VALUE_OPTIONAL, 'Package Description', 'My awesome package'),
                new InputOption('license', null, InputOption::VALUE_OPTIONAL, 'License, eg: mit,agpl-3,gpl-3,lgpl-3,mozilla-public-2,apache-2,unlicense'),
                new InputOption('output', null, InputOption::VALUE_OPTIONAL, $_SERVER['PWD']),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $this->getOptions([], $input);

        // 参数验证
        if (empty($options['vendor'])) {
            throw new \InvalidArgumentException('"vendor" options is empty.');
        }

        if (empty($options['package'])) {
            throw new \InvalidArgumentException('"package" options is empty.');
        }

        if (!preg_match('/^[a-z]([0-9a-z\-\_]*)$/i', $options['package'])) {
            throw new \InvalidArgumentException('"package" options is error.');
        }

        if (!is_writable($options['output'])) {
            throw new \InvalidArgumentException('"output" is not writable.');
        }

        // Set variables
        $vendorName  = $options['vendor'];
        $packageName = $options['package'];
        $outputPath  = $options['package'];
        $stubPath    = realpath(__DIR__ . '/../../stubs/laravel-package');

        // Check output is exists or not
        if (is_dir($outputPath)) {
            throw new \Exception("Dir '{$outputPath}' is exists.", 1);
        }

        $stubs = array_merge(
            [$outputPath],
            glob($stubPath . '/*', GLOB_BRACE) ?: []
            , glob($stubPath . '/.*') ?: []
            , glob($stubPath . '/*/*') ?: []
        );

        // Make dirs
        foreach ($stubs as $dir) {
            if (is_file($dir)) {
                continue;
            }

            if (in_array(basename($dir), ['.', '..'])) {
                continue;
            }

            $targetDir = str_replace($stubPath, $outputPath, $dir);

            if (is_dir($targetDir)) {
                throw new \Exception("Dir '{$targetDir}' is exists!", 1);
            }

            mkdir($targetDir);
        }

        $pathReplaces = [
            $stubPath     => $outputPath,
            '.stub'       => '',
            'DummyClass'  => Str::studly($packageName),
            'DummyVendor' => $vendorName,
        ];

        $contentReplaces = [
            'DummyVendor'       => Str::studly($vendorName),
            'DummyPackage'      => Str::studly($packageName),
            'DummyClass'        => Str::studly($packageName),
            'dummy-vendor'      => Str::snake(Str::studly($vendorName), '-'),
            'dummy-package'     => Str::snake(Str::studly($packageName), '-'),
            'dummy-config'      => Str::snake(Str::studly($packageName), '-'),
            'dummy-abstract'    => Str::snake(Str::studly($packageName), '-'),
            'dummy-view'        => Str::snake(Str::studly($packageName), '-'),
            'dummy-assets'      => Str::snake(Str::studly($packageName), '-'),
            'dummy-lang'        => Str::snake(Str::studly($packageName), '-'),
            'dummy-author'      => $options['author'],
            'dummy-email'       => $options['email'],
            'dummy-description' => $options['description'],
        ];

        // Replace stubs
        foreach ($stubs as $stub) {
            if (is_dir($stub)) {
                continue;
            }

            // Replace filename
            $target  = str_replace(array_keys($pathReplaces), array_values($pathReplaces), $stub);
            $content = file_get_contents($stub);

            // Replace content
            $content = str_replace(array_keys($contentReplaces), array_values($contentReplaces), $content);

            if (!file_put_contents($target, $content)) {
                throw new \Exception("{$target} create failed");
            }
        }

        $style = new SymfonyStyle($input, $output);
        $style->success("Build [{$packageName}] success");
    }

    private function getOptions(array $config, InputInterface $input): array
    {
        $options = [];

        $options['vendor']      = $input->getOption('vendor') ?? 'my-vendor';
        $options['package']     = $input->getOption('package') ?? 'my-package';
        $options['author']      = $input->getOption('author') ?? 'author';
        $options['email']       = $input->getOption('email') ?? 'author@domain.com';
        $options['description'] = $input->getOption('description') ?? 'My awesome package';
        $options['license']     = $input->getOption('license') ?? 'MIT';
        $options['output']     = $input->getOption('output') ?? $_SERVER['PWD'];

        return $options;
    }
}
