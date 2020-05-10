<?
/*
#	Класс ObjectProp - свойства объекта
# 	@version 4.1 February 2012
*/

Class ObjectProp {
	public $name;			# название объекта
	public $title;			# title объекта
	public $header;			# заголовок объекта
	public $tags;			# теги объекта	
	public $keywords;       # ключевые слова
	public $description;    # описание
    public $sortnumber;		# сортировочный номер
	public $sorttype;		# тип сортировки (name, sortnumber, date)
	public $sortorder;		# флаг: порядок сортировки (true - возрастающий, false - убывающий)
    public $frontaccess;	# флаг: отображение на сайте
	public $menu;			# флаг: включается в выборку объекта SiteMenu
	public $restr;			# флаг: объект в закрытой зоне
	private $error;         # отладочная информация об ошибке
	private $action;		# отладочная информация о действии

	function __construct($name="", $title="", $header="", $keywords ="", $description = "", $sortnumber = 0.0, $sorttype = "num", $sortorder = true, $frontaccess = true, $menu = true, $restr = false, $tags="") {
		$this->name = $name;
		$this->title = $title;
		$this->header = $header;
		$this->keywords = $keywords;
		$this->tags = $tags;
		$this->description = $description;
		$this->sortnumber = doubleval($sortnumber);
		$this->sorttype = $sorttype;
		$this->sortorder = (bool)$sortorder;
		$this->frontaccess = (bool)$frontaccess;
		$this->menu = (bool)$menu;
		$this->restr = (bool)$restr;
		$this->action = "Инициализация переменных класса";
	}

	public function setData($oid, $lang)	{
		# Запись свойств объекта в БД
		GLOBAL $db_link;
		$this->action = "Запись свойств объекта в БД";
		$db_link->autocommit(false);

		try {
			# удаление старых занчений свойств
			if(!$db_link->query("DELETE FROM ".DATABASE_PREF."objectsprop WHERE an_oid = " . intval($oid) . " AND an_lang = " . intval($lang)))	throw new Exception("Ошибка. Невозможно удалить свойства объекта (oid = ".intval($oid).")");
			# запись новых значений свойств
            if(!$db_link->query("INSERT INTO ".DATABASE_PREF."objectsprop (an_oid, an_lang, an_name, an_title, an_header, an_keywords, an_description, an_sortnumber, an_sortorder, an_sorttype, an_frontendaccess, an_menu,  an_restr, an_tags) VALUES (". intval($oid) . ", ".intval($lang).", '".$db_link->real_escape_string($this->name)."','".$db_link->real_escape_string($this->title)."','".$db_link->real_escape_string($this->header)."','".$db_link->real_escape_string($this->keywords)."','".$db_link->real_escape_string($this->description)."',".$this->sortnumber.", ".($this->sortorder ? 1 : 0).", '".$this->sorttype."', ".($this->frontaccess ? 1 : 0).", ".($this->menu ? 1 : 0).", ".($this->restr ? 1 : 0).", '".$db_link->real_escape_string($this->tags)."')"))
            throw new Exception("Ошибка. Невозможно обновить свойства объекта(oid = ".intval($oid).")");
			$db_link->commit();

		} catch (Exception $e) {
			# откат
        	$this->error = $e->getMessage();
        	$db_link->rollback();
		}

		$db_link->autocommit(true);
	}

	# загрузка свойств объекта из БД
	static public function getData($oid)	{
		GLOBAL $db_link;
		$return = array();

		try	{
			if (!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."objectsprop WHERE an_oid =".intval($oid)))  throw new Exception("Ошибка. Невозможно получить свойства объекта (oid = ".$oid.")");
 		}
		catch (Exception $e)	{
			die($e->getMessage());
		}
			while( $res = $result->fetch_assoc() )
				$return[$res["an_lang"]] = new ObjectProp(stripslashes($res["an_name"]), stripslashes($res["an_title"]), stripslashes($res["an_header"]), stripslashes($res["an_keywords"]), stripslashes($res["an_description"]), $res["an_sortnumber"], $res["an_sorttype"], (bool)$res["an_sortorder"], (bool)$res["an_frontendaccess"], (bool)$res["an_menu"], (bool)$res["an_restr"], stripslashes($res["an_tags"]));

    		$result->close();

		return $return;
	}

	# возвращает переменные ошибок и статусов
    public function getStatus()	{
    	return $this->action."<br>".$this->error;
    }
}

?>