<?php
namespace Example\ApiServer\App\User\Operation;

use Minwork\Operation\Basic\Update;
use Minwork\Event\Traits\Connector;
use Minwork\Operation\Object\OperationEvent;

class UpdateUserData extends Update
{
    // Enables connection to beforeUpdate method
    const EVENT_BEFORE = 'beforeUpdate';
    
    // Used to connect opration methods to events
    use Connector;

    public function __construct()
    {
        parent::__construct();
        $this->connect();
    }

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