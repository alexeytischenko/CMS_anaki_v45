<?
session_start();
include("../config.php");
include("../anaki_namespace.php");

#логин в ВКонтакте
if (isset($_REQUEST["code"]))	{

    	$url = "https://api.vkontakte.ru/oauth/access_token?client_id=".VKONTAKTE_AppID."&client_secret=".VKONTAKTE_AppSecret."&code=" . $_REQUEST['code'];
		$response = json_decode(@file_get_contents($url));
		if ($response->error) {
	  		die('Ошибка');
		}

		$arrResponse = json_decode(@file_get_contents("https://api.vkontakte.ru/method/getProfiles?uid={$response->user_id}&access_token={$response->access_token}&fields=sex,bdate,country,city,has_mobile"))->response;

		/*
		  $arrResponse[0]->uid
		  $arrResponse[0]->sex
		  $arrResponse[0]->bdate
		*/

		$fbuser = new User(0, "VK", $arrResponse[0]->uid);
     	if ($fbuser->uid > 0)	{     		if ($fbuser->active == 1)	{
            	$_SESSION['zona_uid'] = $fbuser->uid;
            	$_SESSION['zona_name'] = $fbuser->lastname . " " . $fbuser->name;
           		$_SESSION['zona_admin_IP'] = $_SERVER['REMOTE_ADDR'];
            	$_SESSION["zona_expire"] = time() + (60 * $sysProperties["session_exp"]);
            }
            else $_SESSION["networklogin_error"] = "Ваш аккаунт заблокирован.";
        }
        else {
        	$fbuser = new User(-1);
        	$logpss = uniqid("");
        	if(!$fbuser->saveData($arrResponse[0]->uid, $arrResponse[0]->first_name, $arrResponse[0]->last_name, "", $logpss, $logpss, array("sex"=>(($arrResponse[0]->sex==2)? 1 : 2)), true)
        		||
        	!$fbuser->addNetworkData("VK", $arrResponse[0]->uid))
        		 $_SESSION["networklogin_error"] = "Невозможно создать аккаунт";
        	else	{
        		$_SESSION['zona_uid'] = $fbuser->uid;
            	$_SESSION['zona_name'] = $fbuser->lastname . " " . $fbuser->name;
           		$_SESSION['zona_admin_IP'] = $_SERVER['REMOTE_ADDR'];
            	$_SESSION["zona_expire"] = time() + (60 * $sysProperties["session_exp"]);
        	}
        }

}
if (isset($_SESSION["networklogin_page"]))	{	header('Location:/page/'.$_SESSION["networklogin_page"].'/');
}


?>