<?
//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true)	die();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html>
	<head>
		<title>Пользователи</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/<?=ADMIN_TEMPLATE_STYLE?>" type="text/css" rel="StyleSheet" />
		<script src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/jquery-ui-1.8.18.custom.min.js" type="text/javascript"></script>
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/jquery-ui-1.8.18.custom.css" type="text/css" rel="stylesheet" />
		<script language="JavaScript" src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/admin.js" type="text/javascript"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			<?if (isset($curUser) && $curUser instanceof User)	{?>
			//табы поля редактирования
		    $(function () {
			    var tabContainers = $('div.tabs > div'); // получаем массив контейнеров
			    tabContainers.hide().filter($('#edit_user')).show(); // прячем все, кроме первого
			    // далее обрабатывается клик по вкладке
			    $('div.tabs ul.tabNavigation a').click(function () {
			        tabContainers.hide(); // прячем все табы
			        tabContainers.filter(this.hash).show(); // показываем содержимое текущего
			        $('div.tabs ul.tabNavigation a').removeClass('selected'); // у всех убираем класс 'selected'
			        $(this).addClass('selected'); // текушей вкладке добавляем класс 'selected'
			        return false;
			    }).filter($('#tab_first')).click();
			});
			<?}?>
		});
		</script>
	</head>
<body>
<?include("menu.php");?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">

 <tr valign="top"><td width="30%">
 			 <fieldset class="list">
			 <div style="padding-left:5px;">
				 <div class="head">Создать пользователя:
				 <span title='новый пользователь'><a href='?page=user&action=useredit&uid=-1'> <img src='_Templates/<?=ADMIN_TEMPLATE?>/_Images/icon_admin.gif' border='0'></a></span></div>
				</div>
			 </fieldset>
 			 <fieldset class="list">
			 <table width="100%" cellpadding="0" cellspacing="0" border="0">
			<?
				$adm_list = User::getUserList("an_lastname");
				for ($i = 0; $i < sizeof($adm_list); $i++)	{

					$tmpname = $adm_list[$i]["an_lastname"] . " " . $adm_list[$i]["an_name"];
					if (strlen($tmpname) > $sysProperties["headlength"]) $tmpname = substr($tmpname, 0, $sysProperties["headlength"]);
						 	echo "<tr class='row".(($uid==$adm_list[$i]["an_uid"]) ? "selected" : "")."' height='25' title='".$adm_list[$i]["an_lastname"] . " " . $adm_list[$i]["an_name"]."' width='100%'><td><img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_admin.gif' hspace='2'></td><td width='100%' style='padding-left:5px'>";
						 	echo "<a href='/_Admin/?page=user&action=useredit&uid=".$adm_list[$i]["an_uid"]."' >";
						 	echo $tmpname . "</a>";
							echo "</td><td>";
							if ($adm_list[$i]["an_active"]==0) echo "<img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_ceye.gif' title='Пользователь заблокирован'>";
							echo "</td><td>";
							echo "<a href=\"JavaScript:if(confirm('Внимание! пользователь будет удален.')) {document.location.href='/_Admin/?uid=".$adm_list[$i]["an_uid"]."&page=user&action=userdelete'}\"><img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_delete.gif' hspace='5' title='удалить пользователя' border='0'></a>";
						 	echo "</td></tr>";
				}
				?>
			 </table>

			 </fieldset>
		 </td>
	<td width="70%">
	<?if (isset($curUser) && $curUser instanceof User)	{?>
		<?if(isset($user_info->userdata["debug"]) && $user_info->userdata["debug"]):?>
		<fieldset class="noticepanel"><?=$curUser->getNoticeMessage()?></fieldset>
		<?endif;?>
		<?
		$actMess = $curUser->getActionMessage();
		if (strlen($actMess) > 0) :?>
		<fieldset class="actionpanel"><?=$actMess?></fieldset>
		<?endif?>
		<?if ($curUser->errorStatus) :?>
		<fieldset class="errorpanel"><?=$curUser->getErrorMessage()?></fieldset>
		<?endif?>
	<?}?>
		<?if ($showUserEditfield) :?>
		<form method="post" action="/_Admin/?page=user" enctype="multipart/form-data" id="objectpage">
		<div class="tabs">
		<!-- Оглавление вкладок -->
		    <ul class="tabNavigation">
		        <li><a id="tab_first" href="#edit_user">Пользователь</a></li>
		        <li><a id="tab_third" href="#third">Дополнительная информация</a></li>
		    </ul>

        <div id="edit_user">

	<table cellpadding="5" cellspacing="5">
	<tr valign="top"><td>Имя: *<br><input type="text" name="name" value="<?=$curUser->name?>"></td><td>Фамилия:<br><input type="text" name="lastname" value="<?=$curUser->lastname?>"></td></tr>
	<?if ($curUser->uid != 1 && $curUser->uid != -1)	{?>
	<tr valign="top"><td colspan="2">Дата регистрации: <?=$curUser->regdate?></td></tr>
	<tr valign="top"><td colspan="2"><?=(($curUser->active==0)? "Пользователь заблокирован. <a href='/_Admin/?page=user&uid=".$curUser->uid."&action=useract'>Активировать?</a>" : "<a href='/_Admin/?page=user&uid=".$curUser->uid."&action=userblock'>Блокировать пользователя?</a>")?></td></tr>
	<?}?>
	<tr valign="top"><td>Логин: *<br><input type="text" name="login" value="<?=$curUser->login?>"></td><td>E-mail: <br><input type="text" name="email" value="<?=$curUser->email?>"></td></tr>
	<tr valign="top"><td>Пароль: <?=(($curUser->uid == -1)? "*" : "")?><br><input type="password" name="passwd"></td><td>Подтвердить пароль: <?=(($curUser->uid == -1)? "*" : "")?><br><input type="password" name="newpasswd"></td></tr>

	</table>
	<input type="hidden" name="uid" value="<?=$curUser->uid?>">
	<input type="hidden" name="action" value="userupdate">
	<div style="padding-left:5px;padding-top:10px;padding-bottom:10px;"><span style="padding-left:25px;">* - обязательные для заполнения поля</span></div>


		 </div>
		 <div id="third">

               <table cellpadding="5" cellspacing="5">
				<?
					//дополнительные данные пользователя
					reset($userData);
					while (list($key, $val) = each($userData))	{
						echo "<tr valign=\"top\"><td><strong>".$val["name"].": ".(($val["ness"])? "*" : "")."</strong><br>";
						switch ($val["type"])	{
							case "line" :
								echo "<input type='text' name='userdata_".$key."' value=\"".((isset($curUser->userdata[$key])) ? htmlspecialchars(stripslashes($curUser->userdata[$key])) : "")."\"  class='dynvarchar'>";
								break;
							case "text" :
								echo "<textarea name='userdata_".$key."'  class='dynvarchar'>".((isset($curUser->userdata[$key])) ? htmlspecialchars(stripslashes($curUser->userdata[$key])) : "")."</textarea>";
								break;
							case "checkbox" :
								echo "<input type='checkbox' name='userdata_".$key."' ".((isset($curUser->userdata[$key]) && strlen($curUser->userdata[$key])>0)? "checked" : "").">";
								break;
							case "file"	:
								$load = (isset($curUser->userdata[$key])) ? $curUser->userdata[$key] : "";
				                if (strlen($load) > 0)	{echo "<img src='/_Upload/_users/".$load."' width='70' height='70'>";}
				                echo "<input name=\"userdata_".$key."\" type=\"file\">
				                    <input type=hidden name='load_".$key."' value='".$load."'>";
								break;
							case "Choose" :
								echo "<input type='hidden' name='mult_" . $key . "' value='1'>";
				                $md = new Multidata((isset($curUser->userdata[$key])) ? $curUser->userdata[$key] : "");
				                if (!$val["multiple"])	{
									echo "<select name=\"userdata_" . $key . "\">";
				                    echo "<option></option>";
				                }
				                while (list ($k, $v) = each($val["options"]))	{
									if (!$val["multiple"])
										echo "<option value=\"" . $k . "\"";
				                    else
										echo "<input type='checkbox' name=\"userdata_" . $key . "_". $k ."\" value=\"1\"";
				                    if ($md->ifMatch($k))	{
										if (!$val["multiple"])	echo " selected";
				                        else	echo " checked";
									}
				                    echo ">" . $v . "<br>";
								}
								if (!$val["multiple"])	echo "</select>";
								break;
						}
						echo "</td></tr>";
					}
					?>
				</table>
		 </div>
		 </div>
		 <div style="padding-left:5px;padding-top:10px;padding-bottom:10px;"><input type="submit" class="save_button" value="Сохранить"> <span style="padding-left:25px;">* - обязательные для заполнения поля</span></div>
		</form>
		<?endif ?>
	</td></tr>
	<tr><td colspan="2"><?include("footer.php"); ?></td></tr>
</table>
</body>
</html>
