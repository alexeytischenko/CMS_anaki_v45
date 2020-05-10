<?
# Уровень отладки
error_reporting(E_ALL);

define('ROOTPATH',  realpath(dirname(__FILE__)."/") );
@setlocale('LC_CTYPE', 'ru_RU.UTF8');
@setlocale('LC_TIME', 'ru_RU.UTF8');
@mb_internal_encoding("UTF8");
@mb_regex_encoding("UTF8");

define('VKONTAKTE_AppID','');
define('VKONTAKTE_AppSecret','');
define('VKONTAKTE_Domain', $_SERVER['HTTP_HOST']);
define('FACEBOOK_AppID','');
define('FACEBOOK_AppSecret','');
define('FACEBOOK_Domain', $_SERVER['HTTP_HOST']);

define('DATABASE_NAME',  'anaki4');
define('DATABASE_HOST',  'localhost');
define('DATABASE_LOGIN', 'root');
define('DATABASE_PASSW', '');
define('DATABASE_PREF', 'anaki_');

$db_link = new mysqli(DATABASE_HOST, DATABASE_LOGIN, DATABASE_PASSW, DATABASE_NAME);

/* to ensure compatibility with PHP versions prior to 5.2.9 and 5.3.0.*/
if (mysqli_connect_error()) {
    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}
$db_link->query("SET NAMES 'utf8'");

# типы полей БД
$BDType["TextLine"] = "an_varchar";
$BDType["TextMultLine"] = "an_text";
$BDType["WYSIWYG"] = "an_text";
$BDType["TextPlain"] = "an_text";
$BDType["LoadFile"] = "an_varchar";
$BDType["IETable"] = "an_text";
$BDType["Link"] = "an_varchar";
$BDType["Choose"] = "an_varchar";
$BDType["Integer"] = "an_integer";
$BDType["Float"] = "an_float";
$BDType["Checkbox"] = "an_bool";
$BDType["Date"] = "an_date";
$BDType["User"] = "an_varchar";


# системные настройки
$sysProperties = array(
	"site_name" => "Сайт",
	"session_exp" => 240,
	"headlength" => 35,
	"innerObjectsCount" =>20,
	"defVcatalog" => 'home',
	"defLang" => 1,
	"searchAnons" => 300,
	"searchPaging" => 10,
	"mailtosend" => 10,
	"useTemplateTranslation" => true //подключение файла с переводом текстовых констант для языковых версий сайта
);

include("config_langs.php");
include("config_objects.php");

if ($sysProperties["useTemplateTranslation"])	$translation = simplexml_load_file(ROOTPATH.'/translate.xml');

?>