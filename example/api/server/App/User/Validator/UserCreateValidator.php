<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\User\Validator;

use Minwork\Validation\Object\Validator;
use Minwork\Validation\Utility\Rule;
use Minwork\Validation\Utility\Field;

/**
 * Validator for user model create operation
 * 
 * @author Christopher Kalkhoff
 *        
 */
class UserCreateValidator extends Validator
{

    public function __construct()
    {
        $config = [
            new Field('email', [
                new Rule('isNotEmpty'),
                new Rule('isEmail', '', [false])
            ]),
            new Field('first_name', [
                new Rule('isNotEmpty'),
                new Rule('isAlphabeticOnly')
            ]),
            new Field('last_name', [
                new Rule('isNotEmpty'),
                new Rule('isAlphabeticOnly')
            ])
        ];
        parent::__construct($config);
    }
}