<?php

use waterloomatt\Pipeline;

require_once(__DIR__ . '/src/Pipeline.php');


// The object to pass into the pipeline.
class Document
{
    public function __construct(
        public string $title,
        public string $author,
        public string $body
    ) {
    }
}

interface TransformDocumentPipeline
{
    function transform(Document $document, Closure $next): Document;
}

// Now, define some pipes
$pipes = [];

class FixSpelling implements TransformDocumentPipeline
{
    public function transform(Document $document, Closure $next): Document
    {
        // Business logic...

        return $next($document);
    }
}

class FilterBadWords implements TransformDocumentPipeline
{
    public function transform(Document $document, Closure $next): Document
    {
        // Business logic...

        return $next($document);
    }
}

class PublishDocument implements TransformDocumentPipeline
{
    public function transform(Document $document, Closure $next): Document
    {
        // Business logic...

        return $next($document);
    }
}

$pipes = [
    new FixSpelling(),
    new FilterBadWords(),
    new PublishDocument(),
];

$document = new Document('How To Use a Pipeline', 'waterloomatt', 'Just pass it, dude.');

$output = (new Pipeline())
    ->send($document)
    ->through($pipes)
    ->via('transform')
    ->thenReturn();

var_dump($output);