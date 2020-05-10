<?
/*
#	Класс Multidata - Обработка перечней idшников объектов
# 	@version 4.1 February 2012
*/

class Multidata  {
    public $row;            # исходная строка
    public $ids_array;		# массив полученый из исходной строки
	private $error;         # отладочная информация об ошибке

	# принимает перечень id-шников через "," - преобразует в массив
    public function __construct($value, $check_access = false)	{

	    $this->ids_array = array();
	    if ($value == "" || $value == ",," || $value == ",")
	    	$testidValues = array();
	    else
			$testidValues = split(",", $value);

		reset($testidValues);
		$this->row = "";
		while (list($key, $val) = each($testidValues))	{
			if (intval($val) > 0)	{
				if ($check_access)	{					# пропускать id-шники недоступных на сайте объектов					$tempObj = new PlainObject($this->ids_array[$i]);
                	if (!$tempObj->objProp[$lang]->frontaccess)
                		continue;
				}

				$this->ids_array[] = $val;
				$this->row .= $val.",";
			}
		}

		if (strlen($this->row) > 0) $this->row = "," . $this->row;
    }


	# проверяет наличие id в списке
    public function ifMatch($testValue)	{    	if (in_array($testValue, $this->ids_array))	return true;
	        else return false;
    }

    # список объектов с названиями
    public function getObjectsList($lang)	{

		$return = array();
		GLOBAL $db_link;

		if (sizeof($this->ids_array) == 0) return $return;

		try {
			if(!$result = $db_link->query("SELECT an_name, an_oid FROM ".DATABASE_PREF."objectsprop WHERE an_oid IN (" . implode(", ", $this->ids_array) . ") AND an_lang = ".intval($lang)))	throw new Exception("Ошибка. Невозможно получить список объектов по массиву ids");

		} catch (Exception $e) {
			$this->error = $e->getMessage();
        	die($e->getMessage());
		}

		while( $res = $result->fetch_assoc() )	{
			$return[] = array("an_oid" => $res["an_oid"], "an_name" => stripslashes($res["an_name"]));
		}

		$result->close();

        return $return;
    }

    # возвращает переменные ошибок и статусов
    public function getStatus()	{
    	return $this->error;
    }

}

?>