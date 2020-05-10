<?
//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true)	die();

reset($objectsTypes[$curObject->objectType]["fields"]);
while(list($key, $val) = each($objectsTypes[$curObject->objectType]["fields"]))	{
	echo "<div>";
	switch ($val["type"]) {

		case "TextPlain" :
			echo $val["name"] . "<br>";
			echo "<br><textarea name='Repeater" . $i . "_" . $key . "' id='Repeater" . $i . "_" . $key . "' style='width: 620px; height:250px;' class='mceNoEditor'>";
			 if (isset($curObject->values[$i][$val["name"]]))	echo htmlspecialchars(CustomLibs::nl2br_revert($curObject->values[$i][$val["name"]]));
			 echo "</textarea><br><br>";
			break;
		case "TextLine" :
			echo $val["name"] . "<br>";
			echo "<input type='text'  name='Repeater" . $i . "_" . $key . "' value='";
			if (isset($curObject->values[$i][$val["name"]]))	echo htmlspecialchars($curObject->values[$i][$val["name"]]);
			echo "' class='text_field large'><br><br>";
			break;
		case "TextMultLine" :
			echo $val["name"] . "<br>";
			$tempar = $curObject->values[$i][$val["name"]];
			if (isset($curObject->values[$i][$val["name"]]) && sizeof($tempar) > 0)	{
				$hidval = sizeof($tempar);
			}
			else	$hidval = "";
			echo "<input type='hidden' name='Repeater" . $i . "_" . $key . "' id='Repeater" . $i . "_" . $key . "' value='" . $hidval . "'><div id='mult" . $i . "_" . $key . "'>";
			for ($l = 0; $l < sizeof($tempar); $l++)	{
				echo "<input type='text' id='Repeater" . $i . "_" . $key . "_". $l ."' name='Repeater" . $i . "_" . $key . "_". $l ."' value='" .htmlspecialchars($tempar[$l]). "' class='text_field large'><br>";
			}
			echo "</div><a href=\"javascript:addField('" . $i . "_" . $key . "');void(0);\">добавить поле</a>";

			break;
		case "WYSIWYG" :
			echo $val["name"];
			echo "<br><textarea name='Repeater" . $i . "_" . $key . "' id='Repeater" . $i . "_" . $key . "' style='width: 620px; height:250px;'>";
			if (isset($curObject->values[$i][$val["name"]]))	echo htmlspecialchars($curObject->values[$i][$val["name"]]);
			echo "</textarea><br><br>";

			break;
		case "LoadFile" :
			echo $val["name"];
			if (isset($curObject->values[$i][$val["name"]]) && strlen($curObject->values[$i][$val["name"]]) > 0)	{
				echo " <a href='?oid=".$curObject->oid."&action=deletefile&filelang=".$i."&flid=" . $key . (($foid > 0)? "&foid=".$foid : "")."'>удалить</a><br>";
				$fdata = GetImageSize("../_Upload/" . $curObject->values[$i][$val["name"]]);
				if (($fdata[2] > 0) && ($fdata[2] < 4))
					echo "<img src='/_Upload/".(($objectsTypes[$curObject->objectType]["fields"][$key]["image"]["preview"]) ? "_prev/" : "").$curObject->values[$i][$val["name"]]."' width='70' height='70'>";
				else echo "файл: /_Upload/" . $curObject->values[$i][$val["name"]];
				echo "<input type=hidden name='Repeater" . $i . "_" . $key . "' value='".$curObject->values[$i][$val["name"]]."'> ";
			}
			else
				echo "<br><input type='file' name='Files" . $i . "_" . $key . "'>";
			break;
		case "IETable" :
			echo $val["name"];
			if (isset($curObject->values[$i][$val["name"]]) && sizeof($curObject->values[$i][$val["name"]]) > 0)	{
				echo " <a href='?oid=".$curObject->oid."&action=deleteIE&filelang=".$i."&flid=" . $key . "'>удалить</a><br>";
				//echo "файл загружен (<a href='?oid=".$curObject->oid."&sub=5&lang=".$i."&flid=" . $key . "'>выгрузить файл</a>)";
				echo "<input type=hidden name='Repeater" . $i . "_" . $key . "' value='".serialize($curObject->values[$i][$val["name"]])."'> ";
			}
			else
				echo "<br><input type='file' name='Files" . $i . "_" . $key . "'>";
			break;
		case "Link" :
            echo $val["name"];
            $prop = $curObject->getLinkProperties($key);
            if ($prop["multiple"])	{

				$multd = new Multidata((isset($curObject->values[$i][$val["name"]])) ? implode(",", $curObject->values[$i][$val["name"]]) : "");

			   	echo "<input type='hidden' id='Repeater" . $i . "_" . $key . "' name='Repeater" . $i . "_" . $key . "' value='" . $multd->row . "'>";
				echo " <a href=\"javascript:editLink(" . $i . ", '" . $key . "', " . $prop["target"] . ", '" . $prop["lookinside"] . "', " . $prop["tartype"] . ", whichBro('Repeater" . $i . "_" . $key . "').value);\">отредактировать список</a><br><br>";
	            echo "<div id=link" . $i . "_" . $key . ">";

	            $al = $multd->getObjectsList($i);
	            for ($g = 0; $g < sizeof($al); $g++)	{
	                if ($prop["tartype"] != "")
	                    echo "<img src='_Templates/default/_Images/obj_s" . $prop["tartype"] . ".gif'> ";
	                echo $al[$g]["an_name"] . "<br>";
	            }
	            echo "</div>";
            }
            else	{
            	echo "<br><select name='Repeater" . $i . "_" . $key . "'>";
            	echo "<option></option>";
                $list = new ObjectsMenu($prop["target"], 1, array($prop["tartype"]), 0, false, false, false);
				$ar = $list->getTree();
				$sel = new Multidata((isset($curObject->values[$i][$val["name"]])) ? implode(",", $curObject->values[$i][$val["name"]]) : "");
                //print_r($sel);
				for ($j = 0; $j < sizeof($ar); $j++)	{
				    echo "<option value='," . $ar[$j]["an_oid"] . ",'";
				    if ($sel->ifMatch($ar[$j]["an_oid"]))
				        echo " selected";
				    echo ">" . $ar[$j]["an_name"] . "</option>";
				}

            	echo "</select>";

            }
            echo "<br><br>";
			break;
		case "User" :
            echo $val["name"];
            $prop = $curObject->getUserProperties($key);
            if ($prop["multiple"])	{
				$multd = new Multidata((isset($curObject->values[$i][$val["name"]])) ? implode(",", $curObject->values[$i][$val["name"]]) : "");

				echo "<input type='hidden' id='Repeater" . $i . "_" . $key . "' name='Repeater" . $i . "_" . $key . "' value='" . $multd->row . "'>";
				echo " <a href=\"javascript:editUser(" . $i . ", '" . $key . "', whichBro('Repeater" . $i . "_" . $key . "').value);\">отредактировать список</a><br><br>";

		        echo "<div id=link" . $i . "_" . $key . ">";

		    	for ($g = 0; $g < sizeof($multd->ids_array); $g++)	{
		    		$tmp_u = new User($multd->ids_array[$g]);
					echo "<img src=_Templates/default/_Images/icon_admin.gif> " . $tmp_u->name . " " . $tmp_u->lastname . " <a href = '/_Admin/?page=user&action=useredit&uid=".$multd->ids_array[$g]."'>[".$tmp_u->email."]</a><br>";		    		}
	   			echo "</div>";
			} else {
            	echo "<br><select name='Repeater" . $i . "_" . $key . "'>";
            	echo "<option></option>";
                $ar = User::getUserList("an_name");
				$sel = new Multidata((isset($curObject->values[$i][$val["name"]])) ? implode(",", $curObject->values[$i][$val["name"]]) : "");

				for ($j = 0; $j < sizeof($ar); $j++)	{
				    echo "<option value='," . $ar[$j]["an_uid"] . ",'";
				    if ($sel->ifMatch($ar[$j]["an_uid"]))
				        echo " selected";
				    echo ">" . $ar[$j]["an_name"] . " " . $ar[$j]["an_lastname"] . " [".$ar[$j]["an_email"]."]</option>";
				}

            	echo "</select>";

			}
            echo "<br><br>";
			break;

		case "Choose" :
			echo $val["name"]. "<br>";
			echo "<input type='hidden' name='mult_" . $key . "' value='1'>";
			$prop = $curObject->getSubFieldsValues($key);
            $md = new Multidata((isset($curObject->values[$i][$val["name"]])) ? implode(",", $curObject->values[$i][$val["name"]]) : "");
            if (!$prop["multiple"])	{
				echo "<select name=\"Repeater" . $i . "_" . $key . "\">";
                echo "<option></option>";
    		}

    		reset($prop["options"]);
            while (list ($k, $v) = each($prop["options"]))	{
				if (!$prop["multiple"])
					echo "<option value=\"" . $k . "\"";
                else
					echo "<input type='checkbox' name=\"Repeater" . $i . "_" . $key . "_". $k ."\" value=\"1\"";
                if ($md->ifMatch($k))	{
					if (!$prop["multiple"])	echo " selected";
                    else	echo " checked";
				}
                echo ">" . $v . "<br>";

			}
			if (!$prop["multiple"])	echo "</select>";
            echo "<br><br>";
			break;
		case "Integer" :
			echo $val["name"] . "<br><input type='text' maxlength='19' class='text_field small' name='Repeater" . $i . "_" . $key . "' value='";
			if (isset($curObject->values[$i][$val["name"]]))	echo intval($curObject->values[$i][$val["name"]]);
			echo "'><br><br>";
			break;
		case "Float" :
			echo $val["name"] . "<br><input type='text' maxlength='19' class='text_field small' name='Repeater" . $i . "_" . $key . "' value='";
			if (isset($curObject->values[$i][$val["name"]]))	echo $curObject->values[$i][$val["name"]];
			echo "'><br><br>";
			break;
		case "Checkbox" :
			echo $val["name"] . "<br><input type='checkbox' name='Repeater" . $i . "_" . $key . "' " . (isset($curObject->values[$i][$val["name"]]) ? "checked" : "") . "><br><br>";
			break;
		case "Date" :
			?>
			<script type="text/javascript">
				$(function(){
					$('#pdate<?=$i?>').datepicker({
						inline: true,
						dateFormat: "yy-mm-dd"
					});
				});
			</script>
			<?
			echo $val["name"] . "<br><input type='text' name='Repeater" . $i . "_" . $key . "' value = '" . (isset($curObject->values[$i][$val["name"]]) ? date('Y-m-d',$curObject->values[$i][$val["name"]]) : "") . "' id=\"pdate" . $i . "\"><br><br>";
			break;
	}
	echo "</div>";
}

?>