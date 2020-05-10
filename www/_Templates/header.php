<a href="<?=(($lang==1)? "/" : "/".$slang."/home/")?>">
	<img src="/images/<?=$slang?>/logo.gif" alt="<?=TemplateTranslate("Название", $slang)?>" width="369" height="91" />
</a>
<a href="<?=(($showPage->url!="home")? "/rus/".$showPage->url."/" : "/")?>" class="lang_1<?=(($lang==1)? "a" : "")?>" title="Русский">
	<span>русский</span>
</a>
<a href="/eng/<?=$showPage->url?>/" class="lang_2<?=(($lang==2)? "a" : "")?>" title="English">
	<span>english</span>
</a>

		<? 	//меню верхнего уровня
			//ObjectsMenu(родитель, язык, массив типов, кол-во уровеней меню)
       	$menutop = new ObjectsMenu(1, $lang, array(1), 1);
       	$menuList = $menutop->getSiteMenu();
       	if (isset($menuList[0]) && sizeof($menuList[0]) > 0)	{
		?>
	     <p>
	       <?foreach($menuList[0] as $v):?>
	       <a href="/<?=$slang?>/<?=$v["an_url"]?>/"><?=$v["an_name"]?></a><br />
	       <?endforeach;?>
         </p>
        <?}?>


        <?  //меню верхнего уровня с вложениями
        	//ObjectsMenu(родитель, язык, массив типов, кол-во уровеней меню)
      	$menu = new ObjectsMenu(1, $lang, array(1), 2);
	   	$menuList = $menu->getSiteMenu();
       	if (isset($menuList[0]) && sizeof($menuList[0])>0)	{?>
        <ul>
			<?foreach($menuList[0] as $v):
			?>
			<li><a href="/<?=$slang?>/<?=$v["an_url"]?>/"<?=((ifactive($v["an_oid"])) ? " class=\"active\"" : "")?>><?=$v["an_name"]?></a>
			<?
				if (isset($menuList[$v["an_oid"]]) && sizeof($menuList[$v["an_oid"]]) > 0) {?>
			<ul>
			 	<?	foreach($menuList[$v["an_oid"]] as $v2):	?>
	           <li><a href="/<?=$slang?>/<?=$v2["an_url"]?>/"><?=$v2["an_name"]?></a></li>
	            <?	endforeach;?>
	        </ul>
	        <?	}  ?>
	        </li>
			<?endforeach;?>
	    </ul>
		<?}?>
