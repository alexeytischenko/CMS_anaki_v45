<?
session_start();
//только авторизованные админы
if (!isset($_SESSION['an_uid']) || $_SESSION['admin_IP'] != $_SERVER['REMOTE_ADDR'] || time() > $_SESSION["expire"]) die();

include("../config.php");
include("../anaki_namespace.php");

ObjectGallery::deleteFoto($_REQUEST["id"], $_REQUEST["oid"], $objectsTypes[$_REQUEST["type"]]["gallery_photos"]["preview"]);

?>