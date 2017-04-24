<?php
namespace Example\ApiClient\App\User\Controller;

use Example\ApiClient\App\Main\Controller\MainController;
use Minwork\Http\Object\Request;
use Minwork\Basic\Controller\Controller;
use Example\ApiClient\App\Main\Utility\JsonResponse;
use Minwork\Helper\ArrayHelper;
use Minwork\Http\Utility\cUrl;
use Example\ApiClient\App\Main\View\Webpage;
use Minwork\Helper\Formatter;
use Minwork\Storage\Basic\Session;
use Minwork\Event\Traits\Connector;
use Minwork\Core\Framework;
use Minwork\Basic\Interfaces\ControllerInterface;
use Minwork\Basic\Interfaces\FrameworkInterface;

class UserController extends MainController
{
    use Connector;

    const SESSION_USER_ID = 'user_id';

    const DEFAULT_USER_DATA = [
        'email' => 'test@test.com',
        'first_name' => 'Test',
        'last_name' => 'Test'
    ];

    const DEFAULT_USER_UPDATE_DATA = [
        'email' => 'test@test.com',
        'new_email' => 'test2@test.com',
        'last_name' => 'Testing'
    ];

    protected $storage;

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Controller\Controller::setFramework()
     *
     */
    public function setFramework(FrameworkInterface $framework): ControllerInterface
    {
        parent::setFramework($framework);
        // As connector you could also specify Framework full name (with namespace) because it contains constants with event names.
        // Another way would be to copy event name into this controller constants and provide either '$this' or 'null' as connector argument
        $this->connect([
            Framework::EVENT_BEFORE_CONTROLLER_RUN
        ], $this->getFramework()
            ->getEventDispatcher());
        
        return $this;
    }

    public function beforeRun()
    {
        $this->storage = new Session();
    }

    private function getUserId($data, $key = 'id')
    {
        return array_key_exists($key, $data) ? intval($data[$key]) : $this->storage->get(self::SESSION_USER_ID);
    }

    public function create()
    {
        if (array_key_exists('User', $_POST)) {
            $request = new Request();
            $response = new JsonResponse($this->makeRequest($request->setBody(Formatter::cleanData($_POST['User'])), 'user/create'));
            if ($response->isSuccess()) {
                $data = $response->getData();
                $userId = ArrayHelper::handleElementByKeys($data, [
                    'user',
                    'id'
                ]);
                if (is_null($userId)) {
                    $response->addError('No user id provided');
                } else {
                    $this->storage->set(self::SESSION_USER_ID, $userId);
                }
            }
            return $this->dump($request, $response, 'Create user - response');
        }
        
        return ($this->show(new Webpage('user/create', [
            'form' => [
                'action' => "/{$this->getFramework()->getRouter()->getUrl()}",
                'data' => self::DEFAULT_USER_DATA
            ]
        ]), 'Create user'));
    }

    public function read()
    {
        if (array_key_exists('User', $_POST)) {
            $request = new Request();
            return $this->dump($request->setMethod(cUrl::METHOD_GET), new JsonResponse($this->makeRequest($request, "user/read/{$this->getUserId($_POST)}")), 'Read user - response');
        }
        return ($this->show(new Webpage('user/read', [
            'form' => [
                'action' => "/{$this->getFramework()->getRouter()->getUrl()}",
                'data' => [
                    'id' => $this->storage->get(self::SESSION_USER_ID)
                ]
            ]
        ]), 'Read user'));
    }

    public function update()
    {
        if (array_key_exists('User', $_POST)) {
            $request = new Request();
            $body = Formatter::cleanData($_POST['User']);
            return $this->dump($request->setMethod(cUrl::METHOD_PATCH)
                ->setBody(array_filter($body)), new JsonResponse($this->makeRequest($request, "user/update/{$this->getUserId($body)}")));
        }
        
        return ($this->show(new Webpage('user/update', [
            'form' => [
                'action' => "/{$this->getFramework()->getRouter()->getUrl()}",
                'data' => self::DEFAULT_USER_UPDATE_DATA + [
                    'id' => $this->storage->get(self::SESSION_USER_ID)
                ]
            ]
        ]), 'Create user'));
    }

    public function delete()
    {
        if (array_key_exists('User', $_POST)) {
            $request = new Request();
            return $this->dump($request->setMethod(cUrl::METHOD_DELETE), new JsonResponse($this->makeRequest($request, "user/delete/{$this->getUserId($_POST)}")), 'Delete user - response');
        }
        return ($this->show(new Webpage('user/delete', [
            'form' => [
                'action' => "/{$this->getFramework()->getRouter()->getUrl()}",
                'data' => [
                    'id' => $this->storage->get(self::SESSION_USER_ID)
                ]
            ]
        ]), 'Delete user'));
    }
}