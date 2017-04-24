<?php
namespace Example\ApiServer\App\User\Controller;

use Example\ApiServer\App\Main\Controller\MainController;
use Minwork\Helper\Formatter;
use Example\ApiServer\App\User\Model\User;
use Minwork\Operation\Basic\Create;
use Example\ApiServer\App\User\Validator\UserCreateValidator;
use Example\ApiServer\App\Main\Utility\JSON;
use Example\ApiServer\App\User\Validator\UserUpdateValidator;
use Minwork\Operation\Basic\Update;
use Minwork\Operation\Basic\Delete;
use Minwork\Http\Interfaces\ResponseInterface;
use Example\ApiServer\App\User\Operation\UpdateUserData;
use Minwork\Http\Utility\cUrl;

class UserController extends MainController
{

    const ERROR_USER_NOT_FOUND = 'USER_NOT_FOUND';

    /**
     * Create user
     * 
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        if (! $this->authenticate() || ! $this->checkMethod(cUrl::METHOD_POST)) {
            return $this->getResponse();
        }
        $data = Formatter::cleanData($this->getRequest()->getBody());
        $user = new User();
        if ($user->execute(new Create(), [
            $data
        ], new UserCreateValidator())) {
            return $this->respond(new JSON([
                'user' => [
                    'id' => $user->getId()
                ]
            ]));
        }
        return $this->respond((new JSON())->setErrors($user->getErrors()));
    }

    /**
     * Read user data
     * 
     * @param int $id            
     * @return ResponseInterface
     */
    public function read($id): ResponseInterface
    {
        if (! $this->authenticate() || ! $this->checkMethod(cUrl::METHOD_GET)) {
            return $this->getResponse();
        }
        $id = Formatter::cleanData($id);
        $user = new User($id);
        if ($user->exists()) {
            return $this->respond(new JSON([
                'user' => $user->getData()
            ]));
        }
        return $this->triggerError(self::ERROR_USER_NOT_FOUND);
    }

    /**
     * Update user data (email, first_name, last_name, new_email -> email)<br>
     * First email field is used only for verification purposes
     *
     * @param int $id            
     * @return ResponseInterface
     */
    public function update(int $id): ResponseInterface
    {
        if (! $this->authenticate() || ! $this->checkMethod(cUrl::METHOD_PATCH)) {
            return $this->getResponse();
        }
        $data = Formatter::cleanData($this->getRequest()->getBody());
        $user = new User($id);
        if (! $user->exists()) {
            return $this->triggerError(self::ERROR_USER_NOT_FOUND);
        }
        if ($user->execute(new UpdateUserData(), [
            $data
        ], new UserUpdateValidator())) {
            return $this->respond(new JSON([
                'user' => $user->getData()
            ]));
        }
        return $this->respond((new JSON())->setErrors($user->getErrors()));
    }

    /**
     * Delete user
     * 
     * @param int $id            
     * @return ResponseInterface
     */
    public function delete($id): ResponseInterface
    {
        if (! $this->authenticate() || ! $this->checkMethod(cUrl::METHOD_DELETE)) {
            return $this->getResponse();
        }
        $id = Formatter::cleanData($id);
        $user = new User($id);
        if ($user->exists()) {
            return $this->respond(new JSON([], $user->executeOperation(new Delete(), [])));
        }
        return $this->triggerError(self::ERROR_USER_NOT_FOUND);
    }
}