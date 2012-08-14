<?php

/*
 * This file is part of the Pinocchio library.
 *
 * (c) José Nahuel Cuesta Luengo <nahuelcuestaluengo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pinocchio;

use \Pinocchio\Parser\Php;


/**
 * Pinocchio Worker
 * This class is in charge of bootstrapping the process of the source files.
 *
 * @author José Nahuel Cuesta Luengo <nahuelcuestaluengo@gmail.com>
 */
class Worker
{
    /**
     * Configuration instance.
     *
     * @var \Pinocchio\Configuration
     */
    protected $configuration;

    /**
     * Logger instance.
     *
     * @var \Pinocchio\Logger\LoggerInterface
     */
    protected $logger;

    /**
     * Factory method for easy method chaining.
     *
     * @return Worker
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Constructor.
     * Handles the creation of the output directory, if needed.
     */
    public function __construct()
    {
        $this->configuration = new Configuration();

        $this->createOutputDir();
    }

    /**
     * Process the source files and generate their corresponding output files.
     */
    public function process()
    {
        $logger = $this->getLogger();
        $logger->log("Starting...\n");

        $formatter = $this->createFormatter();
        $parser    = $this->createParser();
        $outputDir = $this->configuration->get('output');
        $sources   = $this->configuration->getSources();
        $count     = count($sources);

        $logger->log("Using {$outputDir} as output directory.\n\n");

        foreach ($sources as $pinocchio) {
            $outputFile = $outputDir . '/' . $pinocchio->getOutputFilename($outputDir);

            $logger->log("Processing {$pinocchio->getTitle()} into {$outputFile}...");

            $formatter->format($parser->parse($pinocchio), $outputFile);

            $logger->log(" Done\n");
        }

        $logger->log("\nFinished processing {$count} source files.\n");
    }

    /**
     * Create - if needed - the output directory provided by the configuration.
     *
     * @throws \RuntimeException If the directory cannot be created.
     */
    public function createOutputDir()
    {
        $outputDir = $this->configuration->get('output');

        if (!is_writable($outputDir)) {
            @mkdir($outputDir, 0777, true);

            if (!is_writable($outputDir)) {
                throw new \RuntimeException("Output directory {$outputDir} is not writable.");
            }
        }
    }

    /**
     * Create a Formatter and return it.
     *
     * @return \Pinocchio\Formatter
     */
    public function createFormatter()
    {
        return new Formatter($this->configuration);
    }

    /**
     * Create a PHP Parser and return it.
     *
     * @return \Pinocchio\Parser\Php
     */
    public function createParser()
    {
        return new Php;
    }

    /**
     * Get the Logger instance.
     *
     * @return \Pinocchio\Logger\LoggerInterface
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            if ($this->configuration->get('silent')) {
                $this->logger = new Logger\NullLogger();
            } else {
                $loggerClass = $this->configuration->get('logger') ?: '\\Pinocchio\\Logger\\StandardLogger';
                $loggerOpts  = $this->configuration->get('logger_options') ?: array();

                $this->logger = new $loggerClass($loggerOpts);
            }
        }

        return $this->logger;
    }
}
