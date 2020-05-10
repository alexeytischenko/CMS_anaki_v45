//доступ к элементам документа по id-шникам
function whichBro(varName)
{
	if (document.all){return document.all[varName];}
        else if (document.getElementById){return document.getElementById(varName);}
}
//доступ к элементам родительского документа по id-шникам
function whichBroOp(varName)
{
    if (window.opener.document.all){return window.opener.document.all[varName];}
        else if (window.opener.document.getElementById){return window.opener.document.getElementById(varName);}
}
//список ссылок
function editLink(lang, varName, id, inside, type, selected)
{
    editWindow = window.open('link.php?var=' + varName + '&lang=' + lang + "&id=" + id + "&inside=" + inside + "&type=" + type + "&selected=" + selected, "linkWindow", "width=700, height=600, status=yes,resizable=yes,scrollbars=yes");
    editWindow.focus();
}
//список пользователей
function editUser(lang, varName, selected)
{
    editUWindow = window.open('link_user.php?var=' + varName + '&lang=' + lang + "&selected=" + selected, "linkWindow", "width=700, height=600, status=yes,resizable=yes,scrollbars=yes");
    editUWindow.focus();
}
//список тегов
function editTags(lang, selected)
{
    editTWindow = window.open('link_tag.php?lang=' + lang + "&selected=" + selected, "linkWindow", "width=700, height=600, status=yes,resizable=yes,scrollbars=yes");
    editTWindow.focus();
}
//длобавить новый input в множественное поле
function addField (fld)	{

	if ( whichBro("Repeater" + fld).value == "")
	{
		whichBro("Repeater" + fld).value = "0";
	}

	cnt = whichBro("Repeater" + fld).value;

	var arr = new Array();
	for (i = 0; i < cnt; i++ )
	{
		arr[i] = whichBro('Repeater' + fld + '_' + i).value;
	}
	whichBro("mult" + fld).innerHTML +="<input type='text'  id='Repeater" + fld + "_" + cnt + "'  name='Repeater" + fld + "_" + cnt + "' class='dynvarchar'><br>";

	for (i = 0; i < cnt; i++ )
	{
		whichBro('Repeater' + fld + '_' + i).value = arr[i];
	}

	whichBro("Repeater" + fld).value ++;
}