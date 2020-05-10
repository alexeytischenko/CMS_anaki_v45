<?//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true || (isset($showPage->objData[$lang]["Внутренний шаблон"]) && $showPage->objData[$lang]["Внутренний шаблон"]=="general"))	die("ошибка подключения шаблона");
?>

<title><?=TemplateTranslate("Название", $slang)?> - <?=gettitle($lang)?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="keywords" content="<?=getmetakey($lang)?>" />
<meta name="description" content="<?=getmetadesc($lang)?>" />

    <?include("header.php");?>

<?	//боковое меню
	//ObjectsMenu(родитель, язык, массив типов)
    $menuleft = new ObjectsMenu(1, $lang, array(1), 2);
   	$menuList = $menuleft->getSiteMenu();
      	if (isset($menuList[0]) && sizeof($menuList[0])>0)	{?>
       <ul>
		<?foreach($menuList[0] as $v):
		?>
		<li><a href="/<?=$slang?>/<?=$v["an_url"]?>/"<?=((ifactive($v["an_oid"])) ? " class=\"active\"" : "")?>><?=$v["an_name"]?></a>
		<?
			if (isset($menuList[$v["an_oid"]]) && sizeof($menuList[$v["an_oid"]]) > 0) {?>
		<ul>
		 	<?	foreach($menuList[$v["an_oid"]] as $v2):	?>
           <li><a href="/<?=$slang?>/<?=$v2["an_url"]?>/"<?=((ifactive($v2["an_oid"])) ? " class=\"active\"" : "")?>><?=$v2["an_name"]?></a></li>
            <?	endforeach;?>
        </ul>
        <?	}  ?>
        </li>
		<?endforeach;?>
    </ul>
	<?}?>

  <div id="search">
    <form id="form1" method="post" action="/<?=$slang?>/search/">
      <input name="str" type="text" id="textfield" value="<?=TemplateTranslate("search", $slang)?>" onclick="javascript:if(this.value=='<?=TemplateTranslate("search", $slang)?>')this.value='';" />
      <input type="hidden" name="sub" value="1">
      <input type="submit" name="button" id="button" value=" " />
    </form>

  <?
  //получение значения поля с возможностью наследовать от родителей, если текущее не установлено
  echo PlainObject::getParentFieldValue($showPage->oid, $lang, "Контакты");
  ?>

	<?//список с фильтрацией по значению поля
	$fieldsVal = array();
	$fieldsVal[0] = array (
		"id"=>"3_sector",
		"vals" => array(
			",".$showPage->oid.","
		)
	);
	$news = new ObjectsList(17, $lang, 3, true, false, true, 0, 0, $fieldsVal);
	foreach ($news->getList(1,1) as $publ)	:?>
		<h3><?=$publ["an_name"]?></h3>
		<div class="news"><a href="/<?=$slang?>/<?=$publ["an_oid"]?>/news/"><p><?=$publ["Анонс"]?></p></a></div>
	<?endforeach;?>

   <p class="bread">
   <?
	//хлебные крошки
         for ($i = 0; $i < sizeof($showPage->path); $i++)	{
         	if ($i != 0)	echo " <img src=\"/images/arrow_5.gif\" alt=\"\" width=\"13\" height=\"14\" /> ";
         	if ($i != (sizeof($showPage->path)-1) || $id > 0)	echo "<a href=\"/page/".$showPage->path[$i]["an_url"]."/\">";
         	echo $showPage->path[$i]["an_name"];
         	echo "</a>";
         }
         if ($id > 0)	echo " <img src=\"/images/arrow_5.gif\" alt=\"\" width=\"13\" height=\"14\" /> " . $showInnerObj->objProp[$lang]->name;
	?>
  </p>
    <h1><?=getheader($lang)?> </h1>

    <?
		//подключение внутреннего шаблона, либо вывод значения поля "Текст"
		if (isset($showPage->values[$lang]["Внутренний шаблон"]))
			include("_Templates/".$showPage->values[$lang]["Внутренний шаблон"].".php");
		else
			echo $showPage->values[$lang]["Текст"];
	?>


	<?include("footer.php");?>
