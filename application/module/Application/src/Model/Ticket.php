<?php 

namespace Application\Model;

class Ticket
{
	public $id;
    public $titre;
    public $commentaire;

    public function __construct(array $data = [])
    {
        $this->id     = !empty($data['id']) ? $data['id'] : null;
        $this->titre = !empty($data['titre']) ? $data['titre'] : null;
        $this->commentaire  = !empty($data['commentaire']) ? $data['commentaire'] : null;
    }

    public function exchangeArray(array $data)
    {
        $this->id     = !empty($data['id']) ? $data['id'] : null;
        $this->titre = !empty($data['titre']) ? $data['titre'] : null;
        $this->commentaire  = !empty($data['commentaire']) ? $data['commentaire'] : null;
    }

}