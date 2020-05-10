<?
//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die("ошибка подключения шаблона");

if (isset($_SESSION['zona_uid']) && $_SESSION['zona_uid'] > 0)	echo "<p>Вы уже зарегистрированы";
else	{
	$showeditfield = true;
	$showerrors = false;
	$showactions = false;

	//создаем пользователя
	$curUser = new User(-1);
	if (isset($_POST["sub"]) && $_POST["sub"] == 1)	{

		if ($_SESSION['captcha'] == $_POST['captcha_phrase'])	{

			if (!$curUser->newBlockedUser($_POST["login"], $_POST["name"], $_POST["lastname"], $_POST["email"], $_POST["passwd"], $_POST["newpasswd"], User::postUserData()))
				$showerrors = true;
			else	{
				$showactions = true;
				$showeditfield = false;
			}
		}
		else	{
			$showerrors = true;
			$curUser->addErrorMessage("<p>Контрольная фраза не совпадает с символами, отображенными на рисунке");
		}
	}
	?>

		<?if ($showactions) :?>
		<fieldset class="actionpanel"><?=$curUser->getActionMessage()?></fieldset>
		<?endif?>
		<?if ($showerrors) :?>
		<p><span class="login_error"><?=$curUser->getErrorMessage()?></span></p>
		<?endif?>
		<?if ($showeditfield) :?>
		<?include("regform.php");?>
				  <?endif?>

<?}?>