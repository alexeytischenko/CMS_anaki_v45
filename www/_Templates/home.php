<?//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true || (isset($showPage->objData[$lang]["Внутренний шаблон"]) && $showPage->objData[$lang]["Внутренний шаблон"]=="home"))	die("ошибка подключения шаблона");
?>

<title><?=TemplateTranslate("Название", $slang)?> - <?=gettitle($lang)?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="keywords" content="<?=$showPage->values[$lang]["an_keywords"]?>" />
<meta name="description" content="<?=$showPage->values[$lang]["an_description"]?>" />

    <?include("header.php");?>


        <? //слайдер
    	$banners = new ObjectsList(103, $lang, 4, true, false);
        $bannersList = $banners->getList();
        for ($i = 0; $i < sizeof($bannersList); $i++)	{
           $size = GetImageSize("_Upload/".$bannersList[$i]["Фото"]);
          // print_r($size);
        	echo "<div class=\"slide \" ><a href=\"".$bannersList[$i]["Ссылка"]."\"><img src=\"/_Upload/".$bannersList[$i]["Фото"]."\"  alt=\"side\" ".$size[3]." /></a></div>";
        }
    	?>




       <?=$showPage->values[$lang]["Текст"];?>

     <form id="form1" method="post" action="/<?=$slang?>/search/">
      <input name="str" type="text" id="textfield" value="<?=TemplateTranslate("search", $slang)?>" onclick="javascript:if(this.value=='<?=TemplateTranslate("search", $slang)?>')this.value='';" />
      <input type="hidden" name="sub" value="1">
      <input type="submit" name="button" id="button" value=" " />
    </form>


     <? //последние новости
     $news = new ObjectsList(102, $lang, 3, true, false);
     foreach ($news->getList(1,2) as $publ)	:
     ?>
     <div class="news_cont">
          <p><a href="/<?=$slang?>/<?=$publ["an_oid"]?>/news/"><img src="<?=(isset($publ["Картинка"]))? "/_Upload/".$publ["Картинка"] : "/images/nofoto.jpg"?>" alt="" width="100" height="98" class="img_news" /></a><?=$publ["an_name"]?> <a href="/<?=$slang?>/<?=$publ["an_oid"]?>/news/"><img src="/images/<?=$slang?>/more.gif" alt="<?=TemplateTranslate("подробнее", $slang)?>" width="74" height="21" align="absmiddle" /></a></p>
        </div>
     <?endforeach;?>



   <?//текстовый блок
   $bl1 = new PlainObject(122, 1);
   echo $bl1->values[$lang]["Текст"];
   ?>


<?include("footer.php");?>
