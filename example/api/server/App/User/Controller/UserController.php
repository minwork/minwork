<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\User\Controller;

use Example\ApiServer\App\Main\Controller\MainController;
use Minwork\Helper\Formatter;
use Example\ApiServer\App\User\Model\User;
use Minwork\Operation\Basic\Create;
use Example\ApiServer\App\User\Validator\UserCreateValidator;
use Example\ApiServer\App\Main\Utility\JSON;
use Example\ApiServer\App\User\Validator\UserUpdateValidator;
use Minwork\Operation\Basic\Delete;
use Minwork\Http\Interfaces\ResponseInterface;
use Example\ApiServer\App\User\Operation\UpdateUserData;
use Minwork\Http\Utility\cUrl;

/**
 * Controller responsible for user CRUD operations
 *
 * @author Christopher Kalkhoff
 *        
 */
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
        if ($user->validateThenExecute(new Create(), new UserCreateValidator(), $data)) {
            return $this->respond(new JSON([
                'user' => [
                    'id' => $user->getId()
                ]
            ]));
        }
        return $this->respond((new JSON())->setErrorsStorage($user->getErrorsStorage()));
    }

    /**
     * Read user data
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function read(int $id): ResponseInterface
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
     * First email field is used for verification purposes
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
        if ($user->validateThenExecute(new UpdateUserData(), new UserUpdateValidator(), $data)) {
            return $this->respond(new JSON([
                'user' => $user->getData()
            ]));
        }
        return $this->respond((new JSON())->setErrorsStorage($user->getErrorsStorage()));
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        if (! $this->authenticate() || ! $this->checkMethod(cUrl::METHOD_DELETE)) {
            return $this->getResponse();
        }
        $id = Formatter::cleanData($id);
        $user = new User($id);
        if ($user->exists()) {
            return $this->respond(new JSON([], $user->execute(new Delete())));
        }
        return $this->triggerError(self::ERROR_USER_NOT_FOUND);
    }
}