<?

session_start();
include("../config.php");
if (!isset($_SESSION['an_uid']) || $_SESSION['admin_IP'] != $_SERVER['REMOTE_ADDR'] || time() > $_SESSION["expire"]) die();
$_SESSION["expire"] =  time() + (60 * $sysProperties["session_exp"]);

foreach ($_GET['listItem'] as $position => $item) :
	$db_link->query("UPDATE ".DATABASE_PREF."fotos SET an_sort=$position WHERE an_parent = '".$_GET['oid']."' AND an_oid = ".$item);
endforeach;

//print_r ($sql);
?>