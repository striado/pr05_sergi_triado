<?php
/**
*
* @author: Sergi Triadó <s.triado@sapalomera.cat>
*
*/

// Creem l'objecte per fer peticions
include "model/http.request.php";

$http = new HttpRequest("environment/environment.json");
$environment = $http->getEnvironment();
$uploadOk = 0;
// Si hi ha un article introduït per POST fem l'insert a la BBDD abans de mostrar els articles

if(!empty($_POST['insertArticle']) && !empty($_POST['article'])){

    // Comprovem que s'ha seleccionat un fitxer
    if (!empty($_FILES['upload_img']['name'])) {
        // Directori on s'enviarà l'imatge i extensió per comprovar
        $target_dir = "public/assets/img/";
        $target_file = $target_dir . basename($_FILES['upload_img']['name']);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Comprovem que és una imatge
        if(!empty($_FILES['upload_img']['tmp_name'])){
            $check = getimagesize($_FILES['upload_img']['tmp_name']);
            
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                $uploadOk = 0;
                $errors['img']['fake'] = true;
            }    
        } else {
            $uploadOk = 0;
            $errors['img']['sizeLimit'] = true;
        }

        // Comprovem que el fitxer no existeixi ja
        if (file_exists($target_file)) {
            $errors['img']['repeated'] = true;
            $uploadOk = 0;
        }

        // Comprovem que la imatge és del format que ens interessa
        if ($imageFileType != 'jpg' && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $uploadOk = 0;
            $errors['img']['formatNotAllowed'] = true;
        } else {
            $uploadOk = 1;
        }
    }

    $insertUrl = $environment->protocol . $environment->baseUrl . $environment->dir->modules->api->article->create;

    $article = $_POST['article'];
    $insertData = array('article' => $article, 'autor' => $_SESSION['username']);

    if ($uploadOk != 0) {
        $insertData['imatge'] = "public/assets/img/" . basename($_FILES['upload_img']['name']);
    }
    $insertResult = $http->makePostRequest($insertUrl, $insertData);

    if ($insertResult != null) {
        if(isset($insertResult->success)){
            move_uploaded_file($_FILES["upload_img"]["tmp_name"], $target_file);
            header("Location: " . $environment->protocol . $environment->baseUrl);
        } else {
            $insert = false;
        }
    } else {
        $insert = false;
    }
}

// Establim el numero de pagina en la que l'usuari es troba.
# si no troba cap valor, assignem la pagina 1.
$pagina = (empty($_GET['pagina'])) ? 1 : intval($_GET['pagina']);

// definim quants post per pagina volem carregar.

$post_per_pag = (!empty($_GET['post_x_pag']) && (intval($_GET['post_x_pag']) > 0)) ? intval($_GET['post_x_pag']) : 5;

// Revisem des de quin article anem a carregar, depenent de la pagina on es trobi l'usuari.
# Comprovem si la pagina en la que es troba es més gran d'1, sino carreguem des de l'article 0.
# Si la pagina es més gran que 1, farem un càlcul per saber des de quin post carreguem

$primer_article = ($pagina > 1) ? ($pagina - 1) * $post_per_pag : 0;

// Fem la petició HTTP al backend per rebre els articles

$url = $environment->protocol . $environment->baseUrl . $environment->dir->modules->api->article->read;

$data = array('offset' => $primer_article, 'row_count' => $post_per_pag);

# Si l'usuari està loguejat, incluïm el seu nom d'usuari a la petició per rebre els SEUS articles

if (isset($_SESSION['username'])) {
    $data['username'] = $_SESSION['username'];
}

# Fem la petició i rebem les dades

$result = $http->makePostRequest($url, $data);

if ($result != null) {
    $num = count($result->articles);
} else {
    $num = 0;
}

// Calculem el total d'articles per a poder conèixer el número de pàgines de la paginació
$quantitat = $result->count;

// Calculem el numero de pagines que tindrà la paginació. Llavors hem de dividir el total d'articles entre els POSTS per pagina

$maxim_pagines = ($quantitat % $post_per_pag > 0) ? floor($quantitat / $post_per_pag + 1) : floor($quantitat / $post_per_pag);

// Incluim la vista

require 'templates/articles/articles.vista.php';

?>