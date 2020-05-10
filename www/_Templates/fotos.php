<?

	$fotos = new ObjectGallery($showPage->oid);

	for ($i = 1; $i <= sizeof($fotos->fotos_array); $i++)	{

  		echo "<a href=\"/_Fotos/" . $showPage->oid . "/" . $fotos->fotos_array[$i-1]["an_filename"] . "\" rel='gal[1]'>";
  		echo "<img src=\"/_Fotos/" . $showPage->oid . "_prev/" . $fotos->fotos_array[$i-1]["an_filename"]."\" title=\"\" /></a>&nbsp;";
  		if (floor($i/3)==$i/3)	echo "<br>";
	}

?>