<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Interfaces;

/**
 * Interface that must be implemented by Model in order to be used by ModelBinder
 *
 * @author Christopher Kalkhoff
 *        
 */
interface BindableModelInterface
{
    /**
     * Get model id which can be either single value or an array in form of [{id_name} => {id_value}, ...]
     *
     * @return string|int|array
     */
    public function getId();
    
    /**
     * Get name of the database field which will store model id
     *
     * @return string
     */
    public function getBindingFieldName(): string;
}