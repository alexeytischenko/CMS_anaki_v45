<?
session_start();
include("../config.php");
include("../anaki_namespace.php");

//подготовка переменных
$lang = 1;
define("INDEX_STARTED", true);

$page = (isset($_REQUEST["page"])) ? $_REQUEST["page"] : "index";
$action = (isset($_REQUEST["action"])) ? $_REQUEST["action"] : "";
$flid = (isset($_REQUEST["flid"])) ? $_REQUEST["flid"] : "";

$foid = (isset($_REQUEST["foid"])) ? intval($_REQUEST["foid"]) : 0;
$fopn = (isset($_REQUEST["fopn"])) ? intval($_REQUEST["fopn"]) : 0;
$oid = (isset($_REQUEST["oid"])) ? intval($_REQUEST["oid"]) : 0;
$aid = (isset($_REQUEST["aid"])) ? intval($_REQUEST["aid"]) : 0;
$uid = (isset($_REQUEST["uid"])) ? intval($_REQUEST["uid"]) : 0;
$lang = (isset($_REQUEST["lang"])) ? intval($_REQUEST["lang"]) : 1;
$pg = (isset($_REQUEST["pg"])) ? intval($_REQUEST["pg"]) : 1;
$objtype = (isset($_REQUEST["objType"]) && in_array($_REQUEST["objType"], array_keys($objectsTypes)) ) ? intval($_REQUEST["objType"]) : 0;



// авторизация
if (isset($_POST['auth_name'])) {
  	$uid = Admin::loginAdmin($_POST['auth_name'], $_POST['auth_pass']);
  	if ($uid > 0) {
		$_SESSION["an_uid"] = $uid;
		$_SESSION["admin_IP"] = $_SERVER["REMOTE_ADDR"];
		$_SESSION["expire"] = time() + (60 * $sysProperties["session_exp"]);
	}
	header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
  	exit;
}

//выход
if (isset($_REQUEST['action']) AND $_REQUEST['action']=="logout") {
	unset($_SESSION["an_uid"]);
	unset($_SESSION["admin_IP"]);
	unset($_SESSION["expire"]);
	header("Location: http://".$_SERVER['HTTP_HOST']."/_Admin/");
	exit;
}

//принудительный выход
if (!isset($_SESSION['an_uid']) || $_SESSION['admin_IP'] != $_SERVER['REMOTE_ADDR'] || time() > $_SESSION["expire"])	{
	unset($user_info);
	unset($_SESSION["an_uid"]);
	unset($_SESSION["admin_IP"]);
	unset($_SESSION["expire"]);
	include("_Templates/default/login.php");
	exit;
}


//////////////////////////////////////////////////
# панель редактирования для различных страниц
//объекты
$showEditfield = ($oid == 0) ? false : true;
$showSaveAsfield = false;
//администраторы
$showAdminEditfield = false;
if ($aid > 0)	{
	$editUser = new Admin($aid);
    if ($editUser->checkPermission())	$showAdminEditfield = true;
}	elseif ($aid == -1)	$showAdminEditfield = true;
//пользователи
$showUserEditfield = ($uid == 0) ? false : true;;

////////////////////////////////////////////////
switch ($action)	{

////////////////действия над объектами

	case "open"	:
	    $tmpAd = new Admin($_SESSION['an_uid']);
	    $tmpAd->opencloseFolder($fopn);
	break;
	case "close"	:
	    $tmpAd = new Admin($_SESSION['an_uid']);
	    $tmpAd->opencloseFolder($fopn, false);
	break;
	case "add"	:
	    $tmpAd = new Admin($_SESSION['an_uid']);
	    $tmpAd->adddeleteBookmark($fopn);

	break;
	case "remove"	:
	    $tmpAd = new Admin($_SESSION['an_uid']);
	    $tmpAd->adddeleteBookmark($fopn, false);
	break;
	case "deletefile"	:
		# удаление загруженного файла
	  	$curObject = new PlainObject($oid, 0, "", false);
		$curObject->deleteFile($_REQUEST["filelang"], $flid, $curObject->objectType);
		$curObject->buildObject();
		$showactions = true;
	break;
	case "deleteIE"	:
		# удаление загруженного файла
	  	$curObject = new PlainObject($oid, 0, "", false);
		$curObject->deleteFieldValue($_REQUEST["filelang"], $flid);
		$curObject->buildObject();
		$showactions = true;
	break;
	case "delete"	:
		# удаление объекта
	  	$curObject = new PlainObject($oid);
		if($curObject->deleteObject())	$showactions = true;
	    $oid = 0;
	    $showEditfield = false;
	break;
	case "edit"	:
		# объект для редактирования
        if ($oid != -1)	$curObject = new PlainObject($oid);
		else	$curObject = new PlainObject($oid, $objtype);
	break;
	case "saveas"	:
		# объект для save as
        $curObject = new PlainObject($oid);
        $showEditfield = false;
        $showSaveAsfield = true;
	break;
	case "saveasupdate"	:
		$oldObject = new PlainObject($oid);
		
		unset($curObject);
		$curObject = new PlainObject(-1, $objtype);
		unset($prop);
        unset($odt);
		//подготовка значений переменных
		for ($i = 1; $i <= sizeof($languages); $i++)	{
			//свойства
			$prop[$i] = $oldObject->objProp[$i];
			$prop[$i]->name = $_POST["name".$i];
			//значения полей
			$odt[$i] = new ObjectData(ObjectData::convertData($objtype, $oldObject->values[$i]));	
		}

		if (!$curObject->createObject("", $_POST["objPar"], date("Y-m-d H:i"), $_SESSION['an_uid'], (($oldObject->isFolder) ? true : false), (($oldObject->inside) ? true : false), $odt, $prop))
			$showerrors = true;
		else {
			$showactions = true;
			
		}
		$oid = $curObject->oid;
		$showEditfield = false;
	break;
	case "update"	:
	    #создание или обновление объекта

     	//загрузка и сортировка фотогалереи
		if ($oid != -1 && $objectsTypes[$objtype]["gallery"])	{

			//загрузка фотографий фотогалереи
			if (isset($_FILES['Filedata']["tmp_name"][0]) && strlen($_FILES['Filedata']["tmp_name"][0]) > 0) {

				$fileCount = count($_FILES['Filedata']["tmp_name"]);

                for ($i = 0; $i < $fileCount; $i++) {

                	$f['Filedata'] = array('name' => $_FILES['Filedata']['name'][$i], 'type' => $_FILES['Filedata']['type'][$i], 'tmp_name' => $_FILES['Filedata']['tmp_name'][$i], 'size' => $_FILES['Filedata']['size'][$i]);

					ObjectGallery :: saveFoto($oid, $f, $objectsTypes[$objtype]["gallery_photos"]);
				}
	        }


        	//сортировка фотогалереи
			ObjectGallery::setGallerySort($oid, $_POST["fotolist_order"]);
		}
		//запись названий
		$tmp_ftlist = new ObjectGallery($oid);
		reset($tmp_ftlist);
		while (list(, $v1) = each($tmp_ftlist->fotos_array))	{
			$nm_arr = array();
			for ($i = 1; $i <= sizeof($languages); $i++)	{
				if (isset($_POST["galftname_".$i."_".$v1["an_oid"]]) && strlen($_POST["galftname_".$i."_".$v1["an_oid"]])>0)
					$nm_arr[$i] = $_POST["galftname_".$i."_".$v1["an_oid"]];
				else $nm_arr[$i] = "";	
			}
			if (sizeof($nm_arr) > 0)	ObjectGallery::addName($v1["an_oid"], serialize($nm_arr));
		}

		$curObject = new PlainObject($oid, $objtype, "", false);
        unset($prop);
        unset($odt);
		//подготовка значений переменных
		for ($i = 1; $i <= sizeof($languages); $i++)	{
			//свойства
			$prop[$i] = new ObjectProp($_POST["name".$i], $_POST["title".$i], $_POST["header".$i], $_POST["keywords".$i], $_POST["description".$i], $_POST["objSortNumber".$i], $_POST["objSortType".$i], (isset($_POST["objSortOrder".$i])? true : false), (isset($_POST["frontAccess".$i])? true : false), (isset($_POST["menushow".$i])? true : false), (isset($_POST["restr".$i])? true : false), $_POST["Tags".$i]);

			//значения полей
			$odt[$i] = new ObjectData(ObjectData::prepareData($curObject->objectType, $_POST, $i));
			//print_R($odt[$i]);
		}

		if ($oid != -1)	{
			if (!$curObject->updateObject($_POST["objUrl"], $_POST["objPar"], $_POST["oldPar"], $_POST["objCreateDate"], $_SESSION['an_uid'], (isset($_POST["objIsFolder"]) ? true : false), (isset($_POST["inside"]) ? true : false), $odt, $prop))
				$showerrors = true;
			else
				$showactions = true;
		}
		else	{
			if (!$curObject->createObject($_POST["objUrl"], $_POST["objPar"], $_POST["objCreateDate"], 1, (isset($_POST["objIsFolder"]) ? true : false), (isset($_POST["inside"]) ? true : false), $odt, $prop))
				$showerrors = true;
			else
				$showactions = true;
			$oid = $curObject->oid;
		}
		$curObject->buildObject();
	break;

////////////////действия над администраторами
    case "admindelete"	:
	        $curUser = new Admin($aid);
			if ($curUser->deleteAdmin())	{
				$aid = 0;
				$showAdminEditfield = false;
			}
    break;
    case "adminblock"	:
	        $curUser = new Admin($aid);
			$curUser->banAdmin();
    break;
    case "adminact"	:
	        $curUser = new Admin($aid);
			$curUser->restoreAdmin();
    break;
	case "adminedit"	:
		# пользователь для редактирования
        $curUser = new Admin($aid);
	break;
    case "adminupdate"	:
	        $curUser = new Admin($aid);

			$tempD = array();
	        if (isset($_POST["admintempl"]))	$tempD["template"] = $_POST["admintempl"];
	        if (isset($_POST["adminstyle"]))	$tempD["template_style"] = $_POST["adminstyle"];
	        if (isset($_POST["admindebug"]))	$tempD["debug"] = $_POST["admindebug"];

			if ($curUser->saveData($_POST["login"], $_POST["name"], $_POST["passwd"], $_POST["newpasswd"], $tempD))	{
				$aid = $curUser->uid;
			}
    break;

////////////////действия над пользователями
    case "userdelete"	:
	        $curUser = new User($uid);
			if ($curUser->deleteUser())	{
				$uid = 0;
				$showUserEditfield = false;
			}
    break;
    case "userblock"	:
	        $curUser = new User($uid);
			$curUser->banUser();
    break;
    case "useract"	:
	        $curUser = new User($uid);
			$curUser->restoreUser();
    break;
	case "useredit"	:
		# пользователь для редактирования
        $curUser = new User($uid);
	break;
    case "userupdate"	:
	        $curUser = new User($uid);
			if ($curUser->saveData($_POST["login"], $_POST["name"], $_POST["lastname"], $_POST["email"], $_POST["passwd"], $_POST["newpasswd"], User::postUserData()))	{
				$uid = $curUser->uid;
			}
    break;

}

///////////////////////////////////

$user_info = new Admin($_SESSION['an_uid']);
$_SESSION["expire"] =  time() + (60 * $sysProperties["session_exp"]);
define('ADMIN_TEMPLATE', (isset($user_info->userdata["template"])? $user_info->userdata["template"] : 'default'));
define('ADMIN_TEMPLATE_STYLE', (isset($user_info->userdata["template_style"])? $user_info->userdata["template_style"] : 'admin_default.css'));


////////////////////////////////////

include("_Templates/".ADMIN_TEMPLATE."/".$page.".php");

?>
