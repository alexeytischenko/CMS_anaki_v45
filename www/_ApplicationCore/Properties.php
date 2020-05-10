<?
/*
#	Класс Properties - Работа со специальными свойствами
# 	@version 4.1 February 2012
*/


class Properties {
    # конструктор - получение свойств объекта
	public $oid;
	public $prop;				# свойство
	private $error = array();   # массив сообщений об ошибках
	public $errorStatus;    	# флаг: в процессе выполнения метода произошла ошибка
	private $action = array();  # массив сообщений о текущих изменениях
	private $notice = array();	# массив вспомогательных сообщений

	function __construct($oid)	{
        global $db_link;
		$this->oid = intval($oid);

		try	{
			if(!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."properties WHERE an_oid = ".$this->oid))
				throw new Exception("Properties: Ошибка во время получения из БД свойств объекта, oid = " . $this->oid);

			if ($db_link->affected_rows == 0)	throw new Exception("Properties: не найдено данных об объекте, oid = " . $this->oid);

			while ($row = $result->fetch_assoc())	{
				$this->prop[$row["an_lang"]][stripslashes($row["an_flag"])]["value"] = stripslashes($row["an_value"]);
				$this->prop[$row["an_lang"]][stripslashes($row["an_flag"])]["date"] = stripslashes($row["an_date"]);
			}

			$this->notice[] = "Properties: получены свойства объекта, oid = " . $this->oid;
			$result->close();

		} catch (Exception $e)	{
			$this->error[] = $e->getMessage();
            $this->errorStatus = true;
		}
	}

	#редактирование данных пользователя
	public static function saveProp($oid, $lang, $vl, $flag, $dt = "")	{
		global $db_link;

		if (strlen($dt) == 0)	$dt = date("Y-m-d H:i");

		try	{
			if (!Properties::deleteProp($lang, $flag, intval($oid)))	{
				throw new Exception ("");
			}
			if($db_link->query("INSERT INTO ".DATABASE_PREF."properties (an_lang, an_oid, an_value, an_flag, an_date) VALUES (" . intval($lang) . ", " . intval($oid) . ", " . intval($vl) . ", '".$db_link->real_escape_string($flag)."', '".$db_link->real_escape_string($dt)."')"))
			return true;

		}	catch (Exception $e)	{
			return false;
		}
	}

	#удаление свойства
	public static function deleteProp($lang, $flag, $oid)	{
		global $db_link;

		try	{
			if(!$db_link->query("DELETE FROM ".DATABASE_PREF."properties WHERE an_oid=" . intval($oid) . " AND an_lang=" . intval($lang) . " AND an_flag ='" . $db_link->real_escape_string($flag) . "'"))
			throw new Exception ("");
			return true;

		}	catch (Exception $e)	{
			return false;
		}
	}

	#удаление всех свойств
	public static function deleteAllProp($oid)	{
		global $db_link;

		try	{
			if(!$db_link->query("DELETE FROM ".DATABASE_PREF."properties WHERE an_oid=" . intval($oid)))
			throw new Exception ("");
			return true;

		}	catch (Exception $e)	{
			return false;
		}
	}


	public static function GetPropList($lang, $flag, $datint = 0, $limrec = 0, $oredbydate = false)	{
		global $db_link;

		$dtadd = ($datint > 0) ? " AND  DATEDIFF(an_date, NOW()) >= -".intval($datint) : "";

		try	{
			if(!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."properties WHERE an_flag='" . $db_link->real_escape_string($flag) . "' AND an_lang = '" . intval($lang) . "' ".$dtadd." ORDER by ".(($oredbydate)? "an_date" : "an_value") . " DESC". (($limrec > 0) ? " LIMIT " . intval($limrec) : "")))
			throw new Exception ("");

			$arr = array();
			while ($row = $result->fetch_assoc())	{
				$arr[] = array(
					"an_oid" => $row["an_oid"],
					"an_value" => $row["an_value"],
					"an_flag" => stripslashes($row["an_flag"]),
					"an_date" => strtotime($row["an_date"]),
					"an_dateH" => stripslashes($row["an_date"])
				);
			}
			return $arr;

		}	catch (Exception $e)	{
			return false;
		}


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