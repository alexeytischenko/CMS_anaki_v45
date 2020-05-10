<?
/*
#	Класс Search - Поиск по сайту
# 	@version 4.1 February 2012
*/

    class Search	{

        private $accessCondition;
		private $dt;
        private $langId;
        private $parent;
        private $left;
        private $right;
		private $level;
        private $objectType;
		private $fieldid;

        function  __construct($parentObj, $lang, $oType, $fieldID, $fieldVal, $exact = false)	{

			global $db_link, $objectsTypes, $BDType;

            $this->parent = $parentObj;
            $this->langId = $lang;
            $this->objectType = $oType;
			$this->fieldid = $fieldID;

            //чтение настроек родительского объекта
            $this->setParentProp();
			$colName = $BDType[$objectsTypes[$oType]["fields"][$fieldID]["type"]];

			$what = "";
			$whereTo = "an_type = " . $this->objectType;
			if ($this->right > 0) $whereTo .= " AND an_left > " . $this->left . " AND an_right < " . $this->right . " AND ";
			else  $whereTo .= " AND ";

			if ($exact)	$what = " = '" . $db_link->real_escape_string($fieldVal) . "' ";
			else	$what = " LIKE '%" . $db_link->real_escape_string($fieldVal) . "%' ";

			$query = "SELECT t.an_oid FROM ".DATABASE_PREF."objectsdata as d, ".DATABASE_PREF."objectstree as t WHERE " . $whereTo . "d.an_oid = t.an_oid AND an_field = '" . $db_link->real_escape_string($fieldID) . "' AND an_lang = " . $this->langId . " AND " . $colName . $what ;
			$result = $db_link->query($query) or die("Ошибка " . $query);
			$idstr[] = 0;
			while($res = $result->fetch_assoc())	$idstr[] = $res["an_oid"];

			$query = "SELECT t.an_oid FROM ".DATABASE_PREF."objectsprop as d, ".DATABASE_PREF."objectstree as t WHERE " . $whereTo . "d.an_oid = t.an_oid AND an_lang = " . $this->langId . " AND an_name" . $what;
			$result = $db_link->query($query) or die("Ошибка " . $query);
			$idstr1[] = 0;
			while($res = $result->fetch_assoc())	$idstr1[] = $res["an_oid"];

			$this->accessCondition = " ".DATABASE_PREF."objectstree.an_oid IN(" . implode(",", array_unique (array_merge ($idstr, $idstr1))) . ") ";

        }


        # читает свойства родительского объекта и устанавливает поля объекта Search
        private function setParentProp()	{        	global $db_link;

            if ($this->parent != 0)	{
                //left, right, level
                $result = $db_link->query("SELECT an_left, an_right, an_level FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".intval($this->parent)) or die("cant't get parents properties");
				$res = $result->fetch_assoc();
                $this->left = $res["an_left"];
                $this->right = $res["an_right"];
                $this->an_level = $res["an_level"];

            }
        }

		# построение списка подходящих объектов
        public function getList($url = "")	{
 			global $objectsTypes, $BDType;
 			global $db_link;

            //выборка всех объектов попадающих под критерии
            $query = "SELECT an_name, an_sortnumber, ".DATABASE_PREF."objectstree.an_oid, an_isfolder, an_url, an_type, an_date FROM (".DATABASE_PREF."objectstree LEFT JOIN ".DATABASE_PREF."objectsprop ON ".DATABASE_PREF."objectstree.an_oid = ".DATABASE_PREF."objectsprop.an_oid AND ".DATABASE_PREF."objectsprop.an_lang = " . intval($this->langId) . ") WHERE " . $this->accessCondition . " AND an_frontendaccess = 1 ";
			$result = $db_link->query($query) or die("Can't get List");

            $listofobjects = array();
			$inStr = "0";
			$i = 0;

			while($res = $result->fetch_assoc())	{
				$listofobjects[$i]["an_name"] = stripslashes($res["an_name"]);
				$listofobjects[$i]["an_oid"] = $res["an_oid"];
				$listofobjects[$i]["an_type"] = $res["an_type"];
				$listofobjects[$i]["an_url"] = stripslashes($res["an_url"]);
				$listofobjects[$i]["an_date"] = stripslashes($res["an_date"]);

				if (strlen($listofobjects[$i]["an_url"]) == 0)	{
					$listofobjects[$i]["an_url"] = $url;
					$listofobjects[$i]["linkid"] = $listofobjects[$i]["an_oid"];
				}

				if ($i > 0) $inStr .= ", ";
				else  $inStr = "";
				$inStr .= $listofobjects[$i]["an_oid"];

				$i++;
			}

			//текст найденой страницы
			if (strlen($objectsTypes[$this->objectType]["searchDescription"]) > 0)	{

				$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."objectsdata WHERE an_oid IN (" . $inStr . ") AND an_field = '".$objectsTypes[$this->objectType]["searchDescription"]."' AND an_lang = ".intval($this->langId)) or die("Can't get fields values");

				while($res = $result->fetch_assoc())	{
					$id = $res["an_oid"];
					reset($listofobjects);
					while (list($key,$val) = each($listofobjects))	{
						if ($val["an_oid"] == $id)	{
							$index = $key;
							break;
						}
					}
					$namandtype = ObjectData::getFieldNameAndType($this->objectType, $res["an_field"]);
					$listofobjects[$index]["searchtext"] = stripslashes($res[$BDType[$namandtype["type"]]]);

				}
			}

			return $listofobjects;
        }

}