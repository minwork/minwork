<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\View;

use Minwork\Basic\Interfaces\ViewInterface;
use Minwork\Helper\Formatter;
use Minwork\Http\Interfaces\ResponseInterface;
use Throwable;

/**
 * Basic view used in response object as content storage
 *
 * @author Christopher Kalkhoff
 *        
 */
class View implements ViewInterface
{

    /**
     * Template file path
     *
     * @var string
     */
    protected $filepath;

    /**
     * Data that can be accessed in view using $data variable
     *
     * @var array
     */
    protected $data;

    /**
     * View content in form of file parsed to string
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @param string $filepath            
     * @param array $data            
     */
    public function __construct(string $filepath, array $data = [])
    {
        $this->filepath = $filepath;
        $this->data = $data;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ViewInterface::__toString()
     */
    public function __toString(): string
    {
        return $this->getContent();
    }

    /**
     * Get content of supplied file in context of data
     *
     * @return mixed
     */
    protected function getFileContent()
    {
        if (empty($this->content)) {
            if (! file_exists($this->filepath)) {
                trigger_error('File does not exists: ' . Formatter::cleanString($this->filepath), E_USER_WARNING);
                return '';
            }

            /** @noinspection PhpUnusedLocalVariableInspection */
            $data = $this->data;
            ob_start();
            
            try {
                include ($this->filepath);
                $this->content = ob_get_contents();
            } catch (Throwable $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
                return '';
            }
            
            ob_end_clean();
        }
        
        return $this->content;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ViewInterface::getContent()
     */
    public function getContent()
    {
        return $this->getFileContent();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ViewInterface::getContentType()
     */
    public function getContentType(): string
    {
        return ResponseInterface::CONTENT_TYPE_HTML;
    }
}