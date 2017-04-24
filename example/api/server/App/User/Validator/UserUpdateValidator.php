<?php
namespace Example\ApiServer\App\User\Validator;

use Minwork\Validation\Object\Validator;
use Minwork\Validation\Utility\Rule;
use Minwork\Validation\Utility\Field;

class UserUpdateValidator extends Validator
{
    public function __construct() {
        $config = [
            // If email field is not email validation will stop at this field and immidiately return error
            new Field('email', [new Rule('isNotEmpty'), new Rule('isEmail', '', [false], Rule::IMPORTANCE_CRITICAL)]),
            new Field('first_name', [new Rule('isAlphabeticOnly')], false),
            new Field('last_name', [new Rule('isAlphabeticOnly')], false),
            new Field('new_email', [new Rule('isEmail', '', [false])], false),
            new Rule([$this, 'requireOneOrMoreFields'], 'You need to specify at least one field to update'),
            new Rule([$this, 'sameEmail'], 'Specified email doesn\'t match user email'),
        ];
        parent::__construct($config);
    }
    
    public function requireOneOrMoreFields($data, $fields = ['first_name', 'last_name', 'new_email']): bool
    {
        foreach ($fields as $field)
        {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }
        return false;
    }
    
    public function sameEmail($data): bool
    {
        // Check if provided email matches current user email
        return $this->getObject()->getData('email') == $data['email'];
    }
}