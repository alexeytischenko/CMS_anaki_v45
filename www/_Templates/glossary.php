<?
//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die("ошибка подключения шаблона");
//проверка допустимого типа объекта
if ($id > 0 && !in_array($showInnerObj->objectType, array(5)))	die("недопустимый тип объекта");

if ($id > 0)	{

	echo $showInnerObj->values[$lang]["Текст"];
	echo "<br><p><a href=\"/".$slang."/0/0/".$mod."/".$showPage->url."/\">".TemplateTranslate("назад к глоссарию", $slang)."</a>";

}	else	{

	if ($mod=="")	$mod = "а";

   	$glossary = new ObjectsList($showPage->oid, $lang, 6, true, false);
	$glossList = $glossary->getList();



	$letters = array();
	$results = array();
	foreach($glossList as $v):
		$k = mb_strtolower(mb_substr($v["an_name"], 0, 1));
		$letters[$k]++;
		if ($k == $mod)	$results[] = $v;
	endforeach;
    //print_R($letters);

	$a = array('А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Э','Ю','Я','br','A','B','C','D','E','F','G','H','I','K','L','M','N','O','P','R','S','T','U','V','W','X','Y','Z');

    ?><p><?
	reset($a);
	while (list(,$val) = each($a))	{
		if ($val=='br')	{echo "</p><p>";	continue;}
		if ($letters[mb_strtolower($val)] > 0)	echo (($mod==mb_strtolower($val))? "<strong>" : "")."<a href=\"/".$slang."/0/0/".mb_strtolower($val)."/".$showPage->url."/\">".$val."</a> ".(($mod==mb_strtolower($val))? "</strong>" : "");
		else echo $val . " ";
	}
     ?></p>
      <?foreach($results as $v):
      ?>
      <p><?=((strlen($v["Текст"]) > 0) ? "<a href=\"/".$slang."/".$v["an_oid"]."/0/".$mod."/".$showPage->url."/\">" : "")?> <?=$v["an_name"]?></a><br />
     <?=$v["Анонс"]?></p>
	<?endforeach;?>


<?}?>