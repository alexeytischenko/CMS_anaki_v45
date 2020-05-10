<?

session_start();
include("../config.php");
include("../anaki_namespace.php");

//только авторизованные админы
if (!isset($_SESSION['an_uid']) || $_SESSION['admin_IP'] != $_SERVER['REMOTE_ADDR'] || time() > $_SESSION["expire"]) die();
$user_info = new Admin($_SESSION['an_uid']);
$_SESSION["expire"] =  time() + (60 * $sysProperties["session_exp"]);
define('ADMIN_TEMPLATE', (isset($user_info->userdata["template"])? $user_info->userdata["template"] : 'default'));


$flist = new ObjectGallery($_REQUEST["oid"]);


for ($i = 0; $i < sizeof($flist->fotos_array); $i++)	{
	echo "<li id=\"listItem_".$flist->fotos_array[$i]["an_oid"]."\" class='foto_list'>";
	echo "<table width=70%><tr><td width='50'>";
	echo "<img src=\"/_Fotos/".$_REQUEST["oid"]."_prev/".$flist->fotos_array[$i]["an_filename"]."\" alt=\"move\" width=\"50\" height=\"50\" class=\"handle\" /></td>";
	echo "<td align=left><u>filename: </u>".$flist->fotos_array[$i]["an_filename"];
	for ($j = 1; $j <= sizeof($languages); $j++)	{
		echo "<br><u>Название/alt(".$languages[$j]["sname"]."): </u><input type='text' name='galftname_".$j."_".$flist->fotos_array[$i]["an_oid"]."' value='".@htmlspecialchars($flist->fotos_array[$i]["an_name"][$j])."' />";//
	}
	echo "</td><td valign='middle' width='10'><img src=\"/_Admin/_Templates/".ADMIN_TEMPLATE."/_Images/icon_delete.gif\" onclick=\"foto_del(".$flist->fotos_array[$i]["an_oid"].");\">";
	echo "</td></tr></table></li>";
}

?>