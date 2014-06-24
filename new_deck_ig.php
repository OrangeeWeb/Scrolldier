<?php 
include('admin/mysql/connect.php');
include('admin/mysql/function.php');
$x = new xClass();

session_start();
if (isset($_GET['logout'])) {
	$x->logout();
}
if (!isset($_SESSION['username'])) {
	header("location: ".$main."login.php");
}




//preg_replace("/[^0-9]/","",'604-619-5135');
//FIX http://i.imgur.com/VHQ1OTR.jpg
	if (isset($_POST['deckSubmit']) && !empty($_POST['deckSubmit'])) {
			if (!empty($_POST['link'])) {
					
					if (empty($_SESSION['username'])) {
						$_SESSION['username'] = $_POST['deck_author'];
					}
	
			
					if (isset($_POST['type_growth'])) {
						$growth = 1;
					} else {
						$growth = 0;
					}
					if (isset($_POST['type_order'])) {
						$order = 1;
					} else {
						$order = 0;
					}
					if (isset($_POST['type_energy'])) {
						$energy = 1;
					} else {
						$energy = 0;
					}
					if (isset($_POST['type_decay'])) {
						$decay = 1;
					} else {
						$decay = 0;
					}
					if (isset($_POST['type_wild'])) {
						$wild = 1;
					} else {
						$wild = 0;
					}
					
					if (isset($_POST['comp'])) {
						$comp = 1;
					} else {
						$comp = 0;
					}
					if (isset($_POST['isHidden'])) {
						$isHidden = 1;
					} else {
						$isHidden = 0;
					}
					
				//http://a.scrollsguide.com/deck/load?id=265 CHECK TO THIS LATER
				    $query = $db->prepare("INSERT INTO decks (deck_title, deck_author, growth, energy, tOrder, decay, wild, meta, scrolls, text, competative, JSON, isHidden) VALUES(:deck_title, :deck_author, :growth, :energy, :order, :decay, :wild, :meta, :scrolls, :text, :competative, :JSON, :isHidden)");
				
					$json = $_POST['link'];	
					$data = json_decode($json, TRUE);
					
					$duplicate = "";
					$phpArray = array(
						"msg" => "success",
						"data" => array(
							"scrolls" =>array_unique(array()),
							"name" => $data['deck'],
							"deleted" => 0,
							"resources" => array("growth")
						),
						"apiversion" => 1
						
					);
					$total = 0;
					for ($i = 0; $i < count($data['types']); $i++) {
							$count = array_count_values($data['types']);
							$toInsert = array(
							        "id" => $data['types'][$i],
							        "c" => $count[$data['types'][$i]]
							);
							array_push($phpArray['data']["scrolls"], $toInsert);
							$total++;
					}
					$phpArray['data']['scrolls'] = array_map("unserialize", array_unique(array_map("serialize", $phpArray['data']['scrolls'])));
					$phpArray['data']['scrolls'] = array_values($phpArray['data']['scrolls']);
					

					$data2 = json_encode($phpArray);
					
					if (empty($data['deck'])) {
						$data['deck'] = "Unknown";
					}
					
					$arr = array(
							'deck_title' => $data['deck'],
							'deck_author' => $_SESSION['username'],
							'growth' => $growth,
							'energy' => $energy,
							'order' => $order,
							'decay' => $decay,
							'wild' => $wild,
							'meta' => $_POST['meta'],
							'scrolls' => $total,
							'text' => $_POST['description'],
							'competative' => $comp,
							'JSON' => $data2,
							'isHidden' => $isHidden
							
						);

					$x->arrayBinder($query, $arr);
					
					if ($query->execute()) {
						$_GET['success'] = "Deck Submitted";
						header("location: ".$main."decks/");
					}

				
				
			} else {
				$_GET['info'] = "Enter a Deck Link";
			}
	}

//vars; title, scrolls, link, type_order, type_energy, type_growth, type_decay, type_wild, meta
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Scrolls - New Deck</title>
	<link rel="stylesheet" href="<?php echo($main) ?>css/style.css" />
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
	<link rel="icon" type="image/png" href="img/bunny.png">
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<script src="<?php echo($main) ?>jquery.js"></script>	 
	<script src="<?php echo($main) ?>plugins/ckeditor/ckeditor.js"></script>	
</head>
<body style="padding-bottom: 0px;">
	<?php include('inc_/menu.php'); ?>
	 
 	<div class="container">
 		<div class="div-3 div-margin">
 			<p>Don't post a deck that you found or have not made yourself. When you are posting it will show up in your name!</p>
 		</div>
 		<form method="post" class="div-marign" action="">			
			<div class="div-3 div-marign">
					<label for="deck_link">In-Game export text (JSON string)</label><br />
					<input type="text" class="textbox full" name="link" id="deck_link" value='<?php if (isset($_GET["json"])) {
						$JSONoutput = str_replace("'", "&#39;", $_GET["json"]);
						$JSONoutput = str_replace("\"", "&#34;", $JSONoutput);
						echo($JSONoutput);
					} ?>' placeholder=""/>
			</div>
			
			<div class="div-3 div-marign">
					<label for="deck_scrolls">What type of deck is this?</label><br />
					<div class="chooseBox clearfix">
						<div class="checkbox">
							<ul class="">
							  <li>
							      <input id="order_checkbox2" type="checkbox" checked="checked" name="type_order" value="">
							      <label class="checkbox" for="order_checkbox2"><i class="icon-order"></i></label> 
							      
							  </li>
							  <li>  
							      <input id="energy_checkbox2" type="checkbox" name="type_energy" value="">
							      <label class="checkbox" for="energy_checkbox2"><i class="icon-energy"></i></label> 
							     
							  </li>
							  <li>
							      <input id="growth_checkbox2" type="checkbox" name="type_growth" value="">
							      <label class="checkbox" for="growth_checkbox2"><i class="icon-growth"></i></label> 
							  </li>
							 <li class="">
							     <input id="decay_checkbox2" type="checkbox" name="type_decay" value="">
							     <label class="checkbox" class="" for="decay_checkbox2"><i class="icon-decay"></i></label> 
							 </li>
							 <li class="">
							     <input id="wild_checkbox2" type="checkbox" name="type_wild" value="">
							     <label class="checkbox" class="" for="wild_checkbox2"><i class="icon-wild"></i></label> 
							 </li>
							</ul>
						</div>
					</div>
			</div>
			
			<div class="div-3 div-marign">
				<label>What meta is this deck designed for</label><br />
				<label class="select">
				<select name="meta">
					<option selected value="0.124.0">0.124.0 (Latest, Main Server)</option>
					<option value="0.123.0">0.123.0 (Latest, Test Server)</option>
					<option value="0.122.0">0.122.0</option>
					<option value="0.121.0">0.121.0</option>
					<option value="0.119.1">0.119.1</option>
					<option value="0.117">0.117</option>
					<option value="0.112.2">0.112.2</option>
					<option value="0.110.5">0.110.5</option>
					<option value="0.105">0.105</option>
					<option value="0.103">0.103</option>
					<option value="0.97">0.97</option>
				</select>
				</label>
 			</div>
 			<div class="div-3">
 				<input type="checkbox" name="comp" id="comp" value="1" />
 				<label for="comp">Is this a competitive deck? 1600+ Rating (Master Caller)</label>
 			</div>
 			<div class="div-3">
 				<input type="checkbox" name="isHidden" id="isHidden" value="1" />
 				<label for="isHidden">Make deck hidden, so only you can see it (Direct link still works for everyone)</label>
 			</div>
 			<div class="div-4">
 				<label for="editor">Write a summary of your deck, how do you play it?</label>
 				<textarea name="description" class="ckeditor" id="editor"></textarea>
 			</div>
 			<div class="div-3 div-marign">	
 				<input type="hidden" name="author" value="<?php echo($_SESSION['username']) ?>" />
 				<input type="hidden" name="deckSubmit" value="deckSubmit" />
 				<input type="submit" name="submit" class="btn" value="Post deck" />
 			</div>
 		</form>
 		
 	</div>
 <?php include("inc_/footer.php"); ?>
</body>
</html>