<?php
namespace Minwork\Storage\Object;

use Minwork\Helper\Arr;
use Minwork\Storage\Interfaces\StorageInterface;

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

    /**
     *
     * @param array $array
     *            Pointer to the array which will be used as a storage
     */
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
            if ($decoded !== false) {
                return $decoded;
            }
        }
        return $value;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::get($key)
     */
    public function get($key)
    {
        return $this->decode(Arr::getNestedElement($this->array, $key));
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::set($key, $value)
     */
    public function set($key, $value): StorageInterface
    {
        $this->array = Arr::setNestedElement($this->array, $key, $this->encode($value));
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\StorageInterface::isset($key)
     */
    public function isset($key): bool
    {
        return ! is_null(Arr::getNestedElement($this->array, $key));
    }

    /**
     *
     * {@inheritdoc}
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
        if ($this->isset($key)) {
            $var = $this->get($key);
            return is_array($var) || (is_object($var) && $var instanceof \Countable) ? count($var) : 1;
        }
        return 0;
    }
}