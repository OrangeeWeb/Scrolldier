<?php 
include('admin/mysql/connect.php');
include('admin/mysql/function.php');
include('admin/mysql/deck.php');
$x = new xClass();
$deckData = new deck();
session_start();
if (isset($_GET['logout'])) {
	$x->logout();
}

$query = $db->prepare("SELECT * FROM decks WHERE id=:id");
$arr = array(
		'id' => $_GET['d']
	);

$x->arrayBinder($query, $arr);
$query->execute();		
$row = $query->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['name']) && isset($_POST['submit']) && isset($_POST['comment'])) {
	
	$query = $db->prepare("INSERT INTO comment (byUser, comment, commentToID, headID, cWhere) VALUES (:name, :comment, :commentToID, :headID, 2)");
	$arr = array(
			'name' => $_POST['name'],
			'comment' => $_POST['comment'],
			'commentToID' => $row['id'],
			'headID' => $_POST['headID']
		);
		
	$x->arrayBinder($query, $arr);
	
	if ($query->execute()) {
		if ($row['deck_author'] != $_SESSION['username']) {
			$x->setNotificationDeck($row['deck_author'], $_POST['name'], $row['id']);
		}
		
		$hasSent = array();
		array_push($hasSent, strtolower($row['deck_author']));
		array_push($hasSent, strtolower($_SESSION['username']));
		
		
		$replyQuery = $db->prepare("SELECT byUser FROM comment WHERE commentToID=:id AND cWhere=2");
		$reply_arr = array(
				'id' => $row['id']
			);
		$x->arrayBinder($replyQuery, $reply_arr);	
		
		
		if ($replyQuery->execute()) {
			while ($reply = $replyQuery->fetch(PDO::FETCH_ASSOC)) {
			
				if (!in_array(strtolower($reply['byUser']), $hasSent)) {
					array_push($hasSent, strtolower($reply['byUser']));
					$x->setNotificationReply($reply['byUser'], $_POST['name'], $main."deck/".$row['id'], "deck");
				}
			}
		}
		
		
	}		
} 

if (isset($_POST['postID']) && !empty($_POST['postID'])) {
	$x->delComment($_POST['postID'], $_SESSION['username']);
}

if (isset($_POST['warningUser']) && !empty($_POST['warningUser'])) {
	$x->warnUser($_POST['warningUser']);
	$x->warnPost($_POST['warningPost']);
	
}

if (isset($_POST['VoteUp']) && !empty($_POST['VoteUp'])) {
	$x->deckVote($_POST['deckID'], true, $_SESSION['username']);
}
if (isset($_POST['VoteDown']) && !empty($_POST['VoteDown'])) {
	$x->deckVote($_POST['deckID'], false, $_SESSION['username']);
}

if (isset($_POST['favoriteBtn']) && !empty($_POST['deckID'])) {
	$deckData->addFavoriteDeck($_SESSION['username'], $_POST['deckID']);
}

if (!isset($_GET['s']) || empty($_GET['s'])) {
	$_GET['s'] = 1;
}




if (isset($_POST['submitDelete'])) {
	
	
	$query = $db->prepare("DELETE FROM decks where id = :id AND deck_author = :ign");
	$arr = array(
			'id' => $row['id']
		);
	
	$x->arrayBinderINT($query, $arr);
	$arr = array(
			'ign' => $_SESSION['username']
		);
	
	$x->arrayBinder($query, $arr);
	
	if ($query->execute()) {
		header("location: ".$main."my/decks");
	}
	
		
}

$listOfScrolls = array();			
$json = $row['JSON'];
$data = json_decode($json, TRUE);
if ($data['msg'] == "success") { 
	
	
	
	for ($i = 0; $i < count($data['data']['scrolls']); $i++) {
	
		$query = $db->prepare("SELECT * FROM scrollsCard WHERE id=:id");
		$arr = array(
				'id' => $data['data']['scrolls'][$i]['id']
			);
		
		$x->arrayBinder($query, $arr);
		$query->execute();		
		$card = $query->fetch(PDO::FETCH_ASSOC);
	  
	  
	  	$scrollsCost = 0;
	  	$scrollType = "";
	  	
	  	if (!empty($card['costorder'])) {
	  		
	  		$scrollsCost = $card['costorder'];
	  		$scrollType = "order";
	  		
	  	} elseif (!empty($card['costgrowth'])) {
	  	
	  		$scrollsCost = $card['costgrowth'];	
	  		$scrollType = "growth";
	  		
	  	} elseif (!empty($card['costenergy'])) {
	  	
	  		$scrollsCost = $card['costenergy'];
	  		$scrollType = "energy";
	  	
	  	}elseif (!empty($card['costdecay'])) {
	  	
	  		$scrollsCost = $card['costdecay'];
	  		$scrollType = "decay";
	  		
	  	}
	  
	  	$singelScroll = array(
	  		6 => $scrollType,
	  		2 => $scrollsCost,
	  		5 => $card['name'],
	  		3 => $card['image'],
	  		4 => $data['data']['scrolls'][$i]['c'],
	  		
	  		
	  		7 => 0,
	  		8 => 0,
	  		9 => $card['description'],
	  		10 => $card['passiverules_1'],
	  		11 => $card['passiverules_2'],
	  		12 => $card['passiverules_3'],
	  		13 => $card['types'],
	  		14 => $card['kind'],
	  		15 => $card['id']
	  		
	  	);
	  
	  	array_push($listOfScrolls, $singelScroll);
	  
	}
} 
function my_sort($a,$b) {
if ($a==$b) return 0;
   return ($a<$b)?-1:1;
}
				
				
usort($listOfScrolls, "my_sort");

		
//Export to plain text	
$planeTextExport = "";
$total = 0;
for ($ex = 0; $ex < count($listOfScrolls); $ex++){

	$cost = $listOfScrolls[$ex][4];
	$total = $total + $cost;
	$planeTextExport .= $cost."x ".$listOfScrolls[$ex][5]."\n";
	$cost = 0;
}
$planeTextExport .= "\nTotal: ".$total;

//export to in-game JSON

$deckTitle = preg_replace('/(\'|&#0*39;|")/', '', $row['deck_title']);

$JSONExport = '{"deck":"'.$deckTitle.'","author":"'.$row['deck_author'].'","types":[';

for ($ex2 = 0; $ex2 < count($listOfScrolls); $ex2++){
	for ($i = 0; $i < $listOfScrolls[$ex2][4]; $i++) {
		$JSONExport .= $listOfScrolls[$ex2][15].",";
	}
}

$JSONExport = trim($JSONExport,",");
$JSONExport .= "]}";

$dataArray = $deckData->getDeckDetails($row['id']);

$deckType = $dataArray['faction'][0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo($row['deck_title']) ?> - Deck - Scrolldier.com</title>
	<link rel="icon" type="image/png" href="<?php echo($main) ?>img/bunny.png">
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="<?php echo($main) ?>css/style.css" />
	<link rel="stylesheet" href="<?php echo($main) ?>css/library.css" />
	<script src="<?=$main?>jquery.js"></script>
</head>
<body>
	<?php include('inc_/menu.php') ?>
	<div class="body" id="blog">
		
		<div class="container">
				<div class="container clearfix">
					
					<div class="left">
						<i class="icon-deck"></i>	
					</div>
					
					<div class="left">
						<h1 class="left"><a href="<?php echo($main) ?>deckbuilder/<?php echo($row['id']) ?>"><?php echo($row['deck_title']) ?></a>
						
						</h1>
						
						
						<small class="left clear"><?php echo($x->ago($row['time'])) ?> ago by <a href="<?php echo($main) ?>user/<?php echo($row['deck_author']) ?>"><?php echo($row['deck_author']) ?></a>, for scrolls version: <?php echo($row['meta']) ?>, with a Score of <?php echo($row['vote']) ?></small>
						<br />
						<?php if ($row['meta'] == $deckData->getLatestTestServerVersion()) {
							echo("<small class='color-warning left clear'>This deck is for the current Test server version</small>");
						} elseif ($row['meta'] != $deckData->getLatestMainServerVersion()) {
							echo("<small class='color-red left clear'>This deck is outdated</small>");
						} ?>
					</div>
					<div class="right">
					<?php if (isset($_SESSION['username'])) { ?>
						
						
							<?php if ($x->hasVoted($_SESSION['username'], $row['id'])) { ?>
							
								<form method="post" action="" class="left">
									<input type="hidden" name="VoteUp" value="VoteUp" />
									<input type="hidden" name="deckID" value="<?php echo($row['id']) ?>" />
									<input type="submit" class="btn-modern btn-pagina btn-no-margin" name="submit" value="Vote Up" />
								</form>
								
								<form method="post" action="" class="left">
									<input type="hidden" name="VoteDown" value="VoteDown" />
									<input type="hidden" name="deckID" value="<?php echo($row['id']) ?>" />
									<input type="submit" class="btn-modern btn-pagina btn-no-margin" name="submit" value="Vote Down" />
								</form>
							<?php } ?>
							<?php if ($x->isFavDeck($_SESSION['username'], $row['id'])) { ?>
							<form method="post" action="" class="left">
								<input type="hidden" name="voteFav" value="voteFav" />
								<input type="hidden" name="deckID" value="<?php echo($row['id']) ?>" />
								<button type="sumbit" name="favoriteBtn" class="btn-modern btn-pagina btn-no-margin">Favorite</button>
							</form>
							<?php } ?>
							<?php if ($row['deck_author'] == $_SESSION['username']) { ?>
							
							<a href="<?php echo($main) ?>editdeck/<?php echo($row['id']) ?>" class="btn-modern btn-pagina btn-no-margin left">Edit</a>
							
							<form method="post" action="" class="left">
								<button type="submit" class="btn-modern btn-pagina btn-no-margin" name="submitDelete">Delete</button>
							</form>
							
							<?php } ?>
					<?php } ?>
					</div>
				</div>
			
			<div class="news_wall right">
				<div class="clearfix">
					<button class="btn-modern btn-pagina btn-no-margin left" id="btn-Export-submit">Export</button>
					<a class="btn-modern btn-pagina btn-no-margin left" href="<?php echo($main."u/makeDeckImage.php?d=".$row['id']) ?>">Deck as image (WIP)</a>
						<div class="modern left clearfix export" id="export">
							<textarea class="exportBox" rows="10" readonly><?php echo($planeTextExport) ?></textarea>
							<input type="text" class="exportBox" readonly name="" value='<?php echo($JSONExport) ?>' />
						</div>
					</div>
					<div class="modern clearfix">
						  <div class="left">
							<?php if ($row['growth'] == 1) {
								echo('<i class="icon-growth"></i>');
							}
							
							if ($row['decay'] == 1) {
								echo('<i class="icon-decay"></i>');
							}
							
							if ($row['tOrder'] == 1) {
								echo('<i class="icon-order"></i>');
							}
							
							if ($row['energy'] == 1) {
								echo('<i class="icon-energy"></i>');
							}
							
							if ($row['wild'] == 1) {
								echo('<i class="icon-wild"></i>');
							}
							 ?>	
						</div>
						<div class="right">
							<div class="left"><i class="icon-scrolls"></i></div>
							<div class="left" style="margin-left: 5px; margin-top: 1px;">
							<?php if ($row['scrolls'] < 50) {
								echo("<span class='color-red'>".$row['scrolls']."</span>");
							} else {
								echo($row['scrolls']);
							} ?>
							</div>
						</div>
					</div>
				
				
				<?php include("inc_/curve.php"); ?>
				<?php echo(addBigColoredCurve($row['id'])); ?>
					

					<?php for ($j = 0; $j < count($listOfScrolls); $j++) { ?>
						
				<div class="clearfix" id="ScrollsNr<?php echo($listOfScrolls[$j][3]); ?>" data-id="<?php echo($listOfScrolls[$j][15]); ?>">
					<div id="" class="deckScrollList mR " style="overflow: hidden;"> 
						 <span class="left">
							<span class="resource"><i class="icon-<?php echo($listOfScrolls[$j][6]) ?> small"></i><?php echo($listOfScrolls[$j][2]) ?></span>
						</span>
						
						<span class="left"><?php echo($listOfScrolls[$j][5]); ?></span>

						<span class="right">
							<img class="listScroll" src="<?php echo($main) ?>resources/cardImages/<?php echo($listOfScrolls[$j][3]) ?>.png" alt="" />
						</span>
						
						<span class="right" style="margin-right: 20px;">x<?php echo($listOfScrolls[$j][4]); ?></span>
					</div>
					<div class="deckScrollsInfo hidden">
						<?php if (!empty($listOfScrolls[$j][13])) {
							echo("<p>".$listOfScrolls[$j][14].": ".$listOfScrolls[$j][13]."</p>");
						} ?>
					
						<?php if (!empty($listOfScrolls[$j][10])) {
							echo("<p>* ".$listOfScrolls[$j][10]."</p>");
						} ?>
						<?php if (!empty($listOfScrolls[$j][11])) {
							echo("<p>* ".$listOfScrolls[$j][11]."</p>");
						} ?>
						<?php if (!empty($listOfScrolls[$j][12])) {
							echo("<p>* ".$listOfScrolls[$j][12]."</p>");
						} ?>
						<p><?php echo($listOfScrolls[$j][9]); ?></p>
					</div>
					</div>
					<?php } ?>
 			</div>
				<div class="news left">
				<div class="clearfix div-margin">
						<p><?php echo($row['text']) ?></p>
				</div>
				
				<?php include("inc_/comment.php"); ?>
				
				<div class="containerComment">	
				
				<?php
				$query = $db->prepare("SELECT * FROM comment WHERE commentToID=:id AND cWhere=2 ORDER BY TIME DESC");
				$arr = array(
						'id' => $_GET['d']
					);
				$x->arrayBinder($query, $arr);	
					
				
				
				$query->execute();		
				while ($comment = $query->fetch(PDO::FETCH_ASSOC)) {
						
				?>
				
					<div class="avatar scrolls">
						<img src="<?php echo($main) ?>resources/head_<?php echo($comment['headID']) ?>.png" alt="" />
					</div>
					
					<?php $userGuild = $x->getGuild($comment['byUser']) ?>
					<div class="commentPost scrolls">
						<h4 class="clearfix">
						<?php if (!$x->hasGuild($comment['byUser'])) { ?>
							<div class="left" style="margin-right: 10px;">
									<img src="<?php echo($userGuild['badge_url']) ?>" height="22px" alt="" />
							</div>
						<?php } ?>
						<a class="left" href="<?php echo($main) ?>user/<?php echo(strip_tags($comment['byUser'])) ?>">
							<?php echo(strip_tags($comment['byUser'])) ?>
						</a>
						
						
						
						
						<?php if (isset($_SESSION['username']) && $_SESSION['rank'] < 3  || $_SESSION['username'] == $comment['byUser']) { ?>
						<small>
						
						<form method="post" class="right" action="">
							<input type="hidden" name="postID" value="<?php echo($comment['id']) ?>" />
							<button type="submit" class="btn-modern btn-no-margin" style="padding: 0px;"><i class="icon-trash" style="margin: 3px 3px 1px 3px;"></i></button>
						</form>
						<!--<form method="post" class="right" action="">
							<input type="hidden" name="warningUser" value="<?php echo($comment['byUser']) ?>" />
							<input type="hidden" name="warningPost" value="<?php echo($comment['id']) ?>" />
							<input type="submit" class="warBtn" name="" value="Warning<?php if ($comment['Warning'] >= 1) {
								echo("(".$comment['Warning'].")");
							} ?>" />
							
						</form>-->
						</small>
						<?php } ?>
						
						<?php $thisRank = $x->getUserRank($comment['byUser']); ?>
						<?php $thisUser = $comment['byUser']; ?>
						<?php include("inc_/icon_comment.php") ?>
						</h4>
						<div class="comment-text">
							<p><?php echo($x->makeClickableLinks(strip_tags($comment['comment']))) ?></p>
						</div>
					</div>
					
					<?php } ?>
					
					
					
				</div>
			</div>
			
					<div class="div-4">
					<div class="span-9 div-center">
						<?php 
							$total_progress = $dataArray['CREATURE'] + $dataArray['STRUCTURE'] + $dataArray['SPELL'] + $dataArray['ENCHANTMENT'];
							
							$creatureProgess = round($dataArray['CREATURE'] / $total_progress * 100, 1);
							$structureProgess =  round($dataArray['STRUCTURE'] / $total_progress * 100, 1);
							$spellProgess =  round($dataArray['SPELL'] / $total_progress * 100, 1);
							$enchantProgess =  round($dataArray['ENCHANTMENT'] / $total_progress * 100, 1);
							
							 ?>
			
						<table  style="width: 100%;" class="align-center">
							<tr>
								<?php if ($creatureProgess != 0) { ?>
								<td><i class="icon-round color-green"></i> <?php echo($creatureProgess) ?>% Creatures (<?php echo($dataArray['CREATURE']) ?>)</td>
								<?php } ?>
								<?php if ($structureProgess != 0) { ?>
								<td><i class="icon-round color-orange"></i> <?php echo($structureProgess) ?>% Structures (<?php echo($dataArray['STRUCTURE']) ?>)</td>
								<?php } ?>
							
								<?php if ($spellProgess != 0) { ?>
								<td><i class="icon-round color-red"></i> <?php echo($spellProgess) ?>% Spells (<?php echo($dataArray['SPELL']) ?>)</td>
								<?php } ?>
							
								<?php if ($enchantProgess != 0) { ?>
								<td><i class="icon-round color-blue"></i> <?php echo($enchantProgess) ?>% Enchantments (<?php echo($dataArray['ENCHANTMENT']) ?>)</td>
								<?php } ?>
							</tr>
						</table>
						
						<div class="progressbar">
							<div class="bar color-green" style="width: <?php echo($creatureProgess) ?>%;"></div>
							<div class="bar color-orange" style="width: <?php echo($structureProgess) ?>%;"></div>
							<div class="bar color-red" style="width: <?php echo($spellProgess) ?>%;"></div>
							<div class="bar color-blue" style="width: <?php echo($enchantProgess) ?>%;"></div>
						</div>
					</div>
					<div class="span-9 div-center">
						<?php 
							$total_progress = $dataArray['CREATURE'] + $dataArray['STRUCTURE'] + $dataArray['SPELL'] + $dataArray['ENCHANTMENT'];
							
							
							$creatureProgess =  round($dataArray[0]['energy'] / $total_progress * 100, 1);
							$structureProgess =  round($dataArray[0]['growth'] / $total_progress * 100, 1);
							$spellProgess =  round($dataArray[0]['order'] / $total_progress * 100, 1);
							$enchantProgess =  round($dataArray[0]['decay'] / $total_progress * 100, 1);
							
							 ?>
			
						<table  style="width: 100%; margin-top: 10px;" class="align-center">
							<tr>
								<?php if ($creatureProgess != 0) { ?>
								<td><i class="icon-round color-energy"></i> <?php echo($creatureProgess) ?>% Energy (<?php echo($dataArray[0]['energy']) ?>)</td>
								
								<?php } ?>
								
								<?php if ($structureProgess != 0) { ?>
								<td><i class="icon-round color-growth"></i> <?php echo($structureProgess) ?>% Growth (<?php echo($dataArray[0]['growth']) ?>)</td>
							
								<?php } ?>
								
								<?php if ($spellProgess != 0) { ?>
								<td><i class="icon-round color-order"></i> <?php echo($spellProgess) ?>% Order (<?php echo($dataArray[0]['order']) ?>)</td>
							
								<?php } ?>
								
								<?php if ($enchantProgess != 0) { ?>
								<td><i class="icon-round color-decay"></i> <?php echo($enchantProgess) ?>% Decay (<?php echo($dataArray[0]['decay']) ?>)</td>
								
								<?php } ?>
							</tr>
						</table>
						
						<div class="progressbar" style="margin-bottom: 10px;">
							<div class="bar color-energy" style="width: <?php echo($creatureProgess) ?>%;"></div>
							<div class="bar color-growth" style="width: <?php echo($structureProgess) ?>%;"></div>
							<div class="bar color-order" style="width: <?php echo($spellProgess) ?>%;"></div>
							<div class="bar color-decay" style="width: <?php echo($enchantProgess) ?>%;"></div>
						</div>
					</div>
			
		</div>

		</div>
	</div>
	<div class="overlay"></div>
	
	<div class="outer-library"></div>
	<?php include("inc_/footer.php"); ?>
	<script>
	$(function() {

		$("[id*=ScrollsNr]").hover(function() {
			$(this).find("div").next("div").toggle();
		});
		
		
		$("#btn-Export-submit").click(function() {
			$("#export").toggle();
		});
		
		$(document).on("click", "div[id^='ScrollsNr'], div[class*='image-holder']", function() {
			var id = $(this).attr("data-id");
			$.ajax({
			  type: "POST",
			  url: "<?php echo($main) ?>inc_/ajax/scrollLibrary.php",
			  data: { scroll: id}
			  
			}).done(function(data) {
				$(".outer-library").show();
				$(".overlay").show();
			    $(".outer-library").html(data);
			 });
		});
		
		$(document).on("click", "#closeDeck", function() {
			var id = $(this).attr("data-id");
			$.ajax({
			  type: "POST",
			  url: "<?php echo($main) ?>inc_/ajax/scrollLibrary.php",
			  data: { scroll: id}
			  
			}).done(function(data) {
				$(".outer-library").show();
				$(".overlay").show();
			    $(".outer-library").html(data);
			 });
		});
		
		$(document).on("click", "#show-all-decks", function() {
			var id = $(this).attr("data-id");
			$.ajax({
			  type: "POST",
			  url: "<?php echo($main) ?>inc_/ajax/relatedDeck.php",
			  data: { scroll: id}
			  
			}).done(function(data) {
				$(".outer-library").show();
				$(".overlay").show();
			    $(".outer-library").html(data);
			 });
		});
		
		$(document).on("click", "#close", function() {
			$(".outer-library").hide();
			$(".overlay").hide();
		});
		
		
		$(".outer-library").click(function(e){
			 if(e.target != this) return
			 $(".outer-library").hide();
			 $(".overlay").hide();
		});
		
		$(".overlay").click(function() {
			$(".outer-library").hide();
			$(".overlay").hide();
		});
	});
	
	</script>
</body>
</html>