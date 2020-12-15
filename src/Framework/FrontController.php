<?php

namespace Djs\Framework;
use Djs\Application\AutenticationManager;
use Djs\Application\LivreController;
use Djs\Application\LivreStorageFile;

class FrontController
{
    /**
     * request et response
     */
    protected $request;
    protected $response;
    protected $post;
    protected $autenticationManager;
    protected $livreStroage;

    /**
     * constructeur de la classe.
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->post=$_POST;
        $this->autenticationManager=new AutenticationManager();
        $this->livreStroage=new LivreStorageFile('bd.txt');
    }

    /**
     * méthode pour lancer le contrôleur et exécuter l'action à faire
     */
    public function execute()
    {
        $feedback = key_exists('feedback', $_SESSION) ? $_SESSION['feedback'] : '';
        $_SESSION['feedback'] = '';
    	$view = new View('Application/templates/template.php',$feedback);
   	
        // demander au Router la classe et l'action à exécuter
        $router = new Router($this->request);
        $className = $router->getControllerClassName();
        $action = $router->getControllerAction();

        // instancier le controleur de classe et exécuter l'action
        $controller = new $className($this->request, $this->response, $view,$this->autenticationManager,$this->livreStroage);
        $controller->execute($action);
        
        if ($this->request->isAjaxRequest()) {
        	$content = $view->getPart('content');
        } else {
        	$content = $view->render();
        }
        
       $this->response->send($content);
    }
}

?>