<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Basic;

use Minwork\Error\Object\ErrorPrototype;

/**
 * Form error - error bind to specific field
 *
 * @author Christopher Kalkhoff
 *        
 */
class ErrorForm extends ErrorPrototype
{

    const TYPE = "form_error";

    /**
     * Create form error for field specified by name
     * 
     * @param string $name
     *            Field name like <i>email</i> or <i>Prefix[0][name][0]</i>
     * @param string $message
     *            Error message
     */
    public function __construct($name, $message)
    {
        parent::__construct($message);
        $this->setFieldName($name);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Object\ErrorPrototype::getType()
     */
    public function getType(): string
    {
        return self::TYPE;
    }
}