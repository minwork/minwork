<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiClient\App\Main\View;

use Minwork\Basic\View\View;

/**
 * Basic view for application templates using specified basic templates path
 *
 * @author Christopher Kalkhoff
 *        
 */
class Webpage extends View
{

    const EXTENSION_PHP = '.php';

    const EXTENSION_HTML = '.html';

    const TEMPLATES_PATH = 'web/templates/';

    /**
     *
     * @param string $name
     *            Folder path (optional) and file name (without extension) e.g. <i>main/index</i>
     * @param array $data Data array which will be passed to template context
     */
    public function __construct(string $name, array $data = [], $extension = self::EXTENSION_PHP)
    {
        parent::__construct(self::TEMPLATES_PATH . $name . $extension, $data);
    }
}