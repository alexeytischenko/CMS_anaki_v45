<?php

/*
#	Класс CustomLibs - Вспомогательные методы
# 	@version 4.3 November 2013
*/

class CustomLibs  {

	//транслитерация названий
	public static function transliteral	($str)	{

    	$chars = array("А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"j","З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"i","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"j","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y","ы"=>"i","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"," "=> "-", "/"=> "-");
    	return preg_replace('/[^.A-Za-z0-9_\-]/', '', strtr($str,$chars));

	}

	// поле сортировки списков
    public static function sortby ($str="")	{

    	$return = "an_sortnumber";
    	switch ($str)	{
         case "date":
             $return = "an_date";
             break;
         case "name":
             $return = "an_name";
             break;
     	}
        return $return;
    }

	// порядок сортировки списков
    public static function order ($booly = true)	{

    	if ($booly)	return "DESC";
    	else	return "ASC";

    }

	// русское название месяцов
	public static function rus_month($id, $lower = true, $YaLetter = true)	{

    	if($YaLetter)
    	$months = array(0 => "Января", 1 => "Февраля", 2 => "Марта", 3 => "Апреля", 4 => "Мая", 5 => "Июня", 6 => "Июля", 7 => "Августа", 8 => "Сентября", 9 => "Октября", 10 => "Ноября", 11 => "Декабря");
    	else
    	$months = array(0 => "Январь", 1 => "Февраль", 2 => "Март", 3 => "Апрель", 4 => "Май", 5 => "Июнь", 6 => "Июль", 7 => "Август", 8 => "Сентябрь", 9 => "Октябрь", 10 => "Ноябрь", 11 => "Декабрь");
    	
    	if ($lower)	return mb_strtolower($months[$id-1]);
    	else
    	return $months[$id-1];

	}

	// подстановка значения в поле формы
	public static function getFormValue($fname, $curvalue = "")	{

		if (isset($_POST[$fname]) && strlen($_POST[$fname]) > 0)	return htmlspecialchars($_POST[$fname]);
		if (isset($curvalue) && strlen($curvalue) > 0)	return htmlspecialchars($curvalue);
        return "";
	}

	//возвращает дату словами
	public static function getHumanDate ($dt, $minutes=true)	{

	    $tod = strtotime("now");
	    $return = date('d ', $dt). CustomLibs::rus_month(date('m', $dt)). date(' Y ', $dt) .(($minutes)? date(' H:i', $dt) : "");

		if (round(abs($tod-$dt)/60/60/24)==1) $return = "вчера " . (($minutes)? date('H:i', $dt) : "");
		if (round(abs($tod-$dt)/60/60/24)==0) $return = "сегодня " . (($minutes)? date('H:i', $dt) : "");

	    return $return;
	}

	//конвертирует br в переносы строк - ф-ция обратная nl2br()
	public static function nl2br_revert($string) {
	    return preg_replace('`<br(?: /)?>([\\n\\r])`', '$1', $string);
	}

	//создание файла sitemap.xml
	public static function sitemap($parent, $lang, $otypes = array()) {
		global $languages;

	    $w = fopen(ROOTPATH . "/sitemap". (($lang > 1) ? "_" . $languages[$lang]["sname"] : "") .".xml",'w');
		$text = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$text .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

		$pages = new ObjectsMenu($parent, $lang, $otypes);
		$pagesList = $pages->getTree();

		for ($i = 0; $i < sizeof($pagesList); $i++)	{
		 	$text .= "<url>\n
	<loc>http://".$_SERVER['HTTP_HOST']."/page/" . ((strlen($pagesList[$i]["an_url"])>0) ? $pagesList[$i]["an_url"] : $lang."/" .$pagesList[$i]["an_oid"] . "/" .PlainObject::getParentUrl($pagesList[$i]["an_oid"], $lang)) . "/</loc>\n
	<lastmod>".date('Y-m-d', $pagesList[$i]["an_modifdate"])."</lastmod>\n
	<priority>0.5</priority>
</url>\n";
		}
		$text .= "</urlset>";

		fwrite($w,$text);

		fclose($w);
	}
}


// ф-ции для работы с шаблонами
# ф-ция проверки необходимости выделять активный пункт меню
function ifactive($oid, $ignoreInner = true)	{
	global $showPage, $showInnerObj;
	
	if($ignoreInner || !isset($showInnerObj))
		return in_array($oid, $showPage->pathIds);
	else {
		$showInnerObj->getPath($lang);
		return in_array($oid, $showInnerObj->pathIds);
	}
	
}

# ф-ция показа баннера
function bannerid($val)	{
	preg_match("/(\d{1,})$/", $val, $matches);
	return $matches[0];
}

# ф-ция получения заголовка
function getheader($lang, $ignorInner = false)	{
 	global $showPage, $showInnerObj, $id;
 	$head = "";
 	if ($ignorInner || $id <= 0) 	$head = (isset($showPage->values[$lang]["an_header"]) && strlen($showPage->values[$lang]["an_header"]) > 0) ? $showPage->values[$lang]["an_header"] : $showPage->objProp[$lang]->name;
	else	$head = $showInnerObj->objProp[$lang]->name;
    return strip_tags($head);
}

# ф-ция получения title
function gettitle($lang, $ignorInner = false)	{
 	global $showPage, $showInnerObj, $id;
 	$head = "";
 	if ($ignorInner || $id <= 0) 	$head = (isset($showPage->values[$lang]["an_title"]) && strlen($showPage->values[$lang]["an_title"]) > 0) ? $showPage->values[$lang]["an_title"] : $showPage->values[$lang]["an_name"];
	else	$head = $showInnerObj->values[$lang]["an_name"];

	return strip_tags($head);
}

# ф-ция получения meta desc
function getmetadesc($lang, $ignorInner = false)	{
 	global $showPage, $showInnerObj, $id;
 	$metad = "";
 	if ($ignorInner || $id <= 0) 	$metad = $showPage->values[$lang]["an_description"];
	else	$metad = $showInnerObj->values[$lang]["an_description"];

	return $metad;
}

# ф-ция получения meta key
function getmetakey($lang, $ignorInner = false)	{
 	global $showPage, $showInnerObj, $id;
 	$metak = "";
 	if ($ignorInner || $id <= 0) 	$metak = $showPage->values[$lang]["an_keywords"];
	else	$metak = $showInnerObj->values[$lang]["an_keywords"];

	return $metak;
}

?>