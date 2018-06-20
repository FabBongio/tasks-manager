<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;


use Application\Model\TicketTable;
use Application\Model\Ticket;	
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use RuntimeException;
use Asana\Client;


class IndexController extends AbstractActionController
{
	private $table;

	public function __construct(TicketTable $table)
	{
		$this->table = $table;
	}

	public function indexAction()
	{
		return new ViewModel();
	}

	public function userAction()
	{

	}

	public function adminAction()
	{
		return new ViewModel([
			'tickets' => $this->table->fetchAll(),

		]);
	}

	public function submitAction()
	{
		$titre = $this->getRequest()->getPost('Titre');
		$commentaire = $this->getRequest()->getPost('Commentaire');

		$data =[
			'titre' => $titre,
			'commentaire' => $commentaire
		];
		$ticket = new Ticket($data);
		$this->table->saveTicket($ticket);
	}

	public function replyAction()
	{

		$ticketId = $this->params()->fromRoute('id_ticket');
		$ticket = $this->table->getTicket($ticketId);
		$subtasks = $this->table->getSubtasks($ticketId);

		$viewModel = new ViewModel([
			'ticket' => $ticket,
			'ticketId' => $ticketId,
			'subtasks' => $subtasks
		]);
		$viewModel->setTerminal(true);

		return $viewModel;
	}
}
