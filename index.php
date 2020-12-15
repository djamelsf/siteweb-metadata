<?php

namespace Djs\Framework;
set_include_path("./src");

session_start();


require ("autoload.php");




/* Cette page est simplement le point d'arrivée de l'internaute
 * sur notre site. On se contente de lancer le FrontController.
 *
 */

$server = $_SERVER;



// simuler une requête AJAX
//$server['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
$request = new Request($_GET, $_POST, $_FILES, $server,$_SESSION);
$response = new Response();
$router = new FrontController($request, $response);
$router->execute();

?>