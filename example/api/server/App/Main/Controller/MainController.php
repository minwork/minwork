<?php
namespace Example\ApiServer\App\Main\Controller;

use Minwork\Basic\Controller\Controller;
use Example\ApiServer\App\Main\Utility\JSON;
use Minwork\Http\Utility\HttpCode;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Helper\ArrayHelper;

/**
 *
 * @method \Minwork\Http\Object\Response getResponse()
 */
class MainController extends Controller
{

    const ERROR_INVALID_REQUEST_METHOD = 'INVALID_REQUEST_METHOD';
    
    const ERROR_INVALID_METHOD = 'INVALID_METHOD';

    const ERROR_UNAUTHORIZED = 'UNAUTHORIZED';

    protected function triggerError(string $error, int $code = HttpCode::BAD_REQUEST): ResponseInterface
    {
        $json = new JSON();
        $json->addError($error);
        if (! HttpCode::isError($this->getResponse()->getHttpCode())) {
            $this->getResponse()->setHttpCode($code);
        }
        return $this->getResponse()->setObject($json);
    }

    protected function respond(JSON $json): ResponseInterface
    {
        if ($json->hasErrors() && ! HttpCode::isError($this->getResponse()->getHttpCode())) {
            $this->getResponse()->setHttpCode(HttpCode::BAD_REQUEST);
        }
        
        return $this->getResponse()->setObject($json);
    }

    protected function checkMethod($method): bool
    {
        if (! in_array($this->getRequest()->getMethod(), ArrayHelper::forceArray($method))) {
            $this->triggerError(self::ERROR_INVALID_REQUEST_METHOD, HttpCode::METHOD_NOT_ALLOWED);
            return false;
        }
        return true;
    }

    protected function authenticate(): bool
    {
        $headers = $this->getRequest()->getHeaders();
        if(!array_key_exists(API_AUTHORIZATION_HEADER_NAME, $headers) || $headers[API_AUTHORIZATION_HEADER_NAME] != API_AUTHORIZATION_HEADER_VALUE) {
            $this->triggerError(self::ERROR_UNAUTHORIZED, HttpCode::UNAUTHORIZED);
            return false;
        }
        return true;
    }
    
    public function show(): ResponseInterface
    {
        return $this->triggerError(self::ERROR_INVALID_METHOD);
    }
}