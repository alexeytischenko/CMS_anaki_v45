<ul>
      	<?
      	$menuleft = new ObjectsMenu(1, $lang, array(1));
       	$menuList = $menuleft->getSiteMenu();

       if (isset($menuList[0]) && sizeof($menuList[0])>0)	{
       ?>
        <ul>
<?
	      	// print_R($menuList);
	        foreach($menuList[0] as $v):
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
<?	}?>



