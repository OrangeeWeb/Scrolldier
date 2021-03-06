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

$query = $db->prepare("UPDATE decks SET views=views+1 WHERE id=:id");
	$arr = array(
			'id' => $row['id']
		);
	
	$x->arrayBinderINT($query, $arr);
	$query->execute();


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
	  		2 => $scrollsCost,
	  		3 => $card['image'],
	  		4 => $data['data']['scrolls'][$i]['c'],
	  		5 => $card['name'],
	  		6 => $scrollType,
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
	<script src="<?php echo($main) ?>plugins/lightbox/js/jquery-1.11.0.min.js"></script>
</head>
<body>
	<div class="body" id="blog">
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
							<div class="left" style="margin-left: 5px; margin-top: 1px;"><?php echo($row['scrolls']) ?></div>
						</div>
					</div>
				
				
				<?php include("inc_/curve.php"); ?>
				<?php echo(addBigColoredCurve($row['id'])); ?>
					

					<?php for ($j = 0; $j < count($listOfScrolls); $j++) { ?>
						
				<div class="clearfix" id="ScrollsNr<?php echo($listOfScrolls[$j][3]); ?>">
					<div id="" class="deckScrollList mR " style="overflow: hidden;"> 
						 <span class="left">
							<span class="resource"><i class="icon-<?php echo($listOfScrolls[$j][6]) ?> small"></i><?php echo($listOfScrolls[$j][2]) ?></span>
						</span>
						
						<span class="left"><?php echo($listOfScrolls[$j][5]); ?></span>

						<span class="right">
							<a href="<?php echo($main) ?>resources/cardImages/<?php echo($listOfScrolls[$j][3]) ?>.png" data-title="<?php echo($listOfScrolls[$j][5]); ?>, x<?php echo($listOfScrolls[$j][4]); ?>" data-lightbox="Scrolls"><img class="listScroll" src="<?php echo($main) ?>resources/cardImages/<?php echo($listOfScrolls[$j][3]) ?>.png" alt="" /></a>
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

		
		
	<script>
	$(function() {

	
	
	$("[id*=ScrollsNr]").hover(function() {
		$(this).find("div").next("div").toggle();
	});
	
	
	$("#btn-Export-submit").click(function() {
		$("#export").toggle();
	});
	
	});
	
	</script>
</body>
</html>