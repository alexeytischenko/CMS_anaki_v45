<?
function TemplateTranslate($nm, $slang)	{	global $sysProperties;	if (!$sysProperties["useTemplateTranslation"])	return $nm;	global $translation;

  	foreach ($translation->text as	$text)	{    	if ($text["name"]==$nm)      		foreach ($text->children() as $tname=>$tval)	if ($tname == $slang)	return  $tval;
  	}    return $nm;
}



?>