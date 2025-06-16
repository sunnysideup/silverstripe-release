<?php

namespace Sunnysideup\Release;

use SilverStripe\Control\Director;
use SilverStripe\Core\Flushable;
use SilverStripe\ORM\DB;

class AddToGitIgnore implements Flushable
{
    public static function flush()
    {
        if (Director::isDev()) {
            self::addToGitIgnore();
        } else {
            self::createSakeSymlink();
        }
    }

    protected static function addToGitIgnore()
    {
        $filePath = Director::baseFolder() . '/' . '.gitignore';
        $linesToAdd = ['/release.log', '/release_running', '/release-running'];
        if (!file_exists($filePath)) {

            file_put_contents($filePath, implode(PHP_EOL, $linesToAdd) . PHP_EOL);
            return;
        }
        $currentContent = file($filePath, FILE_IGNORE_NEW_LINES);
        $missingLines = array_filter($linesToAdd, fn($line) => !in_array($line, $currentContent));

        if (!empty($missingLines)) {
            if (is_writable($filePath)) {
                file_put_contents($filePath, PHP_EOL . implode(PHP_EOL, $missingLines) . PHP_EOL, FILE_APPEND);
            } else {

                if (!Director::is_cli()) {
                    echo '<pre>';
                }
                die('ERROR: Please add the following lines to your .gitignore file: ' . PHP_EOL . implode(PHP_EOL, $missingLines) . PHP_EOL);
                if (!Director::is_cli()) {
                    echo '</pre>';
                }
            }
        }
    }

    protected static function createSakeSymlink()
    {
        $baseFolder = Director::baseFolder();
        $sakePath = $baseFolder . '/vendor/bin/sake';
        $linkPath = $baseFolder . '/sake';

        if (!file_exists($linkPath)) {
            if (symlink($sakePath, $linkPath)) {
                DB::alteration_message("Symlink created: $linkPath → $sakePath", 'created');
            } else {
                DB::alteration_message("Failed to create symlink.", 'deleted');
            }
        }
    }
}
