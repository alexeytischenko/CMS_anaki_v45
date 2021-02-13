<?
/*
#	Класс ObjectsFotos - Фотогалерея принадлежащая объекту
# 	@version 4.1 February 2013
*/

class ObjectGallery  {

    public $id;            	# id родительского объекта
    public $fotos_array;	# массив фотографий
    public $sortorder;		# порядок сортировки фотографий
	private $error;         # отладочная информация об ошибке
    private $action;		# отладочная информация о действии

	# принимает перечень id-родительского объекта и получает список фотографий
    public function __construct($id)	{
    	GLOBAL $db_link;
    	$this->action = "получение списка галереи";

    	try {

    		if (!$result = $db_link->query("SELECT an_sortorder FROM ".DATABASE_PREF."fotos_order WHERE an_parent=".intval($id)))
    			throw new Exception("Ошибка. Невозможно получить значение порядка сортировки");
    		$this->sortorder = (is_object($so = $result->fetch_object())) ? intval($so->an_sortorder) : 0;
			if(!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."fotos WHERE an_parent = ".intval($id)." ORDER BY an_sort ". (($this->sortorder > 0) ? "DESC" : "ASC")))	throw new Exception("Ошибка. Невозможно получить список фотографий");

		} catch (Exception $e) {
			$this->error = $e->getMessage();
		}

		while($res = $result->fetch_assoc() )	{
			$this->fotos_array[] = array("an_oid" => $res["an_oid"], "an_name" => unserialize(stripslashes($res["an_name"])), "an_filename" => stripslashes($res["an_filename"]));
		}

		$result->close();
	}

	# установка порядка сортировки
	public static function setGallerySort($parent, $sortOr)	{
       	GLOBAL $db_link;

       	try {
			if (!$db_link->query("DELETE FROM ".DATABASE_PREF."fotos_order WHERE an_parent=".intval($parent)))
				throw new Exception("Ошибка. Невозможно удалить запись о сортировке галереи");
			if (!$db_link->query("INSERT INTO ".DATABASE_PREF."fotos_order (an_parent, an_sortorder) VALUES (".intval($parent).", ".intval($sortOr).")"))
				throw new Exception("Ошибка. Невозможно добавить запись о сортировке галереи");
		}	catch (Exception $e) {
        	die($e->getMessage());
		}
	}


	# запись фотографии
	public static function saveFoto($parent, $upfile, $fotoProperties, $realname = "")	{
		GLOBAL $db_link;
        $path = ROOTPATH."/_Fotos/";

	   	// создаем директории, если еще не создана
	   	if (!file_exists($path . intval($parent)))	mkdir($path . intval($parent), 0777);
		$save_path = $path . intval($parent)."/";

        $upload_name = "Filedata";

  		$file_name = CustomLibs :: transliteral	(basename($upfile[$upload_name]['name']));

        ////////////////////////////ресайз и создание превью
		$size = GetImageSize($upfile[$upload_name]["tmp_name"]);
		//1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF
		if ($size[2]==2)	$im = imagecreatefromjpeg($upfile[$upload_name]["tmp_name"]);
		elseif($size[2]==1)	 $im = imagecreatefromgif($upfile[$upload_name]["tmp_name"]);
		else	$im = imagecreatefrompng($upfile[$upload_name]["tmp_name"]);
		$newh = $size[1];
		$neww = $size[0];

		for ($i = 0; $i < sizeof($fotoProperties["preview"]); $i++)	{

			// создаем _prev директории, если еще не создана
			$suff = ($i == 0) ? "" : $i;
            if (!file_exists($path . intval($parent) . "_prev" . $suff))	mkdir($path . intval($parent) . "_prev" . $suff, 0777);
            $save_path_prev = $path . intval($parent) . "_prev" . $suff ."/";

			//начальная точка на исходнике и ширина/высота зоны, попадающей в превью
			//по-молчанию все исходное фото попадает в превью
			$shiftX = 0;
			$shiftY = 0;
			$qsize1 = $size[0];
			$qsize2 = $size[1];

			//размеры превьюх
			$preview_x = $fotoProperties["preview"][$i]["preview_width"];
			$preview_y = $fotoProperties["preview"][$i]["preview_height"];

			//////////////////////// обработка изображений///////////////////////////////


			$need_to_shift = true;
			//в случае если для превью задан только один размер, обрезать исходник не нужно, достаточно пропорционально уменьшить его
			//такая же ситуация если и исходник и превью квадратные
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

			//запись превью фото
			$im1 = imagecreatetruecolor($preview_x, $preview_y);
			imagecopyresampled($im1, $im, 0, 0, $shiftX, $shiftY, $preview_x, $preview_y, $qsize1, $qsize2);

			if ($size[2]==2)	imagejpeg($im1, $save_path_prev.$file_name, 100);
			elseif($size[2]==1)	imagegif($im1, $save_path_prev.$file_name);
			else imagepng($im1, $save_path_prev.$file_name, 9);

			chmod($save_path_prev.$file_name, 0777);
			imagedestroy($im1);
		}

		//resize большого фото
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
		
		//наложение водного знака
		if (isset($fotoProperties["watermark"]) && is_array($fotoProperties["watermark"]) && strlen($fotoProperties["watermark"]["file"])>0)	{
			$placeX = 0;
			$placeY = 0;
			$stamp = imagecreatefrompng(ROOTPATH . $fotoProperties["watermark"]["file"]);
			$sx = imagesx($stamp);
			$sy = imagesy($stamp);
			if (isset($fotoProperties["watermark"]["position"]["xy"]) && is_array($fotoProperties["watermark"]["position"]["xy"]))	{
				//точные координаты
				$placeX = $fotoProperties["watermark"]["position"]["xy"][0];
				$placeY = $fotoProperties["watermark"]["position"]["xy"][1];
			}	elseif(isset($fotoProperties["watermark"]["position"]["place"]) && is_array($fotoProperties["watermark"]["position"]["place"]))	{
				//высчитываем положение

				//x
				if ($fotoProperties["watermark"]["position"]["place"]["x"]=="left")
					$placeX = $fotoProperties["watermark"]["position"]["place"]["margin"];
				elseif ($fotoProperties["watermark"]["position"]["place"]["x"]=="right")
					$placeX = imagesx($im2) - $sx - $fotoProperties["watermark"]["position"]["place"]["margin"];
				else
					$placeX = imagesx($im2)/2 - $sx/2;
				//y
				if ($fotoProperties["watermark"]["position"]["place"]["y"]=="top")
					$placeY = $fotoProperties["watermark"]["position"]["place"]["margin"];
				elseif ($fotoProperties["watermark"]["position"]["place"]["y"]=="bottom")
					$placeY = imagesy($im2) - $sy - $fotoProperties["watermark"]["position"]["place"]["margin"];
				else
					$placeY = imagesy($im2)/2 - $sy/2;					
				
			}	else {
				$placeY = imagesy($im2) - $sy - 10;
				$placeX = imagesx($im2) - $sx - 10;
			}
				
			imagecopy ($im2, $stamp, $placeX, $placeY, 0, 0, imagesx($stamp), imagesy($stamp));
		}
		
		
		if ($size[2]==2)	imagejpeg($im2, $save_path.$file_name, 100);
		elseif($size[2]==1)	imagegif($im2, $save_path.$file_name);
        else imagepng($im2, $save_path.$file_name, 9);

        //изменение прав и высвобождение ресурсов
		chmod($save_path.$file_name, 0777);
		imagedestroy($im);
		imagedestroy($im2);

		//запись в базу
		try	{
			if (!$result = $db_link->query("SELECT MAX(an_sort) AS s_max FROM ".DATABASE_PREF."fotos WHERE an_parent=".intval($parent))) throw new Exception("Ошибка. Невозможно получить максимальный сортировочный номер в списке фотографий");
			$sr_num = $result->fetch_object()->s_max;

			if (!$db_link->query("INSERT INTO ".DATABASE_PREF."fotos (an_name, an_filename, an_sort, an_parent) VALUES('".$db_link->real_escape_string($realname)."', '".$file_name."', ".intval($sr_num + 1).", ".intval($parent).")")) throw new Exception("Ошибка. Невозможно добавить запись о новой фотографии в список");

			$result->close();

		}	catch (Exception $e)	{
			die($e->getMessage());
		}

	}

	# добавить к картинке название
	public static function addName($id, $name)	{
       	GLOBAL $db_link;

		try {
			if (!$db_link->query("UPDATE ".DATABASE_PREF."fotos SET an_name = '".$db_link->real_escape_string($name)."' WHERE an_oid=".intval($id)))
				throw new Exception("Ошибка. Невозможно добавить название к фотографии");

		} catch (Exception $e) {
        	die($e->getMessage());
		}

	}

 	# удаление фотографии из галереи
	public static function deleteFoto($id, $parent, $previews)	{
		GLOBAL $db_link;
		$path = ROOTPATH."/_Fotos/";

		$db_link->autocommit(false);

		try {
			if (!$result = $db_link->query("SELECT an_filename FROM ".DATABASE_PREF."fotos WHERE an_oid=".intval($id)))
				throw new Exception("Ошибка. Невозможно получить название файла фотографии");
			$nm = $result->fetch_object()->an_filename;
			if (!$db_link->query("DELETE FROM ".DATABASE_PREF."fotos WHERE an_oid=".intval($id)))
				throw new Exception("Ошибка. Невозможно удалить запись с фотографией");

			$result->close();

			//$e = new Exception("Ошибка. Невозможно удалить файл");
			unlink($path . intval($parent)."/".$nm);
			for ($i = 0; $i < sizeof($previews); $i++)
				unlink($path . intval($parent)."_prev".(($i==0)? "" : $i)."/".$nm);
			$db_link->commit();

		}	catch (Exception $e) {
			die($e->getMessage());
			$db_link->rollback();
		}

		$db_link->autocommit(true);
	}

	# удаление всего содержимого галереи
	public static function deleteAll($parent, $previews)	{
		GLOBAL $db_link;
		$path = ROOTPATH."/_Fotos/";

		try {
			if (!$result = $db_link->query("SELECT an_filename FROM ".DATABASE_PREF."fotos WHERE an_parent = ".intval($parent)))
				throw new Exception("Ошибка. Невозможно получить названия файлов фотографий");
			if (!$db_link->query("DELETE FROM ".DATABASE_PREF."fotos WHERE an_parent=".intval($parent)))
				throw new Exception("Ошибка. Невозможно удалить фотографии галереи");

            while( $res = $result->fetch_assoc() )	{
				unlink($path . intval($parent)."/".$res["an_filename"]);
				for ($i = 0; $i < sizeof($previews); $i++)
					unlink($path . intval($parent)."_prev".(($i==0)? "" : $i)."/".$res["an_filename"]);
			}


		}	catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}

		if ($result instanceof mysqli_result) $result->close();
		return true;

	}

	# загрузка всех названий картинок файлом
	public function uploadAllNames()	{

	}

	# возвращает переменные ошибок и статусов
    public function getStatus()	{
    	return $this->error;
    }
}


?>