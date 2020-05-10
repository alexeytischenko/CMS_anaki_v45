<?
//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die("ошибка подключения шаблона");
//проверка допустимого типа объекта
if ($id > 0 && !in_array($showInnerObj->objectType, array(3)))	die("недопустимый тип объекта");

if ($id > 0)	{
    //print_r($showInnerObj);
	echo "<span class='green_norm'>".date('d ', $showInnerObj->createDate).CustomLibs::rus_month(date('m', $showInnerObj->createDate)).date(' Y', $showInnerObj->createDate)."</span>";
	echo $showInnerObj->values[$lang]["Текст"];
	echo "<br><p><a href=\"/".$slang."/".$showPage->url."/\" class='small'>".TemplateTranslate("назад к списку новостей", $slang)."</a>";
?>


	<script type="text/javascript" src="http://yandex.st/share/share.js" charset="utf-8"></script>
	<div class="yashare-auto-init" data-yashareType="button" data-yashareQuickServices="moikrug,facebook,twitter,lj,vkontakte,odnoklassniki"></div>

	<iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2F<?=urlencode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])?>&amp;layout=standard&amp;show_faces=true&amp;width=500&amp;action=like&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:500px; height:80px;" allowTransparency="true"></iframe>

  	<div id="fb-root"></div>
  	<script src="http://connect.facebook.net/en_US/all.js#appId=APP_ID&amp;xfbml=1"></script>
  	<fb:comments href="http://<?=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']?>" num_posts="5" width="600"></fb:comments>

<?
}	else	{

	$pubList = new ObjectsList($showPage->oid, $lang, 3, true, false);
	foreach($pubList->getList($pg, 10) as $publ)	{?>
	  	<div class="news_in">
            <div class='news_in1'>    <a href="/<?=$slang?>/<?=$publ["an_oid"]?>/<?=$showPage->url?>/"><img src="<?=(isset($publ["Картинка"]))? "/_Upload/".$publ["Картинка"] : "/images/nofoto.jpg"?>" alt="" width="100"  class="pic_2" /></a>
			  </div>
			 <div class='news_in2'> <p><span class="green_norm"><?=date('d ', $publ["an_date"]).CustomLibs::rus_month(date('m', $publ["an_date"])).date(' Y', $publ["an_date"])?> </span><br /><?=$publ["an_name"]?> <a href="/<?=$slang?>/<?=$publ["an_oid"]?>/<?=$showPage->url?>/"><img src="/images/<?=$slang?>/more.gif" alt="<?=TemplateTranslate("подробнее", $slang)?>"  align="absmiddle" /></a></p></div>
           <div class="clear"></div> </div>

	<?
	}

	$pc = $pubList->getPageCount(10);
	if ($pc > 1)
		for ($i = 0; $i < $pc; $i++)	{
			if ($i > 0) echo " | ";
			if ($pg != ($i+1))	echo "<a href=\"/".$slang."/0/".($i + 1)."/".$showPage->url."/\">";
			echo ($i + 1)."</a>";
		}

}


?>