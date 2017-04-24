<?php
namespace Minwork\Storage\Object;

use Minwork\Storage\Interfaces\StorageInterface;
use Minwork\Helper\ArrayHelper;

/**
 * Abstract implementation of array storage
 * 
 * @author Christopher Kalkhoff
 *        
 */
class AbstractArrayStorage implements StorageInterface
{

    /**
     * Array used as storage
     * 
     * @var array
     */
    protected $array;

    public function __construct(array &$array)
    {
        $this->array = &$array;
    }

    /**
     * Encode value before write
     * 
     * @param mixed $value            
     */
    protected function encode($value)
    {
        return is_object($value) ? serialize($value) : $value;
    }

    /**
     * Decode value after read
     * 
     * @param mixed $value            
     */
    protected function decode($value)
    {
        if (is_string($value)) {
            $decoded = @unserialize($value);
            if ($decoded === false) {
                return $value;
            }
            return $decoded;
        }
        return $value;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::get($key)
     */
    public function get($key)
    {
        return $this->decode(ArrayHelper::handleElementByKeys($this->array, ArrayHelper::forceArray($key)));
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::set($key, $value)
     */
    public function set($key, $value): StorageInterface
    {
        ArrayHelper::handleElementByKeys($this->array, ArrayHelper::forceArray($key), $this->encode($value));
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::isset($key)
     */
    public function isset($key): bool
    {
        return ! is_null(ArrayHelper::handleElementByKeys($this->array, ArrayHelper::forceArray($key)));
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::unset($key)
     */
    public function unset($key): StorageInterface
    {
        if ($this->isset($key)) {
            if (is_string($key)) {
                unset($this->array[$key]);
            } elseif (is_array($key)) {
                $array = $this->array;
                $tmp = &$array;
                
                do {
                    $k = array_shift($key);
                    
                    if (! is_array($tmp)) {
                        return $this;
                    }
                    $tmp = &$tmp[$k];
                } while ((count($key) > 1));
                $k = array_shift($key);
                unset($tmp[$k]);
                $this->array = $array;
            }
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::count()
     */
    public function count($key): int
    {
        return $this->isset($key) ? 1 : 0;
    }
}