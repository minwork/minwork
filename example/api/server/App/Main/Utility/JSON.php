<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\Main\Utility;

use Minwork\Http\View\Json as JSONView;
use Minwork\Error\Traits\Errors;
use Minwork\Error\Interfaces\ErrorsStorageInterface;
use Minwork\Error\Object\Error;
use Minwork\Error\Basic\FieldError;

/**
 * Extended JSON View implementation
 *
 * @author Christopher Kalkhoff
 *        
 */
class JSON extends JSONView
{
    use Errors {
        setErrorsStorage as setErrorsTrait;
    }

    const DATA_SUCCESS = "success";

    const DATA_RESPONSE = "response";

    const DATA_ERROR = "error";

    const DATA_ERROR_GLOBAL = "global";

    const DATA_ERROR_FORM = "form";

    /**
     *
     * @param array $data            
     * @param string $success            
     */
    public function __construct(array $data = [], $success = true)
    {
        parent::__construct($data);
        $this->setSuccess($success);
    }

    /**
     * Set success flag
     * 
     * @param bool $success            
     * @return self
     */
    public function setSuccess(bool $success = true): self
    {
        $this->data[self::DATA_SUCCESS] = $success;
        return $this;
    }

    /**
     * Copy errors from provided errors storage to JSON data
     * 
     * @param ErrorsStorageInterface $errors            
     * @return self
     */
    public function setErrorsStorage(ErrorsStorageInterface $errors): self
    {
        $this->setErrorsTrait($errors);
        $this->data[self::DATA_ERROR][self::DATA_ERROR_GLOBAL] = array_map('strval', $errors->getErrors(Error::TYPE));
        $this->data[self::DATA_ERROR][self::DATA_ERROR_FORM] = array_map('strval', $errors->getErrors(FieldError::TYPE));
        $this->setSuccess(false);
        return $this;
    }

    /**
     * Set JSON data at DATA_RESPONSE key
     * 
     * @param string|array $response            
     * @return self
     */
    public function setResponse($response): self
    {
        $this->data[self::DATA_RESPONSE] = is_array($response) ? $response : strval($response);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\View\Json::getContent()
     */
    public function getContent(): string
    {
        if ($this->hasErrors()) {
            $this->setErrorsStorage($this->getErrorsStorage());
        }
        return parent::getContent();
    }
}