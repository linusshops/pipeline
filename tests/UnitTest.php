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
}
