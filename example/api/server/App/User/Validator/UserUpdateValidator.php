<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\User\Validator;

use Minwork\Error\Object\Error;
use Minwork\Validation\Object\Validator;
use Minwork\Validation\Utility\Rule;
use Minwork\Validation\Utility\Field;

/**
 * Validator for user model update operation
 *
 * @author Christopher Kalkhoff
 *        
 */
class UserUpdateValidator extends Validator
{

    public function __construct()
    {
        $config = [
            // If email field is not email validation will stop at this field and immidiately return error
            new Field('email', [new Rule('isNotEmpty'), new Rule('isEmail', null, Rule::CRITICAL, true, false)]),
            new Field('first_name', [new Rule('isAlphabeticOnly')], false),
            new Field('last_name', [new Rule('isAlphabeticOnly')], false),
            new Field('new_email', [new Rule('isEmail', '', [false])], false),
            new Rule([$this, 'requireOneOrMoreFields'], new Error('You need to specify at least one field to update')),
            new Rule([$this, 'sameEmail'], new Error('Specified email doesn\'t match user email')),
        ];
        parent::__construct($config);
    }

    /**
     * Validation method to check if $data contain at least one of specified fields
     * 
     * @param array $data            
     * @param array $fields            
     * @return bool
     */
    public function requireOneOrMoreFields(array $data, array $fields = ['first_name', 'last_name', 'new_email']): bool
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validation method to compare current model email with the one provided in $data array
     * 
     * @param array $data            
     * @return bool
     */
    public function sameEmail(array $data): bool
    {
        // Check if provided email matches current user email
        return $this->getContext()->getData('email') == $data['email'];
    }
}