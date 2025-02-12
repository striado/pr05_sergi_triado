<?php
/**
*
* @author: Sergi Triadó <s.triado@sapalomera.cat>
*
*/
class ImageManager {
    private static $llista_imatges;

    //GETTER
    public static function getImatges(){
        return self::$llista_imatges;
    }

    /**
     * Upload Image
     *
     * @return boolean
     * 
     * Métode que crea una nova imatge a la BBDD
     */
    public static function upload_image($path, $username){
        $user = ControlUsuaris::get_usuari($username);

        $imatge = new Imatge($path, $user->getId());

        return $imatge->create();
    }

    /**
    * Read Images
    *
    * @return array
    * 
    * Métode que llegeix totes les imatges amb paginació
    */
    public static function read_images($offset, $row_count){
        self::$llista_imatges = array();

        $query = "SELECT i.id, i.path, u.username FROM imatges i LEFT JOIN usuaris u ON i.user = u.id LIMIT :offset, :row_count";
        $params = array(':offset' => $offset, ':row_count' => $row_count);

        Connexio::connect();
        $stmt = Connexio::execute_int_params($query, $params);

        $result = $stmt->fetchAll();

        $num = count($result);

        if ($num > 0) {
            foreach ($result as $row) {
                extract($row);
                    
                $imatge = new Imatge($row['path'], $row['username'], $row['id']);

                array_push(self::$llista_imatges, $imatge);
            }
        }
        Connexio::close();
    }

    /**
    * Read images by user
    *
    * @return array
    * 
    * Métode que llegeix totes les imatges d'un usuari amb paginació
    */
    public static function read_images_by_user($offset, $row_count, $username){
        self::$llista_imatges = array();

        $query = "SELECT i.id, i.path, u.username FROM imatges i LEFT JOIN usuaris u ON i.user = u.id WHERE u.username = :username LIMIT :offset, :row_count";

        Connexio::connect();
        $conn = Connexio::getConn();
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':row_count', $row_count, PDO::PARAM_INT);

        $stmt->execute();

        $result = $stmt->fetchAll();

        $num = count($result);

        if ($num > 0) {
            foreach ($result as $row) {
                extract($row);
                $imatge = new Imatge($row['path'], $row['username'], $row['id']);

                array_push(self::$llista_imatges, $imatge);
            }
        }
        Connexio::close();
    }
    
    /**
    * Read Images
    *
    * @return array
    * 
    * Métode que llegeix totes les imatges
    */
    public static function read_all_images($username){
        self::$llista_imatges = array();

        $query = "SELECT i.id, i.path, u.username FROM imatges i LEFT JOIN usuaris u ON i.user = u.id WHERE u.username = :username";

        $params = array(':username' => strtoupper($username));

        Connexio::connect();
        $stmt = Connexio::execute($query, $params);

        $result = $stmt->fetchAll();
        Connexio::close();

        $num = count($result);

        if ($num > 0) {
            foreach ($result as $row) {
                extract($row);
                $imatge = new Imatge($row['path'], $row['username'], $row['id']);

                array_push(self::$llista_imatges, $imatge);
            }
        }
    }

    /**
    * Image Count
    *
    * @return int
    * 
    * Métode que llegeix el número total d'imatges
    */
    public static function image_count(){
        $query = "SELECT COUNT(id) AS quantitat FROM imatges";

        Connexio::connect();
        $stmt = Connexio::execute($query);

        $count = $stmt->fetch();
        return intval($count['quantitat']);
    }

    /**
    * Image Count by user
    *
    * @return int
    * 
    * Métode que llegeix el número total d'imatges d'un usuari
    */
    public static function image_count_by_user($username){
        $query = "SELECT COUNT(i.id) AS quantitat FROM imatges i LEFT JOIN usuaris u ON i.user = u.id WHERE u.username = :username";

        $params = array(':username' => $username);

        Connexio::connect();
        $stmt = Connexio::execute($query, $params);

        $count = $stmt->fetch();
        return intval($count['quantitat']);
    }

    /**
    * Find Image
    *
    * @return Imatge
    * 
    * Métode que llegeix una imatge per ID i la retorna
    */
    public static function find_image($id){
        $query = "SELECT * FROM imatges WHERE id = :id";
        $params = array(':id' => $id);

        Connexio::connect();
        $stmt = Connexio::execute($query, $params);

        $result = $stmt->fetchAll();
        Connexio::close();

        $num = count($result);

        if ($num > 0) {
            foreach ($result as $row) {
                extract($row);
                
                $imatge = new Imatge($row['path'], $row['user'], $row['id']);

                return $imatge;
            }
        } else {
            return null;
        }
    }

    /**
    * Find image by path
    *
    * @return Imatge
    * 
    * Métode que llegeix una imatge pel path i la retorna
    */
    public static function find_image_by_path($path){
        $query = "SELECT * FROM imatges WHERE path = :path";
        $params = array(':path' => $path);

        Connexio::connect();
        $stmt = Connexio::execute($query, $params);

        $result = $stmt->fetchAll();
        Connexio::close();

        $num = count($result);

        if ($num > 0) {
            foreach ($result as $row) {
                extract($row);
                
                $imatge = new Imatge($row['path'], $row['user'], $row['id']);

                return $imatge;
            }
        } else {
            return null;
        }
    }

    /**
    * Delete Image
    *
    * @return array
    * 
    * Métode que elimina una imatge
    */
    public static function delete_image($id){
        $imatge = self::find_image($id);

        if($imatge === null){
            return false;
        }

        if ($imatge->delete()) {
            return array('success' => true, 'path' => $imatge->getPath());
        } else {
            return array('error' => true);
        }
    }

    /**
    * Update Image
    *
    * @return resultat
    * 
    * Métode que fa un update d'una imatge
    */
    public static function update_image($id, $newPath){
        $imatge = self::find_image($id);

        if($imatge === null){
            return false;
        }
        $imatge->setPath($newPath);
        return $imatge->update();
    }
}
?>