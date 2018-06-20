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
use Zend\Mail\Message as MailMessage;
use Zend\Mail\Transport\Sendmail as MailSender;

class IndexController extends AbstractActionController {

    private $table;

    public function __construct(TicketTable $table) {
        $this->table = $table;
    }

    public function indexAction() {
        return new ViewModel();
    }

    public function userAction() {
        
    }

    public function adminAction() {
        return new ViewModel([
            'tickets' => $this->table->fetchAll(),
        ]);
    }

    public function submitAction() {
        $titre = $this->getRequest()->getPost('Titre');
        $commentaire = $this->getRequest()->getPost('Commentaire');
        $email = $this->getRequest()->getPost('Email');

        $data = [
            'titre' => $titre,
            'commentaire' => $commentaire,
            'email' => $email
        ];
        $ticket = new Ticket($data);
        $this->table->saveTicket($ticket);
    }

    public function replyAction() {

        $ticketId = $this->params()->fromRoute('id_ticket');
        $mainTicket = $this->table->getTicket($ticketId);
        $subtasks = $this->table->getSubtasks($ticketId);

        $tickets = $subtasks->data;
        array_unshift($tickets, $mainTicket);

//        $tickets = array_filter($tickets, function($t) {
//            return !empty(array_filter($t->custom_fields, function($field) {
//               return ($field->name == "email" && !empty($field->text_value));
//            }));
//        });

        $viewModel = new ViewModel([
            'main_ticket' => $mainTicket,
            'tickets' => $tickets,
        ]);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function sendEmailAction() {
        $reponse = $this->getRequest()->getPost('reply');
        $emails = $this->getRequest()->getPost('emails');

        $mail = new MailMessage();
        $mail->setBody($reponse);
        
        /*Email source*/
        $mail->setFrom('fabrice.bongio@gmail.com');
        
        /*Emails destinataires*/
        foreach ($emails as $email) {
            $mail->addTo($email);
        }
        /*Objet des mails*/
        $mail->setSubject('Réponse à votre ticket');

        $transport = new Mail\Transport\Sendmail();
        $transport->send($mail);
    }

}
