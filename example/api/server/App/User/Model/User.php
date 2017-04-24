<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\User\Model;

use Minwork\Basic\Model\Model;
use Minwork\Event\Traits\Connector;
use Minwork\Operation\Object\OperationEvent;
use Minwork\Helper\DateHelper;
use Example\ApiServer\App\Main\Utility\Factory;

/**
 * User model stored in database
 *
 * @author Christopher Kalkhoff
 *        
 */
class User extends Model
{
    // Used to connect model methods to events
    use Connector;
    
    // Enables connection to beforeCreate method
    const EVENT_BEFORE_CREATE = 'beforeCreate';
    
    // Enables connection to beforeUpdate method
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';

    /**
     *
     * @param int|null $id            
     */
    public function __construct($id = null)
    {
        parent::__construct(Factory::getUserStorage(), $id);
        $this->connect();
    }

    /**
     * Append created field for create operation
     *
     * @param OperationEvent $event            
     */
    public function beforeCreate(OperationEvent $event)
    {
        $arguments = $event->getArguments();
        // Data for model is first argument
        $arguments[0]['created'] = DateHelper::now();
        $event->setArguments($arguments);
    }

    /**
     * Append last_modified field for update operation
     *
     * @param OperationEvent $event            
     */
    public function beforeUpdate(OperationEvent $event)
    {
        $arguments = $event->getArguments();
        // Data for model is first argument
        $arguments[0]['last_modified'] = DateHelper::now();
        $event->setArguments($arguments);
    }
}