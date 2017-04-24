<?php
namespace Example\ApiServer\App\User\Validator;

use Minwork\Validation\Object\Validator;
use Minwork\Validation\Utility\Rule;
use Minwork\Validation\Utility\Field;

class UserCreateValidator extends Validator
{
    public function __construct() {
        $config = [
            new Field('email', [new Rule('isNotEmpty'), new Rule('isEmail', '', [false])]),
            new Field('first_name', [new Rule('isNotEmpty'), new Rule('isAlphabeticOnly')]),
            new Field('last_name', [new Rule('isNotEmpty'), new Rule('isAlphabeticOnly')]),
        ];
        parent::__construct($config);
    }
}