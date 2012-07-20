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
        $formatter = $this->createFormatter();
        $parser    = $this->createParser();
        $outputDir = $this->configuration->get('output');

        foreach ($this->configuration->getSources() as $pinocchio) {
            $outputFile = $outputDir . '/' . $pinocchio->getOutputFilename($outputDir);

            $formatter->format($parser->parse($pinocchio), $outputFile);
        }
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
}
