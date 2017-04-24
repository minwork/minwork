<?php
namespace Example\ApiServer\App\User\Model;

use Minwork\Basic\Model\Model;
use Minwork\Event\Traits\Connector;
use Minwork\Operation\Object\OperationEvent;
use Minwork\Helper\DateHelper;
use Example\ApiServer\App\Main\Utility\Factory;

class User extends Model
{
    // Enables connection to beforeCreate method
    const EVENT_BEFORE_CREATE = 'beforeCreate';
    
    // Enables connection to beforeUpdate method
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    
    // Used to connect model methods to events
    use Connector;

    /**
     *
     * @param int $id            
     */
    public function __construct($id = null)
    {
        parent::__construct(Factory::getUserStorage(), $id);
        $this->connect();
    }
    
    // Append created field for create operation
    public function beforeCreate(OperationEvent $event)
    {
        $arguments = $event->getArguments();
        $arguments[0]['created'] = DateHelper::now();
        $event->setArguments($arguments);
    }
    
    // Append last_modified field for update operation
    public function beforeUpdate(OperationEvent $event)
    {
        $arguments = $event->getArguments();
        $arguments[0]['last_modified'] = DateHelper::now();
        $event->setArguments($arguments);
    }
}