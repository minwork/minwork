<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiClient\App\Main\Utility;

use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Error\Traits\Errors;
use Minwork\Helper\Formatter;
use Minwork\Error\Object\Error;
use Minwork\Error\Basic\FieldError;

/**
 * Utility class for handling cUrl Response as JSON
 *
 * @author Christopher Kalkhoff
 *        
 */
class JsonResponse
{
    use Errors;

    const FIELD_SUCCESS = 'success';

    const FIELD_ERRORS = 'error';

    /**
     * If response parsing was successful and its data flagged as success
     *
     * @var bool
     */
    protected $success = false;

    /**
     * Response data field
     *
     * @var array
     */
    protected $data = [];

    /**
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * 
     * @param ResponseInterface $response            
     */
    public function __construct(ResponseInterface $response)
    {
        $this->setResponse($response);
    }

    /**
     * Set and parse Response object
     * 
     * @param ResponseInterface $response            
     * @return self
     */
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
                                    case FieldError::TYPE:
                                        foreach ($errors as $field => $error) {
                                            $this->addError($field, $error);
                                        }
                                        break;
                                    case Error::TYPE:
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

    /**
     * If Response parsing was successful and its data flagged as success
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Set if JSON is flagged as success
     * 
     * @param bool $success            
     * @return self
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    /**
     * Get JSON data parsed from Response
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}