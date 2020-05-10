<?
	#обновление объекта
	function PlainObject_updateObject($curObj)	{    	//ф-ция получает ссылку на объект
    	global $languages;
    	if ($curObj->objectType != 1 && $curObj->objectType != 3)	return;
/*
        $curObj->buildObject();

    	for ($l = 1; $l <= sizeof($languages); $l++)	{
    		//обновление карты сайта
        	CustomLibs::sitemap(81, $l, array(1,3));
    		//последние обновления
    		if ($curObj->objectType == 3 && $curObj->objProp[$l]->frontaccess == 1) Properties::saveProp($curObj->oid, $l, 0, 'last');
    		if ($curObj->objectType == 3 && $curObj->objProp[$l]->frontaccess == 0) Properties::deleteProp($l, 'last', $curObj->oid);
        }
*/
	}

    #создание объекта
	function PlainObject_createObject($curObj)	{
     	//ф-ция получает ссылку на объект
    	global $languages;
    	if ($curObj->objectType != 1 && $curObj->objectType != 3)	return;
 /*
        $curObj->buildObject();

    	for ($l = 1; $l <= sizeof($languages); $l++)	{    		//обновление карты сайта
        	CustomLibs::sitemap(81, $l, array(1,3));    		//последние обновления
    		if ($curObj->objectType == 3 && $curObj->objProp[$l]->frontaccess == 1) Properties::saveProp($curObj->oid, $l, 0, 'last');
    		if ($curObj->objectType == 3 && $curObj->objProp[$l]->frontaccess == 0) Properties::deleteProp($l, 'last', $curObj->oid);
        }
*/
	}

 	#удаление объекта
	function PlainObject_deleteObject($curObj)	{
     	//ф-ция получает ссылку на объект
    	global $languages;
    	if ($curObj->objectType != 1)	return;
/*
    	$curObj->buildObject();

        for ($l = 1; $l <= sizeof($languages); $l++)	{
			//обновление карты сайта
        	CustomLibs::sitemap(81, $l, array(1,3));
        	//последние обновления
    		if ($curObj->objectType == 3) Properties::deleteProp($l, 'last', $curObj->oid);
        }
*/
	}

?>