<?php
namespace Example\ApiClient\App\Main\View;

use Minwork\Basic\View\View;

class Webpage extends View
{
    const EXTENSION_PHP = '.php';
    const EXTENSION_HTML = '.html';
    
    const TEMPLATES_PATH = 'web/templates/';

    /**
     * 
     * @param string $name Folder path (optional) and file name (without extension) e.g. <i>main/index</i>
     * @param array $data 
     */
    public function __construct(string $name, array $data = [], $extension = self::EXTENSION_PHP)
    {
        parent::__construct(self::TEMPLATES_PATH . $name . $extension, $data);
    }
}