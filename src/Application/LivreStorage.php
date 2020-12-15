<?php
namespace Djs\Application;
/* Interface représentant un système de stockage des poèmes. */
interface LivreStorage {
	/* Renvoie l'instance de Poem correspondant à l'identifiant donné,
	 * ou null s'il n'y en a pas. */
	public function read($id);

	/* Renvoie un tableau associatif id=>poème avec tous les poèmes de la base. */
	public function readAll();

	public function create(Livre $l);
	public function delete($id);
	public function update($id, Livre $l);
	public function reinit();
}

?>
