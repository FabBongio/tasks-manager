<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent as MvcEvent;
use Zend\EventManager\EventInterface as EventInterface;

class Module implements ConfigProviderInterface {

    const VERSION = '3.0.2dev';

    public function getConfig() {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig() {
        return [
            'factories' => [
                Model\TicketTable::class => function($container) {
                    $tableGateway = $container->get(Model\TicketTableGateway::class);
                    return new Model\TicketTable($tableGateway);
                },
                Model\TicketTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Ticket());
                    return new TableGateway('ticket', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    public function getControllerConfig() {
        return [
            'factories' => [
                Controller\IndexController::class => function($container) {
                    return new Controller\IndexController(
                            $container->get(Model\TicketTable::class)
                    );
                },
            ],
        ];
    }

    function onBootstrap(EventInterface $e) {
        $application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), 100);
    }

    function onDispatchError(MvcEvent $e) {
        $viewModel = $e->getViewModel();
        $viewModel->setTemplate('error/index.phtml');
    }

}
