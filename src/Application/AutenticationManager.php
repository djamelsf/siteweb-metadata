<?php
namespace Djs\Application;

class AutenticationManager{
    private $users;

    function __construct(){
        $this->users = array(
            'jml' => array(
                'id' => 12,
                'nom' => 'Lecarpentier',
                'prenom' => 'Jean-Marc',
                'mdp' => 'toto',
                'statut' => 'admin'
            ),
            'alex' => array(
                'id' => 5,
                'nom' => 'Niveau',
                'prenom' => 'Alexandre',
                'mdp' => 'toto',
                'statut' => 'admin'
            ),
            'julia' => array(
                'id' => 12,
                'nom' => 'Dupont',
                'prenom' => 'Julia',
                'mdp' => 'toto',
                'statut' => 'redacteur'
            )
        );

    }

    public function verification($post){
        foreach ($this->users as $key => $value) {
            if ($value['nom']==$post['user'] && $value['mdp']==$post['password']){
                return true;
            }
        }
       return false;
    }

    public function isConnected(){
        return isset($_SESSION['user']);
    }

    public function deconnexion(){
        unset($_SESSION['user']);
    }

}
?>