<?php
namespace Example\ApiServer\App\Main\Utility;

use Minwork\Http\View\Json as JSONView;
use Minwork\Error\Traits\Errors;
use Minwork\Error\Interfaces\ErrorsStorageInterface;
use Minwork\Error\Basic\ErrorGlobal;
use Minwork\Error\Basic\ErrorForm;

class JSON extends JSONView
{

    const DATA_SUCCESS = "success";
    const DATA_RESPONSE = "response";
    const DATA_ERROR = "error";
    const DATA_ERROR_GLOBAL = "global";
    const DATA_ERROR_FORM = "form";
    use Errors;

    public function __construct(array $data = [], $success = true)
    {
        parent::__construct($data);
        $this->setSuccess($success);
    }

    public function __toString(): string
    {
        return $this->getContent();
    }

    public function setSuccess(bool $success = true): self
    {
        $this->data[self::DATA_SUCCESS] = $success;
        return $this;
    }

    public function setErrors(ErrorsStorageInterface $errors): self
    {
        $errorsList = $errors->getErrors();
        if (array_key_exists(ErrorGlobal::TYPE, $errorsList)) {
            $this->data[self::DATA_ERROR][self::DATA_ERROR_GLOBAL] = array_map('strval', $errorsList[ErrorGlobal::TYPE]);
        }
        if (array_key_exists(ErrorForm::TYPE, $errorsList)) {
            $this->data[self::DATA_ERROR][self::DATA_ERROR_FORM] = array_map('strval', $errorsList[ErrorForm::TYPE]);
        }
        $this->setSuccess(false);
        return $this;
    }
    
    public function setResponse($response): self
    {
        $this->data[self::DATA_RESPONSE] = is_array($response) ? $response : (string) $response;
        return $this;
    }
    
    public function getContent()
    {
        if ($this->hasErrors()) {
            $this->setErrors($this->getErrors());
        }
        return parent::getContent();
    }
}