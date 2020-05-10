<?
//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die("ошибка подключения шаблона");

$textSize = $sysProperties["searchAnons"];
$pageDiv = $sysProperties["searchPaging"];
$input_str = TemplateTranslate("search", $slang);
$str = trim($_REQUEST["str"]);

if (strlen($_REQUEST["str"]) > 0 && $_REQUEST["sub"] == 1)	{

	$input_str = $_REQUEST["str"];
	$showresult = true;

	$slist = array();
	for ($j = 0; $j < sizeof($searchSections); $j++)	{
		$l = new Search($searchSections[$j]["oid"], $lang, $searchSections[$j]["type"], $searchSections[$j]["field"], $_REQUEST["str"], false);
		$templist = $l->getList($searchSections[$j]["url"]);
		$slist = array_merge($slist, $templist);
	}
}	else  $showresult = false;

?>
	<form method="post" action="/<?=$slang?>/search/">
		<input name="str" type="text" class="search_2" style="width:200px; height:20px" value="<?=$input_str?>" onclick="javascript:if(this.value=='<?=$input_str?>')this.value='';">
		<input type="hidden" name="sub" value="1">
		<input type="submit" name="Submit" value=" " />
	</form>

	<?if ($showresult) :?>

		<?
		$startindex = ($pg - 1) * $pageDiv;
		$endindex = ((($pg * $pageDiv)>sizeof($slist)) ? sizeof($slist) : ($pg * $pageDiv));

		if(sizeof($slist) > 0)	{?>
		<p><?=($startindex + 1)?> - <?=$endindex?> <?=TemplateTranslate("of", $slang)?> <?=sizeof($slist)?> <?=TemplateTranslate("found", $slang)?>
		<?
		}
		else echo "<p>".TemplateTranslate("ничего не найдено", $slang);


		for($i = $startindex; $i < $endindex; $i++)	{

			//текст описания страницы
			$text = strip_tags($slist[$i]["searchtext"]);
			$tIndex = mb_strpos($text, $str);
			if (!$tIndex)	$tIndex = 0;

            if (($tIndex - $textSize/2) > 0)	$text = mb_substr($text , $tIndex - $textSize/2, $textSize);
            else	$text = mb_substr($text, 0, $textSize);

			$text = mb_ereg_replace($str, "<b>".$str."</b>", $text);

			//url родительского объекта
			if (strlen($slist[$i]["an_url"])==0)
				$slist[$i]["an_url"] = PlainObject::getParentUrl($slist[$i]["an_oid"], $lang);

			echo "<p>" . ($i + 1) . ". <a href='/".$slang.((strlen($slist[$i]["linkid"]) > 0) ? "/".$slist[$i]["linkid"] : "")."/".$slist[$i]["an_url"]."/' class='search2'>".$slist[$i]["an_name"]."</a>
			<br>" . $text . "...</p>
			<p style='font-size:11px'>
			URL: <a href='/".$slang.((strlen($slist[$i]["linkid"]) > 0) ? "/".$slist[$i]["linkid"] : "")."/".$slist[$i]["an_url"]."/'>".$_SERVER['HTTP_HOST']."/".$slang.((strlen($slist[$i]["linkid"]) > 0) ? "/".$slist[$i]["linkid"] : "")."/".$slist[$i]["an_url"]."/</a></p>";
		}
		?>
		<div class="pageSwitch">
		<?
		$pageCount = ceil(sizeof($slist) / $pageDiv);
		if ($pageCount > 1)	{
			for ($i = 1; $i <= $pageCount; $i++)	{
				if ($i > 1) echo " | ";
				if ($pg == $i) echo "<b>".$i."</b>";
				else echo "<a href='/?page=".$showPage->url."&sub=1&lang=".$lang."&pg=".$i."&str=".urlencode($_REQUEST["str"])."'>" . $i . "</a>";
			}
		}
		?>
		</div>
	<?endif?>
