#siteweb-metaDocs

L'objectif de ce projet est de réaliser un catalogue de fichiers PDF (documentations, livres, etc) au format PDF.

La page d'accueil affiche les fichiers présents dans un dossier livres avec leur titre et une éventuelle image. Pour chaque fichier, un lien envoie vers la page de détails du fichier.

Partie publique :

    La page de détails affiche les informations sur le fichier en faisant une extraction des métadonnées qu'il contient (extraites avec exiftool). On peut aussi afficher une capture de la première page du PDF (convert sait faire ça) Le code HTML de la page du livre inclut les Microdata, les données Open Graph et Twitter Cards qui le décrivent.

Partie à accès restreint :

    L'utilisateur connecté a la possibilité d'ajouter un fichier par upload réalisé en AJAX. Les métadonnées du fichier sont extraites de celle-ci et proposées dans un formulaire pour modification et/ou validation. Lors de l'enregistrement des informations du formulaire, les données saisies ainsi sont alors inscrites dans le fichier PDF pour s'assurer de la cohérence des données.

Paiement electronique :

    un fichier peut être acheté en ligne. L'utilisateur saisit simplement son adresse email, effectue le paiement et ensuite reçoit par mail sa facture d'achat.
