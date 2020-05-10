<?
//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true)	die();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html>
	<head>
		<title>Администраторы</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/<?=ADMIN_TEMPLATE_STYLE?>" type="text/css" rel="StyleSheet" />
		<script src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script language="JavaScript" src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/admin.js" type="text/javascript"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			<?if (isset($curUser) && $curUser instanceof Admin)	{?>
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
		<script type="text/javascript">
		  jsHover = function() {
		    var hEls = document.getElementById("nav").getElementsByTagName("LI");
		    for (var i=0, len=hEls.length; i<len; i++) {
		      hEls[i].onmouseover=function() { this.className+=" jshover"; }
		      hEls[i].onmouseout=function() { this.className=this.className.replace(" jshover", ""); }
		    }
		  }
		  if (window.attachEvent && navigator.userAgent.indexOf("Opera")==-1) window.attachEvent("onload", jsHover);
		</script>
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/menu.css" rel="stylesheet" type="text/css">
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/dropdown.css" media="all" rel="stylesheet" type="text/css" />
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/style.css" rel="stylesheet" type="text/css">
	</head>
<body>
<?include("menu.php");?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">

 <tr valign="top"><td width="30%">
 			 <fieldset class="list">
			 <div style="padding-left:5px;">
				 <div class="head">Создать администратора:
				 <span title='новый администратор'><a href='?page=admin&action=adminedit&aid=-1'> <img src='_Templates/<?=ADMIN_TEMPLATE?>/_Images/icon_admin.gif' border='0'></a></span></div>
				</div>
			 </fieldset>
 			 <fieldset class="list">
			 <table width="100%" cellpadding="0" cellspacing="0" border="0">
			<?
				$adm_list = Admin::getAdminList();
				for ($i = 0; $i < sizeof($adm_list); $i++)	{					if ($_SESSION["an_uid"]!=1 && $adm_list[$i]["uid"]==1)	continue;

					$tmpname = $adm_list[$i]["name"];
					if (strlen($tmpname) > $sysProperties["headlength"]) $tmpname = substr($tmpname, 0, $sysProperties["headlength"]);
						 	echo "<tr class='row".(($aid==$adm_list[$i]["uid"]) ? "selected" : "")."' height='25' title='".$adm_list[$i]["name"]."' width='100%'><td><img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_admin.gif' hspace='2'></td><td width='100%' style='padding-left:5px'>";
						 	if (!$adm_list[$i]["super"] || $user_info->super) echo "<a href='/_Admin/?page=admin&action=adminedit&aid=".$adm_list[$i]["uid"]."' >";
						 	echo $tmpname . "</a>";
							echo "</td><td>";
							if ($adm_list[$i]["active"]==0) echo "<img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_ceye.gif' title='администратор заблокирован'>";
							echo "</td><td>";
							if (!$adm_list[$i]["super"] || $user_info->super)
								echo "<a href=\"JavaScript:if(confirm('Внимание! администратор будет удален.')) {document.location.href='/_Admin/?aid=".$adm_list[$i]["uid"]."&page=admin&action=admindelete'}\"><img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_delete.gif' hspace='5' title='удалить администратора' border='0'></a>";
						 	echo "</td></tr>";
				}
				?>
			 </table>

			 </fieldset>
		 </td>
	<td width="70%">
	<?if (isset($curUser) && $curUser instanceof Admin)	{?>
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
		<?if ($showAdminEditfield) :?>
		<form method="post" action="/_Admin/?page=admin" enctype="multipart/form-data" id="objectpage">
		<div class="tabs">
		<!-- Оглавление вкладок -->
		    <ul class="tabNavigation">
		        <li><a id="tab_first" href="#edit_user">Администратор</a></li>
		        <li><a id="tab_third" href="#third">Настройки</a></li>
		    </ul>

        <div id="edit_user">

	<table cellpadding="5" cellspacing="5">
	<tr valign="top"><td>Имя: *<br><input type="text" name="name" value="<?=$curUser->name?>"></td></tr>
	<?if ($curUser->uid != 1 && $curUser->uid != -1)	{?>
	<tr valign="top"><td>Дата регистрации: <?=$curUser->regdate?></td></tr>
	<tr valign="top"><td><?=(($curUser->active==0)? "Администратор заблокирован. <a href='/_Admin/?page=admin&aid=".$curUser->uid."&action=adminact'>Активировать?</a>" : "<a href='/_Admin/?page=admin&aid=".$curUser->uid."&action=adminblock'>Блокировать администратора?</a>")?></td></tr>
	<?}?>
	<tr valign="top"><td>Логин: *<br><input type="text" name="login" value="<?=$curUser->login?>"></td></tr>
	<tr valign="top"><td>Пароль: <?=(($curUser->uid == -1)? "*" : "")?><br><input type="password" name="passwd"></td></tr>
	<tr valign="top"><td>Подтвердить пароль: <?=(($curUser->uid == -1)? "*" : "")?><br><input type="password" name="newpasswd"></td></tr>
	</table>
	<input type="hidden" name="aid" value="<?=$curUser->uid?>">
	<input type="hidden" name="action" value="adminupdate">
	<div style="padding-left:5px;padding-top:10px;padding-bottom:10px;"><span style="padding-left:25px;">* - обязательные для заполнения поля</span></div>


		 </div>
		 <div id="third">
		 		<script language="JavaScript">
				function themestyle()	{					if($('#admin_template').val()=='<?=$curUser->userdata["template"] ?>')						$('#template_style').attr('disabled','');
					else
						$('#template_style').attr('disabled','disabled');
				}
		 		</script>
               <table cellpadding="5" cellspacing="5">
				<tr valign="top"><td width="50%">Тема:<br>
				<select name="admintempl" id="admin_template" onChange="themestyle()">
					<option value="default">default</option>
				<?
               	$handle = opendir("_Templates");
				while (($file = readdir($handle))!= false)
					if(is_dir("_Templates/" . $file) && ($file != ".") && ($file != "..") && ($file != "default"))
						echo "<option value='".$file."' " . (($file==$curUser->userdata["template"]) ? "selected" : "") .">".$file."</option>";
				closedir($handle);
               ?>
               </select>
               <p>Стиль:<br>
				<select name="adminstyle" id="template_style">
					<option value="admin_default.css">default</option>
				<?
               	$handle = opendir("_Templates/".(strlen($curUser->userdata["template"]) > 0 ? $curUser->userdata["template"] : "default")."/_Styles/");
				while (($file = readdir($handle))!= false)
					if(is_file("_Templates/".ADMIN_TEMPLATE."/_Styles/" . $file) && ($file != ".") && ($file != "..") && ($file != "admin_default.css") && preg_match('/^admin_/', $file))
						echo "<option value='".$file."' " . (($file==$curUser->userdata["template_style"]) ? "selected" : "") .">".$file."</option>";
				closedir($handle);
               ?>
               </select>
               </td>
               <td>Выводить отладочную информацию:<br>
                   <input type="checkbox" name="admindebug" value="1" <?=(isset($curUser->userdata["debug"]) && strlen($curUser->userdata["debug"])>0 ? "checked" : "") ?>>
               </td></tr>
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
