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
            'custom_fields' => $this->table->getCustom('715802023054510')
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
        $message = $this->getRequest()->getPost('reply'); //Message du mail
        $emails = $this->getRequest()->getPost('emails'); //Liste des emails destinataires
        $listId = $this->getRequest()->getPost('listId'); //Liste des id des tâches à mettre en terminée
        $sujet = 'Réponse à votre ticket'; //Sujet du mail
        
        $from = 'fabbongio@gmail.com'; //email source (celle liée à $username)
        
        $username = 'fabbongio@gmail.com'; //email ou nom d'utilisateur 
        $password = ''; //et mot de passe pour te connecter à ton compte gmail
        
        $host = 'smtp.gmail.com'; //le service utilisé pour l'envoie de mail (pour gmail : smtp.gmail.com)
        $port = 465; //le port du service utilisé (pout gmail : 25 ou 465)
        

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
