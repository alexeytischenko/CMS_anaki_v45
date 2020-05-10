<?
include("../config.php");
include("../anaki_namespace.php");
session_start();
//только авторизованные админы
if (!isset($_SESSION['an_uid']) || $_SESSION['admin_IP'] != $_SERVER['REMOTE_ADDR'] || time() > $_SESSION["expire"]) die("");
$_SESSION["expire"] =  time() + (60 * $sysProperties["session_exp"]);

$user_info = new Admin($_SESSION['an_uid']);
define('ADMIN_TEMPLATE', (isset($user_info->userdata["template"])? $user_info->userdata["template"] : 'default'));
define('ADMIN_TEMPLATE_STYLE', (isset($user_info->userdata["template_style"])? $user_info->userdata["template_style"] : 'admin_default.css'));


$sub = (isset($_REQUEST["sub"])) ? intval($_REQUEST["sub"]) : 0;
?>
<html>
<head runat="server">
    <title>Link User редактор</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="_Templates/<?=ADMIN_TEMPLATE?>/admin.css" type="text/css" rel="StyleSheet" />
	<script language="JavaScript" src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/admin.js" type="text/javascript"></script>
</head>
<body style="margin:10px;">
<?
	if ($sub != 1)	{
?>
<form id="form1" method="post">
<input type="hidden" name="var" value="Repeater<?=$_REQUEST["lang"] ?>_<?=$_REQUEST["var"]?>" />
<input type="hidden" name="link" value="link<?=$_REQUEST["lang"] ?>_<?=$_REQUEST["var"]?>" />
<input type="hidden" name="lang" value="<?=$_REQUEST["lang"]?>" />
<fieldset style="padding:15px; margin:15px;" class="editpanel">
<?
$list = User::getUserList("an_name");
$sel = new Multidata($_REQUEST["selected"]);

echo "<input type=hidden name=cnt value=".sizeof($list).">";
for ($i = 0; $i < sizeof($list); $i++)	{
    echo "<input type='checkbox' name='catch".$i."' value='" . $list[$i]["an_uid"] . "'";
    if ($sel->ifMatch($list[$i]["an_uid"]))
        echo " checked";
    echo ">" . $list[$i]["an_name"] . " " . $list[$i]["an_lastname"] . " [".$list[$i]["an_email"]."]<br>";
}
?>
</fieldset>
<input type="hidden" name="sub" value="1">
<input type="submit" value="Сохранить" style="margin-left:15px;">
</form>
<?
	} else	{
		$spisok = "";
		$arg = "";
		for ($i = 0; $i < $_REQUEST["cnt"]; $i++)	{
			if (strlen($_REQUEST["catch".$i]) > 0)	$arg .= $_REQUEST["catch".$i] . ",";
		}
		if (strlen($arg) > 0)	$arg = "," . $arg;
		//echo $arg; exit;
	    $md = new Multidata($arg);
	    for ($g = 0; $g < sizeof($md->ids_array); $g++)	{
	    	$tmp_u = new User($md->ids_array[$g]);
			$spisok .= "<img src=_Templates/default/_Images/icon_admin.gif> " . $tmp_u->name . " " . $tmp_u->lastname . " [".$tmp_u->email."]<br>";	
	    }
	
	    echo "<script>\n whichBroOp('" . $_REQUEST["var"] . "').value = '" . $arg . "'; \n whichBroOp('" . $_REQUEST["link"] . "').innerHTML = '" . $spisok . "'; \n window.close();</script>";
}
?>

</body>

</html>

