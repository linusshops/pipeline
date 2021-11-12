<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use waterloomatt\Pipeline;

require_once(__DIR__ . '/../src/Pipeline.php');

class PipelineTest extends TestCase
{
    public function testEmptyPipeline()
    {
        $document = new StdClass();
        $document->title = 'Test Title';
        $document->author = 'Test Author';
        $document->body = 'Test Body';

        $output = (new Pipeline())
            ->send($document)
            ->through([])
            ->via('transform')
            ->thenReturn();

        $this->assertEquals($document, $output);
    }

    public function testArithmetic()
    {
        $pipes = [
            function ($input, $next) {
                $input = $input * 10;
                return $next($input);
            },

            function ($input, $next) {
                $input = $input / 5;
                return $next($input);
            },

            function ($input, $next) {
                $input = $input + 1;
                return $next($input);
            },
        ];

        $output = (new Pipeline())
            ->send(10)
            ->through($pipes)
            ->thenReturn();

        $this->assertEquals(21, $output);
    }
}
