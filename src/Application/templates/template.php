<!DOCTYPE html>
<html lang="fr">
<head>
	<title>MetaDocs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
          integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA=="
          crossorigin="anonymous" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700&display=swap" rel="stylesheet">
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="skin/style.css" />

    <?php echo $meta; ?>

</head>
<body>
	<nav class="menu">
		<div id="navbar" class="container">
            <figure class="navbar-logos">
                <img src="logo.png" class="nav-logo img-responsive" alt="logo">
                <a href="" class="ham-icon"><i class="fas fa-bars"></i></a>
            </figure>
            <figure class="navbar-items">
<?php
	foreach ($le_menu as $text => $link) {
		echo "<a href=\"$link\">$text</a>";
	}
?>
            </figure>
		</div>
	</nav>
    <div class="main-section-container container">
<!--        <p class='feedback'> --><?php //echo $feedback; ?><!-- </p>-->
        <?php if ($feedback!=''){
            echo "<p class='feedback'> ".$feedback." </p>";
        }?>
        <figure class="section-header">
            <h1><?php echo $le_titre; ?></h1>
        </figure>
		<?php echo $le_contenu; ?>
	</div>
</body>
</html>

