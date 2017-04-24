<?php
namespace Example\ApiClient\App\Main\Utility;

use Minwork\Http\Object\Response;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Error\Traits\Errors;
use Minwork\Helper\Formatter;
use Minwork\Error\Basic\ErrorGlobal;
use Minwork\Error\Basic\ErrorForm;

class JsonResponse
{

    const FIELD_SUCCESS = 'success';

    const FIELD_ERRORS = 'error';
    use Errors;

    protected $success = false;

    protected $data = [];

    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->setResponse($response);
    }

    public function setResponse(ResponseInterface $response): self
    {
        $this->clearErrors();
        $this->data = [];
        
        if ($response->isEmpty()) {
            $this->addError("Response is empty (code: {$response->getHttpCode()})");
        } else {
            $data = json_decode($response->getContent(), true);
            if (is_null($data)) {
                $this->addError("Response content is not valid json: " . print_r($response->getContent(), true));
            } elseif (is_array($data)) {
                $data = Formatter::cleanData($data);
                
                if (array_key_exists(self::FIELD_ERRORS, $data)) {
                    if (is_array($data[self::FIELD_ERRORS])) {
                        foreach ($data[self::FIELD_ERRORS] as $errorType => $errors) {
                            if (is_array($errors)) {
                                switch ($errorType) {
                                    case ErrorForm::TYPE:
                                        foreach ($errors as $field => $error) {
                                            $this->addError($field, $error);
                                        }
                                        break;
                                    case ErrorGlobal::TYPE:
                                    default:
                                        foreach ($errors as $error) {
                                            $this->addError($error);
                                        }
                                        break;
                                }
                            } else {
                                $this->addError("Invalid errors array: " . print_r($errors, true));
                            }
                        }
                    } else {
                        $this->addError("Error field provided by errors are not array: " . print_r($data[self::FIELD_ERRORS]));
                    }
                    unset($data[self::FIELD_ERRORS]);
                }
                
                if (array_key_exists(self::FIELD_SUCCESS, $data)) {
                    if ($data[self::FIELD_SUCCESS] === false && ! $this->hasErrors()) {
                        $this->addError("Response wasn't success but no errors was provided");
                    }
                    unset($data[self::FIELD_SUCCESS]);
                }
                
                $this->data = $data;
            }
        }
        
        return $this->setSuccess(! $this->hasErrors());
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }
}