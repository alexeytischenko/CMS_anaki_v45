<?
/*
#	Класс ObjectsList - получение списка объектов
# 	@version 4.1 February 2013
#	11/04/2016	Fatal Bug Fix | Change in line 65: SELECT MAX(an_oid) -> SELECT MAX(an_right)
*/

class ObjectsList	{
    public $accessCondition;
    public $dateCondition;

    private $dt = array();
    public $allData;
    public $lookInside;
    public $langId;
    public $parent;
    private $left;
    private $right;
	private $level;
    public $objectType;         # тип объектов, объединяемых в список
    public $sType;              # критерий сортировки списка
    public $sOrder;             # направление сортировки
    public $listSize;           # размер списка
    public $yearsCount;
	public $lag;				# уменьшение количства объектов на первой странице списка
	private $notinlist;         # массив id-шников объектов, исключенных из списка
	private $table_name;        # имя временной таблицы для храниения всего списка
	private $skipHidden;        # флаг, учитывать ли свойство объекта ObjectProp->frontaccess (по-молчанию true, т.е. скрытые объекты в список не попадают)

    function __construct($parentObj, $lang, $oType, $needAllData, $ifLookInside, $skipHidden = true, $YY = 0, $MM = 0, $fieldIDVal = array(), $exact = false, $notin = array(), $tags = array())	{

		global $objectsTypes, $BDType, $db_link;

        $this->parent = $parentObj;
        $this->langId = $lang;
        $this->objectType = $oType;
        $this->allData = $needAllData;
        $this->lookInside = $ifLookInside;
        $this->skipHidden = $skipHidden;
		$this->notinlist = $notin;
		$this->table_name = "list".rand(10000,99999);

        //ограничения по дате
        if ($YY != 0)	$this->dateCondition = " AND year(an_date) = " . intval($YY);
        if ($MM != 0)	$this->dateCondition .= " AND month(an_date) = " . intval($MM);

        //чтение настроек родительского объекта
        if ($this->parent != 0)	{

			//сортировка
            $result = $db_link->query("SELECT an_sortorder, an_sorttype FROM ".DATABASE_PREF."objectsprop WHERE an_oid = ".intval($this->parent)." AND an_lang = ".intval($this->langId)) or die("cant't get parents properties");
			$res = $result->fetch_assoc();
            $this->sType = CustomLibs::sortby($res["an_sorttype"]);
            $this->sOrder = CustomLibs::order($res["an_sortorder"]);

            //left, right, level
            $result = $db_link->query("SELECT an_left, an_right, an_level FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".intval($this->parent)) or die("cant't get parents properties");
			$res = $result->fetch_assoc();
            $this->left = $res["an_left"];
            $this->right = $res["an_right"];
            $this->an_level = $res["an_level"];
            $result->close();
        }
        else	{
        	$result = $db_link->query("SELECT MAX(an_right) AS maxid FROM ".DATABASE_PREF."objectstree") or die("cant't get max right");
        	$this->right = $result->fetch_object()->maxid + 1;
        	$this->left = 0;
            $this->sType = CustomLibs::sortby();
            $this->sOrder = CustomLibs::order();
        }

		//фильтрация по значению поля, например для поиска
		if (sizeof($fieldIDVal) > 0)	{

			$whereTo = "";
			$what = "";
			$fldid = "";
			if ($ifLookInside)
				$whereTo = " an_left > " . $this->left . " AND an_right < " . $this->right . " AND ";
			else
				$whereTo = " an_parent = " . $this->parent . " AND ";
			$what = "(";

			for ($t = 0; $t < sizeof($fieldIDVal); $t++)	{
				if ($t > 0)	{$what .= " OR "; $fldid .= " OR ";}
				$fldid .= " an_field = '" . $db_link->real_escape_string($fieldIDVal[$t]["id"]) . "' ";
				$colName = $objectsTypes[$oType]["fields"][$fieldIDVal[$t]["id"]]["type"];
				for ($tt = 0; $tt < sizeof($fieldIDVal[$t]["vals"]); $tt++)	{
					if ($tt > 0)	$what .= " OR ";
					if ($exact)
						$what .= $BDType[$colName] ." = '" . $db_link->real_escape_string($fieldIDVal[$t]["vals"][$tt]) . "'";
					else
						$what .= $BDType[$colName] ." LIKE '%" . $db_link->real_escape_string($fieldIDVal[$t]["vals"][$tt]) . "%'";
				}
			}

			$what = $what . ") AND ";
			$query = "SELECT t.an_oid FROM ".DATABASE_PREF."objectsdata as d, ".DATABASE_PREF."objectstree as t WHERE " . $whereTo . "d.an_oid = t.an_oid AND (".$fldid.") AND " .  $what . " an_lang = " . $this->langId;
			//echo $query;
			$result = $db_link->query($query);
			$idstr = "0";
			for ($i = 0; $i < $db_link->affected_rows; $i++)	{
				$res = $result->fetch_assoc();
				if ($i != 0)	$idstr .= ",";
				$idstr .= $res["an_oid"];
			}
			$this->accessCondition = " ".DATABASE_PREF."objectstree.an_oid IN(" . $idstr . ") ";
		}	else	{
            if ($this->lookInside)	{
                    $this->accessCondition = " an_left > " . $this->left . " AND an_right < " . $this->right . " AND ";
            }
            else
                $this->accessCondition = " an_parent = " . $this->parent . " AND";

            $this->accessCondition .= " an_type = " . $this->objectType;
		}

		//исключить из списка
		if (sizeof($this->notinlist) > 0)	$this->accessCondition .= ((strlen($this->accessCondition)>0) ? " AND" : "")." ".DATABASE_PREF."objectstree.an_oid NOT IN(" . implode(",", $this->notinlist) . ") ";

		//пропускать ли скрытые объекты
        $this->accessCondition .= ($this->skipHidden) ? " AND an_frontendaccess = 1" : "";

        //теги включение
        if (isset($tags["like"]) && sizeof($tags["like"]) > 0)	{
        	$this->accessCondition .= " AND (";
	        for ($t = 0; $t < sizeof($tags); $t++)	{
	        	if ($t != 0)	$this->accessCondition .= " OR ";
	        	$this->accessCondition .= " an_tags LIKE '%,".$tags["like"][$t].",%'";
	        }
	        $this->accessCondition .= ")";
        }

        //теги исключение
        if (isset($tags["notlike"]) && sizeof($tags["notlike"]) > 0)	{
        	$this->accessCondition .= " AND (";
	        for ($t = 0; $t < sizeof($tags); $t++)	{
	        	if ($t != 0)	$this->accessCondition .= " OR ";
	        	$this->accessCondition .= " an_tags NOT LIKE '%,".$tags["notlike"][$t].",%'";
	        }
	        $this->accessCondition .= ")";
        }

        //создание временной таблицы
        $db_link->query("CREATE TEMPORARY TABLE ".$this->table_name." SELECT an_name, an_menu, an_frontendaccess, an_sortnumber, an_tags, ".DATABASE_PREF."objectstree.an_oid, an_parent, an_isfolder, an_url, an_date FROM (".DATABASE_PREF."objectstree LEFT JOIN ".DATABASE_PREF."objectsprop ON ".DATABASE_PREF."objectstree.an_oid = ".DATABASE_PREF."objectsprop.an_oid AND ".DATABASE_PREF."objectsprop.an_lang = " . intval($this->langId) . ") WHERE " . $this->accessCondition . $this->dateCondition) or die("cant't create temp table");

        // размер списка
        $result = $db_link->query("SELECT COUNT(*) AS cnt FROM ".$this->table_name) or die("cant't get object's count");
        $this->listSize = $result->fetch_object()->cnt;
    }



	# построение страницы списка подходящих объектов
    public function getList($pageNum = 0, $objC = 0, $sTp = "", $sOrd = "", $lag = 0)	{

		global $objectsTypes, $sysProperties, $BDType, $db_link;

		$this->lag = $lag;
        $listofobjects = array();
		$query = "";
		$inStr = "";

		$objOnPage = ($objC == 0) ? $sysProperties["innerObjectsCount"] : $objC;
        $sortType = ($sTp == "") ? $this->sType : CustomLibs::sortby($sTp);
        $sortOrder = (!is_bool($sOrd)) ? $this->sOrder : CustomLibs::order($sOrd);
        //var_dump($sOrd);die($sortOrder);
        $orderCondition = " ORDER BY " . $sortType . " " . $sortOrder;

        if ($pageNum == 0)	{
             //выборка всех объектов попадающих под критерии
             $query = "SELECT * FROM ".$this->table_name." " . $orderCondition;
        }	else	{

            //выборка с ограничением количества объектов
			$start_index = intval(($pageNum - 1) * $objOnPage);
			$howmany = $objOnPage;

			//если задан отступ в выбоке
			if ($this->lag > 0)	{

				//кол-во страниц, затронутых лагом
				$dirtpages = ceil($this->lag / $objOnPage);
				//кол-во записей из выборки на последней из этих страниц
				$remain = $dirtpages * $objOnPage - $this->lag;
				//определяем нужна ли вообще выборка на этой странице или лаг перекрывает
				if ($this->lag >= $pageNum * $objOnPage)	return array();
				if ($pageNum - $dirtpages == 0)	{
					$start_index = 0;
					$howmany = $remain;
				}	else	{
					$start_index = $remain + ($pageNum - $dirtpages - 1) * $objOnPage;
					$howmany = ($pageNum * $objOnPage) - $this->lag - $remain - ($pageNum - $dirtpages - 1) * $objOnPage;
				}
			}

        	$query = "SELECT * FROM ".$this->table_name." " . $orderCondition . " LIMIT ".intval($start_index).",".intval($howmany);
        }
		//echo $query;
		$result = $db_link->query($query) or die("Can't get List");

		$flag = false;
		while( $res = $result->fetch_assoc() )	{
			//теги
			if (strlen($res["an_tags"])>0)	$tg_mult["an_tags"] = split(",", stripslashes(preg_replace(array("/^,/", "/,$/"), "",$res["an_tags"])));
			else	$tg_mult["an_tags"] = array();

			$listofobjects[] = array("an_name" => stripslashes($res["an_name"]), "an_sortnumber" => $res["an_sortnumber"], "an_frontendaccess" => $res["an_frontendaccess"], "an_oid" => $res["an_oid"], "an_parent" => $res["an_parent"],"an_menu" => $res["an_menu"], "an_url" => stripslashes($res["an_url"]), "an_dateH" => stripslashes($res["an_date"]), "an_date" => strtotime($res["an_date"]), "an_isfolder" => $res["an_isfolder"], "an_tags" => stripslashes($res["an_tags"]), "an_multiple" => $tg_mult);

			if ($flag) $inStr .= ", ";
			$inStr .= $res["an_oid"];
			$flag = true;
		}

		//значения полей объектов
     	if ($this->allData && sizeof($listofobjects) > 0)	{
           $result = $db_link->query("SELECT * FROM ".DATABASE_PREF."objectsdata WHERE an_oid IN (" . $inStr . ") AND an_lang = ".intval($this->langId)) or die("Can't get fields values");

			while( $res = $result->fetch_assoc() )	{

				$id = $res["an_oid"];
				reset($listofobjects);
				while (list($key,$val) = each($listofobjects))	{
					if ($val["an_oid"] == $id)	{
						$index = $key;
						break;
					}
				}
				$namandtype = ObjectData :: getFieldNameAndType($this->objectType, $res["an_field"]);
				$listofobjects[$index][$namandtype["name"]] = stripslashes($res[$BDType[$namandtype["type"]]]);
				//дополнительная обработка мультиплов
				if ($namandtype["type"] == "TextMultLine")
					$listofobjects[$index]["an_multiple"][$namandtype["name"]] = split("#field#", stripslashes($res[$BDType[$namandtype["type"]]]));
				if ($namandtype["type"] == "Link" || $namandtype["type"] == "Choose" || $namandtype["type"] == "User")
					$listofobjects[$index]["an_multiple"][$namandtype["name"]] = split(",", stripslashes(preg_replace(array("/^,/", "/,$/"), "",$res[$BDType[$namandtype["type"]]])));
			}
        }

		return $listofobjects;
    }

    # календарь публикаций
	public function getCalendar()	{
        global $db_link;
		$calendar = array();

		$query = "SELECT * FROM (SELECT DISTINCT year(an_date) AS cyear, month(an_date) AS cmonth FROM ".$this->table_name.") AS t ORDER BY cyear DESC, cmonth DESC";

		$result = $db_link->query($query);
		while( $res = $result->fetch_assoc() )	$calendar[$res["cyear"]][$res["cmonth"]] = 1;
		return $calendar;
    }

	# количество страниц, на которое можно разделить все объекты при заданных параметрах
    public function getPageCount($objC = 0)	{
        if ($objC == 0)	$pageCount = 0;
         else
            $pageCount = ceil(($this->listSize + $this->lag) / $objC);
        return $pageCount;
    }

	# деструктор
	function __destruct() {
        global $db_link;
       // print_r($db_link);
        $db_link->query("DROP TEMPORARY TABLE ".$this->table_name);

    }

}