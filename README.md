# Shepherd Process Pool

[![Build Status](https://travis-ci.org/ibrahimgunduz34/ShepherdProcessPoolBundle.svg?branch=master)](https://travis-ci.org/ibrahimgunduz34/ShepherdProcessPoolBundle)

Shepherd is an easy way to run the processes in parallel. 
Also, you can control how many processes you can run in 
parallel or decide to stop all jobs in the queue if anyone 
is failed.
   

## How To Configure 
```yaml
shepherd:
    defaults:
        max_processes: 4
        fail_on_error: true
    pools:
        foo:
            max_processes: 2
            fail_on_error: false
        bar:
            max_processes: 8
```

## How To Install

Call the following command to add the package to your project as a composer dependency.
```$xslt
composer require ibrahimgunduz34/shepherd
```

## How To Use 

Basically, Shepherd creates services for each pool definition. So 
you can simply inject the pools anywhere. It creates services 
with`shepherd.pool.<pool name>` naming convention.

```yaml
App\Service\MyService:
    class: App\Service\MyService
    arguments:
        - 'shepherd.pool.foo'
```

You can keep adding new processes until starting the pool processing.

```php
<?php
namespace App\Service;

use Shepherd\Bundle\ProcessPoolBundle\ProcessPool;
use Symfony\Component\Process\Process;

class MyService 
{
    /** @var ProcessPool */
    private $pool;
    
    function __construct(ProcessPool $pool) {
        $this->pool = $pool; 
    }
    
    public function performSomething() {
        //...
        $this->pool->append(new Process(__DIR__ . '../bin/console', 'do:something', 'param1', 'param2'));
        $this->pool->append(new Process(__DIR__ . '../bin/console', 'do:something', 'param3', 'param4'));
        $this->pool->append(new Process(__DIR__ . '../bin/console', 'do:something', 'param5', 'param6'));
        //...
        //...
        //...
        $this->pool->start();
    }
}
```

## How To Stop The Flow If Anyone Of The Jobs Failed

To fail entire flow if anyone of the jobs failed, you must define `fail_on_error` as true 
when you defined the pool.

```yaml
shepherd:
    pools:
      foo:
        max_processes: 2
        fail_on_error: true        
```

Then you must handle the error which is thrown by `start()` method.

```php
<?php
namespace App\Service;

use Shepherd\Bundle\ProcessPoolBundle\ProcessPool;
use Shepherd\Bundle\ProcessPoolBundle\Exception\ProcessExecutionError;
use Symfony\Component\Process\Process;


class MyService 
{
    /** @var ProcessPool */
    private $pool;
        
    function __construct(ProcessPool $pool) {
        $this->pool = $pool; 
    }
    
    function performSomething() {
        //...
        try {
            $this->pool->start();    
        } catch (ProcessExecutionError $exception) {
            //TODO: Do something...
        }
        
        //...
    }   
}

```
