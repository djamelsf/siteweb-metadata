<?php 
/**
 * 
 */

//require_once("lib/ObjectFileDB.php");
namespace Djs\Application;

class LivreStorageFile implements LivreStorage {

	private $db;
	private $list;

    /** ce constructeur verifie chaque fichier PDF si il a une image dèja crée, sinon il va crée son image et il le stocke
     * sur BD.
     * LivreStorageFile constructor.
     * @param $file
     */
	function __construct($file)
	{
		$this->db = new ObjectFileDB($file);
		$this->list=array();


        $mydir = 'livres/';
        $myfiles = scandir($mydir);
        $allPictures = array_diff($myfiles, array('.', '..'));
        $exif="exiftool";
        $convert = "convert";
        foreach ($allPictures as $key => $value) {
            if(file_exists("livres/._".$value)){
                $tmp="livres/._".$value;
                shell_exec("rm $tmp");
            }
            if (!file_exists('images/'.substr($value, 0, -3).'jpg')) {
                $c = $exif . " -json livres/" . $value;
                $data = shell_exec($c);
                $table = json_decode($data, true);
                $name = substr($value, 0, -3);

                if (isset($table[0]['Title'])) {
                    $livre = new Livre($table[0]['Title'], "images/" . $name . "jpg", $table,10);
                    array_push($this->list, $livre);
                    $command = $convert . " livres/" . $value . "[0] images/" . $name . "jpg";
                    exec($command);
                    $this->create($livre);
                }
            }
        }

    //$this->reinit();

    }

	public function reinit(){
		$this->deleteAll();
		foreach ($this->list as $key => $value) {
			$this->create($value);
		}
	}

	public function read($id) {
        if ($this->db->exists($id)) {
            return $this->db->fetch($id);
        } else {
            return null;
        }
    }

    public function readAll() {
        return $this->db->fetchAll();
    }

	public function create(Livre $l) {
        return $this->db->insert($l);
    }

    public function update($id, Livre $c) {
        if ($this->db->exists($id)) {
            $this->db->update($id, $c);
            return true;
        }
        return false;
    }

    public function delete($id) {
        if ($this->db->exists($id)) {
            $this->db->delete($id);
            return true;
        }
        return false;
    }

	public function deleteAll() {
        $this->db->deleteAll();
    }
}





 ?>