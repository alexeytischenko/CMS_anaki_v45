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
    <title>Link редактор</title>
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
<input type="hidden" name="type" value="<?=$_REQUEST["type"] ?>" />
<fieldset style="padding:15px; margin:15px;" class="editpanel">
<?
$list = new ObjectsMenu($_REQUEST["id"], $_REQUEST["lang"],array(), 0, false, false, ($_REQUEST["inside"]==1) ? true : false);
$ar = $list->getTree();
$sel = new Multidata($_REQUEST["selected"]);
//print_r($ar);
echo "<input type=hidden name=cnt value=".sizeof($ar).">";
for ($i = 0; $i < sizeof($ar); $i++)	{
	if ($ar[$i]["an_type"] != $_REQUEST["type"])	continue;
    echo "<input type=checkbox  style='margin-left:".$ar[$i]["Margin"]."' name=catch".$i." value='" . $ar[$i]["an_oid"] . "'";
    if ($sel->ifMatch($ar[$i]["an_oid"]))
        echo " checked";
    echo ">" . $ar[$i]["an_name"] . "<br>";
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
    $al = $md->getObjectsList($_REQUEST["lang"]);

    for ($g = 0; $g < sizeof($al); $g++)	{
        if ($_REQUEST["type"] != "")
            $spisok .= "<img src=_Templates/default/_Images/obj_s" . $_REQUEST["type"] . ".gif> ";
        $spisok .= $al[$g]["an_name"] . "<br>";
    }

    echo "<script>\n whichBroOp('" . $_REQUEST["var"] . "').value = '" . $arg . "'; \n whichBroOp('" . $_REQUEST["link"] . "').innerHTML = '" . $spisok . "'; \n window.close();</script>";
}
?>

</body>

</html>

