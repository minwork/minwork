<?php
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

class MainController extends Controller
{

    const HEADER_CONTENT_TYPE = 'Content-Type';

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

    protected function dump(RequestInterface $request, JsonResponse $response, $title = 'Response dump'): ResponseInterface
    {
        return new Response($this->show(new Webpage('main/dump', [
            'request' => $request,
            'response' => $response,
            'route' => "/{$this->getFramework()->getRouter()->getUrl()}",
        ])), Response::CONTENT_TYPE_HTML, HttpCode::OK);
    }

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