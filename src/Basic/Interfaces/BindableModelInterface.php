<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Interfaces;

/**
 * Interface that must be implemented by Model in order to use ModelBinder
 * 
 * @author Christopher Kalkhoff
 *        
 */
interface BindableModelInterface extends ModelInterface
{

    /**
     * Get name of the field which will store model id
     * 
     * @return string
     */
    public function getBindingFieldName(): string;
}