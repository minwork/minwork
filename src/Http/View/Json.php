<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\View;

use Minwork\Http\Object\Response;
use Minwork\Basic\Interfaces\ViewInterface;

/**
 * Basic JSON view
 *
 * @author Christopher Kalkhoff
 *        
 */
class Json implements ViewInterface
{

    /**
     * Data which will be parsed to JSON
     *
     * @var array
     */
    protected $data;

    /**
     * Create JSON view
     * @param array $data
     */
    public function __construct($data = [])
    {
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
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ViewInterface::getContent()
     */
    public function getContent(): string
    {
        return json_encode($this->data);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ViewInterface::getContentType()
     */
    public function getContentType(): string
    {
        return Response::CONTENT_TYPE_JSON;
    }

    /**
     * Set JSON array
     *
     * @param array $data            
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Merge current JSON array with specified data
     *
     * @param array $data            
     */
    public function appendData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
}