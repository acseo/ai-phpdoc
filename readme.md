# AI PHPDocs

AI PHPDocs is a tool that uses GPT-3 to automatically add missing PHPDoc comments to your PHP code.

## Prerequisites

This package uses the OpenAI API. Before using AI PHPDocs, you will need to have an OpenAI API key set as an environment variable. 

```shell
export OPENAI_KEY=...
```

## Installation

To install AI PHPDocs, run the following command:


```shell
composer global require acseo/ai-phpdoc
```

## Usage

To add missing PHPDoc comments to a single file, use the following command:

```shell
aiphpdocs file  /path/to/file.php
```

To add missing PHPDoc comments to a directory of files, use the following command. By default it iterates through the current directory for all files, but does not go into subdirectories:

```shell
aiphpdocs dir
```


You may set the `--recursive` flag, or `-r` for short for it to go into subdirectories.

If you pass another variable (regardless of the recursive flag) it will treat it as another directory to sweep through instead of the working directory.

```shell
aiphpdocs dir -r /somewhere/else
```

### Docker usage

You can use the Docker image acseo/ai-phpdoc to use ai-phpdoc via docker

```bash
$ docker run -it -e OPENAI_KEY=sk-xxx -v /path/to/your/code:/code acseo/ai-phpdoc dir -r /code/src
```


## Example

Original PHP File

```php
// file : example.php
<?php

class Calculator
{
    public function add(int $a, int $b) : int
    {
        return $a + $b;
    }
}
```

```bash
$ export OPENAI_KEY=sk-...
$ php bin/aiphpdocs file example.php 
````

Result 

```php
// file : example.php
<?php

class Calculator
{
    /**
     * Adds two integers and returns the sum.
     *
     * @param int $a The first integer to be added.
     * @param int $b The second integer to be added.
     * @return int The sum of the two integers.
    */
    public function add(int $a, int $b) : int
    {
        return $a + $b;
    }
}
```

## License

AI PHPDocs is licensed under the AGPL-3.0 license. See LICENSE for more information.
