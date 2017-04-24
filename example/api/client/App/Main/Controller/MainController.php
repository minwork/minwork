<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiClient\App\Main\Controller;

use Minwork\Basic\Controller\Controller;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Helper\Formatter;
use Minwork\Http\Utility\Url;
use Minwork\Http\Object\Request;
use Minwork\Http\Interfaces\RequestInterface;
use Minwork\Http\Object\Response;
use Minwork\Http\Utility\HttpCode;
use Example\ApiClient\App\Main\Utility\JsonResponse;
use Minwork\Basic\View\View;
use Minwork\Basic\Interfaces\ViewInterface;
use Example\ApiClient\App\Main\View\Webpage;

/**
 * Example of basic client application controller
 *
 * @author Christopher Kalkhoff
 *        
 */
class MainController extends Controller
{

    const HEADER_CONTENT_TYPE = 'Content-Type';

    /**
     * Execute cUrl request to url using specified http method (like GET, POST, PATCH or DELETE)<br>
     * This method appends authorization header and set content type according to application config
     *
     * @see \Minwork\Http\Object\Request
     * @see \Minwork\Http\Object\Response
     * @param RequestInterface $request            
     * @param string $method            
     * @param mixed $config
     *            Used for cUrl request configuration (see Request execute method)
     * @return ResponseInterface
     */
    protected function makeRequest(RequestInterface $request, string $method, $config = null): ResponseInterface
    {
        $url = new Url(Formatter::makeUrl(API_BASIC_URL) . '/' . $method);
        $request->setUrl($url)->appendHeader(API_AUTHORIZATION_HEADER_NAME, API_AUTHORIZATION_HEADER_VALUE);
        
        if (! empty($request->getBody())) {
            $request->appendHeader(self::HEADER_CONTENT_TYPE, API_BODY_CONTENT_TYPE);
        }
        
        $response = $request->execute($config);
        
        return $response;
    }

    /**
     * This method is used to output request and response data to view
     * 
     * @param RequestInterface $request            
     * @param JsonResponse $response            
     * @param string $title            
     * @return ResponseInterface
     */
    protected function dump(RequestInterface $request, JsonResponse $response, $title = 'Response dump'): ResponseInterface
    {
        return new Response($this->show(new Webpage('main/dump', [
            'request' => $request,
            'response' => $response,
            'route' => "/{$this->getFramework()->getRouter()->getUrl()}"
        ])), Response::CONTENT_TYPE_HTML, HttpCode::OK);
    }

    /**
     * Output specific view by enclosing it with main/index.php view
     * 
     * @param ViewInterface|string|null $view            
     * @param string|null $title            
     * @return ViewInterface
     */
    public function show($view = null, $title = null): ViewInterface
    {
        $data = [
            'content' => $view ?? 'Welcome to example of API client, please select desired action from menu above',
            'title' => $title ?? 'Welcome page',
            'links' => [
                'create' => '/user/create',
                'read' => '/user/read',
                'update' => '/user/update',
                'delete' => '/user/delete'
            ],
            'route' => "/{$this->getFramework()->getRouter()->getUrl()}"
        ];
        return new Webpage('main/index', $data);
    }
}