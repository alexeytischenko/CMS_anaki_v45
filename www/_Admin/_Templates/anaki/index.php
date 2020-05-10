<?
//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true)	die();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html>
	<head>
		<title>Структура сайта</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/<?=ADMIN_TEMPLATE_STYLE?>" type="text/css" rel="StyleSheet" />
		<script src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/jquery-ui-1.8.18.custom.min.js" type="text/javascript"></script>
		<link href="_Templates/<?=ADMIN_TEMPLATE?>/_Styles/jquery-ui-1.8.18.custom.css" type="text/css" rel="stylesheet" />
		<script language="JavaScript" src="_Templates/<?=ADMIN_TEMPLATE?>/_Scripts/admin.js" type="text/javascript"></script>

		<script type="text/javascript">
		$(document).ready(function() {
			<?if (isset($curObject) && $curObject instanceof PlainObject)	{?>
			//табы поля редактирования
		    $(function () {
		    	<? if ($curObject->oid == -1)	$tabid = "first";
		    	   else $tabid = "lang1";
		    	?>
			    var tabContainers = $('div.tabs > div'); // получаем массив контейнеров
			    tabContainers.hide().filter($('#<?=$tabid ?>')).show(); // прячем все, кроме первого
			    // далее обрабатывается клик по вкладке
			    $('div.tabs ul.tabNavigation a').click(function () {
			        tabContainers.hide(); // прячем все табы
			        tabContainers.filter(this.hash).show(); // показываем содержимое текущего
			        $('div.tabs ul.tabNavigation a').removeClass('selected'); // у всех убираем класс 'selected'
			        $(this).addClass('selected'); // текушей вкладке добавляем класс 'selected'
			        return false;
			    }).filter($('#tab_<?=$tabid ?>')).click();
			});
			<?}?>
		});
		</script>

		<script type="text/javascript" src="../_Libs/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
				// General options
				language  : "ru",
				mode : "textareas",
				editor_deselector : "mceNoEditor",
				theme : "advanced",
				apply_source_formatting : false,
				relative_urls : false,
				remove_script_host : true,
				document_base_url : "/",
		        verify_html : false,
		        cleanup : true,
		        submit_patch : false,
				plugins : "imagemanager,filemanager,style,layer,table,advhr,advimage,advlink,inlinepopups,media,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras,advlist",

				// Theme options
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,styleselect,|,bullist,numlist,pasteword,image",
				theme_advanced_buttons2 : "tablecontrols,link,unlink,anchor,removeformat,code,fullscreen",
				theme_advanced_buttons3 : "",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
		        extended_valid_elements :"a[class|name|href|target|title|onclick|rel],script[type|src],iframe[src|style|width|height|scrolling|marginwidth|marginheight|frameborder],img[style|class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],object[classid|codebase|width|height|align],param[name|value],embed[quality|type|pluginspage|width|height|src|align|allowFullScreen|scale]",

				// Example content CSS (should be your site CSS)
				content_css : "_Style/style.css",
				<?include("../_Style/editor_style.php");?>
				relative_urls : false,
				remove_script_host : true,
				document_base_url : "/"
			});
		</script>

	</head>
<body>

<?include("menu.php");?>


<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">

 <tr valign="top"><td width="30%">
  			 <fieldset class="list">
			 <div style="padding-left:5px;">
				 <div class="head">Создать объект:</div>
				 <?
				 #иконки создания новых объектов
				 reset($objectsTypes);
				 while(list($key,$val) = each($objectsTypes))	{
				 	echo "<span title='".$val["name"]."'><a href='?oid=-1&objType=".$key.(($foid > 0)? "&foid=".$foid : "")."&action=edit'><img src='_Templates/".ADMIN_TEMPLATE."/_Images/obj_" . $key . ".gif' border='0'></a></span> ";
				 }
				 ?>
				</div>
			 </fieldset>
 			 <fieldset class="list">
			 <table width="100%" cellpadding="0" cellspacing="0" border="0">
			 <?
			$openF = ($foid > 0) ? 	$foid : 0;
			$oMenu = new ObjectsMenu($openF, $lang, array(), 0, false, false, false, false, $user_info->useract["open_folders"]);
			$tree = $oMenu->getTree($pg, $sysProperties["innerObjectsCount"]);


			 ?>
			 <tr class="path" height="10"><td>
			 <?
			$openF = 0;
			if ($foid > 0)	{
			 	$openF = $foid;
				# путь наверх
				echo "<a href='/_Admin/'>...</a>";
				$parentObject = new PlainObject($foid);
				foreach($parentObject->getPath($lang) as $val)
					if ($val["an_oid"] != $foid)
						echo " / <a href='?foid=".$val["an_oid"]."'>" . ( (strlen($val["an_name"]) > 0) ?  htmlspecialchars($val["an_name"]) : "obj".$val["an_oid"])."</a>";
				echo " / " . ((strlen($parentObject->objProp[$lang]->name) > 0) ? $parentObject->objProp[$lang]->name : "obj".$parentObject->oid);
			}
			?></td>
			<td colspan="5" align="right">
				<?if ($foid != 0):?>
			    <?if(!isset($user_info->useract["user_bookmarks"]) || !in_array($foid, (array)$user_info->useract["user_bookmarks"])) {?>
          			<a href='?action=add&fopn=<?=$foid?>&foid=<?=$foid?>'><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/icon_add_b.gif" border="0" hspace="5" alt="добавить в закладки" title="добавить в закладки"></a>
			    <? } else {?>
                    <a href='?action=remove&fopn=<?=$foid?>&foid=<?=$foid?>'><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/icon_remove_b.gif" border="0" hspace="5" alt="удалить из закладок" title="удалить из закладок"></a>
			    <? }?>
			    <?endif;?>
			</td>
			</tr><?

			 for ($i = 0; $i < sizeof($tree); $i++)	{
			 ?>
			 	<tr class="row<?=(($tree[$i]["an_oid"]==$oid) ? "selected" : "")?>" height="25"><td style="padding-left:<?=($tree[$i]["an_level"]-1)*10?>px;" title="<?=htmlspecialchars($tree[$i]["an_name"])?>" width="100%">
				 <?if($tree[$i]["an_isfolder"]) {
			   		if($tree[$i]["an_inside"])	echo "<a href='?action=into&foid=" . $tree[$i]["an_oid"] . "'><img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_cfolder.gif' border='0' hspace='2'></a>";
			   		elseif(in_array($tree[$i]["an_oid"], (array)$user_info->useract["open_folders"])) echo "<a href='?action=close&fopn=" . $tree[$i]["an_oid"] .  ($foid > 0 ? "&foid=".$foid : "") ."'><img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_ofolder.gif' border=0 hspace='2'></a>";
			   		elseif($tree[$i]["an_childrencount"] > 0)	echo "<a href='?action=open&fopn=" . $tree[$i]["an_oid"] . ($foid > 0 ? "&foid=".$foid : "") . "'><img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_cfolder.gif' border=0 hspace='2'></a>";
			   		else	echo "<img src='1x1.gif' width=11 height=11 border=0 hspace='2'>";
			   	}
            	else	echo "<img src='1x1.gif' width=11 height=11 border=0 hspace='2'>"; ?>
				<img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/obj_<?=$tree[$i]["an_type"]?>.gif">
			 	<a href="?oid=<?=$tree[$i]["an_oid"]."&action=edit&pg=".$pg.(($foid > 0)? "&foid=".$foid : "")?>"><?=htmlspecialchars($tree[$i]["an_altname"])?></a> <span class="sortval"><?=$tree[$i]["an_sortvalue"] ?></span></td>
				<td>
				<?if ($tree[$i]["an_menu"]==0) echo "<img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_menu.gif' title='не отображается в меню'>"; ?>
				</td><td>
				<?if ($tree[$i]["an_restr"]==1) echo "<img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_zona.gif' title='закрытая зона' hspace=5>";?>
				</td><td>
				<?if ($tree[$i]["an_frontendaccess"]==0) echo "<img src='_Templates/".ADMIN_TEMPLATE."/_Images/icon_ceye.gif' title='запрещен к показу на сайте'>"; ?>
				</td><?
				if ($objectsTypes[$tree[$i]["an_type"]]["saveas"])	{?><td>
				<a href='?oid=<?=$tree[$i]["an_oid"]?>&pg=<?=$pg?>&action=saveas<?=(($foid > 0)? "&foid=".$foid : "") ?>'><img src='_Templates/<?=ADMIN_TEMPLATE?>/_Images/saveas.gif' title='сохранить как'></a>				
				</td><?}
				?><td><a href="JavaScript: if(confirm('Внимание. Действие приведёт к удалению выбранного объекта Данное действие невозможно отменить')) {document.location.href='?oid=<?=$tree[$i]["an_oid"]?>&pg=<?=$pg?>&action=delete<?=(($foid > 0)? "&foid=".$foid : "") ?>'}"><img src="_Templates/<?=ADMIN_TEMPLATE?>/_Images/icon_delete.gif" hspace="5" title="удалить объект" border="0"></a>
			 	</td></tr>
			<? }  ?>
			 <tr class="row" height="10"><td colspan="6"></td></tr>
			 </table>
<?
			# переключение страниц
            $pSw = $oMenu->getPageCount($sysProperties["innerObjectsCount"]);
            if ($pSw > 1)	{
				echo "<div class='path'>";
                for ($i = 0; $i < $pSw; $i++)	{
                	if ($i != 0) echo " | ";
                   if (($i + 1) != $pg)
                       echo "<a href=\"?pg=" . ($i + 1) . "&foid=".$foid."\">";
                   echo ($i + 1) . "</a>";
                }
				echo "</div>";
            }

			 ?>
			 </fieldset>
		 </td>
	<td width="70%">
	<?if (isset($curObject) && $curObject instanceof PlainObject)	{?>
		<?if(isset($user_info->userdata["debug"]) && $user_info->userdata["debug"]):?>
		<fieldset class="noticepanel"><?=$curObject->getNoticeMessage()?></fieldset>
		<?endif;?>
		<?
		$actMess = $curObject->getActionMessage();
		if (strlen($actMess) > 0) :?>
		<fieldset class="actionpanel"><?=$actMess?></fieldset>
		<?endif?>
		<?if ($curObject->errorStatus) :?>
		<fieldset class="errorpanel"><?=$curObject->getErrorMessage()?></fieldset>
		<?endif?>
	<?}?>
		<?if ($showEditfield) : ?>
		 <form method="post" action="/_Admin/" enctype="multipart/form-data" id="objectpage">
		 	<div class="tabs">
				<!-- Оглавление вкладок -->
			    <ul class="tabNavigation">
			        <li><a id="tab_first" href="#first">Свойства</a></li>
			    <?for ($i = 1; $i <= sizeof($languages); $i++)	{?>
			        <li><a id="tab_lang<?=$i?>" href="#lang<?=$i?>"><?=$languages[$i]["lname"]?></a></li>
				<?} ?>
			        <li><a id="tab_third" href="#third">Вложенные объекты</a></li>
			        <?if ($objectsTypes[$curObject->objectType]["gallery"])	{?><li><a class="" href="#photo">Фотографии</a></li><?} ?>
			    </ul>
			<!-- Это контейнеры содержимого -->
			    <div id="first">
			        <table cellspacing="5" cellpadding="10" border=0 class="main_text">
						<tr valign="top">
						<td>Путь<br /><span class="fieldvalues">
						<?if ($curObject->oid > 0)	{
							$path = $curObject->getPath($lang);
							while (list(,$val) = each($path))	echo htmlspecialchars($val["an_name"])." / ";
						}
						?></span>
						</td>
				        <td>Дата последнего обновления<br /><span class="fieldvalues"><?=date("Y-m-d H:i", $curObject->modifDate)?></span></td>
						<td>Автор последнего обновления<br /><span class="fieldvalues"><?
						$author = new Admin($curObject->modifUser);
						echo $author->name;
						?></span></td>
						<td>ID объекта<br /><span class="fieldvalues"><?=$curObject->oid?></span></td>
						</tr>
						<tr valign="top">
						<td>Тип объекта<br /><span class="fieldvalues">
						<input type=hidden name="objType" value="<?=$curObject->objectType?>">
						<?=$objectsTypes[$curObject->objectType]["name"]?></span></td>
						<td>
							<script type="text/javascript">
								$(function(){
									$('#objCreateDate').datepicker({
										inline: true,
										dateFormat: "yy-mm-dd"
									});
								});
							</script>
						Дата<br /><input type="text" name="objCreateDate" id="objCreateDate" value="<?=((isset($curObject->createDate)) ? date("Y-m-d H:i",$curObject->createDate) : date("Y-m-d H:i"))?>"></td>
						<td colspan="2">URL<br />
						<input type="text" name="objUrl" id="objUrl" MaxLength="20" value="<?=$curObject->url?>" <?=((!$objectsTypes[$curObject->objectType]["isurl"]) ? "disabled" : "")?>>
						</td></tr>
						<tr valign="top">
						<td colspan="4">Принадлежит объекту<br /><select name="objPar" style="width:400px;">
						<option value="0" <?=(($curObject->parent==0)? "selected" : "")?>></option>
						<?foreach(ObjectsMenu::getFoldersTree(0, $lang) as $val):
							echo "<option value='".$val["an_oid"]."' ".(($val["an_oid"]==$curObject->parent || ($curObject->oid == -1 && $foid == $val["an_oid"])) ? "selected" : "").">";
							for ($k = 0; $k < $val["an_margin"]*2;$k++)	echo "&nbsp;";
							echo htmlspecialchars($val["an_altname"])."</option>";
						endforeach;
						?>
						</select></td>
				        </tr>
					</table>
			    </div>

			        <?
				for ($i = 1; $i <= sizeof($languages); $i++)	{ ?>
				<div id="lang<?=$i?>">
                	<table cellpadding="5" cellspacing="5">
                    <tr valign="top">
                    <?if ($objectsTypes[$curObject->objectType]["isactive"])  {?><td nowrap>показывать на сайте<br /><input type="checkbox" name="frontAccess<?=$i?>" <? if($oid == -1 || $curObject->objProp[$i]->frontaccess) {echo "Checked";} ?>></td><?} else {?><input type=hidden name="frontAccess<?=$i?>" value=1><?}?>
                    <?if ($objectsTypes[$curObject->objectType]["ismenu"])  {?><td nowrap>отображать в меню<br /><input type="checkbox" name="menushow<?=$i?>" <?if($oid == -1 || $curObject->objProp[$i]->menu) {echo "Checked";} ?>></td><?} else {?><input type=hidden name="menushow<?=$i?>" value=1><?}?>
                    <td nowrap>закрытая зона<br /><input type="checkbox" name="restr<?=$i?>" <?if(isset($curObject->objProp[$i]->restr) && $curObject->objProp[$i]->restr) {echo "Checked";} ?> <?=((!$objectsTypes[$curObject->objectType]["iszona"]) ? "disabled" : "")?>></td>
					<td nowrap>Номер сортировки<br /><input name="objSortNumber<?=$i?>" type="text" value="<?=number_format($curObject->objProp[$i]->sortnumber, 2, '.', '')?>"></td>
                    </tr>
					</table>
					
					<?if(isset($objectsTypes[$curObject->objectType]["tags"]) && $objectsTypes[$curObject->objectType]["tags"])	{?>
					<table cellpadding="5" cellspacing="5">
						<tr><td colspan="2">Теги  <a href="javascript:editTags('<?=$i?>', whichBro('Tags<?=$i?>').value);">[отредактировать список]</a><br />
						<div id='tags_div<?=$i?>'>
					        <?
					        if (strlen($curObject->objProp[$i]->tags) > 0)	{
					        	$multd = new Multidata($curObject->objProp[$i]->tags);
						    	for ($g = 0; $g < sizeof($multd->ids_array); $g++)	{
						    		$tmp_u = new Tags($multd->ids_array[$g]);
									echo $tmp_u->name . ", ";
					    		}
				    		}
							?>	        
	   					</div>
						   <input type='hidden' id='Tags<?=$i?>' name='Tags<?=$i?>' value='<?=(isset($multd)) ? $multd->row : ""?>' />
						</td></tr>
					</table>
					<?}?>
					
					<table cellpadding="5" cellspacing="5">
					<?if ($objectsTypes[$curObject->objectType]["isname"]) :?>
					<tr><td>Название<br><input type=text name="name<?=$i?>" value="<?=htmlspecialchars($curObject->values[$i]["an_name"])?>" class="dynvarchar"><td></tr>
					<?endif;
					if ($objectsTypes[$curObject->objectType]["ismeta"]) :
                    ?>
                    <tr><td>Title страницы<br><input type=text name="title<?=$i?>" value="<?=htmlspecialchars($curObject->values[$i]["an_title"])?>" class="dynvarchar"><td></tr>
                    <tr><td>Заголовок страницы<br><input type=text name="header<?=$i?>" value="<?=htmlspecialchars($curObject->values[$i]["an_header"])?>" class="dynvarchar"><td></tr>
                    <tr><td>Meta keywords<br><input type=text name="keywords<?=$i?>" value="<?=htmlspecialchars($curObject->values[$i]["an_keywords"])?>" class="dynvarchar"><td></tr>
                    <tr><td>Meta description<br><input type=text name="description<?=$i?>" value="<?=htmlspecialchars($curObject->values[$i]["an_description"])?>" class="dynvarchar"><td></tr>
                    <?endif;?>
                    <tr><td>
					<?include("include_dynfields.php");?>
					</td></tr>
                    </table>
				</div>
				<?}?>

			    <div id="third">
			        <input type="checkbox" name="objIsFolder" id="objIsFolder" <?
					if($curObject->isFolder || ($objectsTypes[$curObject->objectType]["folder"] && $oid == -1)) {echo "Checked";} ?>>Содержит вложенные объекты?
					<div id="panelChildProp">
                    <? 	for ($i = 1; $i <= sizeof($languages); $i++)  	{?>
						<fieldset class="childprops">
						<legend><?=$languages[$i]["lname"]?></legend>
						<table cellSpacing="10" cellPadding="10">
                        <tr valign="top">
						<td>тип сортировки<br />
						<select name="objSortType<?=$i?>">
							<option value="sortnumber">по сортировочному номеру</option>
							<option value="date"<?if ($oid!=-1 && $curObject->objProp[$i]->sorttype=="date") {echo " selected";} ?>>по дате</option>
			                <option value="name"<?if ($oid!=-1 && $curObject->objProp[$i]->sorttype=="name") {echo " selected";} ?>>по алфавиту</option>
                        </select>
				        </td>
						<td><input type="checkbox" <?if ($oid!=-1 && $curObject->objProp[$i]->sortorder) {echo "checked";} ?> name="objSortOrder<?=$i?>">возрастающий порядок?</td>
				        </tr>
						</table>
						</fieldset>
						<?} ?>
						<input type="checkbox" name="inside" <? if($curObject->inside) echo "Checked";?>>Показывать список вложенных объектов в интерфейсе системы управления на отдельной странице?
			        </div>
                    <script type="text/javascript">
                    if ($('#objIsFolder').attr('checked'))	{$('#panelChildProp').show();}
                    else {$('#panelChildProp').hide();}
                    $(function() {
		                $("#objIsFolder").click(function(){
		                        $('#panelChildProp').toggle();
		                })
                	});
                    </script>
			    </div>
			    <?if ($objectsTypes[$curObject->objectType]["gallery"])	{?>
			    <div id="photo">
                	<?if ($curObject->oid > 0)	{?>
		<script type="text/javascript" src="/_Libs/SWFUpload/swfupload.js"></script>
		<script type="text/javascript" src="/_Libs/SWFUpload/swfupload.queue.js"></script>
		<script type="text/javascript" src="/_Libs/SWFUpload/fileprogress.js"></script>
		<script type="text/javascript" src="/_Libs/SWFUpload/handlers.js"></script>
		<script type="text/javascript">
				var swfu;
				window.onload = function() {
				//function	loadswfuploader() {
					var settings = {
						flash_url : "../_Libs/SWFUpload/Flash/swfupload.swf",
						upload_url: "upload.php?oid=<?
							$dir_to_upl = new PlainObject($oid);
							$dir_to_upl->buildObject();
							echo $dir_to_upl->oid;
							echo "&type=".$dir_to_upl->objectType;
						?>",
						post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},
						file_size_limit : "10 MB",
						file_types : "*.jpg; *.gif; *.png;",
						file_types_description : "images",
						file_upload_limit : 100,
						file_queue_limit : 0,
						custom_settings : {
							progressTarget : "fsUploadProgress",
							cancelButtonId : "btnCancel"
						},
						debug: false,

						button_width: "225",
						button_height: "29",
						button_placeholder_id: "spanButtonPlaceHolder",
						button_text: '<span class="theFont">Выбрать файлы для загрузки</span>',
						button_text_style: ".theFont {font-size:11px; font-family:Verdana,Tahoma; text-decoration:underline;}",
						button_text_left_padding: 12,
						button_text_top_padding: 3,

						// The event handler functions are defined in handlers.js
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : uploadSuccess,
						upload_complete_handler : uploadComplete,
						queue_complete_handler : queueComplete	// Queue plugin event
					};

					swfu = new SWFUpload(settings);
			     };
			</script>
						<div class="fieldset flash" id="fsUploadProgress" style="padding-left:10px;">
						<span class="legend"></span>
						</div><br>
						<div id="divStatus" style="padding-left:10px;">0 Файлов загружено</div>
						<table cellpadding="10" border="0"><tr><td>
						<span id="spanButtonPlaceHolder"></span>
						</td><td>
						<input id="btnCancel" type="button" value="Отменить все загрузки" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px;" />
						</td></tr></table>
					<script type="text/javascript">
					  //When the document is ready set up our sortable with it's inherant function(s)
					  	$(document).ready(function() {
					  		//построение списка после загрузки страницы
							getFotosList();
						    $("#foto-list").sortable({
							      handle : '.handle',
							      update : function () {
									  var order = $('#foto-list').sortable('serialize');
									  jQuery.get("process-sortable.php?oid=<?=$curObject->oid ?>&" + order);
							      }
						    });

						});

					function getFotosList() {
					 	var currentTime = new Date();
 						$("#foto-list").load("getfotos_list.php?oid=<?=$curObject->oid ?>&nocashe="+currentTime.getTime());
					 }
					function foto_del(id)	{
						jQuery.get("getfotos_delete.php?oid=<?=$curObject->oid ?>&id="+id+"&type=<?=$curObject->objectType?>");
						getFotosList();
					}
					</script>

					<?$fotolist = new ObjectGallery($curObject->oid); ?>
					<div>Обратная: <input type=radio name="fotolist_order" value="1" <?=(($fotolist->sortorder==1)? " checked" : "")?>>  Прямая: <input type=radio name="fotolist_order" value="0"<?=(($fotolist->sortorder==0)? " checked" : "") ?>></div>
					<ol id="foto-list">
					</ol>
					<?} else echo "<p>Сохраните изменения, прежде чем начать загрузку фотографий"; ?>
			    </div>
			    <?} ?>
			</div>
			<input type="hidden" name="oid" value="<?=$curObject->oid?>">
			<input type="hidden" name="foid" value="<?=$foid?>">
			<input type="hidden" name="pg" value="<?=$pg?>">
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="oldPar" value="<?=$curObject->parent?>">
			<div style="padding-left:5px;padding-top:10px;padding-bottom:10px;"><input type="submit" class="save_button" value="Сохранить"></div>
		 </form>
		 <?endif ?>
		 <?if($showSaveAsfield):?>
		 
		 <form method="post" action="/_Admin/" enctype="multipart/form-data" id="objectpage">
		 	<fieldset class="list"><table cellpadding="5" cellspacing="5">
		 		<h3>Сохранить как</h3>
		 	 <?for ($i = 1; $i <= sizeof($languages); $i++)	{ ?>
		 	 	
					<?if ($objectsTypes[$curObject->objectType]["isname"]) :?>
					<tr><td>Новое название (<?=$languages[$i]["lname"]?>)<br><input type=text name="name<?=$i?>" value="<?=htmlspecialchars($curObject->values[$i]["an_name"])?>" class="dynvarchar"><td></tr>
					<?endif;?>
		 	 
		 	 <?}?>
		 	 <tr valign="top">
						<td colspan="4">Новое местоположение<br /><select name="objPar" style="width:400px;">
						<option value="0" <?=(($curObject->parent==0)? "selected" : "")?>></option>
						<?foreach(ObjectsMenu::getFoldersTree(0, $lang) as $val):
							echo "<option value='".$val["an_oid"]."' ".(($val["an_oid"]==$curObject->parent || ($curObject->oid == -1 && $foid == $val["an_oid"])) ? "selected" : "").">";
							for ($k = 0; $k < $val["an_margin"]*2;$k++)	echo "&nbsp;";
							echo htmlspecialchars($val["an_altname"])."</option>";
						endforeach;
						?>
						</select></td>
				        </tr>
		 	 
		 	 </table>
		 	</fieldset>
			<input type="hidden" name="oid" value="<?=$curObject->oid?>">
			<input type="hidden" name="objType" value="<?=$curObject->objectType?>">
			<input type="hidden" name="foid" value="<?=$foid?>">
			<input type="hidden" name="pg" value="<?=$pg?>">
			<input type="hidden" name="action" value="saveasupdate">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="oldPar" value="<?=$curObject->parent?>">
			<div style="padding-left:5px;padding-top:10px;padding-bottom:10px;"><input type="submit" class="save_button" value="Сохранить"></div>
		 </form>		 
		 
		 <?endif ?>
	</td></tr>
	<tr><td colspan="2"><?include("footer.php"); ?></td></tr>
</table>
</body>
</html>
