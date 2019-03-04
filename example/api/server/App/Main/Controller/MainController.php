<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\Main\Controller;

use Minwork\Basic\Controller\Controller;
use Example\ApiServer\App\Main\Utility\JSON;
use Minwork\Http\Utility\HttpCode;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Helper\Arr;

/**
 * Basic controller providing usefull methods for all derivatives
 *
 * @author Christopher Kalkhoff
 * @method \Minwork\Http\Object\Response getResponse()
 */
class MainController extends Controller
{

    const ERROR_INVALID_REQUEST_METHOD = 'INVALID_REQUEST_METHOD';

    const ERROR_INVALID_METHOD = 'INVALID_METHOD';

    const ERROR_UNAUTHORIZED = 'UNAUTHORIZED';

    /**
     * Set response as JSON containing provided error
     *
     * @param string $error
     *            Error message
     * @param int $code
     *            Http code
     * @return ResponseInterface
     */
    protected function triggerError(string $error, int $code = HttpCode::BAD_REQUEST): ResponseInterface
    {
        $json = new JSON();
        $json->addError($error);
        if (! HttpCode::isError($this->getResponse()->getHttpCode())) {
            $this->getResponse()->setHttpCode($code);
        }
        return $this->getResponse()->setObject($json);
    }

    /**
     * Set response based on provided JSON object
     *
     * @param JSON $json            
     * @return ResponseInterface
     */
    protected function respond(JSON $json): ResponseInterface
    {
        if ($json->hasErrors() && ! HttpCode::isError($this->getResponse()->getHttpCode())) {
            $this->getResponse()->setHttpCode(HttpCode::BAD_REQUEST);
        }
        
        return $this->getResponse()->setObject($json);
    }

    /**
     * Verify that request was made using provided http method(s)
     *
     * @param string|array $method            
     * @return bool
     */
    protected function checkMethod($method): bool
    {
        if (! in_array($this->getRequest()->getMethod(), Arr::forceArray($method))) {
            $this->triggerError(self::ERROR_INVALID_REQUEST_METHOD, HttpCode::METHOD_NOT_ALLOWED);
            return false;
        }
        return true;
    }

    /**
     * Authenticate request assuring that it contain header defined by config constants<br>
     * If authentication fails, this method call triggerError
     *
     * @see \Example\ApiServer\App\Main\Controller\MainController::triggerError()
     *
     * @return bool
     */
    protected function authenticate(): bool
    {
        $headers = $this->getRequest()->getHeaders();
        if (! array_key_exists(API_AUTHORIZATION_HEADER_NAME, $headers) || $headers[API_AUTHORIZATION_HEADER_NAME] != API_AUTHORIZATION_HEADER_VALUE) {
            $this->triggerError(self::ERROR_UNAUTHORIZED, HttpCode::UNAUTHORIZED);
            return false;
        }
        return true;
    }

    /**
     * Default controller method - called when requesting malformed url address
     *
     * @return ResponseInterface
     */
    public function show(): ResponseInterface
    {
        return $this->triggerError(self::ERROR_INVALID_METHOD);
    }
}