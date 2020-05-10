<?
/*
#	Класс ObjectData - данные (значения полей) объекта
# 	@version 4.1 February 2012
#   23/05/2014 поправлена работа с IETable-типом данных
#	28/01/2016 исправлена ошибка в ф-ции prepareData($type, $pst, $lg). $_POST заменен на $pst
*/

Class ObjectData {
	public $data_array;		# массив со значениями полей
	private $error;         # отладочная информация об ошибке
	private $action;		# отладочная информация о действии

	function __construct($data_array) {
		$this->data_array = $data_array;
		$this->action = "Инициализация переменных класса";
	}

	public function setData($oid, $an_type, $lang)	{
		# Запись свойств объекта в БД
		GLOBAL $db_link, $objectsTypes, $BDType;
		$this->action = "Запись данных объекта в БД";
		$db_link->autocommit(false);

		try {

  			# удаление старых занчений полей
			if(!$db_link->query("DELETE FROM ".DATABASE_PREF."objectsdata WHERE an_oid = " . intval($oid) . " AND an_lang = " . intval($lang)))	throw new Exception("Ошибка. Невозможно удалить старые значения полей (oid = ".intval($oid).")");

			# запись новых значений
			reset($objectsTypes[intval($an_type)]["fields"]);
			while(list($key, $val) = each($objectsTypes[intval($an_type)]["fields"]))	{

				if (isset($this->data_array[$key]))	{
					if ($val["type"]=="LoadFile" && is_array($this->data_array[$key]))	{

						$new_name = CustomLibs :: transliteral	($this->data_array[$key]["name"]);
						if (!$objectsTypes[intval($an_type)]["fields"][$key]["keep_real_name"])	{
							$ext = substr($new_name, -4);
							$new_name = uniqid("").((substr($ext,0,1) == ".")? "" : ".").$ext;
                        }

						if (!is_array($objectsTypes[intval($an_type)]["fields"][$key]["image"]))
							copy($this->data_array[$key]["tmp_name"], ROOTPATH."/_Upload/".$new_name);
						else {
							////////////////////////////ресайз фото и создание превью
							$size = GetImageSize($this->data_array[$key]["tmp_name"]);
							//1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF
							if ($size[2]==2)	$im = imagecreatefromjpeg($this->data_array[$key]["tmp_name"]);
							elseif($size[2]==1)	 $im = imagecreatefromgif($this->data_array[$key]["tmp_name"]);
							else	$im = imagecreatefrompng($this->data_array[$key]["tmp_name"]);
							$newh = $size[1];
							$neww = $size[0];

							//размеры фото
							$fotoProperties = $objectsTypes[intval($an_type)]["fields"][$key]["image"];

							//preview
							$shiftX = 0;
							$shiftY = 0;
							$qsize1 = $size[0];
							$qsize2 = $size[1];

							//размеры превьюх
							$preview_x = $fotoProperties["preview_width"];
							$preview_y = $fotoProperties["preview_height"];

							$need_to_shift = true;
							if ($preview_x == 0 && $preview_y > 0)	{
								$preview_x = intval($neww / ($newh/$preview_y));
								$need_to_shift = false;
							}
							if ($preview_y == 0 && $preview_x > 0)	{
								$preview_y = intval($newh / ($neww/$preview_x));
								$need_to_shift = false;
							}
							if ($preview_y == $preview_x && $size == $size[1])	{
									$need_to_shift = false;
							}

							//если все же сложный случай и заданы сразу оба размера превью - исходник нужно обрезать, чтобы он стал пропорциональным превьюхе
					        if ($need_to_shift)	{

								if (
									($size[0] > $size[1] && ($preview_x > $preview_y && $size[0]/$size[1] <= $preview_x/$preview_y))
									||
									($size[0] == $size[1] && $preview_x > $preview_y)
									||
									($size[0] < $size[1] && ($preview_y < $preview_x || $preview_y == $preview_x) || ($preview_y > $preview_x && $size[1]/$size[0] > $preview_y/$preview_x))
								)	{
								/*
								  режем по Y если:
								  	исходное фото горизонтальное, а превью горизонтальное, но более вытянутое или такое же как исходник
								  	исходник квадратный, а превью горизонтальное
								  	исходное фото вертикальное, а превью горизонтальное, квадратное или вертикальное, но более широкое, чем исходник
								*/
										//размеры, до которых обрежется исходник
						   				$qsize1 = $size[0];
						   				$qsize2 = intval($size[0]*$preview_y/$preview_x);
						   				//координаты левого верхнего угла на исходнике
						   				$shiftX = 0;
						   				$shiftY = intval($size[1]/2 - $qsize2/2);
								}
								/*
								  режем по X если:
								  	исходное фото горизонтальное, а превью вертикальное, квадратное либо горизонтальное, но менее вытянутое чем исходник
								  	исходное фото квадратное, а превью вертикальное
								  	исходное фото вертикальное, а превью вертикальное и более узкое, чем исходник
								*/
								else	{
										//размеры, до которых обрежется исходник
						   				$qsize2 = $size[1];
						   				$qsize1 = intval($size[1]*$preview_x/$preview_y);
						   				//координаты левого верхнего угла на исходнике
						   				$shiftY = 0;
						   				$shiftX = intval($size[0]/2 - $qsize1/2);
								}
							}

							if ($objectsTypes[intval($an_type)]["fields"][$key]["image"]["preview"])	{
								//если необходимо создаем превью
								$im1 = imagecreatetruecolor($preview_x, $preview_y);
								imagecopyresampled($im1, $im, 0, 0, $shiftX, $shiftY, $preview_x, $preview_y, $qsize1, $qsize2);

								if ($size[2]==2)	imagejpeg($im1, ROOTPATH."/_Upload/_prev/".$new_name, 100);
								elseif($size[2]==1)	imagegif($im1, ROOTPATH."/_Upload/_prev/".$new_name);
								else imagepng($im1, ROOTPATH."/_Upload/_prev/".$new_name, 9);

	                            chmod(ROOTPATH."/_Upload/_prev/".$new_name, 0777);
	                            imagedestroy($im1);
                            }

							//resize
							if ($size[0] > $fotoProperties["image_max_width"])	{
								$neww = $fotoProperties["image_max_width"];
								$newh = intval($size[1]/($size[0]/$fotoProperties["image_max_width"]));
							}
							if ($newh > $fotoProperties["image_max_height"])	{
								$neww = intval($neww/($newh/$fotoProperties["image_max_height"]));
								$newh = $fotoProperties["image_max_height"];
							}

							$im2 = imagecreatetruecolor($neww, $newh);
							imagecopyresampled($im2, $im, 0, 0, 0, 0, $neww, $newh, imagesx($im), imagesy($im));
							if ($size[2]==2)	imagejpeg($im2, ROOTPATH."/_Upload/".$new_name, 100);
							else	imagegif($im2, ROOTPATH."/_Upload/".$new_name);
							chmod(ROOTPATH."/_Upload/".$new_name, 0777);

							imagedestroy($im);
							imagedestroy($im2);



						}

						$this->data_array[$key] = $new_name;
					}
					//IETable
					if ($val["type"]=="IETable" && is_array($this->data_array[$key]))
						$this->data_array[$key] = $this->putIETable($key, $this->data_array[$key], intval($an_type));


					if(!$db_link->query("INSERT INTO ".DATABASE_PREF."objectsdata (an_oid, an_field, an_lang, " .$BDType[$val["type"]]. ") VALUES (".intval($oid).", '".$key."', ".intval($lang).", '".$db_link->real_escape_string($this->data_array[$key])."')"))	throw new Exception("Ошибка. Невозможно записать новые значения полей (oid = ".intval($oid).")");
				}
            }

			$db_link->commit();

		} catch (Exception $e) {
			# откат
        	$this->error = $e->getMessage();
        	$db_link->rollback();
		}

		$db_link->autocommit(true);
	}

	# загрузка данных полей объекта из БД
	static public function getData($oid, $type)	{
		GLOBAL $db_link, $BDType;
		$return = array();

        try	{
        	if (!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."objectsdata WHERE an_oid =". intval($oid)))	throw new Exception("Ошибка. Невозможно получить данные объекта (oid = ".$oid.")");
       	}
		catch (Exception $e)	{
			die($e->getMessage());
		}

       	while($res = $result->fetch_assoc())	{

			$field = ObjectData :: getFieldNameAndType($type, $res["an_field"]);
			if (sizeof($field) > 0)
				switch ($field["type"])	{
					case "TextMultLine"	:
						# множественные текстовые поля
						$return[$res["an_lang"]][$field["name"]] = split("#field#", stripslashes($res[$BDType[$field["type"]]]));
					break;

					case "Link"	:
						# линки
					    $return[$res["an_lang"]][$field["name"]] = split(",", stripslashes(preg_replace(array("/^,/", "/,$/"), "",$res[$BDType[$field["type"]]])));
					break;

					case "Choose"	:
					    # селекты
					    $return[$res["an_lang"]][$field["name"]] = split(",", stripslashes(preg_replace(array("/^,/", "/,$/"), "",$res[$BDType[$field["type"]]])));
					break;
					
					case "User"	:
					    # пользователи
					    $return[$res["an_lang"]][$field["name"]] = split(",", stripslashes(preg_replace(array("/^,/", "/,$/"), "",$res[$BDType[$field["type"]]])));
					break;
										
					case "IETable"	:
					    # таблица
					    $return[$res["an_lang"]][$field["name"]] = unserialize(stripslashes($res[$BDType[$field["type"]]]));
					break;

					case "Date"	:
					    # дата
					    $return[$res["an_lang"]][$field["name"]] = strtotime($res[$BDType[$field["type"]]]);
					break;

					default :
						# остальные поля
						$return[$res["an_lang"]][$field["name"]] = stripslashes($res[$BDType[$field["type"]]]);
					break;
				}
		}

   		$result->close();
        /*
		reset $return;
		while(list($key, $val) = each($return))	{
			if (empty($val))	{
				$def_lg = ObjectData :: getDefaultLang($type, $fid);
				//if ($def_lg > 0 && $res["an_lang"] != $def_lg)
			}

		}*/

		return $return;
	}

	# получение названия и типа поля по его id
	static public function getFieldNameAndType($type, $fid)	{
		global $objectsTypes;
		$arr = (array)$objectsTypes[$type]["fields"];

		$field = array();

		reset($arr);
		while(list($key, $val) = each($arr))	{
			if ($key == $fid)	{
				$field["name"] = $val["name"];
				$field["type"] = $val["type"];
			}
		}

		//if (!(is_string($field["type"]) && is_string($field["name"])))
		//	$this->error .= " Unknown field id = " . $fid;

		return $field;
	}

	# язык по-умолчанию для конкретного поля
	static public function getDefaultLang($type, $fid)	{
		global $objectsTypes;
		$arr = (array)$objectsTypes[$type]["fields"];
		$lg = 0;

		reset($arr);
		while(list($key, $val) = each($arr))	{
			if ($key == $fid && isset($val["default_lang"]) && intval($val["default_lang"]) > 0)
				$lg = intval($val["default_lang"]);
		}

		return $lg;
	}

	# запись таблицы данных для поля IETable
	private function putIETable($key, $new, $an_type)	{
		global $objectsTypes;

    	$temp_array = file($new["tmp_name"]);

    	$readystring = array();
	    //проверка количества столбцов в шапке
	    $head = $objectsTypes[$an_type]["fields"][$key]["fields"];
		$headerline = split ("\t", $temp_array[0]);

		if(strlen($headerline[sizeof($head)]) > 0)	{

			//print_r($headerline);
			$this->error = "Количество столбцов в таблице превышает ожидаемый. Таблица должна содержать только следующие столбцы:";
			reset($head);
			while(list(,$hn) = each($head))	$this->error .= "'".$hn."',";
			$this->error .= "<br>между ними не могут находится пустые столбцы.<br>Таблица с данными не записана";
			return false;
		}
		else	{
			//первая строка может пропускаться - шапка
            $startindex = ($objectsTypes[$an_type]["fields"][$key]["iscapture"]) ? 1 : 0;

			for ($i = $startindex; $i < sizeof($temp_array); $i++)	{
                  echo   $temp_array[$i];
				if (strlen($temp_array[$i]) > 0)	{
					//разбор строки и проверка
					$strline = split ("\t", $temp_array[$i]);
					//print_r($strline);
					if (sizeof($strline) == 1 && sizeof($head)>1)	{
						$this->error .= $i."Неверный формат файла. Файл должен быть сохранен в формате Text(tab delimited)";
                        if ($objectsTypes[$an_type]["fields"][$key]["iscapture"]) $this->error .=", содержать в первой строке \"шапку\" таблицы.";
                        $this->error .=" В файле не должно содержаться никакой служебной информации, комментариев и т.п., только данные по продуктам";
						//print_r($errors_array);
						break;
					}
					$readystring[] = $strline;
				}
			}
		}

		return serialize($readystring);

	}

	#получает данные объекта и ковертирует их в массив для передачи методу PlainObject::createObject
	public static function convertData($type, $objDt)	{
		global $objectsTypes;

		$returnArray = array();
		reset($objectsTypes[$type]["fields"]);		
		while(list($key, $val) = each($objectsTypes[$type]["fields"]))	{
			if ($val["type"]=="Date") $returnArray[$key] = date('Y-m-d', $objDt[$val["name"]]);
			else $returnArray[$key] = $objDt[$val["name"]];
		}
		
		return $returnArray;
	}

	#получает данные POST-запроса и ковертирует их в массив для передачи методу PlainObject::createObject
	public static function prepareData($type, $pst, $lg)	{
		global $objectsTypes;

		$returnArray = array();
        reset($objectsTypes[$type]["fields"]);
		while(list($key, $val) = each($objectsTypes[$type]["fields"]))	{

			//поля типа Choose
			if (isset($pst["mult_" . $key]) && $pst["mult_" . $key] == 1)	{
   				$tmp = new PlainObject(-1, $type);
				$prop = $tmp->getSubFieldsValues($key);
				$str = "";
				if (!$prop["multiple"])	{
					if (isset($pst["Repeater" . $lg . "_" . $key]) && strlen($pst["Repeater" . $lg . "_" . $key])>0)
						$str = $pst["Repeater" . $lg . "_" . $key] . ",";
				}
				else	{
					while (list ($k,) = each($prop["options"]))
						if ($pst["Repeater" . $lg . "_" . $key . "_". $k] == 1)
							$str .= $k . ",";
				}
				if (strlen($str) > 0) $returnArray[$key] = "," . $str;
			}

			//остальные поля
			if ($val["type"] != "Choose" && isset($pst["Repeater" . $lg . "_" . $key]) && strlen($pst["Repeater" . $lg . "_" . $key])>0)	{
				switch ($val["type"]) {
					case "TextPlain" :
						$pst["Repeater" . $lg . "_" . $key] = nl2br($pst["Repeater" . $lg . "_" . $key]);
						break;
					case "Float" :
						$pst["Repeater" . $lg . "_" . $key] = preg_replace("/(\d*),(\d*)/", "\\1.\\2", $pst["Repeater" . $lg . "_" . $key]);
						break;
					case "Checkbox" :
						$pst["Repeater" . $lg . "_" . $key] = 1;
						break;
					case "TextMultLine" :
						$resLine = "";
						for ($l = 0; $l < $pst["Repeater" . $lg . "_" . $key]; $l++)	{
							if (strlen($pst["Repeater" . $lg . "_" . $key . "_" . $l]) > 0)	{
								if (strlen($resLine) > 0)	$resLine .= "#field#";
								$resLine .= $pst["Repeater" . $lg . "_" . $key . "_" . $l];
							}
						}
						$pst["Repeater" . $lg . "_" . $key] = $resLine;
						break;
				}
				$returnArray[$key] = $pst["Repeater" . $lg . "_" . $key];
			}
			if (isset($_FILES["Files" . $lg . "_" . $key]) && strlen($_FILES["Files" . $lg . "_" . $key]["name"])>0)
				$returnArray[$key] = $_FILES["Files" . $lg . "_" . $key];
		}

	    return $returnArray;
	}

	# возвращает переменные ошибок и статусов
    public function getStatus()	{
    	return $this->action."<br>".$this->error;
    }
}

?>