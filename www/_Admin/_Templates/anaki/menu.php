<?
//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true)	die();
?>

<fieldset class="list">
<div align="right" class="topmenutext">

<?if(isset($user_info->useract["user_bookmarks"]) && sizeof((array)$user_info->useract["user_bookmarks"])) {?>
закладки:
<select style="width:100px;margin-right:30px;" onchange="window.open(this.options [this.selectedIndex].value,'_top');"><option></option>
<?
$user_info->useract["user_bookmarks"] = array_unique($user_info->useract["user_bookmarks"]);
reset($user_info->useract["user_bookmarks"]);
while(list(, $val) = each($user_info->useract["user_bookmarks"]))	{	$tmp = new PlainObject($val);
	echo "<option value='?action=into&foid=".$val."'>".((strlen($tmp->objProp[$lang]->name)>0) ? $tmp->objProp[$lang]->name : "obj".$val."(без названия)")."</option>";

}
?>
</select>
<?}?>
<a href="/_Admin/" <?=($page=="index") ? "class='selected'" : ""?>>структура сайта</a> | <a href="/_Admin/?page=admin" <?=($page=="admin") ? "class='selected'" : ""?>>администраторы</a> | <a href="/_Admin/?page=user" <?=($page=="user") ? "class='selected'" : ""?>>пользователи</a><span style="padding-left:30px;padding-right:10px;">&nbsp;</span><a href="/_Admin/?page=admin&action=adminedit&uid=<?=$user_info->uid?>"><?=$user_info->name?></a> | <a href="/_Admin/?action=logout">выход</a></div>
</fieldset>