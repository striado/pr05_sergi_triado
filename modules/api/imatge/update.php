<?php
/**
*
* @author: Sergi Triadó <s.triado@sapalomera.cat>
*
*/
require_once "../../config/database.php";
require_once "../../control/image_manager.php";
require_once "../../control/control_usuaris.php";
require_once "../../../model/imatge.php";
require_once "../../../model/user.php";

// $data = json_decode(file_get_contents("php://input"));

if (!empty($_POST['id']) && !empty($_POST['newPath'])) {
    $result = ImageManager::update_image($_POST['id'], $_POST['newPath']);

    if ($result) {
        $missatge = array('success' => true);
    } else {
        $missatge = array('error' => true);
    }
}

echo json_encode($missatge);
?>