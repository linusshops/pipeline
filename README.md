# Pipeline - a simple step-by-step processing tool

Based on [Laravel's pipeline](https://laravel.com/api/master/Illuminate/Pipeline/Pipeline.html), this little titan
performs step-by-step processing over an object - any object. See https://laravel.com/docs/10.x/helpers#pipeline for more documentation.


# Table of Contents
1. [Installation](#installation)
1. [Tests](#tests)
1. [Overview](#overview)
1. [Pipes can be classes](#pipes-can-be-classes)
4. [Pipes can be closures](#pipes-can-be-closures)
2. [Pipes can abort further processing](#pipes-can-abort-further-processing)
3. [Pipes can move themselves to the end](#pipes-can-move-themselves-to-the-end-of-the-pipeline)

## Installation
This package can be installed via Composer. 

```bash 
composer require linusshops/pipeline
```

## Tests
Test can be run from PHPUnit. After installation run, 

```bash'
vendor/bin/phpunit
```

## Overview
Imagine an event happens in your system, like a CSV file is uploaded, and it triggers some actions,

1. the file is validated (ex. size, extension, mime-type, etc.)
2. file is processed (ex. parsed and extracted into a relational database)
3. file is archived (ex. moved in a remote share)
4. notifications need to be sent (ex. emails are sent to business users and the end-user)

You probably, have some objects that are responsible for each of these steps,

1. a ```$validator```
2. a ```$processor```
3. an ```$archiver```
4. a ```$notifier```

Good. And in a traditional OOP application, the calling code would look something like,

```php
$validator->validate($file);
$processor->process($file);
$mover->move($file);
$notifier->notify($file);
```

Good. Nothing wrong with that. That logic could live anywhere but in a traditional MVC application it'd probably live in
a controller, model, or some auxiliary of those.

Now, how does the pipeline do it?

```php
(new Pipeline())
    ->send($file)
    ->through([
        $validator,
        $processor,
        $archiver,
        $notifier
    ])
    ->thenReturn();
```

Each pipe will receive the file object and is free to do whatever it wants with it.

When it is done it's work it can call the next pipe, or not. Let's look at some examples.

## Pipes can be classes
```php
class Validate
{
    public function handle(File $file, Closure $next)
    {        
        // ... business logic ...
    
        return $next($file);
    }
}

class Process
{
    public function handle(File $file, Closure $next)
    {        
        // ... business logic ...
    
        return $next($file);
    }
}

class Archive
{
    public function handle(File $file, Closure $next)
    {        
        // ... business logic ...
    
        return $next($file);
    }
}

class Notify
{
    public function handle(File $file, Closure $next)
    {        
        // ... business logic ...
    
        return $next($file);
    }
}

$pipes = [
    new Validate(),
    new Process(),
    new Archive(),
    new Notify()
];

(new Pipeline())
    ->send(new File())   // Start with a file
    ->through($pipes)    // validate, process, archive, notify
    ->thenReturn();
```

## Pipes can be closures
```php
$pipes = [
    // Multiply by 10
    function ($input, $next) {
        // Modify the input
        $input = $input * 10;
        
        // Run the next pipe with the modified input
        return $next($input);
    },

    // Divide by 5
    function ($input, $next) {
        // Modify the input
        $input = $input / 5;
        
        // Run the next pipe with the modified input
        return $next($input);
    },

    // Add 1
    function ($input, $next) {
        // Modify the input
        $input = $input + 1;
        
        // Run the next pipe with the modified input
        return $next($input);
    },
];

$output = (new Pipeline())
    ->send(10)           // Start with 10
    ->through($pipes)    // Multiply by 10, divide by 5, add 1
    ->thenReturn();
    
// Output: 21
```

## Pipes can abort further processing
```php
$pipes = [
    fn($input, $next) => $next($input . 'A'),
    function ($input, $next) {
        // Abort further processing by returning the current $input.
        // The important part is that we don't call `$next($input)`.
        // We can return anything, false, null, $input etc. as long as it doesn't
        // Run the next pipe with the modified input.
        if ($input === 'A') {
            return $input;
        }

        // The remainder of this, as well as the next pipe,
        // will not execute. 
        $input .= 'B';

        return $next($input);
    },
    fn($input, $next) => $next($input . 'C'),
];

$output = (new Pipeline())
    ->send('')           // Start with an empty string
    ->through($pipes)    // Append A. Immediately stop further processing and return A.
    ->thenReturn();
    
// Output: A
```

## Pipes can move themselves to the end of the pipeline
```php
$pipes = [
    function ($input, $next) {
        // Immediately run the next pipes and get their results.
        $result = $next($input);
        $result .= 'A';

        return $result;
    },
    fn($input, $next) => $next($input . 'B'),
    fn($input, $next) => $next($input . 'C'),
];

$output = (new Pipeline())
    ->send('')           // Start with an empty string
    ->through($pipes)    // The first pipe immediately calls the next pipe, so we move on to B, then C, then finally A is run at the end. 
    ->thenReturn();
    
// Output: BCA
```
