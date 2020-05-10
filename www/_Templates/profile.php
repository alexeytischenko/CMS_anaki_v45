<?
//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die("ошибка подключения шаблона");

if (!isset($_SESSION['zona_uid']))	exit;

$showeditfield = true;
$showerrors = false;
$showactions = false;

$curUser = new User($_SESSION['zona_uid']);

if (isset($_POST["sub"]) && $_POST["sub"] == 1)	{

	if (!$curUser->saveData($_POST["login"], $_POST["name"], $_POST["lastname"], $_POST["email"], $_POST["passwd"], $_POST["newpasswd"], User::postUserData()))
		$showerrors = true;
	else	$showactions = true;

}
?>


	<?if ($showactions) :?>
	<fieldset class="actionpanel"><?=$curUser->getActionMessage()?></fieldset>
	<?endif?>
	<?if ($showerrors) :?>
	<fieldset class="errorpanel"><?=$curUser->getErrorMessage()?></fieldset>
	<?endif?>
	<?if ($showeditfield) :?>
	<?include("regform.php");?>
   <?endif?>

