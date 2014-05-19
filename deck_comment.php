<?php 
include('admin/mysql/connect.php');
include('admin/mysql/function.php');
$x = new xClass();
session_start();
if (isset($_GET['logout'])) {
	$x->logout();
}
if (isset($_POST['name']) && isset($_POST['submit']) && isset($_POST['comment'])) {
	
	$query = $db->prepare("INSERT INTO comment (byUser, comment, commentToID, headID, cWhere) VALUES (:name, :comment, :commentToID, :headID, 2)");
	$arr = array(
			'name' => $_POST['name'],
			'comment' => $_POST['comment'],
			'commentToID' => $_GET['d'],
			'headID' => $_POST['headID']
		);
		
	$x->arrayBinder($query, $arr);
	$query->execute();			
} 

if (isset($_POST['postID']) && !empty($_POST['postID'])) {
	$x->delComment($_POST['postID']);
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

if (!isset($_GET['s']) || empty($_GET['s'])) {
	$_GET['s'] = 1;
}

$query = $db->prepare("SELECT * FROM decks WHERE id=:id");
$arr = array(
		'id' => $_GET['d']
	);

$x->arrayBinder($query, $arr);
$query->execute();		
$row = $query->fetch(PDO::FETCH_ASSOC);
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
	<link href="<?php echo($main) ?>plugins/lightbox/css/lightbox.css" rel="stylesheet" />
	<script src="<?php echo($main) ?>plugins/lightbox/js/jquery-1.11.0.min.js"></script>
	<script src="<?php echo($main) ?>plugins/lightbox/js/lightbox.min.js"></script>
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
						<h1><a href="http://www.scrollsguide.com/deckbuilder/#<?php echo($row['link']) ?>" target="_blank"><?php echo($row['deck_title']) ?></a></h1>
						<small><?php echo($x->ago($row['time'])) ?> by <a href="<?php echo($main) ?>user/<?php echo($row['deck_author']) ?>"><?php echo($row['deck_author']) ?></a>, for scrolls version: <?php echo($row['meta']) ?>, with a Score of <?php echo($row['vote']) ?></small>
					</div>
					
					
					<?php if (isset($_SESSION['username'])) { ?>
							
						<div class="div-3 clearfix">
							<?php if ($x->hasVoted($_SESSION['username'], $row['id'])) { ?>
							
								<form method="post" action="" class="left">
									<input type="hidden" name="VoteUp" value="VoteUp" />
									<input type="hidden" name="deckID" value="<?php echo($row['id']) ?>" />
									<input type="submit" class="btn-modern" name="submit" value="Vote Up" />
								</form>
								
								<form method="post" action="" class="left">
									<input type="hidden" name="VoteDown" value="VoteDown" />
									<input type="hidden" name="deckID" value="<?php echo($row['id']) ?>" />
									<input type="submit" class="btn-modern" name="submit" value="Vote Down" />
								</form>
							
							<?php } ?>
						
						<?php if ($row['deck_author'] == $_SESSION['username']) { ?>
							<a href="<?php echo($main) ?>editdeck/<?php echo($row['id']) ?>" class="btn-modern btn-pagina left">edit</a>
						<?php } ?>
						</div>
					<?php } ?>
				</div>
				
			<div class="news_wall right">
				
					<div class="modern clearfix border-radius-bottom-none">
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
							<div class="left" style="margin-left: 5px; margin-top: 1px;"><?php echo($row['scrolls']) ?></div>
						</div>
					</div>
				
				
				<?php include("inc_/curve.php"); ?>
				<?php echo(addBigColoredCurve($row['id'])); ?>
				<?php 
				
				$json = $row['JSON'];
				$data = json_decode($json, TRUE);
				if ($data['msg'] == "success") { 
					
					$curve = array(
						1 => intval(0),
						2 => intval(0),
						3 => intval(0),
						4 => intval(0),
						5 => intval(0),
						6 => intval(0),
						7 => intval(0),
						8 => intval(0),
						9 => intval(0),
						10 => intval(0),
						11 => intval(0),
						12 => intval(0),
						13 => intval(0)
					);
					$listOfScrolls = array();
					
					for ($i = 0; $i < count($data['data']['scrolls']); $i++) {
					
						$query = $db->prepare("SELECT * FROM scrollsCard WHERE id=:id");
						$arr = array(
								'id' => $data['data']['scrolls'][$i]['id']
							);
						
						$x->arrayBinder($query, $arr);
						$query->execute();		
						$card = $query->fetch(PDO::FETCH_ASSOC);
					  
					  	$singelScroll = array(
					  		2 => $card['name'],
					  		3 => $card['image'],
					  		4 => $data['data']['scrolls'][$i]['c'],
					  		5 => $card['costorder'],
					  		6 => $card['costgrowth'],
					  		7 => $card['costdecay'],
					  		8 => $card['costenergy'],
					  		9 => $card['description'],
					  		10 => $card['passiverules_1'],
					  		11 => $card['passiverules_2'],
					  		12 => $card['passiverules_3'],
					  		13 => $card['types'],
					  		14 => $card['kind']
					  		
					  	);
					  
					  	array_push($listOfScrolls, $singelScroll);
					  
					 	if ($card['rarity'] == 0) {
					 		$scrollClass = "mR";
					 	}
					 	if ($card['rarity'] == 1) {
					 		$scrollClass = "mR";
					 	}
					 	if ($card['rarity'] == 2) {
					 		$scrollClass = "mR";
					 	}
					}
				} 
				function my_sort($a,$b)
				{
				if ($a==$b) return 0;
				   return ($a<$b)?-1:1;
				}
				
				
				usort($listOfScrolls, "my_sort");

				?>
					

					<?php for ($j = 0; $j < count($listOfScrolls); $j++) { ?>
						
				<div class="clearfix" id="ScrollsNr<?php echo($listOfScrolls[$j][3]); ?>">
					<div id="" class="deckScrollList <?php echo($scrollClass) ?> " style="overflow: hidden;"> 
						 <span class="left">
						 <?php if (!empty($listOfScrolls[$j][5])) { ?>
							<span class="resource"><i class="icon-order small"></i><?php echo($listOfScrolls[$j][5]) ?></span>
						<?php } ?>
						<?php if (!empty($listOfScrolls[$j][6])) { ?>
							<span class="resource"><i class="icon-growth small"></i><?php echo($listOfScrolls[$j][6]) ?></span>
						<?php } ?>
						<?php if (!empty($listOfScrolls[$j][7])) { ?>
							<span class="resource"><i class="icon-decay small"></i><?php echo($listOfScrolls[$j][7]) ?></span>
						<?php } ?>
						<?php if (!empty($listOfScrolls[$j][8])) { ?>
							<span class="resource"><i class="icon-energy small"></i><?php echo($listOfScrolls[$j][8]) ?></span>
						<?php } ?>
						</span>
						
						<span class="left"><?php echo($listOfScrolls[$j][2]); ?></span>

						<span class="right">
							<a href="<?php echo($main) ?>resources/cardImages/<?php echo($listOfScrolls[$j][3]) ?>.png" data-title="<?php echo($listOfScrolls[$j][2]); ?>, x<?php echo($listOfScrolls[$j][4]); ?>" data-lightbox="Scrolls"><img class="listScroll" src="<?php echo($main) ?>resources/cardImages/<?php echo($listOfScrolls[$j][3]) ?>.png" alt="" /></a>
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
				
				<div class="containerComment">	
				
				<?php
				$query = $db->prepare("SELECT * FROM comment WHERE commentToID=:id AND cWhere=2 ORDER BY TIME");
				$arr = array(
						'id' => $_GET['d']
					);
				$x->arrayBinder($query, $arr);	
					
				function makeClickableLinks($s) {
				  return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
				}
				
				
				$query->execute();		
				while ($comment = $query->fetch(PDO::FETCH_ASSOC)) {
						
				?>
				
					<div class="avatar scrolls">
						<img src="<?php echo($main) ?>resources/head_<?php echo($comment['headID']) ?>.png" alt="" />
					</div>
					<div class="commentPost scrolls">
						<h4 class="clearfix"><a class="left" href="<?php echo($main) ?>user/<?php echo($comment['byUser']) ?>"><?php echo(strip_tags($comment['byUser'])) ?></a>
						
						<?php $userGuild = $x->getGuild($comment['byUser']) ?>
						<?php if (!$x->hasGuild($comment['byUser'])) { ?>
							<div class="left" style="margin-left: 10px;"><img src="<?php echo($userGuild['badge_url']) ?>" height="16px" alt="" /></div>
						<?php } ?>
						
						<?php if (isset($_SESSION['username']) && $_SESSION['rank'] < 3) { ?>
						<small>
						
						<form method="post" class="right" action="">
							<input type="hidden" name="postID" value="<?php echo($comment['id']) ?>" />
							<input type="submit" class="delBtn" name="" value="Delete" />
						</form>
						<form method="post" class="right" action="">
							<input type="hidden" name="warningUser" value="<?php echo($comment['byUser']) ?>" />
							<input type="hidden" name="warningPost" value="<?php echo($comment['id']) ?>" />
							<input type="submit" class="warBtn" name="" value="Warning<?php if ($comment['Warning'] >= 1) {
								echo("(".$comment['Warning'].")");
							} ?>" />
						</form>
						</small>
						<?php } ?>
						</h4>
						<p><?php echo(makeClickableLinks(strip_tags($comment['comment']))) ?></p>
					</div>
					
					<?php } ?>
					
					
					
				</div>
				<?php if (isset($_SESSION['username'])) { ?>
				<div class="containerComment">
					<div class="avatar scrolls">
						<?php if (isset($_SESSION['username'])) { ?>
							<img src="<?php echo($main) ?>resources/head_<?php echo($_SESSION['headID']) ?>.png" alt="" />
						<?php } else { ?>
							<img src="<?php echo($main) ?>resources/head_195.png" alt="" />
						<?php } ?>
					</div>
					<div class="scrolls comment clearfix">
						<h4>Write a comment about this?</h4>
						<small>Comments: <?php echo($x->totalComments($_GET['s'])) ?></small>
						<form method="post" class="commentBox" action="">
						
							<?php if (isset($_SESSION['username'])) { ?>
								<input type="hidden" class="textbox full div-3" name="name" value="<?php echo($_SESSION['username']) ?>" />
								<input type="hidden" name="headID" value="<?php echo($_SESSION['headID']) ?>" />
							<?php } else { ?>
								<input type="text" class="textbox full div-3" name="name" placeholder="InGameName" value="" />
								<input type="hidden" name="headID" value="195" />
							<?php } ?>
						
							
							<textarea name="comment" class="textarea full" placeholder="Comment"></textarea><br />
							<div class="div-3">
							<input type="submit" class="btn" name="submit" value="Post" />
							</div>
						</form>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php include("inc_/footer.php"); ?>
	
	<script>
	$(function() {

	$("[id*=ScrollsNr]").click(function() {
		$(this).find("div").next("div").toggle();
	});
	
	});
	
	</script>
</body>
</html>