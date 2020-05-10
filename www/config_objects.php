<?
# типы объектов сайта
/*
  ключи массива $objectsTypes[..]["fields"] не могут быть числами или принимать значения:
  	an_url
    an_type
    an_date
    an_parent
  рекомендуется строить названия ключей как комбинацию id - типа объекта и краткого описания поля, общая длина названия при этом не должна превышать 20 символов. Например: 1_text
*/

/*
    если поле типа "LoadFile", содержит настройки "image", то загружать можно только графические файлы типа gif,jpg,png
*/


$objectsTypes = array (
	1 => array (
		"name" => "Страница сайта",
		"saveas" => false,
		"isname" => true,
		"ismeta" => true,
		"tags" => true,
		"iszona" => true,
		"isurl" => true,
		"ismenu" => true,
		"isactive" => true,
		"folder" => true,
		"gallery" => true,
		"gallery_photos" => array( 
			"preview" => array(
				array ("preview_width" => 82,"preview_height" => 82)
			),
			"watermark" => array(
				"file" => "/_Images/white_logo.png",
				"position" => array(
					//"xy" => array(100,50), //точные координаты
					"place" => array(
						"x" => "right", //позиция (right-top правый верхний угол, left-bottom левый нижний) 
						"y" => "top",
						"margin" => 10 //отступ от краёв изображения
					)
				)
			),
			"image_max_width" => 1200,
			"image_max_height" => 800
		),
		"searchDescription" => "1_text",
		"fields" => array (
			"1_text" => array (
				"name" => "Текст",
				"type" => "WYSIWYG"
			),
			"1_ad" => array (
				"name" => "Баннеры",
				"type" => "TextPlain"
			),
			"1_date" => array (
				"name" => "Дата",
				"type" => "Date"
			),
			"1_template" => array (
				"name" => "Шаблон страницы",
				"type" => "TextLine"
			),
			"1_modul" => array (
				"name" => "Внутренний шаблон",
				"type" => "TextLine"
			),
			"1_redirect" => array (
				"name" => "Редирект",
				"type" => "TextLine"
			)
		)
	),
	2 => array (
		"name" => "Рубрика",
		"isname" => true,
		"ismenu" => false,
		"gallery" => false,
		"ismeta" => false,
		"isactive" => false,
		"iszona" => false,
		"isurl" => false,
		"folder" => true,
		"fields" => array (
		)
	),
	3 => array (
		"name" => "Новости",
		"isname" => true,
		"ismenu" => false,
		"gallery" => false,
		"ismeta" => false,
		"isactive" => true,
		"iszona" => false,
		"isurl" => false,
		"folder" => false,
		"searchDescription" => "3_text",
		"fields" => array (
			"3_img" => array (
				"name" => "Картинка",
				"type" => "LoadFile",
				"keep_real_name" => false,
				"default_lang" => 1,
				"image" => array(
					"image_max_width" => 800,
					"image_max_height" => 600,
					"preview" => true,
					"preview_width" => 139,
					"preview_height" => 93
				)
			),
			"3_user"	=> array(
				"name" => "Автор",
				"type" => "User",
				"multiple" => false,
			),
			"3_anons" => array (
				"name" => "Анонс",
				"type" => "TextPlain"
			),
			"3_text" => array (
				"name" => "Текст",
				"type" => "WYSIWYG"
			),
			"3_corp"	=> array(
				"name" => "Корпус",
				"type" => "Link",
				"tartype" => "1",
				"target" => "81",
				"multiple" => true,
				"lookinside" => false
			)
		)
	),
	4 => array (
		"name" => "Включение",
		"isname" => true,
		"ismenu" => false,
		"gallery" => false,
		"ismeta" => false,
		"isactive" => false,
		"iszona" => false,
		"isurl" => false,
		"folder" => false,
		"fields" => array (
			"4_text" => array (
				"name" => "Текст",
				"type" => "WYSIWYG"
			)
		)
	),
	5 => array (
		"name" => "Баннер",
		"isname" => true,
		"isactive" => true,
		"ismenu" => false,
		"gallery" => false,
		"ismeta" => false,
		"iszona" => false,
		"isurl" => false,
		"folder" => false,
		"fields" => array (
			"5_foto"	=> array(
				"name" => "Фото",
				"type" => "LoadFile"
			),
			"5_link" => array (
				"name" => "Ссылка",
				"type" => "TextLine"
			)
		)
	),
	6 => array (
		"name" => "Термин",
		"isname" => true,
		"isactive" => true,
		"ismenu" => false,
		"gallery" => false,
		"ismeta" => false,
		"iszona" => false,
		"isurl" => false,
		"folder" => false,
		"fields" => array (
			"6_anons" => array (
				"name" => "Анонс",
				"type" => "TextPlain"
			),
			"6_text" => array (
				"name" => "Текст",
				"type" => "WYSIWYG"
			)
		)
	),
	7 => array (
		"name" => "Марка кабеля",
		"isname" => true,
		"isactive" => true,
		"ismenu" => false,
		"gallery" => false,
		"ismeta" => false,
		"iszona" => false,
		"isurl" => false,
		"folder" => false,
		"fields" => array (
			"7_csv" => array (
				"name" => "Профили",
				"type" => "IETable",
                "iscapture" => false,
                "fields" => array (
                    0 => "название",
                    1 => "метраж"
                )
			)
		)
	)
);

$searchSections = array (
	"0" => array (
		"oid" => 0,
		"type" => 1,
		"field" => "1_text"
	),
	"1" => array (
		"oid" => 0,
		"type" => 3,
		"field" => "3_text",
		"url" => "news"
	)
);

$userData = array (
	"avat" => array (
		"name" => "Аватар",
		"type" => "file",
		"ness" => false
	),
	"sex" => array (
		"name" => "Пол",
		"type" => "Choose",
		"multiple" => false,
		"options" => array(1 => "Мужской", 2 => "Женский"),
		"ness" => false
	),
	"tel" => array (
		"name" => "Телефон",
		"type" => "line",
		"ness" => true
	),
	"mobile" => array (
		"name" => "Мобильный",
		"type" => "line",
		"ness" => false
	),
	"addrress" => array (
		"name" => "Адрес",
		"type" => "line",
		"ness" => false
	),
	"office" => array (
		"name" => "Организация",
		"type" => "line",
		"ness" => false
	),
	"job" => array (
		"name" => "Должность",
		"type" => "line",
		"ness" => false
	),
	"web" => array (
		"name" => "http",
		"type" => "line",
		"ness" => false
	),
	"inn" => array (
		"name" => "Дополнительная информация",
		"type" => "text",
		"ness" => false
	),
	"news" => array (
		"name" => "Подписаться на новости",
		"type" => "checkbox",
		"ness" => false
	)
);


?>