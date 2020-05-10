<?
/*
#	Класс User - Работа с пользователями системы
# 	@version 4.3 February 2014
#	4.3 в preg_match (проверка email учитывается что расширение домена может быть больше 4х символов saveData, saveTempData)
*/

class User {
	# конструктор - получение данных об пользователе
	public $uid;				# id пользователя
	public $login;          	# логин
	public $network;          	# аккаунт авторизуется через соцсеть (Facebook, ВКонтакте)
	public $networkid;          # ID пользователя в соцсети
	public $passwd;         	# пароль
	public $name;           	# имя
	public $lastname;			# фамилия пользователя
	public $email;              # email пользователя
	public $regdate;        	# дата регистрации
	public $active;         	# флаг: активирован ли пользователь
	public $userdata;       	# персональные настройки пользователя
	private $error = array();   # массив сообщений об ошибках
	public $errorStatus;    	# флаг: в процессе выполнения метода произошла ошибка
	private $action = array();  # массив сообщений о текущих изменениях
	private $notice = array();	# массив вспомогательных сообщений

	function __construct($uid, $network = "", $networkid = 0)	{
		global $db_link;
		$this->errorStatus = false;
		$this->notice[] = "User: создание объекта User";

		//если пользователь пришел из соцсети
		if ($network != "" && $networkid > 0)	{
			$this->notice[] = "User: создание объекта User на основе данных из соцсети (".$network.")";
			try	{
	            if(!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."users WHERE an_network = '".$db_link->real_escape_string($network)."' AND an_networkid = '".$db_link->real_escape_string($networkid) . "'"))
					throw new Exception("User: Ошибка во время получения из БД данных пользователя по указателю соцсети, networkid = " . $networkid);
				if ($db_link->affected_rows == 1)	{
					$this->notice[] = "User: получены данные пользователя по указателю соцсети, networkid = " . $networkid;

					$row = $result->fetch_assoc();
					$uid = $row["an_uid"];
				}
				else throw new Exception("User: - Пользователь не существует, networkid = " . $networkid);

				$result->close();
			} catch (Exception $e)	{
				$this->error[] = $e->getMessage();
            	$this->errorStatus = true;
            	$uid = 0;
			}
		}

		$this->uid = intval($uid);

		if ($uid == -1 || $uid == 0)	{
			$this->notice[] = "User: uid = " . $uid;
			return;
		}

		$this->getData();
	}

	# получение всех данных пользователя
	public function getData()	{
		global $db_link;

		$this->errorStatus = false;
		$this->notice[] = "User::getData - получение данных пользователя";

		try	{
			if(!$result = $db_link->query("SELECT * FROM ".DATABASE_PREF."users WHERE an_uid = ".$this->uid))
				throw new Exception("User::getData - Ошибка во время получения из БД данных пользователя, uid = " . $this->uid);
			if ($db_link->affected_rows == 1)	{
				$this->notice[] = "User::getData - получены данные пользователя, uid = " . $this->uid;

				$row = $result->fetch_assoc();
				$this->uid = $row["an_uid"];
				$this->network = stripslashes($row["an_network"]);
				$this->networkid = stripslashes($row["an_networkid"]);
				$this->name = stripslashes($row["an_name"]);
				$this->lastname = stripslashes($row["an_lastname"]);
				$this->email = stripslashes($row["an_email"]);
				$this->login = stripslashes($row["an_login"]);
				$this->passwd = stripslashes($row["an_passwd"]);
				$this->regdate = $row["an_regdate"];
				$this->active = $row["an_active"];
				$this->userdata = unserialize(base64_decode($row["an_userdata"]));
			}
			else throw new Exception("User::getData - Пользователь не существует, uid = " . $this->uid);

			$result->close();

		} catch (Exception $e)	{
			$this->error[] = $e->getMessage();
            $this->errorStatus = true;
		}

	}

	#блокировка администратора
	public function banUser()	{
		global $db_link;

		try {
			if(!$db_link->query("UPDATE ".DATABASE_PREF."users SET an_active = 0 WHERE an_uid=".$this->uid))
				throw new Exception("User::banUser - Ошибка блокировки пользователя, uid = " . $this->uid);
			else	$this->active = 0;
			$this->action[] = "User::banUser - Пользователь заблокирован, uid = " . $this->uid;
			return true;

		} catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	# удаление пользователя
	public function deleteUser() {
		global $db_link;

		try	{
			if(!$db_link->query("DELETE FROM ".DATABASE_PREF."users WHERE an_uid=".$this->uid))
			throw new Exception ("User::deleteUser - Ошибка удаления пользователя, uid = " . $this->uid);
			$this->action[] = "User::deleteUser - Пользователь удален, uid = " . $this->uid;
			return true;

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	# активация пользователя
	public function restoreUser() {
		global $db_link;

		try	{
			if(!$db_link->query("UPDATE ".DATABASE_PREF."users SET an_active = 1 WHERE an_uid=".$this->uid))
				throw new Exception ("User::restoreUser - Ошибка активации пользователя, uid = " . $this->uid);
			else	$this->active = 1;
			$this->action[] = "User::restoreUser - Пользователь активирован, uid = " . $this->uid;
			return true;

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	#редактирование данных пользователя
	public function saveData($login, $name, $lastname, $email, $passwd, $newpasswd, $userdata, $ignorNess = false)	{
		global $db_link, $userData;
        $this->notice[] = "User::saveData - изменение данных пользователя";

		try {
			//проверка заполнения переменных
			if (strlen($name) == 0)	throw new Exception("User::saveData - Необходимо ввести имя");
			if (strlen($email) > 0 && !preg_match("/^[-a-zA-Z0-9_.]{1,}@[-a-zA-Z0-9_.]{1,}\.[a-zA-Z]{2,}$/", $email))	throw new Exception("User::saveData - Неверный формат e-mail");
			if (strlen($login) == 0)	{
				$this->notice[] = "User::saveData - Логин не введен, попытка использовать email";
    			if (strlen($email) > 0)	{
    				$login = $email;
    				$this->notice[] = "User::saveData - В качестве логина используется email";
    			}
    			else	throw new Exception("User::saveData - Необходимо ввести логин или e-mail");
			}
			elseif (!preg_match("/^[-a-zA-Z0-9_.@]{1,}$/", $login))	throw new Exception("User::saveData - Для ввода логина можно использовать только латинские символы, цифры, а также символы &nbsp;.&nbsp;-&nbsp;_&nbsp;@");

			if (!$ignorNess)	{
				reset($userData);
				while (list($key, $val) = each($userData))
					if ((!isset($userdata[$key]) || strlen($userdata[$key]) == 0) && $val["ness"])	throw new Exception("User::saveData - Необходимо заполнить обязательное поле '" . $val["name"] . "'");
			}


			if ((isset($passwd) && $passwd == $newpasswd))	{

				if ($this->uid == -1)	{
					if (strlen($passwd) == 0) throw new Exception("User::saveData - Необходимо ввести пароль");
					if(!$db_link->query("INSERT INTO ".DATABASE_PREF."users (an_login, an_name, an_lastname, an_email, an_passwd, an_regdate, an_active, an_userdata) VALUES ('".$db_link->real_escape_string($login)."', '".$db_link->real_escape_string($name)."', '".$db_link->real_escape_string($lastname)."', '".$db_link->real_escape_string($email)."', '".md5($passwd)."', '".date("Y-m-d H:i")."', 1, '".base64_encode(serialize($userdata))."')"))
					throw new Exception("User::saveData - пользователь с таким логином уже существует.");

					$this->uid = $db_link->insert_id;
				}
				else	{
					if(!$db_link->query("UPDATE ".DATABASE_PREF."users SET an_login='".$db_link->real_escape_string($login)."', an_name='".$db_link->real_escape_string($name)."', an_lastname='".$db_link->real_escape_string($lastname)."', an_email='".$db_link->real_escape_string($email)."', an_userdata = '".base64_encode(serialize($userdata))."' WHERE an_uid=".$this->uid))
						throw new Exception("User::saveData - Ошибка редактирования данных пользователя, uid = " . $uid);
					if (strlen($passwd) > 0)	{
						if(!$db_link->query("UPDATE ".DATABASE_PREF."users SET an_passwd='".md5($passwd)."' WHERE an_uid=".$this->uid))
							throw new Exception("User::saveData - Невозможно изменить пароль пользователя, uid = " . $uid);
						else   $this->notice[] = "User::saveData - пароль пользователя изменен, uid = " . $this->uid;
					}
				}
				$this->action[] = "User::saveData - Данные пользователя изменены, uid = " . $this->uid;
				$this->getData();

			}
			else	throw new Exception("User::saveData - Значение поля \"Пароль\" и \"Подтвердить пароль\" должны совпадать");

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}

		//возможные дополнительные действия после успешной отработки метода
		if (function_exists("User_saveData"))	User_saveData($this);

		return true;
	}

	#указание на соцсеть пользователя
	public function addNetworkData($network, $networkid)	{
		global $db_link;
        $this->notice[] = "User::addNetworkData - запись данных соцсети пользователя";

		try {
			if ($networkid <= 0)	throw new Exception("User::addNetworkData - Некорректное значение ID соцсети");

			if(!$db_link->query("UPDATE ".DATABASE_PREF."users SET an_network='".$db_link->real_escape_string($network)."', an_networkid = '".$db_link->real_escape_string($networkid) . "' WHERE an_uid=".$this->uid))
				throw new Exception("User::addNetworkData - Ошибка редактирования данных пользователя, uid = " . $this->uid);

			$this->action[] = "User::addNetworkData - Данные соцсети внесены, uid = " . $this->uid;
			$this->getData();

			return true;

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}
	}

	#создание заблокированного пользователя
    public function newBlockedUser($login, $name, $lastname, $email, $passwd, $newpasswd, $userdata, $ignorNess = false)	{
		$return = $this->saveData($login, $name, $lastname, $email, $passwd, $newpasswd, $userdata, $ignorNess);
		if ($return)	{
			$this->banUser();
			$this->action[] = "User::newBlockedUser - успешный вызов User::saveData. Внимание! Аккаунт не активирован";
			return true;
		}	else	{
			$this->errorStatus = true;
			$this->error[] = "User::newBlockedUser - неудачный вызов User::saveData";
			return false;
		}
	}


	#запись промежуточных данных пользователя, возвращает активационный линк
	public function saveTempData($login, $name, $lastname, $email, $passwd, $newpasswd, $userdata, $ignorNess = false)	{
		global $db_link, $userData;
        $this->notice[] = "изменение промежуточных данных пользователя";

		try {
			//проверка заполнения переменных
			if (strlen($name) == 0)	throw new Exception("User::saveTempData - Необходимо ввести имя");
			if (strlen($email) > 0 && !preg_match("/^[-a-zA-Z0-9_.]{1,}@[-a-zA-Z0-9_.]{1,}\.[a-zA-Z]{2,}$/", $email))
			throw new Exception("User::saveTempData - Неверный формат e-mail");
			if (strlen($login) == 0)	{
				$this->notice[] = "User::saveTempData - Логин не введен, попытка использовать email";
    			if (strlen($email) > 0)	{
    				$login = $email;
    				$this->notice[] = "User::saveTempData - В качестве логина используется email";
    			}
    			else	throw new Exception("User::saveTempData - Необходимо ввести логин или e-mail");
			}
			elseif (!preg_match("/^[-a-zA-Z0-9_.@]{1,}$/", $login))	throw new Exception("User::saveTempData - Для ввода логина можно использовать только латинские символы, цифры, а также символы &nbsp;.&nbsp;-&nbsp;_&nbsp;@");

			if (!$ignorNess)	{
				reset($userData);
				while (list($key, $val) = each($userData))
					if ((!isset($userdata[$key]) || strlen($userdata[$key]) == 0) && $val["ness"])
					throw new Exception("User::saveTempData - Необходимо заполнить обязательное поле '" . $val["name"] . "'");
			}

			//проверка логина в таблице пользователей
			$result = $db_link->query("SELECT COUNT(*) AS cnt FROM ".DATABASE_PREF."users WHERE an_login='".$db_link->real_escape_string($login)."'");
			$cnt = $result->fetch_object()->cnt;
			if ($cnt != 0)	throw new Exception("User::saveTempData - Пользователь с таким логином уже существует.");

			if (isset($passwd) && $passwd == $newpasswd && $this->uid == -1)	{

				if (strlen($passwd) == 0) throw new Exception("User::saveTempData - Необходимо ввести пароль");
				$key_str = uniqid("temp_", true);
				if(!$db_link->query("INSERT INTO ".DATABASE_PREF."users_temp (an_login, an_name, an_lastname, an_passwd, an_regdate, an_active, an_email, an_userdata, an_key) VALUES ('".$db_link->real_escape_string($login)."', '".$db_link->real_escape_string($name)."', '".$db_link->real_escape_string($lastname)."', '".md5($passwd)."', '".date("Y-m-d H:i")."', 1, '".$db_link->real_escape_string($email)."', '".base64_encode(serialize($userdata))."', '".$key_str."')"))	throw new Exception("User::saveTempData - Пользователь с таким логином уже существует.");

			}	else	throw new Exception("User::saveTempData - Значение поля \"Пароль\" и \"Пароль-повторить\" должны совпадать");

		}	catch (Exception $e)	{
			$this->error[] = $e->getMessage();
			$this->errorStatus = true;
			return false;
		}

        $this->action[] = "User::saveTempData - Создана временная пользовательская запись, активационный ключ: " . $key_str;

		//возможные дополнительные действия после успешной отработки метода
		if (function_exists("User_saveTempData"))	User_saveTempData($key_str, $name, $lastname, $email);

		return $key_str;
	}


	# авторизация пользователя
	public static function loginUser($lg, $pw)	{
		global $db_link;

		$result = $db_link->query("SELECT an_uid FROM ".DATABASE_PREF."users WHERE an_login='".$db_link->real_escape_string($lg)."' AND an_passwd='".md5($pw)."' AND an_active = 1") or die("User::loginUser - ошибка авторизации");
		if ($db_link->affected_rows == 1)	return $result->fetch_object()->an_uid;
  		else return 0;

	}

	# список пользователей
	public static function getUserList($order = "an_regdate")	{
		global $db_link;

		switch($order)	{

			case "an_name" :
				$ordadd = "an_name ASC";
			break;
			case "an_lastname" :
				$ordadd = "an_lastname, an_name ASC";
			break;
			default :
			    $ordadd = "an_regdate DESC";
			break;

		}

		$arr = array();
		$result = $db_link->query("SELECT an_uid, an_name, an_lastname, an_email, an_active, an_regdate FROM ".DATABASE_PREF."users ORDER BY ".$ordadd)
		   or die ("User::getUserList - Ошибка во время получения из БД списка пользователей.");

		while ($row = $result->fetch_assoc())	{
	 		$arr[] = array(
	 		 	"an_uid" 		=> $row["an_uid"],
	 		 	"an_name" 		=> stripslashes($row["an_name"]),
	 		 	"an_lastname"	=> stripslashes($row["an_lastname"]),
	 		 	"an_email"		=> stripslashes($row["an_email"]),
	 		 	"an_active"		=> $row["an_active"],
	 		 	//"an_userdata" 	=> unserialize(base64_decode($row["an_userdata"])),
	 		 	"an_regdate" 	=> stripslashes($row["an_regdate"])
	 		);
		}
		$result->close();

		return $arr;
	}

	# преобразование дополнительных данных пользователя в массив
	public static function postUserData()	{
	    global $userData;

		reset($userData);
		$udata = array();
		while (list($key, $val) = each($userData))	{
			//поля типа Choose
			if (isset($_POST["mult_" . $key]) && $_POST["mult_" . $key] == 1)	{
				$str = "";
				if (!$val["multiple"])	{
					if (isset($_POST["userdata_" . $key]) && strlen($_POST["userdata_" . $key])>0)
						$str = $_POST["userdata_" . $key] . ",";
				}
				else	{
					reset($_POST["userdata_" . $key]);
					while (list (, $v) = each($_POST["userdata_" . $key]))
						$str .= $v . ",";
				}
				if (strlen($str) > 0) $udata[$key] = "," . $str;

			}
            //файлы
			elseif ($val["type"] == "file")	{

	        	if (isset($_FILES["userdata_" . $key]) && strlen($_FILES["userdata_" . $key]["name"])>0)	{
	        		$file_name = rand(1000, 9999) . CustomLibs :: transliteral	(basename($_FILES["userdata_" . $key]["name"]));
					$udata[$key] = $file_name;
					////////////////////////////ресайз и запись
					$im = imagecreatefromjpeg($_FILES["userdata_" . $key]["tmp_name"]);
					$size = GetImageSize($_FILES["userdata_" . $key]["tmp_name"]);
					$newh = $size[1];
					$neww = $size[0];

					//resize
					if ($size[0] > $val["image_max_width"])	{
						$neww = $val["image_max_width"];
						$newh = intval($size[1]/($size[0]/$val["image_max_width"]));
					}
					if ($newh > $val["image_max_height"])	{
						$neww = intval($neww/($newh/$val["image_max_height"]));
						$newh = $val["image_max_height"];
					}

					$im2 = imagecreatetruecolor($neww, $newh);
					imagecopyresampled($im2, $im, 0, 0, 0, 0, $neww, $newh, imagesx($im), imagesy($im));
					imagejpeg($im2, ROOTPATH . "/_Upload/_users/".$file_name, 100);
					chmod(ROOTPATH . "/_Upload/_users/".$file_name, 0777);

					imagedestroy($im);
					imagedestroy($im2);
					//copy($_FILES["userdata_" . $key]["tmp_name"], ROOTPATH . "/_Upload/_users/" . $file_name);
				}
				else	$udata[$key] = 	$_POST["load_" . $key];
			}
			else $udata[$key] = $_POST["userdata_".$key];
		}

    	return $udata;
	}

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