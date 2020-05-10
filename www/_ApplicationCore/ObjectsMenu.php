<?
/*
#	Класс ObjectsMenu - Работа со структурой дерева объектов для бек-енда и меню сайта
# 	@version 4.1 February 2012
*/

Class ObjectsMenu	{
	private $accessCondition;
	private $typeConditions;
	private $dt;
	private $langId;
	private $parent;			# родительский объект
	private $onlyFolders;		# получать только папки
	private $forceOpenAll;		# принудительно расскрывать все папки
	private $an_menu;			# только объекты разрешенные к показу в меню (an_menu=1)
	private $skipHidden;        # пропускать скрытые объекты
	private $exType;			# получать только объекты конкретных типов
	private $margin;			# сколько уровней дерева получать; 0 - все уровни
	private $pageNum;
	private $objCount;
	private $openFolders;       # папки, открытые для пользователя административного интерфейса
	public $menuSize;			# количество пунктов меню

    function __construct($parent, $lang, $exType = array(), $margin = 0, $an_menu = true, $skipHidden = true, $forceOpen = true, $onlyFolders = false, $f_array = array())		{    	global $db_link;

		$this->openFolders = (array)$f_array;
	    $this->langId = intval($lang);
        $this->pageNum = 0;
        $this->objCount = 0;
        $this->parent = intval($parent);
        $this->margin = ($margin > 0) ? intval($margin) : 0;
        $this->forceOpenAll = ($forceOpen) ? true : false;
		$this->onlyFolders = ($onlyFolders) ? true : false;
		$this->an_menu = ($an_menu) ? true : false;
		$this->skipHidden = ($skipHidden) ? true : false;
        $this->accessCondition = ($this->an_menu) ? " AND an_menu = 1" : "";
        $this->accessCondition .= ($this->onlyFolders) ? " AND an_isfolder = 1" : "";
        $this->accessCondition .= ($this->skipHidden) ? " AND an_frontendaccess = 1" : "";
        if (sizeof($exType) > 0) $this->typeConditions = " AND an_type IN (".implode(",", $exType).")";

        //кол-во пунктов меню первого уровня
        $result = $db_link->query("SELECT COUNT(*) AS cnt FROM ".DATABASE_PREF."objectstree, ".DATABASE_PREF."objectsprop WHERE an_parent =".$this->parent.$this->typeConditions." AND ".DATABASE_PREF."objectstree.an_oid = ".DATABASE_PREF."objectsprop.an_oid  AND ".DATABASE_PREF."objectsprop.an_lang = ".$this->langId.$this->accessCondition) or die("Ошибка Can't get objects count! (parent = ".$this->parent.")");
		$this->menuSize = $result->fetch_object()->cnt;

	}


	# получение дерева объектов
	public function getTree($page=0, $objcount=0)	{
        global $db_link;

		if ($page > 0 )	$this->pageNum = $page;
		if ($objcount > 0)	$this->objCount = $objcount;
		$stype = CustomLibs::sortby();
		$sorder = CustomLibs::order();

		if ($this->parent != 0) {
			$result = $db_link->query("SELECT an_sortorder, an_sorttype FROM ".DATABASE_PREF."objectsprop WHERE an_oid = ".$this->parent." AND an_lang = ".intval($this->langId)) or die("Ошибка Can't get object properties! (an_oid = ".$this->parent.")");
			$res = $result->fetch_assoc();
	        $stype = CustomLibs::sortby($res["an_sorttype"]);
	        $sorder = CustomLibs::order($res["an_sortorder"]);
		}

        $this->BuildTree($this->parent, $stype, $sorder, 1);

		return $this->dt;
	}

    # получение объектов одного уровня
    private function BuildTree($root, $sortType, $sortOrder, $marg, $parent_url = "")	{
		//получение вложенных объектов
		global $sysProperties, $db_link, $adminOpenFolders;
		$limitadd = "";
		if (($this->pageNum > 0) && ($this->objCount > 0))	{
			$limitadd = " LIMIT ".($this->pageNum - 1) * $this->objCount.",". $this->objCount;
            //для вложенных объектов (если при этом их родитель isopen = 1) разбиение на страницы должно быть отменено, поэтому обнуляем соответствующие параметры перед поиском внутренних объектов
            $this->pageNum = 0;
            $this->objCount = 0;
		}

		$query = "SELECT an_name, an_menu, an_date, an_modifdate, an_url, an_parent, an_restr, ".DATABASE_PREF."objectstree.an_oid, an_type, an_sortnumber, an_sortorder, an_sorttype, an_date, an_isfolder, an_inside, an_frontendaccess FROM (".DATABASE_PREF."objectstree LEFT JOIN ".DATABASE_PREF."objectsprop ON ".DATABASE_PREF."objectstree.an_oid = ".DATABASE_PREF."objectsprop.an_oid AND ".DATABASE_PREF."objectsprop.an_lang = ".intval($this->langId).") WHERE an_parent = ".intval($root)." " . $this->typeConditions . " " . $this->accessCondition . " ORDER BY " . $sortType . " " . $sortOrder . $limitadd;
		//echo $query;
		$result = $db_link->query($query) or die("Ошибка Can't get objects (parent = ".$root.")");

		while($res = $result->fetch_assoc())	{
			//наполнение item в результирующий array
			$dr["an_oid"] = $res["an_oid"];

			$tmpName = stripslashes($res["an_name"]);
			if (strlen($tmpName) > $sysProperties["headlength"]) $tmpName = substr($tmpName, 0, $sysProperties["headlength"]);
			if (strlen($tmpName) == 0)	$tmpName = "obj" . $res["an_oid"] . "(без названия)";
			$dr["an_altname"] = $tmpName;
			$dr["an_name"] = stripslashes($res["an_name"]);

            $dr["an_frontendaccess"] = $res["an_frontendaccess"];
			$dr["an_menu"] = $res["an_menu"];
			$dr["an_date"] = strtotime($res["an_date"]);
			$dr["an_dateH"] = stripslashes($res["an_date"]);
			$dr["an_modifdate"] = strtotime($res["an_modifdate"]);
			$dr["an_url"] = $res["an_url"];
			$dr["an_parenturl"] = $parent_url;
			$dr["an_parent"] = $res["an_parent"];
			$dr["an_restr"] = $res["an_restr"];
            $dr["an_type"] = $res["an_type"];
            $dr["an_isfolder"] = (bool)$res["an_isfolder"];
            $dr["an_inside"] = (bool)$res["an_inside"];
            $dr["an_level"] = $marg;

			//кол-во вложенных элементов
           	$resultC = $db_link->query("SELECT COUNT(*) AS cnt FROM ".DATABASE_PREF."objectstree WHERE an_parent = ".intval($dr["an_oid"])) or die("cant't get children count");
        	$dr["an_childrencount"] = $resultC->fetch_object()->cnt;

			if ($sortType=="an_sortnumber") $dr["an_sortvalue"] = number_format($res["an_sortnumber"], 2, '.', '');
			elseif ($sortType=="an_date") $dr["an_sortvalue"] = date('Y-m-d', $dr["an_date"]);
			else $dr["an_sortvalue"] = "";

			$this->dt[] = $dr;
			//вызов метода для вложенных объектов
            if (($this->margin==0 || $marg < $this->margin) && $res["an_isfolder"] && (!$res["an_inside"] || $this->forceOpenAll) && (in_array($res["an_oid"], $this->openFolders) || $this->forceOpenAll))	{
				//формирование критериев сортировки
                $sType = CustomLibs::sortby($res["an_sorttype"]);
                $sOrder = CustomLibs::order($res["an_sortorder"]);

                $this->BuildTree($res["an_oid"], $sType, $sOrder, $marg + 1, ((strlen($dr["an_url"])>0) ? $dr["an_url"] : $parent_url));
			}
		}
	}

	# количество страниц, на которое можно разделить все объекты при заданных параметрах
    public function getPageCount($objC = 0)	{
        if ($objC == 0)	return 0;
         else  return ceil($this->menuSize / $objC);
    }


	# получение массива, удобного для построения меню сайта
    public function getSiteMenu()	{
        global $db_link;

        $sitemenu = array();
        $this->getTree();

        reset($this->dt);
        while (list(, $val) = each($this->dt))	{        	if ($val["an_parent"] == $this->parent)	$sitemenu[0][] = $val;
        	else	$sitemenu[$val["an_parent"]][] = $val;
        }
        return  $sitemenu;
    }


	# получение папок по маркерам
    public static function getFoldersTree($root, $lang)	{
        global $db_link;

		$left = 0;
		$right = 0;
		if ($root == 0)		{
			$result = $db_link->query("SELECT MAX(an_right) AS mright FROM ".DATABASE_PREF."objectstree") or die("Ошибка Can't get MAX right marker!");
			$right = intval($result->fetch_object()->mright);
		}
		else	{
			$result = $db_link->query("SELECT an_left, an_right FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".intval($root)) or die("Ошибка Can't get right&left markers!");
			$res = $result->fetch_assoc();
			$left = $res["an_left"];
			$right = $res["an_right"];
		}

		$result = $db_link->query("SELECT an_name, an_frontendaccess, an_right, an_left, an_level,  ".DATABASE_PREF."objectstree.an_oid FROM ".DATABASE_PREF."objectstree, ".DATABASE_PREF."objectsprop WHERE an_left BETWEEN ".$left." AND ".$right." AND an_isfolder = 1 AND ".DATABASE_PREF."objectstree.an_oid = ".DATABASE_PREF."objectsprop.an_oid AND ".DATABASE_PREF."objectsprop.an_lang = ".intval($lang)." ORDER BY an_left ASC") or die("can't get object");

		while($res = $result->fetch_assoc())	{

	        $tmpName = stripslashes($res["an_name"]);
			if (strlen($tmpName) == 0)	$tmpName = "obj" . $res["an_oid"] . "(без названия)";
	        $dtRes[] = array("an_oid" => $res["an_oid"],
	        				"an_name" => stripslashes($res["an_name"]),
		        			"an_altname" => $tmpName,
	        				"an_margin" => $res["an_level"]
	        );
	    }

	    $result->close();
	    return $dtRes;
	}




}

?>