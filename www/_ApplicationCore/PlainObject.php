<?
/*
#	Класс PlainObject - Работа с объектами структуры сайта
# 	@version 4.2 February 2013
#	28.01.2016 добавлена ф-ция setSort($lang, $val)
*/


Class PlainObject {
	public $oid;				# id объекта
	public $url;				# url
	public $objectType;			# тип объекта
	public $parent;				# родительский объект
	private $an_left;			# левый маркер дерева
	private $an_right;			# правый маркер дерева
	private $an_level;			# уровень вложенности
	public $createDate;			# дата создания объекта
	public $modifDate;			# дата последнего изменения
	public $modifUser;			# автор последнего изменения
	public $owner;				# владелец объекта
	public $isFolder;			# флаг: может ли объект содержать вложенные объекты
	public $inside;				# флаг: показывать вложенные объекты на отдельной странице
	public $values;				# массив всех данных объекта
//	public $objData;			# значения полей объекта
	public $objProp;			# свойства объекта
	public $path;				# массив объектов верхних уровней
	public $pathIds;			# массив id объектов верхних уровней

	private $error = array();   # массив сообщений об ошибках
	public $errorStatus;    	# флаг: в процессе выполнения метода произошла ошибка
	private $action = array();  # массив сообщений о текущих изменениях
	private $notice = array();	# массив вспомогательных сообщений


	# конструктор класса объекта
	function __construct($oid = -1, $objectType = 0, $url = "", $autobuild = true) {
		GLOBAL $db_link;
		$this->errorStatus = false;
		$this->notice[] = "PlainObject: создание экземпляра объекта";

 		$this->oid = ($oid != -1) ? intval($oid) : -1;
		$this->objectType = ($objectType != 0) ? intval($objectType) : 0;
		$this->url = $url;

		if ($this->oid > 0 && $this->objectType == 0)  {
			try	{
				if (!$result = $db_link->query("SELECT an_type FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".$oid))
					throw new Exception("PlainObject: Ошибка Can't get object type! (id = ".$oid.")");
				$this->objectType = $result->fetch_object()->an_type;
			} catch (Exception $e)	{
				$this->error[] = $e->getMessage();
	            $this->errorStatus = true;
			}
			$result->close();
		}

		if (strlen($this->url) > 0 && $this->oid == 0)	{
			try	{
				if (!$result = $db_link->query("SELECT an_oid, an_type FROM ".DATABASE_PREF."objectstree WHERE an_url = '".$db_link->real_escape_string($this->url)."'"))
					throw new Exception("PlainObject: Ошибка Can't get object type and id!! (url = ".$this->url.")");
				$res = $result->fetch_object();
				$this->objectType = $res->an_type;
				$this->oid = $res->an_oid;

			} catch (Exception $e)	{
				$this->error[] = $e->getMessage();
	            $this->errorStatus = true;
			}
			$result->close();
		}

		// проверка существует ли такой объект
		if ($this->oid >= 0 && $this->objectType == 0)	{
			$this->error[] = "PlainObject: Ошибка. Объект(oid = ".$this->oid.") не существует";
			$this->errorStatus = true;
		}
		// если необходимо - можно сразу загрузить в него данные
		elseif ($this->oid > 0 && $autobuild)
			$this->buildObject();

}

	# загрузка данных объекта
    public function buildObject()	{

    /*
     и свойства и данные на выходе пихать в один массив
     Этот метод будет возвращать массив с удобной структурой
     а структура объекта не изменится.
   */

		GLOBAL $db_link, $objectsTypes, $BDType;

		try {

			//получение значений динамических полей объекта
			$this->values = ObjectData :: getData($this->oid, $this->objectType);

	        // загрузка основных языконезависимых полей ("ядра") объекта / таблица ".DATABASE_PREF."objectstree
			if (!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."objectstree WHERE an_oid =".$this->oid))	throw new Exception("PlainObject: Ошибка Can't get object (oid = ".$this->oid.")");

			$res = $result->fetch_assoc();
			$this->values["an_url"] = $this->url = stripslashes($res["an_url"]);
			$this->values["an_type"] = $this->objectType;
			$this->values["an_date"] = $this->createDate = strtotime($res["an_date"]);
			$this->values["an_dateH"] = stripslashes($res["an_date"]);
			$this->values["an_parent"] = $this->parent = $res["an_parent"];

			$this->an_left = $res["an_left"];
			$this->an_right = $res["an_right"];
			$this->an_level = $res["an_level"];
			$this->modifDate = strtotime($res["an_modifdate"]);
			$this->modifUser = $res["an_modifuser"];
			$this->owner = $res["an_owner"];
			$this->isFolder = (bool)$res["an_isfolder"];
			$this->inside = (bool)$res["an_inside"];

            //получение свойств объекта
            $this->objProp = ObjectProp :: getData($this->oid);
            reset($this->objProp);
            while(list($key, $val) = each($this->objProp))	{
            	$this->values[$key]["an_name"] = $val->name;
            	$this->values[$key]["an_title"] = $val->title;
            	$this->values[$key]["an_header"] = $val->header;
            	$this->values[$key]["an_tags"] = $val->tags;
            	$this->values[$key]["an_keywords"] = $val->keywords;
            	$this->values[$key]["an_description"] = $val->description;
            }

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}

        $this->notice[] = "PlainObject: получены данные объекта, oid = " . $this->oid;
        if ($result instanceof mysqli_result) $result->close();
		return true;
    }


	# создание нового объекта
	public function createObject($url, $par, $createD, $modifU, $isF, $inside, $objD, $objP) {
		global $languages, $objectsTypes, $BDType, $db_link;

		// если УРЛ - обязательный параметр
		if ($objectsTypes[$this->objectType]["isurl"])	{
			# очистка от всех пробельных и русских символов
			$oldURL = $url;
			$url = preg_replace(array("/\s*/", "/[а-яА-Я]*/"), "", $url);
			if (strlen($url) == 0 || $this->checkUrl($url))	{
				while(true)	{
					$url = "obj".rand(10000,99999);
					if (!$this->checkUrl($url)) break;
				}
			}
			$this->notice[] = "PlainObject: URL проверен ". (($oldURL != $url)? " и изменен " : "") ." (".$url.")";
		}

		// сдвиг вправо: пересчитываются маркеры дерева an_left, an_right объектов правее данного / ".DATABASE_PREF."objectstree
        if (!$this->shiftTreeR($par))	{
        	$this->error[] = "PlainObject: ошибка пересчета маркеров дерева";
        	$this->errorStatus = true;
        	return false;
        } else $this->notice[] = "PlainObject: Пересчитаны маркеры дерева объектов";

        # инициализация полей объекта
        $this->url = $url;
        $this->parent = $par;
        $this->createDate = strtotime($createD);
        $this->modifDate = strtotime("now");//date("Y-m-d H:i");
        $this->modifUser = $modifU;
		$this->owner = $modifU;
        $this->isFolder = (bool)$isF;
        $this->inside = (bool)$inside;

		if(!$db_link->query("INSERT INTO ".DATABASE_PREF."objectstree (an_url, an_type, an_parent, an_date, an_modifdate, an_modifuser, an_isfolder, an_inside, an_owner, an_left, an_right, an_level) VALUES ('".$db_link->real_escape_string($this->url)."', '".intval($this->objectType)."', '".intval($this->parent)."', '".date("Y-m-d H:i", $this->createDate)."', '".date("Y-m-d H:i", $this->modifDate)."', ".intval($this->modifUser).", ".($this->isFolder ? 1 : 0).", ".($this->inside ? 1 : 0).", ".intval($this->modifUser).", ".$this->an_left.", ".$this->an_right.", ".$this->an_level.")"))
			die("can't create object");
		else 	$this->notice[] = "PlainObject: основные данные нового объекта записаны";
		$this->oid = $db_link->insert_id;

  		reset($languages);
  		while(list($key, ) = each($languages))	{
  			//Запись свойств и значений динамических полей объекта
  			//если не установлена сотрировка
  			if ($objP[$key]->sortnumber==0) $objP[$key]->sortnumber = doubleval($this->oid);
  			//объект скрывается если не установлено название при обязательном параметре isname
  			if ($objectsTypes[$this->objectType]["isname"] && $objP[$key]->name=="") $objP[$key]->frontaccess = false;
  			if (isset($objP[$key]) && ($objP[$key] instanceof ObjectProp))	$objP[$key]->setData($this->oid, $key);
  			else {
  				$this->notice[] = "PlainObject: objP[".$key."] не является экземпляром класса ObjectProp";
  			}
     		if (isset($objD[$key]) && ($objD[$key] instanceof ObjectData))	$objD[$key]->setData($this->oid, $this->objectType, $key);
     		else {
     			$this->notice[] = "PlainObject: objD[".$key."] не является экземпляром класса ObjectData";
     		}
  		}
        $this->action[] = "PlainObject: Объект создан oid = " . $this->oid;

		//возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_createObject"))	PlainObject_createObject($this);

		return (!$this->errorStatus) ? true : false;
	}

	# обновление объекта
	public function updateObject($url, $par, $oldPar, $createD, $modifU, $isF, $inside, $objD, $objP) {
		global $languages, $objectsTypes, $BDType, $db_link;


		// если УРЛ - обязательный параметр
		if ($objectsTypes[$this->objectType]["isurl"])	{
			// очистка от всех пробельных и русских символов

			$oldURL = $url;
			$url = preg_replace(array("/\s*/", "/[а-яА-Я]*/"), "", $url);
			if (strlen($url) == 0 || $this->checkUrl($url))	{
				while(true)	{
					$url = "obj".rand(10000,99999);
					if (!$this->checkUrl($url)) break;
				}
			}
			$this->notice[] = "PlainObject: URL проверен ". (($oldURL != $url)? " и изменен " : "") ." (".$url."), oid = " . $this->oid;
		}

		# при смене родителя проверяется не выбран ли в качестве родителя сам объект или его потомок
		$correctparent = true;
		if ($par != $oldPar)	{
			$tempPar = $par;
			if ($par != $this->oid)	{
				while (true)	{
					if (!$result = $db_link->query("SELECT an_parent FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".intval($tempPar)))
						die("Ошибка Can't get parent object, while checking the tree! (id = ".$tempPar.")");
					$tempPar = $result->fetch_object()->an_parent;
					if ($tempPar == $this->oid)	{
						$correctparent = false;
						break;
					}
					if ($tempPar == 0)	break;
				}
				if ($result instanceof mysqli_result) $result->close();
			}
			else	$correctparent = false;
		}

		if (!$correctparent)	{
			$this->error[] = "PlainObject: Некорректное значение родительского объекта, oid = " . $this->oid;
			$this->errorStatus = true;
			return false;
		}	else	$this->notice[] = "PlainObject: корректное значение родительского объекта, oid = " . $this->oid;

        # инициализация полей объекта
        $this->url = $url;
        $this->parent = $par;
        $this->createDate = strtotime($createD);
        $this->modifDate = strtotime("now");//date("Y-m-d H:i");
        $this->modifUser = $modifU;
        $this->isFolder = (bool)$isF;
        $this->inside = (bool)$inside;

		if(!$db_link->query("UPDATE ".DATABASE_PREF."objectstree SET an_url = '".$db_link->real_escape_string($this->url)."', an_parent = ".intval($this->parent).",  an_date = '".date("Y-m-d H:i", $this->createDate)."', an_modifdate = '".date("Y-m-d H:i", $this->modifDate)."', an_modifuser = ".intval($this->modifUser).", an_isfolder = ".($this->isFolder ? 1 : 0).", an_inside = ".($this->inside ? 1 : 0)." WHERE an_oid = ".$this->oid))
			die("can't update object (id = ".$this->oid. ")");
		else 	$this->notice[] = "PlainObject: основные данные объекта обновлены, oid = " .$this->oid;

		// обновление значений динамических полей и свойств объекта
		reset($languages);
  		while(list($key, ) = each($languages))	{
  			//Запись свойств и значений динамических полей объекта
  			//объект скрывается если не установлено название при обязательном параметре isname
  			if ($objectsTypes[$this->objectType]["isname"] && $objP[$key]->name=="") $objP[$key]->frontaccess = false;
  			if (isset($objP[$key]) && ($objP[$key] instanceof ObjectProp))	$objP[$key]->setData($this->oid, $key);
  			else {
  				$this->error[] = "objP[".$key."] не является экземпляром класса ObjectProp, oid = " .$this->oid;
  			}
     		if (isset($objD[$key]) && ($objD[$key] instanceof ObjectData))	$objD[$key]->setData($this->oid, $this->objectType, $key);
     		else {
     			$this->error[] = "objD[".$key."] не является экземпляром класса ObjectData, oid = " .$this->oid;
     		}
  		}

		//в случае смены родителя идет пересчет маркеров дерева
		if ($par != $oldPar)	{
            $this->treeUpdate(0, 0, 0);
       	}
        $this->action[] = "PlainObject: Объект успешно обновлен";

        //возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);

		return (!$this->errorStatus) ? true : false;
	}

	# удаление объекта
	public function deleteObject()	{
        global $objectsTypes, $db_link;


	    if (!$result = $db_link->query("SELECT an_left, an_right FROM ".DATABASE_PREF."objectstree WHERE an_oid =".$this->oid))	{
			$this->error[] = "PlainObject: Ошибка Can't get object properties (oid = ".$this->oid.")";
			$this->errorStatus = true;
			return false;
		}

        $res = $result->fetch_assoc();
	    if (!$result = $db_link->query("SELECT an_oid FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".$this->oid." OR (an_left > ".$res["an_left"]." AND an_right < ".$res["an_right"].")"))	{
	    	$this->error[] = "PlainObject: Невозможно получить список объектов для удаления";
	    	$this->errorStatus = true;
			return false;
		}

        //удаление объекта и вложенных в него
        $files_to_delete = array();
        $db_link->autocommit(false);

		while($res = $result->fetch_assoc())	{

     		//проверка есть ли загруженные файлы для объекта
     		$tmpObj = new PlainObject($res["an_oid"]);
       		reset($objectsTypes[$tmpObj->objectType]["fields"]);
       		while (list(, $v) = each($objectsTypes[$tmpObj->objectType]["fields"]))	{

   			 if ($v["type"]=="LoadFile")	{
   			 	reset($tmpObj->values);
		 		while (list(, $v1) = each($tmpObj->values))	{
		 			if (is_array($v1) && isset($v1[$v["name"]]))	$files_to_delete[] = $v1[$v["name"]];
		 		}
   			 }
           }

           unset($tmpObj);

			//удаление галереи
			ObjectGallery::deleteAll($res["an_oid"], $objectsTypes[$this->objectType]["gallery_photos"]["preview"]);
            if(file_exists(ROOTPATH."/_Fotos/" . $res["an_oid"] . "/Thumbs.db"))	unlink(ROOTPATH."/_Fotos/" . $res["an_oid"] . "/Thumbs.db");
            rmdir(ROOTPATH."/_Fotos/" . $res["an_oid"]);
            for ($i = 0; $i < sizeof($objectsTypes[$this->objectType]["gallery_photos"]["preview"]); $i++)	{
            	if(file_exists(ROOTPATH."/_Fotos/" . $res["an_oid"] . "_prev" .(($i==0) ? "" : $i) . "/Thumbs.db"))	unlink(ROOTPATH."/_Fotos/" . $res["an_oid"] . "_prev" . (($i==0) ? "" : $i) . "/Thumbs.db");
            	rmdir(ROOTPATH."/_Fotos/" . $res["an_oid"] . "_prev" . (($i==0) ? "" : $i) . "/");
            }

           //удаление объекта
	        try	{
				if (!$db_link->query("DELETE FROM ".DATABASE_PREF."objectstree WHERE an_oid=".$res["an_oid"]))
					throw new Exception("PlainObject: Невозможно удалить объект oid = ".$res["an_oid"]);
				if (!$db_link->query("DELETE FROM ".DATABASE_PREF."objectsdata WHERE an_oid=".$res["an_oid"]))
					throw new Exception("PlainObject: Невозможно удалить данные объекта oid = ".$res["an_oid"]);
				if (!$db_link->query("DELETE FROM ".DATABASE_PREF."objectsprop WHERE an_oid=".$res["an_oid"]))
					throw new Exception("PlainObject: Невозможно удалить свойства объекта oid = ".$res["an_oid"]);
				if (!$db_link->query("DELETE FROM ".DATABASE_PREF."properties WHERE an_oid=".$res["an_oid"]))
					throw new Exception("PlainObject: Невозможно удалить дополнительные свойства объекта oid = ".$res["an_oid"]);

			} catch (Exception $e)	{
	 			$db_link->rollback();
                $db_link->autocommit(true);
                $this->error[] = $e->getMessage();
                $this->errorStatus = true;
                return false;
			}

       	}

		$db_link->commit();
		$db_link->autocommit(true);
        if ($result instanceof mysqli_result) $result->close();

  		//удаление файлов
    	while(list(,$f) = each($files_to_delete))	unlink(ROOTPATH."/_Upload/" . $f);

		//перестройка маркеров дерева
		$this->treeUpdate(0, 0, 0);

        $this->action[] = "PlainObject: Объект и, вложенные в него объекты удалены";

        //возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_deleteObject"))	PlainObject_deleteObject($this);


        return true;
    }

	# пересчет маркеров всего дерева при смене родителя
    private function treeUpdate($par, $left, $level)	{
    	global $db_link;

    	$right = $left + 1;
		if(!$result = $db_link->query("SELECT an_oid, an_isfolder, an_level FROM ".DATABASE_PREF."objectstree WHERE an_parent = ".intval($par)))
			die("Ошибка Can't get nested objects! (oid = ".$par.")");

        while($res = $result->fetch_assoc())	{
            $right = $this->treeUpdate($res["an_oid"], $right, $level+1);
		}
		if(!$db_link->query("UPDATE ".DATABASE_PREF."objectstree SET an_left = ".intval($left).", an_right = ".intval($right).", an_level = ".intval($level)." WHERE an_oid = ".intval($par)))
			die("Ошибка Can't update object's markers! (oid = ".$par.")");

        if ($result instanceof mysqli_result) $result->close();
        return $right + 1;
	}

	# пересчет маркеров част объектов при добавлении нового
    private function shiftTreeR($parent) {
    	global $db_link;

		try	{
	        if ($parent == 0) {
	            //если объект вставляется в корень дерева
				if (!$result = $db_link->query("SELECT MAX(an_right) AS last FROM ".DATABASE_PREF."objectstree"))	throw new Exception("PlainObject: Ошибка Can't get max marker!");
				if ($db_link->affected_rows == 0) $mr = 0;
				else	$mr = $result->fetch_object()->last;
	            $this->an_left = $mr + 1;
	            $this->an_right = $mr + 2;
				$this->an_level = 1;
	        }
	        else
	        {
	            //есть ли на этом уровне объекты или он первый
				if (!$result = $db_link->query("SELECT COUNT(an_oid) AS cnt FROM ".DATABASE_PREF."objectstree WHERE an_parent = ".intval($parent)))
					throw new Exception("PlainObject: Ошибка Can't get curent level objects count!");
				$sl = $result->fetch_object()->cnt;
	            if ($sl == 0)	{
	            	//первый объект уровня, лефт родителя
					if (!$result = $db_link->query("SELECT an_left, an_level FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".intval($parent)))
						throw new Exception("PlainObject: Ошибка Can't get parents marker!");
					$res = $result->fetch_assoc();
					$marker = $res["an_left"];
					$level = $res["an_level"];
	            }
	            else
	            {
	            	//на уровне объекты уже есть, берем максимальный райт
					if (!$result = $db_link->query("SELECT MAX(an_right) AS an_right FROM ".DATABASE_PREF."objectstree WHERE an_parent = ".intval($parent)))
						throw new Exception("PlainObject: Ошибка Can't get max right marker!");
					$marker = $result->fetch_object()->an_right;
					if (!$result = $db_link->query("SELECT an_level FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".intval($parent)))
						throw new Exception("PlainObject: Ошибка Can't get level!");
					$level = $result->fetch_object()->an_level;
	            }
	            //увеличение an_left/an_right, присвоение новому объекту значений
	            $this->an_left = $marker + 1;
	            $this->an_right = $marker + 2;
				$this->an_level = $level + 1;

                $db_link->autocommit(false);
				if (!$db_link->query("UPDATE ".DATABASE_PREF."objectstree SET an_left = an_left + 2 WHERE an_left > " . intval($marker)))
					throw new Exception("PlainObject: Ошибка Can't update tree left markers!");

				if (!$db_link->query("UPDATE ".DATABASE_PREF."objectstree SET an_right = an_right + 2 WHERE an_right > " . intval($marker)))
					throw new Exception("PlainObject: Ошибка Can't update tree right markers!");
				$db_link->commit();

	        }
        }	catch (Exception $e)	{

	     	$this->error[] = $e->getMessage();
        	$db_link->rollback();
        	$db_link->autocommit(true);
        	$this->errorStatus = true;
            return false;
        }

        $db_link->autocommit(true);
        if ($result instanceof mysqli_result) $result->close();
        return true;
    }



	# получение свойств линков
	public function getLinkProperties($fid)	{
		global $objectsTypes;

		$arr = $objectsTypes[$this->objectType]["fields"];
		reset($arr);
		while(list($key, $val) = each($arr))	{
			if ($key == $fid)	{
				$field["tartype"] = $val["tartype"];
				$field["target"] = $val["target"];
				$field["lookinside"] = $val["lookinside"];
				$field["multiple"] = $val["multiple"];
			}
		}
		settype ($field["multiple"], "boolean");
		settype ($field["lookinside"], "boolean");

		return $field;
	}

	# получение свойств поля типа User
	public function getUserProperties($fid)	{
		global $objectsTypes;

		$arr = $objectsTypes[$this->objectType]["fields"];
		reset($arr);
		while(list($key, $val) = each($arr))	{
			if ($key == $fid)	{
				$field["multiple"] = $val["multiple"];
			}
		}
		settype ($field["multiple"], "boolean");

		return $field;
	}

	# получение свойств sub-полей
	public function getSubFieldsValues($fid)	{
		global $objectsTypes;
		$arr = $objectsTypes[$this->objectType]["fields"];
		reset($arr);
		while(list($key, $val) = each($arr))	{
			if ($key == $fid)	{
				$field["multiple"] = $val["multiple"];
				$field["options"] = $val["options"];
			}
		}

		settype ($field["multiple"], "boolean");

		return $field;
	}

    # url верхнего уровня
	public static function getParentUrl($oid, $lang)	{
		$url = "";

		$unnk = new PlainObject($oid);
		$unnk->getPath($lang);
		for ($h = (sizeof($unnk->path)-2); $h >= 0; $h--)	{
			if (strlen($unnk->path[$h]["an_url"]) > 0)	{
				$url = $unnk->path[$h]["an_url"];
				break;
			}
		}

		return $url;
	}


    # id объекта заданого типа верхнего уровня
	public static function getParentObjectOfType($oid, $lang, $type=array())	{
		$id = 0;

		$unnk = new PlainObject($oid);
		$unnk->getPath($lang);
		for ($h = (sizeof($unnk->path)-2); $h >= 0; $h--)	{
			if (in_array($unnk->path[$h]["an_type"],$type))	{
				$id = $unnk->path[$h]["an_oid"];
				break;
			}
		}

		return $id;
	}

    # получение значения поля у текущего объекта, либо у родительских
	public static function getParentFieldValue($oid, $lang, $field)	{
	 	$fv = "";
        $unnk = new PlainObject($oid);
	    if (strlen($unnk->values[$lang][$field]) > 0)	$fv = $unnk->values[$lang][$field];
	    else	{
	        $unnk->getPath($lang);
			for ($h = (sizeof($unnk->path)-2); $h >= 0; $h--)	{
				$parObj = new PlainObject($unnk->path[$h]["an_oid"]);
				if (strlen($parObj->values[$lang][$field]) > 0)	{
					$fv = $parObj->values[$lang][$field];
					break;
				}
			}
	    }
		return $fv;
	}

	# путь до объекта
	public function getPath($lang)	{
		global $db_link;

    	$this->path = array();
        try {
			if(!$result = $db_link->query("SELECT an_left, an_right FROM ".DATABASE_PREF."objectstree WHERE an_oid = ".$this->oid)) throw new Exception("PlainObject: Ошибка Can't get left&right marker! (oid=".$this->oid.")");
			$res = $result->fetch_assoc();

			if(!$result = $db_link->query("SELECT ".DATABASE_PREF."objectsprop.an_oid, an_parent, an_url, an_name, an_left, an_type FROM ".DATABASE_PREF."objectstree, ".DATABASE_PREF."objectsprop WHERE	(an_left <= ".$res["an_left"]." AND an_right >= ".$res["an_right"].") AND an_lang = ".intval($lang)." AND ".DATABASE_PREF."objectsprop.an_oid = ".DATABASE_PREF."objectstree.an_oid ORDER BY an_left ASC")) throw new Exception("PlainObject: Ошибка Can't get path objects (oid=".$this->oid.")");

			while($res = $result->fetch_assoc())	{
				$this->path[] = array("an_oid" => $res["an_oid"], "an_name" => stripslashes($res["an_name"]), "an_url" => stripslashes($res["an_url"]), "an_type" => $res["an_type"]);
				$this->pathIds[] =  $res["an_oid"];
			}
		}	catch (Exception $e)	{
	     	$this->error[] = $e->getMessage();
        	$this->errorStatus = true;
        }

		//print_r($result);
		if ($result instanceof mysqli_result) $result->close();
        return $this->path;
	}

	# проверка уникальности УРЛ
	public function checkUrl($url)	{

		GLOBAL $db_link;
		$res = false;

		$addcheck = "";
		if ($this->oid != -1) $addcheck = " AND an_oid != ".$this->oid;

	    $result = $db_link->query("SELECT COUNT(an_oid) AS cnt FROM ".DATABASE_PREF."objectstree WHERE an_url = '".$db_link->real_escape_string($url)."'".$addcheck) or die("Ошибка Fail finding if url is unique");
		if ($result->fetch_object()->cnt != 0)	$res = true;

		return $res;
	}

    /*
	# вывод значения динамического поля	после проверки его существования
 	public function echoField ($lang, $fn)	{

       if(isset($this->values[$lang][$fn])) return  $this->values[$lang][$fn];
       else return "";

 	}
    */

	# получить значение конкретного поля
	function getFieldValue($lang, $fid)	{
		global $objectsTypes, $BDType, $db_link;

		$result = $db_link->query("SELECT ".$BDType[$objectsTypes[$this->objectType]["fields"][$fid]["type"]]." AS val FROM ".DATABASE_PREF."objectsdata WHERE an_oid = ".$this->oid." AND an_field = '".$db_link->real_escape_string($fid)."' AND an_lang = ".intval($lang)) or die("can't get field value");
		$val = $result->fetch_object()->val;
		return stripslashes($val);
	}

	# удалить значение конкретного поля
	function deleteFieldValue($lang, $fid)	{
		GLOBAL $db_link;

		$result = $db_link->query("DELETE FROM ".DATABASE_PREF."objectsdata WHERE an_oid = ".$this->oid." AND an_field = '".$db_link->real_escape_string($fid)."' AND an_lang = ".intval($lang)) or die("can't delete field value");

		//возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);
	}

	# установить значение конкретного поля
	function setFieldValue($lang, $fid, $val)	{
		global $objectsTypes, $BDType, $db_link;

		$this->deleteFieldValue($lang, $fid);
		$result = $db_link->query("INSERT INTO ".DATABASE_PREF."objectsdata (an_oid, an_field, an_lang,  ".$BDType[$objectsTypes[$this->objectType]["fields"][$fid]["type"]].") VALUES (".$this->oid." ,  '".$db_link->real_escape_string($fid)."', ".intval($lang).", '".$db_link->real_escape_string($val)."')") or die("can't set field value");

		//возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);
	}

	# установить Название
	function setName($lang, $val)	{
		GLOBAL $db_link;

		if (strlen($val) == 0)	return false;

		if (!$result = $db_link->query("UPDATE ".DATABASE_PREF."objectsprop SET an_name = '".$db_link->real_escape_string($val)."' WHERE an_oid = ".$this->oid." AND an_lang = ".intval($lang)))	return false;
		return true;
   	}

	# установить теги
	function setTags($lang, $val){
		GLOBAL $db_link;
		if (!$result = $db_link->query("UPDATE ".DATABASE_PREF."objectsprop SET an_tags = '".$db_link->real_escape_string($val)."' WHERE an_oid = ".$this->oid." AND an_lang = ".intval($lang)))	return false;

        //возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);

	}


	# установить frontaccess
	function setAccess($lang, $val)	{
		GLOBAL $db_link;
		if (!$result = $db_link->query("UPDATE ".DATABASE_PREF."objectsprop SET an_frontendaccess = ".intval($val)." WHERE an_oid = ".$this->oid." AND an_lang = ".intval($lang)))	return false;

        //возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);
	}

	# установить сортировочный номер
	function setSort($lang, $val)	{
		GLOBAL $db_link;
		if (!$result = $db_link->query("UPDATE ".DATABASE_PREF."objectsprop SET an_sortnumber = ".doubleval($val)." WHERE an_oid = ".$this->oid." AND an_lang = ".intval($lang)))	return false;

		//возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);
	}

	# установить дату
	function setDate($val)	{
		GLOBAL $db_link;
		if (!$result = $db_link->query("UPDATE ".DATABASE_PREF."objectstree SET an_date = '".$db_link->real_escape_string($val)."' WHERE an_oid = ".$this->oid)) return false;

	}

	# установить parent
	function setParent($val)	{
		GLOBAL $db_link;
		if (!$result = $db_link->query("UPDATE ".DATABASE_PREF."objectstree SET an_parent = ".intval($val)." WHERE an_oid = ".$this->oid)) return false;
        $this->treeUpdate(0, 0, 0);

        //возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);
	}

	# удалить загруженный файл
	function deleteFile($lang, $fid, $type)	{
		GLOBAL $objectsTypes, $db_link;

		$fname = $this->getFieldValue($lang, $fid);
		$this->deleteFieldValue($lang, $fid);

		unlink(ROOTPATH . "/_Upload/" . $fname);
		if($objectsTypes[$type]["fields"][$fid]["image"]["preview"])  unlink(ROOTPATH . "/_Upload/_prev/" . $fname);
		$this->action[] = "Файл удален";

		//возможные дополнительные действия после отработки метода
		if (function_exists("PlainObject_updateObject"))	PlainObject_updateObject($this);
	}

	# получение описания ошибок произошедших во время выполнения методов класса
	public function getErrorMessage($clear = true, $separator = "<br>") {
		$return = implode($separator, $this->error);
		if ($clear)	unset($this->error);

		return $return;
	}

	# результат действия методов класса
	public function getActionMessage($clear = true, $separator = "<br>")	{
		$return = implode($separator, $this->action);
		if ($clear)	unset($this->action);

		return $return;
	}

	# получение описания ошибок произошедших во время выполнения методов класса
	public function getNoticeMessage($clear = true, $separator = "<br>") {
		$return = implode($separator, $this->notice);
		if ($clear)	unset($this->notice);

		return $return;
	}

}
?>