<ul class="badge-icon"><?php

 if ($thisRank == 5) {
	echo("<li><i class='icon-ig-mojang' style='margin-bottom:-5px;'></i><ul><li class='modern'>Mojangster</li></ul></li>");
 }
 
 if ($thisRank == 2) {
 	echo("<li><i class='icon-mod' style='margin-bottom:-5px;'></i><ul><li class='modern'>Moderator of scrolldier</li></ul></li>");
 } 
 
 $igModList = array(
 	"SeeMeScrollin",
 	"kbasten",
 	"acidjib",
 	"spiffydrew",
 	"blinky",
 	"ival1ce",
 	"sysp"
 	);
 
 for ($i = 0; $i < count($igModList); $i++) {
 
 	if (strtolower($igModList[$i]) == strtolower($thisUser)) {
 		echo("<li><i class='icon-ig-mod' style='margin-bottom:-5px;'></i><ul><li class='modern'>Moderator in-game</li></ul></li>");
 	}
 	
 }
 
 

 if ($thisRank == 1) {
 	echo("<li><i class='icon-admin' style='margin-bottom:-5px;'></i><ul><li class='modern'>Admin of scrolldier</li></ul></li>");
 } 
 
 if ($x->hasDonated($thisUser)) {
 	echo("<li><i class='icon-donor' style='margin-bottom:-5px;'></i><ul><li class='modern'>Has donated to Scrolldier.com</li></ul></li>");
 } 
?></ul>