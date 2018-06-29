<?php

namespace Application\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Asana\Client;

class TicketTable {

    private $client;

    public function __construct(TableGateway $tableGateway) {
        $this->client = \Asana\Client::accessToken('0/2f8220a53869893b56151edef6523f71');
    }

    public function fetchAll() {

        $client = $this->client;
        $me = $client->users->me();
        $personalProjectsArray = array_filter($me->workspaces, function($item) {
            return $item->name === 'ilabs.fr';
        });
        $personalProjects = array_pop($personalProjectsArray);

        $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

        $param = [
            "project" => $project[0]->id,
            'opt_fields' => 'notes, name, completed, custom_fields, parent, memberships.section.name'
        ];
        return $client->tasks->findAll($param, array('iterator_type' => false));

        //          BDD
        // return $this->tableGateway->select();
    }

    public function getTicket($id) {
        $client = $this->client;
        $me = $client->users->me();
        $personalProjectsArray = array_filter($me->workspaces, function($item) {
            return $item->name === 'ilabs.fr';
        });
        $personalProjects = array_pop($personalProjectsArray);

        $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

        $param = [
            'project' => $project[0]->id,
            'opt_fields' => 'notes, name, completed, custom_fields'
        ];

        return $client->tasks->findById($id, $param, array('iterator_type' => false));
    }

    public function saveTicket(Ticket $ticket) {

        $titre = $ticket->titre;
        $commentaire = $ticket->commentaire;
        $email = $ticket->email;
        //              Asana
        $client = $this->client;
        $me = $client->users->me();
        $personalProjectsArray = array_filter($me->workspaces, function($item) {
            return $item->name === 'ilabs.fr';
        });
        $personalProjects = array_pop($personalProjectsArray);
        $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

        $param = [
            'name' => $titre,
            'notes' => $commentaire,
            "projects" => $project[0]->id
        ];
        
        $ticketCreated = $client->tasks->createInWorkspace($personalProjects->id, $param);
        
        $custom_fields = $ticketCreated->custom_fields;
        foreach ($custom_fields as $custom_field) {
            if ($custom_field->name == 'email') {
                $idEmail = $custom_field->id;
            }
        }
         $paramTicketCreated = [
                'custom_fields' => [
                    $idEmail => $email
                ]
        ];
        $client->tasks->update($ticketCreated->id, $paramTicketCreated);

    }

    public function deleteTicket($id) {
        $client = $this->client;
        $client->tasks->delete($id);
    }

    public function getSubtasks($id) {
        $client = $this->client;
        $me = $client->users->me();
        $personalProjectsArray = array_filter($me->workspaces, function($item) {
            return $item->name === 'ilabs.fr';
        });
        $personalProjects = array_pop($personalProjectsArray);
        $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

        $param = [
            "projects" => $project[0]->id,
            'opt_fields' => 'notes, name, completed, custom_fields'
        ];

        return $client->tasks->subtasks($id, $param, array('iterator_type' => false));
    }

    public function completeTicket($id) {
        //update le ticket sur asana
        $client = $this->client;
        $param = [
            "completed" => true
        ];
        return $client->tasks->update($id, $param);
    }

    public function getSections() {
        $client = $this->client;
        $me = $client->users->me();
        $personalProjectsArray = array_filter($me->workspaces, function($item) {
            return $item->name === 'ilabs.fr';
        });
        $personalProjects = array_pop($personalProjectsArray);
        $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

        return $client->projects->sections($project[0]->id, [], array('iterator_type' => false));
    }
    
    
}
