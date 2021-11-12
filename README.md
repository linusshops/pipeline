# Pipeline - a simple step-by-step processing tool

[![waterloomatt / pipeline](https://github.com/waterloomatt/pipeline/actions/workflows/php.yml/badge.svg)](https://github.com/waterloomatt/pipeline/actions/workflows/php.yml)

Based on [Laravel's pipeline](https://laravel.com/api/master/Illuminate/Pipeline/Pipeline.html), this little titan
performs step-by-step processing over an object - any object.

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

```phpt
$validator->validate($file);
$processor->process($file);
$mover->move($file);
$notifier->notify($file);
```

Good. Nothing wrong with that. That logic could live anywhere but in a traditional MVC application it'd probably live in
a controller, model, or some auxiliary of those.

Now, how does the pipeline do it?

```phpt
$pipes = [
    $validator,
    $processor,
    $archiver,
    $notifier
];

(new Pipeline())
    ->send($file)
    ->through($pipes)
    ->thenReturn();
```

Yip. It is that simple. Let's look at some examples. We'll start easy and work our way up.

## Pipes can be closures

```phpt
$pipes = [
    // Multiply by 10
    function ($input, $next) {
        $input = $input * 10;
        return $next($input);
    },

    // Divide by 5
    function ($input, $next) {
        $input = $input / 5;
        return $next($input);
    },

    // Add 1
    function ($input, $next) {
        $input = $input + 1;
        return $next($input);
    },
];


$output = (new Pipeline())
    ->send(10)           // Start with 10
    ->through($pipes)    // Multiply by 10, divide by 5, add 1
    ->thenReturn();
    
// Output: 21
```

## Pipes can be classes
```phpt
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