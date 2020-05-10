<?//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die();


if (isset($_POST['auth_name'])) {
    $mod = "login";
	setcookie("auth_n", $_POST['auth_name'], time()+60*60*24*30);
	setcookie("auth_p", $_POST['auth_pass'], time()+60*60*24*30);

	$tempU = User::loginUser($_POST['auth_name'], $_POST['auth_pass']);


  	if ($tempU > 0) {
		$_SESSION["zona_uid"] = $tempU;
		$curUser = new User($tempU);
		$_SESSION["zona_name"] = $curUser->lastname . " " . $curUser->name;
		$_SESSION["zona_admin_IP"] = $_SERVER["REMOTE_ADDR"];
		$_SESSION["zona_expire"] = time() + (60 * $sysProperties["session_exp"]);
	}
	else	$mod = "wrong";
	header("Location: http://".$_SERVER['HTTP_HOST']."/page/".$lang."/".$id."/".$pg."/".$mod."/".$showPage->url."/");
  	exit;
}

if (isset($mod) && $mod == "logout") {
  unset($_SESSION["zona_uid"]);
  unset($_SESSION["zona_name"]);
  unset($_SESSION["zona_admin_IP"]);
  unset($_SESSION["zona_expire"]);
  header("Location: http://".$_SERVER['HTTP_HOST']);
  exit;
}

if (isset($_SESSION['zona_uid']) && $_SESSION['zona_admin_IP'] == $_SERVER['REMOTE_ADDR'] && time() < $_SESSION["zona_expire"]) {
	$_SESSION["zona_expire"] =  time() + (60 * $sysProperties["session_exp"]);
	return;
}
else {    //для возврата в случае логина через соцсети
	$_SESSION["networklogin_page"] = $showPage->url;
	?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Необходимо авторизоваться</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="/images/style.css" rel="stylesheet" type="text/css">
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
    	<td valign="middle" align="center"><h2>Войти</h2>
		  <form name="form2" method="post">
	                   <table width="300" border="0" cellspacing="0" cellpadding="0">
	                     <tr>
	                       <td height="10" colspan="2">&nbsp;</td>
	                     </tr>
				<? if ($mod == "wrong")	:?>
	                     <tr>
	                       <td height="30" colspan="2"><span class="login_error">Неправильно введен логин или пароль</span></td>
	                     </tr>
				<?endif;?>
				<? if (isset($_SESSION["networklogin_error"]))	:?>
	                     <tr>
	                       <td height="30" colspan="2"><span class="login_error"><?=$_SESSION["networklogin_error"]?></span></td>
	                     </tr>
				<?endif;?>
	                     <tr>
	                       <td width="160" height="30" nowrap>Имя пользователя (e-mail)</td>
	                       <td><input name="auth_name" type="text" id="login" value="<?=((isset($_COOKIE["auth_n"])) ? $_COOKIE["auth_n"] : "")?>" style="width:150px "></td>
	                     </tr>
	                     <tr>
	                       <td height="30">Пароль</td>
	                       <td nowrap><input name="auth_pass" type="password" id="pass" value="<?=((isset($_COOKIE["auth_p"])) ? $_COOKIE["auth_p"] : "")?>" style="width:150px "></td>
	                     </tr>
	                     <tr>
	                       <td height="20">&nbsp;</td>
	                       <td valign="top" nowrap><a href="/page/<?=$lang?>/passrec/" class="reg">Забыли пароль?</a> </td>
	                     </tr>
	                     <tr>
	                       <td height="30">&nbsp;</td>
	                       <td nowrap><input type="submit" name="Submit2" value="Войти" style="width:50px "></td>
	                     </tr>
	                     <tr valign="middle" align="center">
  							<td><a href="https://www.facebook.com/dialog/oauth?client_id=<?=FACEBOOK_AppID?>&scope=email,user_about_me,user_birthday,user_location&redirect_uri=<?=urlencode("http://".FACEBOOK_Domain."/_Connectors/fb.php")?>">
       <img src="/_Images/fbconnect.jpg" border="0" alt="Войти через Facebook" title="Войти через Facebook" /></a>
    	</td><td> <a href="http://api.vkontakte.ru/oauth/authorize?client_id=<?=VKONTAKTE_AppID?>&response_type=code&scope=&redirect_uri=<?=VKONTAKTE_Domain?>/_Connectors/vk.php">
            <img src="/_Images/vkontact.jpg" border="0" alt="Войти через вКонтакте" title="Войти через вКонтакте" /></a></td>
  </tr>
	                   </table>
	                   <p>&nbsp;</p>
	               </form></td>
  </tr>

</table>


</body>
</html>
<?
}
exit;
?>