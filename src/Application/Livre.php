<?php
namespace Djs\Application;
/* Représente un livre. */
class Livre {

	protected $title;
	protected $image;
	protected $metadata;
	protected $prix;

	public function __construct($title, $image,$metadata,$prix) {
		$this->title = $title;
		$this->image = $image;
		$this->metadata = $metadata;
		$this->prix=$prix;
	}

	public function getTitle() {
		return $this->title;
	}


	public function getImage() {
		return $this->image;
	}



	public function getMetadata() {
		return $this->metadata;
	}

	public function getPrix(){
		return $this->prix;
	}

}

?>
