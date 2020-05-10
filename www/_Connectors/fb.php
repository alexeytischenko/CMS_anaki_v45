<?
session_start();
include("../config.php");
include("../anaki_namespace.php");

unset($_SESSION["networklogin_error"]);

#логин в Facebook
if (isset($_REQUEST["code"]))	{

    	$token_url = "https://graph.facebook.com/oauth/access_token?"
       . "client_id=".FACEBOOK_AppID."&redirect_uri=".urlencode("http://".FACEBOOK_Domain."/_Connectors/fb.php")."&client_secret=".FACEBOOK_AppSecret."&code=" . $_REQUEST["code"];

     	$response = @file_get_contents($token_url);
     	$params = null;
     	parse_str($response, $params);

     	$graph_url = "https://graph.facebook.com/me?access_token=" . $params['access_token'];

     	$user = json_decode(file_get_contents($graph_url));

     	/*
     	   $user->birthday
     	   $user->gender
     	   $user->work[0]->employer->name
     	   $user->work[0]->position->name
     	*/
//        print_R($user);
		$fbuser = new User(0, "FB", $user->id);

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
        	if(!$fbuser->saveData($user->id, $user->first_name, $user->last_name, $user->email, $logpss, $logpss, array("sex"=>(($user->gender=="male")? 1 : 2), "office" => $user->work[0]->employer->name, "job" => $user->work[0]->position->name), true)
        		||
        	!$fbuser->addNetworkData("FB", $user->id))
        		 $_SESSION["networklogin_error"] = "Невозможно создать аккаунт";
        	else	{        		$_SESSION['zona_uid'] = $fbuser->uid;
            	$_SESSION['zona_name'] = $fbuser->lastname . " " . $fbuser->name;
           		$_SESSION['zona_admin_IP'] = $_SERVER['REMOTE_ADDR'];
            	$_SESSION["zona_expire"] = time() + (60 * $sysProperties["session_exp"]);
        	}
        }

}

if (isset($_SESSION["networklogin_page"]))	{	header('Location:/page/'.$_SESSION["networklogin_page"].'/');
}


?>