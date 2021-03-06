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
use Zend\Stdlib\Response as HttpResponse;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class IndexController extends AbstractActionController {

    /**
     * @var TicketTable
     */
    private $table;

    public function __construct(TicketTable $table) {
        $this->table = $table;
    }

    public function indexAction() {
        return new ViewModel();
    }

    public function userAction() {
        
    }

    //                      admninAction() : affiche 
    public function adminAction() {
        return new ViewModel([
            'tickets' => $this->table->fetchAll(),
            'sections' => $this->table->getSections()
        ]);
    }

    //                      submitAction() : sauvegarde le ticket
    public function submitAction() {
        $titre = $this->getRequest()->getPost('titre');
        $commentaire = $this->getRequest()->getPost('commentaire');
        $email = $this->getRequest()->getPost('email');
        $data = [
            'titre' => $titre,
            'commentaire' => $commentaire,
            'email' => $email
        ];
        $ticket = new Ticket($data);
        $this->table->saveTicket($ticket);
    }

    //                      replyAction() : affiche le formulaire de réponse au ticket
    public function replyAction() {

        if ($this->params()->fromRoute('id_ticket') !== null) {
            $ticketsId[] = $this->params()->fromRoute('id_ticket');
        } else {
            $ticketsId = $this->getRequest()->getPost('selected');
        }

        $tickets = [];
        foreach ($ticketsId as $ticketId) {
            $mainTicket = $this->table->getTicket($ticketId);
            $subtasks = $this->table->getSubtasks($ticketId);
            array_push($tickets, $mainTicket);
            foreach ($subtasks->data as $subtask) {
                array_push($tickets, $subtask);
            }
        }

        $viewModel = new ViewModel([
            'tickets' => $tickets
        ]);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    //                      sendEmail() permet d'envoyer les mails 
    public function sendEmailAction() {

        //récupération du message, des emails destinataires et des id des tâches
        $message = $this->getRequest()->getPost('reply');
        $emails = $this->getRequest()->getPost('emails');
        $listId = $this->getRequest()->getPost('listId');


        $sujet = 'Réponse à votre ticket'; //Sujet du mail
        //Connexion au compte source des mails
        $username = 'fabbongio@gmail.com';
        $password = 'Fab*3610*';

        $from = 'fabbongio@gmail.com'; //email source
        //le service utilisé pour l'envoie de mail (pour gmail : smtp.gmail.com et port 465 ou 25)
        $host = 'smtp.gmail.com';
        $port = 465;


        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->IsHTML();
        $mail->Host = 'localhost';
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SetFrom($from);

        $mail->CharSet = "utf-8";
        $mail->Subject = $sujet;
        $mail->Body = $message;
        //Emails destinataires
        foreach ($emails as $email) {
            $mail->AddAddress($email);
        }

        //Envoie du mail
        if (!$mail->Send()) {
            //Si pas envoyé
            echo 'E-mail non envoyé ';
            echo 'Mailer error:' . $mail->ErrorInfo;
        } else {
            //Si envoyé
            echo 'Message envoyé';
            foreach ($listId as $id) {
                $this->table->completeTicket($id);
            }
        }


        return new HttpResponse();
    }

}
