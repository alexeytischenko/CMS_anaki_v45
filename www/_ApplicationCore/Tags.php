<?
/*
#	Класс User - Работа с тегами объектов
# 	@version 4.1 February 2013
*/

class Tags {
	# конструктор + получение данных по тегу
	public $tid;				# id тега
	public $name;          		# название
	public $count;				# количество упоминаний
	private $error = array();   # массив сообщений об ошибках
	public $errorStatus;    	# флаг: в процессе выполнения метода произошла ошибка
	private $action = array();  # массив сообщений о текущих изменениях
	private $notice = array();	# массив вспомогательных сообщений

	function __construct($tid = 0)	{
		global $db_link;
		$this->errorStatus = false;
		$this->notice[] = "Tags: создание объекта Tags";

		$this->tid = intval($tid);

		if ($tid == -1 || $tid == 0)	{
			$this->notice[] = "Tags: tid = " . $tid;
			return;
		}

		$this->getData();
	}

	# получение всех данных по тегу
	public function getData($lang=0, $name="")	{
		global $db_link;

		$this->errorStatus = false;
		$this->notice[] = "Tags::getData - получение данных";

		try	{
			$query = "";
			if ($this->tid > 0)
				$query = "SELECT * FROM ".DATABASE_PREF."tags WHERE an_tid = ".$this->tid;
			elseif (strlen($name)>0)
				$query = "SELECT * FROM ".DATABASE_PREF."tags WHERE an_lang = " . intval($lang) . " AND an_name = '".$db_link->real_escape_string($name)."'";
			else throw new Exception("Tags::getData - не заданы параметры");


			if(!$result = $db_link->query($query))
				throw new Exception("Tags::getData - Ошибка во время получения из БД данных по тегу, tid = " . $this->tid);
			if ($db_link->affected_rows == 1)	{
				$this->notice[] = "Tags::getData - получены данные, tid = " . $this->tid;

				$row = $result->fetch_assoc();
				$this->tid = $row["an_tid"];
				$this->name = stripslashes($row["an_name"]);
				$this->count = intval($row["an_count"]);
			}
			else throw new Exception("Tags::getData - Тег не существует, tid = " . $this->tid);

			$result->close();

		} catch (Exception $e)	{
			$this->error[] = $e->getMessage();
            $this->errorStatus = true;
		}

	}


	# удаление тега
	public function deleteTag() {
		global $db_link;

		try	{
			if(!$db_link->query("DELETE FROM ".DATABASE_PREF."tags WHERE an_tid=".$this->tid))
			throw new Exception ("Tags::deleteTag - Ошибка удаления тега, tid = " . $this->tid);
			$this->action[] = "Tags::deleteTag - Тег удален, tid = " . $this->tid;
			return true;

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}


	#редактирование данных тега
	public function saveData($lang, $name, $count)	{
		global $db_link;
        $this->notice[] = "Tags::saveData - изменение данных тега";

		try {
			//проверка заполнения переменных
			if (strlen($name) == 0)	throw new Exception("Tags::saveData - Необходимо ввести название");


				if ($this->tid == -1)	{
					if(!$db_link->query("INSERT INTO ".DATABASE_PREF."tags (an_name, an_lang, an_count) VALUES ('".$db_link->real_escape_string($name)."', ".intval($lang).", ".intval($count).")"))
					throw new Exception("Tags::saveData - тег уже существует");

					$this->tid = $db_link->insert_id;
				}
				else	{
					if(!$db_link->query("UPDATE ".DATABASE_PREF."tags SET an_name='".$db_link->real_escape_string($name)."', an_lang= ".intval($lang).", an_count = ".intval($count)." WHERE an_tid=".$this->tid))
						throw new Exception("Tags::saveData - Ошибка редактирования тега, tid = " . $tid);
				}
				$this->action[] = "Tags::saveData - Данные изменены, tid = " . $this->tid;
				$this->getData();

				return true;


		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	# список тегов
	public static function getTagsList($lang, $order = "no", $rang_num = 10)	{
		global $db_link;

		$ordadd = "";
		switch($order)	{

			case "an_name" :
				$ordadd = " ORDER BY an_name ASC";
			break;
			case "an_count" :
				$ordadd = " ORDER BY an_count ASC";
			break;

		}

		$arr = array();

		//максимальное число упоминамия тега
		$result = $db_link->query("SELECT MAX(an_count) AS max FROM ".DATABASE_PREF."tags") or die("can't get max count value");
		$max = $result->fetch_object()->max;

		//все теги
		$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."tags WHERE an_lang = ".intval($lang) . $ordadd)
		   or die ("Tags::getTagsList - Ошибка во время получения из БД списка тегов.");

		while ($row = $result->fetch_assoc())	{
	 		$arr[] = array(
	 		 	"an_tid" => $row["an_tid"],
	 		 	"an_name" 		=> stripslashes($row["an_name"]),
	 		 	"an_count"		=> intval($row["an_count"]),
	 		 	"an_rang"		=> (intval($row["an_count"]) > 0) ? ceil(intval($row["an_count"])/($max/$rang_num)) : 1
	 		);
		}
		$result->close();

		return $arr;
	}

	#пересчет упоминаний тегов
	public static function countTags($lang, $type = 0)	{        global $db_link;

		$tag_counts = array();

		if($type == 0)	{
			if (!$result = $db_link->query("SELECT an_tags FROM ".DATABASE_PREF."objectsprop WHERE an_lang = " . intval($lang) . " AND an_frontendaccess = 1")) return false;
        }	else	{
        	if (!$result = $db_link->query("SELECT an_tags FROM ".DATABASE_PREF."objectsprop, ".DATABASE_PREF."objectstree WHERE an_lang = " . intval($lang) . " AND an_frontendaccess = 1 AND ".DATABASE_PREF."objectsprop.an_oid = ".DATABASE_PREF."objectstree.an_oid AND an_type = " . intval($type))) return false;
        }

        while($res = $result->fetch_assoc())	{
        	if (strlen($res["an_tags"])==0)	continue;
        	$tmp_tags = new Multidata($res["an_tags"]);
        	if (sizeof($tmp_tags->ids_array) > 0)	{        		for($i = 0; $i < sizeof($tmp_tags->ids_array); $i++)
        		   $tag_counts[$tmp_tags->ids_array[$i]]++;
        	}
        }

       	//print_r($tag_counts);
       	reset($tag_counts);
       	while(list($key, $val) = each($tag_counts))	{       		$tg = new Tags($key);
       		$tg->saveData($lang, $tg->name, $val);
       	}	}


	# получение описания ошибок произошедших во время выполнения методов класса
	public function getErrorMessage($clear = true, $separator = "<br>") {
		$return = implode($separator, $this->error);
		if ($clear)	unset($this->error);

		return $return;
	}

	# добавить ошибку
	public function addErrorMessage($mess) {
		$this->error[] = $mess;
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