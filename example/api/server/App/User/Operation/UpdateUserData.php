<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\User\Operation;

use Minwork\Operation\Basic\Update;
use Minwork\Event\Traits\Connector;
use Minwork\Operation\Object\OperationEvent;

/**
 * User model update operation
 *
 * @author Christopher Kalkhoff
 *        
 */
class UpdateUserData extends Update
{
    // Used to connect operation methods to events
    use Connector;
    
    public function __construct()
    {
        parent::__construct();
        $this->connect();
    }

    /**
     * If neccessary rewrite new_email to email field then delete email field (used only for validation)
     *
     * @param OperationEvent $event            
     */
    public function beforeUpdate(OperationEvent $event)
    {
        // Get operation arguments
        $arguments = $event->getArguments();
        $data = reset($arguments);
        $key = key($arguments);
        
        // If nex_email field exists move its value to email field
        if (array_key_exists('new_email', $data)) {
            $data['email'] = $data['new_email'];
            unset($data['new_email']);
            $arguments[$key] = $data;
        } else { // Otherwise get rid of email field (used only for validation)
            unset($arguments[$key]['email']);
        }
        
        $event->setArguments($arguments);
    }
}