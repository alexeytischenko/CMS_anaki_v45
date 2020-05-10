<?
session_start();
include("config.php");
include("anaki_namespace.php");

define("MAIN_STARTED", true);

//print_R(get_defined_vars());

/* получение параметров страницы */
# url
$page = (isset($_REQUEST["page"])) ? $_REQUEST["page"] :  $sysProperties["defVcatalog"];
# язык
if(isset($_REQUEST["slang"]))	{
	reset($languages);
	while(list($key,$val) = each($languages))	if ($val["sname"]==$_REQUEST["slang"])	$lang = $key;
}	else	$lang = (isset($_REQUEST["lang"]) && intval($_REQUEST["lang"]) > 0) ? intval($_REQUEST["lang"]) : $sysProperties["defLang"];
$slang = $languages[$lang]["sname"];
# страница списка
$pg = (isset($_REQUEST["pg"])) ? intval($_REQUEST["pg"]) : 1;
# id объекта на странице
$id = (isset($_REQUEST["id"])) ? intval($_REQUEST["id"]) : 0;
# фильтр публикаций
$flt = (isset($_REQUEST["flt"])) ? intval($_REQUEST["flt"]) : 0;
$mod = (isset($_REQUEST["mod"])) ? $_REQUEST["mod"] : "";
# параметры календаря
$year = (isset($_REQUEST["year"])) ? intval($_REQUEST["year"]) : 0;
$month = (isset($_REQUEST["month"])) ? intval($_REQUEST["month"]) : 0;
$day = (isset($_REQUEST["day"])) ? intval($_REQUEST["day"]) : 0;

/* создание объекта Страница*/
$showPage = new PlainObject(0, 0, $page);

# если тип не Страница, страница запрещена к показу или происходит ошибка выборки, то переадресация на 404
if ((strlen($showPage->getErrorMessage()) > 0) || ($showPage->objectType != 1) || ($showPage->objProp[$lang]->frontaccess == 0))
	header("Location: http://".$_SERVER['HTTP_HOST']."/404.php");

//создание внутреннего объекта
if ($id > 0)	{
	$showInnerObj = new PlainObject($id, 0);
	if (strlen($showInnerObj->getErrorMessage()) > 0)	header("Location: http://".$_SERVER['HTTP_HOST']."/404.php");
}

# проверка необходимости редиректа
if (isset($showPage->values[$lang]["Редирект"]) && strlen($showPage->values[$lang]["Редирект"]) > 0)	{
    if (substr($showPage->values[$lang]["Редирект"], 0, 4) == "http")	{header("Location:".$showPage->values[$lang]["Редирект"]);exit();}
    if (substr($showPage->values[$lang]["Редирект"], 0, 3) == "www")	{header("Location:http://".$showPage->values[$lang]["Редирект"]);exit();}
	if (substr($showPage->values[$lang]["Редирект"], 0, 1) == "/")	{ header("Location:http://" . $_SERVER['HTTP_HOST'] . $showPage->values[$lang]["Редирект"]); exit();}

	header("Location:/page/".$lang."/".$showPage->values[$lang]["Редирект"]."/");exit;
}

# построение пути
//print_r($showPage);
$showPage->getPath($lang);

# подключение шаблона
$template = (isset($showPage->values[$lang]["Шаблон страницы"]) && strlen($showPage->values[$lang]["Шаблон страницы"]) > 0) ? $showPage->values[$lang]["Шаблон страницы"] : "general";


if ($showPage->objProp[$lang]->restr == 1 || (isset($showInnerObj) && $showInnerObj->objProp[$lang]->restr == 1) || $mod == "login" || $mod == "logout" || $mod == "wrong")
	require("_Templates/zona.php");

include("_Templates/".$template.".php");


$db_link->close();
?>