<?php

namespace Admingenerator\GeneratorBundle\Composer;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Composer\Script\CommandEvent;

/**
 * Class ScriptHandler
 *
 * Inspired from SensioDistributionBundle ScriptHandler
 *
 * @package Admingenerator\GeneratorBundle\Composer
 */
class ScriptHandler
{
    /**
     * Installs the assets under the admin/components directory into web root directory.
     *
     * @param $event CommandEvent A instance
     */
    public static function installAssets(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'install Generator assets');
        if (null === $consoleDir) {
            return;
        }
        $webDir = $options['symfony-web-dir'];
        $symlink = '';

        if (!self::hasDirectory($event, 'symfony-web-dir', $webDir, 'install Generator assets')) {
            return;
        }
        static::executeCommand($event, $consoleDir, 'admin:assets-install');
    }

    protected static function hasDirectory(CommandEvent $event, $configName, $path, $actionName)
    {
        if (!is_dir($path)) {
            $event->getIO()->write(sprintf('The %s (%s) specified in composer.json was not found in %s, can not %s.', $configName, $path, getcwd(), $actionName));
            return false;
        }
        return true;
    }

    protected static function executeCommand(CommandEvent $event, $consoleDir, $cmd, $timeout = 300)
    {
        $php = escapeshellarg(self::getPhp(false));
        $phpArgs = implode(' ', array_map('escapeshellarg', self::getPhpArguments()));
        $console = escapeshellarg($consoleDir.'/console');
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }
        $process = new Process($php.($phpArgs ? ' '.$phpArgs : '').' '.$console.' '.$cmd, null, null, null, $timeout);
        $process->run(function ($type, $buffer) use ($event) { $event->getIO()->write($buffer, false); });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
        }
    }

    protected static function getOptions(CommandEvent $event)
    {
        $options = $event->getComposer()->getPackage()->getExtra();
        $options['process-timeout'] = $event->getComposer()->getConfig()->get('process-timeout');
        return $options;
    }

    protected static function getPhp($includeArgs = true)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find($includeArgs)) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }
        return $phpPath;
    }

    protected static function getPhpArguments()
    {
        $arguments = array();
        $phpFinder = new PhpExecutableFinder();
        if (method_exists($phpFinder, 'findArguments')) {
            $arguments = $phpFinder->findArguments();
        }
        if (false !== $ini = php_ini_loaded_file()) {
            $arguments[] = '--php-ini='.$ini;
        }
        return $arguments;
    }

    /**
     * Returns a relative path to the directory that contains the `console` command.
     *
     * @param CommandEvent $event      The command event.
     * @param string       $actionName The name of the action
     *
     * @return string|null The path to the console directory, null if not found.
     */
    protected static function getConsoleDir(CommandEvent $event, $actionName)
    {
        $options = self::getOptions($event);
        if (self::useNewDirectoryStructure($options)) {
            if (!self::hasDirectory($event, 'symfony-bin-dir', $options['symfony-bin-dir'], $actionName)) {
                return;
            }
            return $options['symfony-bin-dir'];
        }
        if (!self::hasDirectory($event, 'symfony-app-dir', $options['symfony-app-dir'], 'execute command')) {
            return;
        }
        return $options['symfony-app-dir'];
    }

    /**
     * Returns true if the new directory structure is used.
     *
     * @param array $options Composer options
     *
     * @return bool
     */
    protected static function useNewDirectoryStructure(array $options)
    {
        return isset($options['symfony-var-dir']) && is_dir($options['symfony-var-dir']);
    }
}