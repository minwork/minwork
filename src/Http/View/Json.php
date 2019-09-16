<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\View;

use Minwork\Basic\Interfaces\ViewInterface;
use Minwork\Http\Object\Response;

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
     *
     * @param array $data            
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
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
     * Set array that will be encoded using json_encode
     *
     * @param array $data
     * @param bool $merge
     * @return Json
     */
    public function setData(array $data, bool $merge = true): self
    {
        $this->data = $merge && !is_null($this->data) ? array_merge_recursive($this->data, $data) : $data;
        return $this;
    }
}