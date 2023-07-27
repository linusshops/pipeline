<?php

declare(strict_types=1);

namespace LinusShops\Pipeline\Tests;

use Exception;
use LinusShops\Pipeline\Pipeline;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the pipeline.
 *
 * @author Matt  Skelton <matt.skelton@fortnine.ca>
 */
class UnitTest extends TestCase
{
    /**
     * Tests that an empty pipeline does not modify the input.
     *
     * @return void
     */
    public function testEmptyPipelineDoesNotModifyInput()
    {
        // Arrange
        $expected = 'A';

        // Act
        $output = (new Pipeline())
            ->send($expected)
            ->through([])
            ->thenReturn();

        // Assert
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests that an exception thrown within a pipe bubbles up.
     *
     * @return void
     */
    public function testExceptionIsThrown()
    {
        // Arrange
        $this->expectException(Exception::class);

        // Act
        (new Pipeline())
            ->send('')
            ->through([
                fn($input, $next) => $next($input . 'A'),
                function ($input, $next) {
                    throw new Exception('An error occurred');
                },
            ])
            ->thenReturn();
    }

    /**
     * Tests that pipes are processed in the expected order.
     *
     * @return void
     */
    public function testPipesAreProcessedInTheCorrectOrder()
    {
        // Arrange
        $expected = 'ABC';

        // Act
        $output = (new Pipeline())
            ->send('')
            ->through([
                fn($input, $next) => $next($input . 'A'),
                fn($input, $next) => $next($input . 'B'),
                fn($input, $next) => $next($input . 'C'),
            ])
            ->thenReturn();

        // Assert
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests that a pipe can push itself to the end of the pipeline.
     *
     * @return void
     */
    public function testPipeIsProcessedLast()
    {
        // Arrange
        $expected = 'BCA';

        $pipes = [
            function ($input, $next) {
                // Immediately run the next pipe.
                $result = $next($input);
                $result .= 'A';

                return $result;
            },
            fn($input, $next) => $next($input . 'B'),
            fn($input, $next) => $next($input . 'C'),
        ];

        // Act
        $output = (new Pipeline())
            ->send('')
            ->through($pipes)
            ->thenReturn();

        // Assert
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests that a pipe can abort further processing.
     *
     * @return void
     */
    public function testPipesCanAbortProcessing()
    {
        // Arrange
        $expected = 'A';

        $pipes = [
            fn($input, $next) => $next($input . 'A'),
            function ($input, $next) {
                // Abort further processing by returning the current $input.
                // The important part is that we don't call `$next($input)`.
                // We can return anything, false, null, $input etc. as long as it doesn't
                // call the next pipe.
                if ($input === 'A') {
                    return $input;
                }

                $input .= 'B';

                return $next($input);
            },
            fn($input, $next) => $next($input . 'C'),
        ];

        // Act
        $output = (new Pipeline())
            ->send('')
            ->through($pipes)
            ->thenReturn();

        // Assert
        $this->assertEquals($expected, $output);
    }
}
