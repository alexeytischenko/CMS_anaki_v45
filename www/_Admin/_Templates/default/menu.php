<?
//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true)	die();
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
 <tr>
    <td valign="top" class="top">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>

          <td width="1"><a href="/_Admin/"><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/button_main.jpg" width="64" height="29" border="0"></a></td>
          <td class="white">Cистема управления сайтом <a href="/" target="_blank"><strong><?=$sysProperties["site_name"]?></strong></a></td>
          <td align="right" class="black">Пользователь <a href="/_Admin/?page=admin&action=adminedit&uid=<?=$user_info->uid?>"><strong><?=$user_info->name?></strong></a> / <a href="/_Admin/?action=logout"><strong>Выйти</strong></a> </td>
        </tr>
      </table></td>

  </tr>
  <tr>
    <td valign="top" class="menu_back"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td nowrap><p>&nbsp;</p>
          <ul id="nav"  style="width:900px ">
            <li><a href="/_Admin/" style="width: 172px "><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/menu_1.jpg" width="172" height="50" border="0"></a></li>
 <li>
   <a href="#" style="width: 187px "><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/menu_2.jpg" width="187" height="50" border="0"></a>

   <ul>
   <?
		 #иконки создания новых объектов
		 reset($objectsTypes);
		 while(list($key,$val) = each($objectsTypes))	{
		 	echo "<li><a href=\"/_Admin/?oid=-1&objType=".$key.(($foid > 0)? "&foid=".$foid : "")."&action=edit\">".$val["name"]."</a></li>";
		 }
	?>

   </ul>
   </li>
 <li><a href="/_Admin/?page=user" style="width: 176px "><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/menu_3.jpg" width="176" height="50" border="0"></a></li>
 <li><a href="/_Admin/?page=admin" style="width: 202px "><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/menu_4.jpg" width="202" height="50" border="0"></a></li>
 <?if(isset($user_info->useract["user_bookmarks"]) && sizeof((array)$user_info->useract["user_bookmarks"])) {?>
<li><a href="#" style="width: 146px "><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/menu_6.jpg" height="50" border="0"></a>
<ul>
	<?
	$user_info->useract["user_bookmarks"] = array_unique($user_info->useract["user_bookmarks"]);
	reset($user_info->useract["user_bookmarks"]);
	while(list(, $val) = each($user_info->useract["user_bookmarks"]))	{
		$tmp = new PlainObject($val);
		echo "<li><a href=\"?action=into&foid=".$val."\">".((strlen($tmp->objProp[$lang]->name)>0) ? $tmp->objProp[$lang]->name : "obj".$val."(без названия)")."</a></li>";

	}
	?>
   </ul>
</li>
<?}?>
          </ul></td>
        <td width="142"><img src="/_Images/logo_2.gif" border="0"></td>
      </tr>
    </table>    </td>
  </tr>
</table>
