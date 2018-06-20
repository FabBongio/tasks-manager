<?php

namespace Application\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Asana\Client;

class TicketTable 
{
  private $client;

  public function __construct() {
    $this->client = \Asana\Client::accessToken('0/2f8220a53869893b56151edef6523f71');
  }

  public function fetchAll()
  {

    $client = $this->client;
    $me = $client->users->me();
    $personalProjectsArray = array_filter($me->workspaces, function($item) { return $item->name === 'ilabs.fr'; });
    $personalProjects = array_pop($personalProjectsArray);

    $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

    $param=[
      "project" => $project[0]->id,
      'opt_fields' => 'notes, name, completed, custom_fields, parent'
    ];

    return $client->tasks->findAll($param, array('iterator_type' => false));

  //          BDD
  // return $this->tableGateway->select();
  }

  public function getTicket($id)
  {
    $client = $this->client;
    $me = $client->users->me();
    $personalProjectsArray = array_filter($me->workspaces, function($item) { return $item->name === 'ilabs.fr'; });
    $personalProjects = array_pop($personalProjectsArray);

    $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

    $param=[
      'project' => $project[0]->id,
      'opt_fields' => 'notes, name, completed, custom_fields'
    ];
    
    return $client->tasks->findById($id, [], array('iterator_type' => false));
  }

  public function saveTicket(Ticket $ticket)
  {
    //              Asana
    $client = $this->client;
    $me = $client->users->me();
    $personalProjectsArray = array_filter($me->workspaces, function($item) { return $item->name === 'ilabs.fr'; });
    $personalProjects = array_pop($personalProjectsArray);
    $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

    $param =[
      'name' => $titre,
      'notes' => $commentaire,
      "projects" => $project[0]->id
    ];
    $client->tasks->createInWorkspace($personalProjects->id, $param);

    //          BDD
    // $id = (int) $ticket->id;
    // if ($id == 0) {
    // $this->tableGateway->insert($data);
    // } else {
    //     if ($this->getTicket($id)) {
    //         $this->tableGateway->update($data, array('id' => $id));
    //     } else {
    //         throw new \Exception('ticket id does not exist');
    //     }
    // }
  }

  public function deleteTicket($id)
  {
    $client->tasks->delete($id);
  }

  public function getSubtasks($id)
  {
    $client = $this->client;
    $me = $client->users->me();
    $personalProjectsArray = array_filter($me->workspaces, function($item) { return $item->name === 'ilabs.fr'; });
    $personalProjects = array_pop($personalProjectsArray);
    $project = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;

    $param =[
      "projects" => $project[0]->id,
      'opt_fields' => 'notes, name, completed'
    ];

    return $client->tasks->subtasks($id, $param, array('iterator_type' => false));
  }
}