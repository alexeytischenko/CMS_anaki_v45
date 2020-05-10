<?
/*
#	Класс Admin - Работа с администраторами системы
# 	@version 4.1 February 2012
*/

class Admin {
	# конструктор - получение данных об администраторе
	public $uid;				# id администратора
	public $login;          	# логин
	public $passwd;         	# пароль
	public $name;           	# имя
	public $regdate;        	# дата регистрации
	public $active;         	# флаг: активирован ли администратор
	public $super;         		# флаг: может контролировать учетные записи других администраторов
	public $userdata;       	# персональные настройки администратора
	public $useract;       		# интерфейсный выбор администратора (раскрытые узлы, закладки)
	private $error = array();   # массив сообщений об ошибках
	public $errorStatus;    	# флаг: в процессе выполнения метода произошла ошибка
	private $action = array();  # массив сообщений о текущих изменениях
	private $notice = array();	# массив вспомогательных сообщений

	function __construct($uid)	{		global $db_link;

		$this->errorStatus = false;
		$this->notice[] = "Admin: создание экземпляра объекта";

		$this->uid = $uid;

		if ($uid == -1 || $uid == 0)	{			$this->notice[] = "Admin: uid = " . $uid;
			return;
		}

		$this->getData();
	}

	# получение всех данных администратора
	public function getData()	{		global $db_link;

		$this->errorStatus = false;
		$this->notice[] = "Admin: получение данных администратора";

		try	{
			if(!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."admins WHERE an_uid = ".$this->uid))
				throw new Exception("Admin: Ошибка во время получения из БД данных администратора, uid = " . $this->uid);
			if ($db_link->affected_rows == 1)	{
				$this->notice[] = "Admin: получены данные администратора, uid = " . $this->uid;

				$row = $result->fetch_assoc();
				$this->uid = $row["an_uid"];
				$this->name = stripslashes($row["an_name"]);
				$this->login = stripslashes($row["an_login"]);
				$this->passwd = stripslashes($row["an_passwd"]);
				$this->regdate = $row["an_regdate"];
				$this->active = $row["an_active"];
				$this->super = (bool)$row["an_super"];
				$this->userdata = @unserialize($row["an_userdata"]);
				$this->useract = @unserialize($row["an_useract"]);
			}
			else throw new Exception("Admin: администратор не существует, uid = " . $this->uid);

			$result->close();

		} catch (Exception $e)	{
			$this->error[] = $e->getMessage();
            $this->errorStatus = true;
		}


	}

	#блокировка администратора
	public function banAdmin()	{		global $db_link;

		try {
			if (!$this->checkPermission())	throw new Exception("Admin: У вас нет прав редактировать эту учетную запись");

			if(!$db_link->query("UPDATE ".DATABASE_PREF."admins SET an_active = 0 WHERE an_uid=".$this->uid))
				throw new Exception("Admin: Ошибка блокировки администратора, uid = " . $this->uid);
			else	$this->active = 0;
			$this->action[] = "Admin: администратор заблокирован, uid = " . $this->uid;
			return true;

		} catch (Exception $e)	{			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	# удаление администратора
	public function deleteAdmin() {
		global $db_link;

		try	{			if (!$this->checkPermission())	throw new Exception("Admin: У вас нет прав редактировать эту учетную запись");

			if(!$db_link->query("DELETE FROM ".DATABASE_PREF."admins WHERE an_uid=".$this->uid))
			throw new Exception ("Admin: Ошибка удаления администратора, uid = " . $this->uid);
			$this->action[] = "Admin: Администратор удален, uid = " . $this->uid;
			return true;

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}


	# активация администратора
	public function restoreAdmin() {		global $db_link;

		try	{
			if (!$this->checkPermission())	throw new Exception("Admin: У вас нет прав редактировать эту учетную запись");

			if(!$db_link->query("UPDATE ".DATABASE_PREF."admins SET an_active = 1 WHERE an_uid=".$this->uid))
				throw new Exception ("Admin: Ошибка активации администратора, uid = " . $this->uid);
			else	$this->active = 1;
			$this->action[] = "Admin: Администратора активирован, uid = " . $this->uid;
			return true;

		}	catch (Exception $e)	{			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}


	#редактирование данных администратора
	public function saveData($login, $name, $passwd, $newpasswd, $userdata)	{		global $db_link;

		try {			if (!$this->checkPermission())	throw new Exception("Admin::saveData - У вас нет прав редактировать эту учетную запись");

			if ($name == "")	throw new Exception("Admin: Необходимо ввести имя");
			if ($login == "")	throw new Exception("Admin: Необходимо ввести логин");
            elseif (!preg_match("/^[-a-zA-Z0-9_.@]{1,}$/", $login))	throw new Exception("Admin::saveData - Для ввода логина можно использовать только латинские символы, цифры, а также символы &nbsp;.&nbsp;-&nbsp;_&nbsp;@");
			if ((isset($passwd) && $passwd == $newpasswd))	{

				if ($this->uid == -1)	{
					if (strlen($passwd) == 0) throw new Exception("Admin::saveData - Необходимо ввести пароль");
					if(!$db_link->query("INSERT INTO ".DATABASE_PREF."admins (an_login, an_name, an_passwd, an_regdate, an_active, an_userdata) VALUES ('".$db_link->real_escape_string($login)."', '".$db_link->real_escape_string($name)."', '".md5($passwd)."', '".date("Y-m-d H:i")."', 1, '".serialize($userdata)."')"))
					throw new Exception("Admin::saveData - администратор с таким логином уже существует.");

					$this->uid = $db_link->insert_id;
					$this->active = 1;
					$this->super = 0;
				}
				else	{
					if(!$db_link->query("UPDATE ".DATABASE_PREF."admins SET an_login='".$db_link->real_escape_string($login)."', an_name='".$db_link->real_escape_string($name)."', an_userdata = '".serialize($userdata)."' WHERE an_uid=".$this->uid))
						throw new Exception("Admin::saveData - Ошибка редактирования данных администратора, uid = " . $uid);
					if (strlen($passwd) > 0)	{
						if(!$db_link->query("UPDATE ".DATABASE_PREF."admins SET an_passwd='".md5($passwd)."' WHERE an_uid=".$this->uid))
						throw new Exception("Admin::saveData - Невозможно изменить пароль администратора, uid = " . $uid);
					}
				}
				$this->action[] = "Admin::saveData - Данные администратора изменены, uid = " . $this->uid;
				$this->getData();

				return true;
			}
			else	throw new Exception("Admin::saveData - Значение поля \"Пароль\" и \"Подтвердить пароль\" должны совпадать");

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	# проверка есть ли права редактировать данные
	public function checkPermission()	{
		//редактировать Администратора (uid=1) может только он сам
		if ($this->uid == 1 && $_SESSION["an_uid"] != 1)	return false;

		//обычный пользователь не может редактировать суперюзера
		$who = new Admin($_SESSION["an_uid"]);
		if ($this->super && !$who->super)	return false;
		return true;
	}

	# добавить|удалить открытую папку в личные настройки
	public function opencloseFolder($foid, $open = true)	{        global $db_link;

        $this->useract["open_folders"] = array_unique((array)$this->useract["open_folders"], SORT_NUMERIC);
		try	{        	if ($open)	$this->useract["open_folders"][] = intval($foid);
        	else	{          		reset($this->useract["open_folders"]);
          		while(list($key, $val) = each($this->useract["open_folders"]))
                	if ($val == $foid)	{                		unset($this->useract["open_folders"][$key]);
                	}
        	}

			if(!$db_link->query("UPDATE ".DATABASE_PREF."admins SET an_useract='".serialize($this->useract)."' WHERE an_uid=".$this->uid))
				throw new Exception("Admin: Ошибка записи настроек администратора, uid = " . $this->uid);

			$this->notice[] = "Admin: Настройки администратора изменены, uid = " . $this->uid;

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	# добавить|удалить открытую папку из избраного
	public function adddeleteBookmark($foid, $add = true)	{
        global $db_link;

        $this->useract["user_bookmarks"] = array_unique((array)$this->useract["user_bookmarks"], SORT_NUMERIC);
		try	{
        	if ($add)	$this->useract["user_bookmarks"][] = intval($foid);
        	else	{
          		reset($this->useract["user_bookmarks"]);
          		while(list($key, $val) = each($this->useract["user_bookmarks"]))
                	if ($val == $foid)	{
                		unset($this->useract["user_bookmarks"][$key]);
                	}
        	}

			if(!$db_link->query("UPDATE ".DATABASE_PREF."admins SET an_useract='".serialize($this->useract)."' WHERE an_uid=".$this->uid))
				throw new Exception("Admin: Ошибка записи настроек администратора, uid = " . $this->uid);

			$this->notice[] = "Admin: Настройки администратора изменены, uid = " . $this->uid;

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	# авторизация администратора
	public static function loginAdmin($lg, $pw)	{
		global $db_link;

		$result = $db_link->query("SELECT an_uid FROM ".DATABASE_PREF."admins WHERE an_login='".$db_link->real_escape_string($lg)."' AND an_passwd='".md5($pw)."' AND an_active = 1") or die("Admin: ошибка авторизации");
		if ($db_link->affected_rows == 1)	return $result->fetch_object()->an_uid;
  		else return 0;

	}

	# массив администраторов
	public static function getAdminList()	{
		global $db_link;

		$arr = array();
		$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."admins ORDER BY an_regdate DESC")
		   or die ("Admin: Ошибка во время получения из БД списка пользователей.");

		while ($row = $result->fetch_assoc())	{
	 		$arr[] = array(
	 		 	"uid" => $row["an_uid"],
	 		 	"name" => stripslashes($row["an_name"]),
	 		 	"active" => $row["an_active"],
	 		 	"super" => (bool)$row["an_super"],
	 		 	"an_userdata" => unserialize($row["an_userdata"]),
	 		 	"an_useract" => unserialize($row["an_useract"])
	 		);
		}
		$result->close();

		return $arr;
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