<?php

namespace Djs\Application;

use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;

class LivreController
{
    protected $request;
    protected $response;
    protected $view;
    protected $autenticationManager;
    protected $livreStorage;

    public function __construct(Request $request, Response $response, View $view, AutenticationManager $autenticationManager, LivreStorage $livreStorage)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->autenticationManager = $autenticationManager;
        $this->livreStorage = $livreStorage;

        if($this->autenticationManager->isConnected()){
            $menu = array(
                "Accueil" => '.',
                "Ajouter un fichier" => '?a=ajouter',
                "Liste des fichiers" => '?a=liste',
                "Déconnexion" => '?a=deconnexion',
            );
        }else{
            $menu = array(
                "Accueil" => '.',
                "login" => '?a=login',
                "À propos" => '?a=propos',
            );
        }

        $this->view->setPart('menu', $menu);
    }

    /**
     * exécuter le contrôleur de classe pour effectuer l'action $action
     *
     * @param $action
     */
    public function execute($action)
    {
            $this->$action();

    }

    public function defaultAction()
    {
        return $this->makeHomePage();
    }

    public function ajouter()
    {
        if($this->autenticationManager->isConnected()) {
            $s = "<div class=\"file-form\"><input type=\"file\" id= 'widget-upload'>";
            $s .= "<figure class=\"file-form-item\"> <progress id=\"pb\"></progress> </figure>";
            $s .= "<figure class=\"file-form-item\">Prix :<input type='number' id=\"prix\" required> </figure>";
            $s .= "<input class='validate-btn' type=\"button\" id=\"btn_uploadfile\" 
     value=\"Charger\" 
     onclick=\"uploadFile();\" > </div>";
            $s .= "<script type=\"text/javascript\">
        function uploadFile() {

   var files = document.getElementById(\"widget-upload\").files;
   var prix = document.getElementById(\"prix\").value;

   if(files.length > 0 ){

      var formData = new FormData();
      formData.append(\"file\", files[0]);
      formData.append(\"prix\", prix);
      
      

      var xhttp = new XMLHttpRequest();

      // Set POST method and ajax file path
      xhttp.open(\"POST\", '?a=upload', true);

      // call on request changes state
      xhttp.onreadystatechange = function() {

         if (this.readyState == 4 && this.status == 200) {

           var response = this.responseText;
           console.log(response);
           if(response.charAt(0) == 1){
              document.location.href='?a=edit&livre='+response.substring(1, 17); 
              
           }else{
              alert(\"File not uploaded.\");
           }
         }
      };
      
      xhttp.upload.addEventListener('progress', function (e) {
		document.getElementById(\"pb\").value = e.loaded  / e.total;
	   });

      // Send request with data
      xhttp.send(formData);

   }else{
      alert(\"Please select a file\");
   }

}
    </script>";

            $this->view->setPart('title', 'Charger un fichier');
            $this->view->setPart('content', $s);

        }else{
            $con="<img class='img-responsive' width='500px;' style='margin-left: auto; margin-right: auto;' src='200.gif'>";
            $this->view->setPart('content', $con);
        }
    }

    public function generateMetaFacebook($table, $id, $img)
    {
        $des="";
        if(isset($table[0]['Description'])){
            $des=$table[0]['Description'];
        }else{
            $des=$table[0]['Subject'];
        }
        $s = '<!-- Facebook Open Graph Meta Tags -->';
        $s .= "<meta property=\"og:url\" content=\"?a=voir&livre=$id\">";
        $s .= "<meta property=\"og:title\" content='" . $table[0]['Title'] . "'>";
        $s .= "<meta property=\"og:type\" content='" . $table[0]['FileType'] . "'>";
        $s .= "<meta property=\"og:description\" content='" . $des . "'>";
        $s .= "<meta property=\"og:image\" content='" . $img . "'>";
        $s .= '<!-- Twitter Meta Tags -->';
        $s.= '<meta name="twitter:card" content="summary_large_image">';
        $s.='<meta property="twitter:domain" content=".">';
        $s.='<meta property="twitter:url" content="https://www.opengraph.xyz/">';
        $s.='<meta name="twitter:title" content="'.$table[0]['Title'].'">';
        $s.='<meta name="twitter:description" content="'.$des.'">';
        $s.='<meta name="twitter:image" content="$img">';

        $this->view->setPart('meta', $s);

    }

    public function login(){
        $s='';
        $s.='<form method="post" action="?a=checklogin" class="login-form">';
        $s.='<label for="name"><b>Pseudo</b></label>';
        $s.='<input type="text" name="user" required>';
        $s.='<label for="psw"><b>Mot de passe</b></label>';
        $s.='<input type="password" name="password" required>';
        $s.='<input type="submit" value="Connexion" class="validate-btn" style="background-color: black;">';
        $s.='</form>';
        $this->view->setPart("title","Connexion");
        $this->view->setPart("content",$s);
    }

    public function checklogin(){
        $b=$this->autenticationManager->verification($_POST);
        if($b){
            $_SESSION['user']=$_POST['user'];
            $this->POSTredirect(".","Bonjour Monsieur ".$_POST['user']);
        }else{
            $this->POSTredirect("?a=login","Mot de passe ou pseudo non reconnu !");
        }
    }



    public function upload()
    {
        $convert = "convert";
        $exif = "exiftool";
        if (isset($_FILES['file']['name'])) {
            // file name
            $filename = $_FILES['file']['name'];
            $name = substr($filename, 0, -3);
            // Location
            $location = 'livres/' . $filename;

            // file extension
            $file_extension = pathinfo($location, PATHINFO_EXTENSION);
            $file_extension = strtolower($file_extension);

            $response = 0;
            // Upload file
            if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
                $command = $convert . " " . $location . "[0] images/" . $name . "jpg";
                exec($command);


                $c = $exif . " -json " . $location;
                $data = shell_exec($c);
                $table = json_decode($data, true);
                if(isset($table[0]['Title'])){
                    $prix=0;
                    if(empty($_POST['prix'])){
                        $prix=10;
                    }else{
                        $prix=$_POST['prix'];
                    }


                    $livre = new Livre($table[0]['Title'], "images/" . $name . "jpg", $table,$prix);
                    $id = $this->livreStorage->create($livre);
                    $response = 1;
                }else{
                    $response=0;
                }
            }

            echo $response;
            echo $id;

            exit;
        }
    }

    public function paiementrefuse(){
        $this->POSTredirect(".","Paiement refusé !");
    }
    public function propos(){
        $content="  <div align='center'>
                <article>
                    
                    <h2>Les comptes utilisateurs</h2>
                    <ul>
                        <li>Utilisateur : <b>Niveau</b> | password : <b>toto</b></li>
                        <li>Utilisateur : <b>Lecarpentier</b> | password : <b>toto</b></li>
                    </ul>
                    <h2>Points techniques</h2>
                    <p>Afin de rendre le site réactif, nous utilisons principalement CSS et Flexbox ainsi que des concepts en cours dont on peut citer le gestionnaire de dépendances (composer), les fichiers pour le stockage des données, les packages PHP, la validation de formulaire, les retours système(feedback) et les namespaces.</p>
                    <p>Sur la page d'accueil de l'application, nous affichons tous les documents PDF sur le site Web.Une fois que l'utilisateur a cliqué sur le document, les métadonnées du document sont accessibles, et nous afficherons toutes les informations. ; Il peut également acheter des fichiers et recevoir des notifications dans son email. L'administrateur connecté (en l'occurrence l'un de nos professeurs) peut consulter, modifier et supprimer tous les documents du site. Il a aussi le droit de télecharger des documents et personaliser ses données juste avant le publier</p>
                    <h2>Compléments réalisés</h2>
                    <ul>
                        <li>upload multiple avec barre de progression</li>
                        <li> ajouter à l'application l'enregistrement des données (dans un fichier) afin que les métadonnées ne soient pas extraites des fichiers à chaque affichage </li>
                        <li>Les documents peuvent être achetés en ligne. Les utilisateurs saisissent tout simplement leur adresse e-mail, effectuent un paiement, puis reçoivent un e-mail avec une page HTML contenant ce document.</li>
                    </ul>
                    <h2>Difficultés recontrées</h2>
                    <p>Malheureusement, avec les circonstances actuelles et la charge des autres matières nous n’avons pas avancé comme nous le pensions: on voulait implementer les twig pour pouvoir dé-corréler les pages HTML du code PHP mais on a perdu du temps a nous familiariser avec le moteur et au finale on a décidé de ne pas l'utiliser.</p>
                    <h2>Conclusion</h2>
                    <p>C’est grâce à ce mini projet que nous avons eu l’opportunité de cumuler les connaissances théoriques acquises en cours avec celles de la pratique pour dévelpper un site reactif qui repond aux besoins d'un site fiable et cohérent. Afin d'améliorer notre site, nous souhaitons prochainement :</p>
                    <ul>
                        <li>Intégrer des web components qui servent a créer des balises HTML personnalisées et réutilisables afin de rendre notre template plus riche et pmus réactive.</li>
                        <li>Prévenir les attaques XSS,qui font référence à l'insertion de balises de script malveillantes et de JavaScript dans notre site Web, ce qui peut se propager a les utilisateurs qui ont consulté la page insérée. Pour éviter les attaques XSS, nous veillerons à ce que les visiteurs n'aient pas le privilège (ou l'opportunité) d'insérer des balises JavaScript ou de script n'importe où sur notre site Web..</li>
                    </ul>
                </article>
                <aside>
                    <h1>À propos des auteurs</h1>
                    <p>Le binôme est composé par &nbsp;: </p>
                    <ul style=\"list-style: none\">
                        <li>Djamel Eddine Sefsaf (<a href=\"mailto:21813169@etu.unicaen.fr\">21813169@etu.unicaen.fr</a>)</li>
                        <li>Oussama Khemim (<a href=\"mailto:21915053@etu.unicaen.fr\">21915053@etu.unicaen.fr</a>)</li>
                    </ul>

                </aside>
            </div>";
        //$content.="<p></p>";
        //$content.="</div>";
        $this->view->setPart("content",$content);
    }

    public function edit()
    {
        $id = $this->request->getGetParam('livre');
        $livre = $this->livreStorage->read($id);
        if($livre!=null && $this->autenticationManager->isConnected()) {
            $meta = $livre->getMetadata();
            $content = "<div>";

            $content .= "<form class=\"file-form\" method ='POST' action='?a=saveEdit&livre=$id'>";
            foreach ($meta[0] as $key => $value) {
                $content .= "<figure class=\"file-form-item\">";
                if (is_array($value)) {
                    $value = json_encode($value);
                    $content .= "<label for=\"\">$key</label> <input type='text' name='$key' value='$value'>";
                } else {
                    $content .= "<label for=\"\">$key</label> <input type='text' name='$key' value='$value'>";
                }
                $content .= "</figure>";
            }
            $content .= "<input class=\"validate-btn\" type='submit' value='Valider'> <br>";
            $content .= "</form> </div>";
            $this->view->setPart('content', $content);
        }else{
            $con="<img class='img-responsive' width='500px;' style='margin-left: auto; margin-right: auto;' src='200.gif'>";
            $this->view->setPart('content', $con);
        }
    }

    public function delete(){
        if($this->autenticationManager->isConnected()) {
            $id = $this->request->getGetParam('livre');
            $livre = $this->livreStorage->read($id);
            if ($livre != null) {
                $string = "<form method='post' action='?a=saveDelete&livre=$id' style='margin-right: auto; margin-left: auto;'>";
                $string .= "Etes vous sûr de supprimer ce livre " . $livre->getTitle() . " ?";
                $string .= "<br> Oui <input type='radio' name='ouiNon' value='oui'> <br>";
                $string .= " Non <input type='radio' name='ouiNon' value='non'> <br>";
                $string .= "<input class='validate-btn' type='submit' value='confirmer'> <br>";
                $string .= "</form>";
                $this->view->setPart('content', $string);
            } else {
                $this->POSTredirect(".", "Livre non existant");
            }
        }else{
            $con="<img class='img-responsive' width='500px;' style='margin-left: auto; margin-right: auto;' src='200.gif'>";
            $this->view->setPart('content', $con);
        }
    }

    public function saveDelete(){
        if(isset($_POST['ouiNon'])){
        $id = $this->request->getGetParam('livre');
        $livre = $this->livreStorage->read($id);
        if($_POST['ouiNon']=='oui'){
            $del="rm ".$livre->getMetaData()[0]['SourceFile'];
            shell_exec($del);
            $this->livreStorage->delete($id);
            $this->POSTredirect(".","Livre supprimé");
        }else{
            $this->POSTredirect("?a=voir&livre=$id","Livre non supprimé");
        }
        }else{
            $this->POSTredirect(".","Suppresion ignorée");
        }
    }

    public function saveEdit()
    {
        $exif = "exiftool";
        $id = $this->request->getGetParam('livre');
        $array = array();
        $array[0] = $_POST;
        $l=$this->livreStorage->read($id);

        $livre = new Livre($_POST['Title'], $l->getImage(), $array,$l->getPrix());
        $a = $this->livreStorage->update($id, $livre);

        $c=$exif." -all= -overwrite_original ".$_POST['SourceFile'];

        shell_exec($c);


        //echo "exiftool -json=".$tmpfname." /Applications/MAMP/htdocs/dm2020/".$_POST['SourceFile'];
        $cmd=$exif." ";
        //shell_exec("exiftool -json= /Applications/MAMP/htdocs/dm2020/".$_POST['SourceFile']);
        foreach($_POST as $key => $value){
            $cmd.="-XMP:$key=\"$value\" ";
        }
        $cmd.=" ".$_POST['SourceFile'];

        //echo $cmd;
        shell_exec($cmd);

        shell_exec("rm ".$_POST['SourceFile']."_original");



        shell_exec($exif.' -delete_original! /livres');


        $this->POSTredirect('?a=voir&livre='.$id, "Le livre a bien été modifié !");

    }

    public function voir()
    {
        $id = $this->request->getGetParam('livre');
        $livre = $this->livreStorage->read($id);
        if($livre!=null) {
            $title = $livre->getTitle();
            $image = $livre->getImage();
            $meta = $livre->getMetadata();

            $this->generateMetaFacebook($meta, $id, $image);
            $content = "<figure class='img-header'>";
            $content .= "<img src='$image' alt='$title' class='img-responsive'>";
            $content .= "<div> <a class='more-info' href='?a=emailpaiement&livre=$id'>Acheter</a> <br> <br> <br>   <p> Prix : " . $livre->getPrix() . " € </p></div>";
            $content .= "</figure>";
            $content .= "<section class=\"dashboard extra-dashboard\">";
            foreach ($meta[0] as $key => $value) {
                if (is_array($value)) {
                    $val = json_encode($value);
                    $content .= "<figure class=\"dashboard-items\">";
                    $content .= "<p class=\"title\">" . $key . "</p>";
                    $content .= "<p class=\"d-author\">" . $val . "</p>";
                    $content .= "</figure>";
                } else {
                    $content .= "<figure class=\"dashboard-items\">";
                    $content .= "<p class=\"title\">" . $key . "</p>";
                    $content .= "<p class=\"d-author\">" . $value . "</p>";
                    $content .= "</figure>";
                }
            }
            $content .= "</section>";
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->POSTredirect(".","livre non trouvé");
        }

    }
    


    public function unknownLivre()
    {

        $content = "Erreur livre non trouvé.";
        $this->view->setPart('content', $content);
    }

    public function makeGalerie()
    {
        //$this->livreStorage->reinit();
        $db = $this->livreStorage->readAll();
        $con = "<section class=\"document-grid\">";
        foreach ($db as $key => $value) {
            $con .= "<figure class=\"document-item\">";
            $con.="<a href=\"?a=voir&amp;livre=$key\">";
            $con .= "<img src='" . $value->getImage() . "' alt='".$value->getTitle()."' class=\"img-responsive\">";
            $con .="</a>";
            $con .="</figure>";
        }
        $con .= "</figure>";
        return $con;
    }

    public function deconnexion(){
        $this->autenticationManager->deconnexion();
        $this->POSTredirect(".","Au revoir");
    }

    public function liste(){
        if($this->autenticationManager->isConnected()) {
            $db = $this->livreStorage->readAll();
            $con = "<section class=\"dashboard\">";
            $con .= "<figure class=\"dashboard-titles\">
        <p class='title'>Titre</p>
        <p class='edit'>Modifier</p>
        <p class='delete'>Suprrimer</p>
        </figure>";
            foreach ($db as $key => $value) {
                $con .= "<figure class=\"dashboard-items\">";
                $con .= "<p class='title'>" . $value->getTitle() . " </p> <p class='edit'><a href='?a=edit&amp;livre=$key'> <i class='fas fa-edit'></i> </a> </p> <p class='delete'> <a href='?a=delete&amp;livre=$key'> <i class='fas fa-trash-alt'></i> </a> </p> ";
                $con .= "</figure>";
            }
            $con .= "</section>";
            $this->view->setPart('title', "Liste des fichiers");
            $this->view->setPart('content', $con);
        }else{
            $con="<img class='img-responsive' width='500px;' style='margin-left: auto; margin-right: auto;' src='200.gif'>";
            $this->view->setPart('content', $con);
        }
    }

    public function paiementaccept(){
        $tab=exec("/users/21813169/www-dev/dm2020/src/Application/Sherlocks/bin/static/response message=".$_POST['DATA']." pathfile=/users/21813169/www-dev/dm2020/src/Application/Sherlocks/param_demo/pathfile");

        $tab=explode("!", $tab);
        //var_dump($tab);

        $email=$tab[28];
        $prix=$tab[5]/100;
        $date=$tab[10];
        $heure=$tab[9];
        $order_id = $tab[27];
        $header="MIME-Version: 1.0\r\n";
        $header.='From:"metaDocs"<supoprt@metadocs.fr>'."\n";
        $header.='Content-Type:text/html; charset="uft-8"'."\n";
        $header.='Content-Transfer-Encoding: 8bit';

        $message='
        <html>
        <body>
        <p>metaDocs facture</p>';
        $message.='<p> Numero facture : '.$order_id.' </p> <br>';
        $message.='<p> Prix : '.$prix.' </p> <br>';
        $message.='<p> Date d achat : '.$date.' </p> <br>';
        $message.='<p> heure d achat : '.$heure.' </p>';
        $message.='</body> </html>';

        mail($email, "Facture d'achat metaDocs", $message, $header);

        $this->POSTredirect(".","Achat effectué avec succès");
    }

    public function emailpaiement(){
        $id = $this->request->getGetParam('livre');
        $livre=$this->livreStorage->read($id);
        $c='<figure class="img-header">';
        $c.='<img class="img-responsive" src="'.$livre->getImage().'" alt="'.$livre->getTitle().'">';
        $c.="<div> <p>Veuillez saisir une adresse e-mail valide, afin de recevoir votre facture d'achat.</p> <br>";
        $c.='<form method="post" action="?a=paiement&livre='.$id.'">';
        $c.='Email : <input type="email" name="email" required>';
        $c.='<input class="more-info" type="submit" value="Valider">';
        $c.='</form> </div>';
        $c.='</figure>';

        $this->view->setPart("content",$c);

    }

    public function paiement(){
        $id = $this->request->getGetParam('livre');
        $livre=$this->livreStorage->read($id);

        $a="amount=".$livre->getPrix()*(100)." ";
        $a1="merchant_id=014295303911111 ";
        $a2="merchant_country=fr ";
        $a3="currency_code=978 ";
        $a4="pathfile=/users/21813169/www-dev/dm2020/src/Application/Sherlocks/param_demo/pathfile ";
        $a30="transaction_id=".rand(0,1000)." ";
        $a5="normal_return_url= ";
        $a6="cancel_return_url= ";
        $a7="automatic_response_url= ";
        $a8="language=fr ";
        $a9="payment_means=CB,2,VISA,2,MASTERCARD,2 ";
        $a10="header_flag=no ";
        $a11="capture_day= ";
        $a12="capture_mode= ";
        $a13="background_id= ";
        $a14="bgcolor= ";
        $a15="block_align= ";
        $a16="block_order= ";
        $a17="textcolor= ";
        $a18="textfont= ";
        $a19="templatefile= ";
        $a20="logo_id= ";
        $a21="receipt_complement= ";
        $a22="caddie= ";
        $a23="customer_id= ";
        $a24="customer_email=".$_POST['email']." ";
        $a25="customer_ip_address= ";
        $a26="data= ";
        $a27="return_context= ";
        $a28="target= ";
        $a29="order_id=".rand(0,1000);
        $string=$a.$a1.$a2.$a3.$a4.$a30.$a5.$a6.$a7.$a8.$a9.$a10.$a11.$a12.$a13.$a14.$a15.$a16.$a17.$a18.$a19.$a20.$a21.$a22.$a23.$a24.$a25.$a26.$a27.$a28.$a29;
        $res=exec("/users/21813169/www-dev/dm2020/src/Application/Sherlocks/bin/static/request ".$string);
        //echo $res;
        $res= substr($res, 4, -1);

        $c="<figure class='img-header'>";
        $c.='<img class="img-responsive" src="'.$livre->getImage().'" alt="'.$livre->getTitle().'">';
        $c.="<div> <p> Veuillez choisir le mode de paiement qui vous convient.</p> <br> <br>".$res."</div>";
        $c.="</figure>";



        $this->view->setPart("content",$c);
        //echo htmlentities($res);
    }

    public function POSTredirect($url, $feedback){
        $_SESSION['feedback'] = $feedback;
        header("Location: ".htmlspecialchars_decode($url), true, 303);
        die;
    }


    public function makeHomePage()
    {
        $title = "Bienvenue !";
        $content = $this->makeGalerie();
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

}

?>