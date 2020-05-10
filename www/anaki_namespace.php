<?
include("_ApplicationCore/ObjectProp.php");
include("_ApplicationCore/ObjectData.php");
include("_ApplicationCore/ObjectGallery.php");
include("_ApplicationCore/Multidata.php");
include("_ApplicationCore/CustomLibs.php");
include("_ApplicationCore/List.php");
include("_ApplicationCore/Tags.php");
include("_ApplicationCore/PlainObject.php");
include("_ApplicationCore/ObjectsMenu.php");
include("_ApplicationCore/Admin.php");
include("_ApplicationCore/User.php");
include("_ApplicationCore/Search.php");
include("_ApplicationCore/Properties.php");
include("_ApplicationCore/XMLConfig.php");


//расширение функционала под конкретные сайты
if (file_exists(ROOTPATH . "/_ApplicationCore/UsersExtensions.php"))  include(ROOTPATH . "/_ApplicationCore/UsersExtensions.php");

?>