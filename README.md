# README
## What is Minwork?
Minwork is a PHP 7 micro framework designed to be fast, compact, easy to use, with interchangeable modules.

Main advantages of Minwork are:
- **Flexible** - every part of the framework can be replaced with your own as long as it implements specified interface
- **Event based** - all major actions trigger corresponding events for easy hooking and modifying application flow
- **Operations** - model utilizes command design pattern which allows to execute, queue and revert any CRUD operation
- **Fast** - due to light weight, small and simple modules with only most necessary functionality as well as no external dependencies Minwork is incredibly fast
- **Minimum effort** - you can create basic application under 1 minute with less than 15 lines of code
- **PHP 7** - utilizes every benefit of PHP 7 to make your work even smoother and more comfortable 
- **IDE friendly** - everything you need to know about any module or method is well documented using PHPDoc

## Example
This is how to create simple Hello World application
```php
<?php
require 'vendor/autoload.php';

use Minwork\Core\Framework;
use Minwork\Basic\Utility\Environment;
use Minwork\Http\Object\Router;
use Minwork\Basic\Controller\Controller;

$controller = new class() extends Controller {
  public function show() {
    return 'Hello world!';
  }
};
$framework = new Framework(new Router(['test' => $controller]), new Environment());
$framework->run('/test');
```

But what if we want to parse real website url? In that case you will need .htaccess file with content as follows
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php?URL=$1 [L,QSA]
```
And your index.php should look like this
```php
<?php
require 'vendor/autoload.php';

use Minwork\Core\Framework;
use Minwork\Basic\Utility\Environment;
use Minwork\Http\Object\Router;
use Minwork\Basic\Controller\Controller;

$controller = new class() extends Controller {
    public function show($name)
    {
        return "Hello {$name}!";
    }
};
$framework = new Framework(new Router(['default_controller' => $controller]), new Environment());
$framework->run($_GET['URL']);
```
Because `show` is default controller method, as a result of calling address `http://yourwebsitename.com/John` framework will output `Hello John!`

If you want output to be returned instead of printed just change last line to `$content = $framework->run($_GET['URL'], true);`