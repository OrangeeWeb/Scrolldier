<?php 
	include('admin/mysql/connect.php');
	include_once('admin/mysql/function.php');
	$x = new xClass();
	
	
	session_start();
	if (isset($_GET['logout'])) {
		$x->logout();
	}
	
	
	
	if (isset($_POST['submit']) && !empty($_POST['search_box'])) {
		$searchRederect = $main."decks/1/".$_POST['search_box']."/";
		
		if (isset($_POST['type_order'])) {
			$searchRederect .= "order/";
		}
		if (isset($_POST['type_energy'])) {
			$searchRederect .= "energy/";
		}
		if (isset($_POST['type_growth'])) {
			$searchRederect .= "growth/";
		}
		if (isset($_POST['type_decay'])) {
			$searchRederect .= "decay/";
		}
		if (isset($_POST['type_wild'])) {
			$searchRederect .= "wild/";
		}
		
		$searchRederect .= $_POST['searchType']."/";
		header("location: ".$searchRederect);
		
	} elseif (isset($_POST['submit']) && empty($_POST['search_box'])) {
		
		header("location: ".$main."decks/1/");
	}
		
		$pageSize = 30;
		
		if (!isset($_GET['page']) || empty($_GET['page'])) {
			$page = 1;
		} else {
			$page = intval($_GET['page']);
		}
		
		$stop = $pageSize;
		
		$start = ($page-1) * $pageSize;
		
		if ($start < 0) {
			$start = 0;
		}
		
		$ressource = "";
		$para = $_GET['para'];
		if (!empty($para)) {
		
			if(($pos = strpos($para, '/')) !== false)
			{
			   $para = substr($para, $pos + 1);
			}
			else
			{
			   $para = get_last_word($para);
			}
			$para = "/".$para;
		
		}
		
		if ($_GET['SearchType'] == "c") {
			$ressource .= " AND (";
			if (strpos($para, "growth")) {
				if ($ressource === " AND (") {
					$ressource .= " growth = 1";
				} else {
					$ressource .= " OR growth = 1";
				}
				
			}
			if (strpos($para, "order")) {
				if ($ressource === " AND (") {
					$ressource .= " tOrder = 1";
				} else {
					$ressource .= " OR tOrder = 1";
				}
			}
			if (strpos($para, "energy")) {
				if ($ressource === " AND (") {
					$ressource .= " energy = 1";
				} else {
					$ressource .= " OR energy = 1";
				}
				
			}
			if (strpos($para, "decay")) {
				if ($ressource === " AND (") {
					$ressource .= " decay = 1";
				} else {
					$ressource .= " OR decay = 1";
				}
			}
			if (strpos($para, "wild")) {
				if ($ressource === " AND (") {
					$ressource .= " wild = 1";
				} else {
					$ressource .= " OR wild = 1";
				}
			}
			$ressource .= ")";
		} else {
			if (strpos($para, "growth")) {
				$ressource .= " AND growth = 1";
			} else {
				$ressource .= " AND growth = 0";
			}
			if (strpos($para, "order")) {
				$ressource .= " AND tOrder = 1";
			} else {
				$ressource .= " AND tOrder = 0";
			}
			if (strpos($para, "energy")) {
				$ressource .= " AND energy = 1";
			} else {
				$ressource .= " AND energy = 0";
			}
			if (strpos($para, "decay")) {
				$ressource .= " AND decay = 1";
			} else {
				$ressource .= " AND decay = 0";
			}
			if (strpos($para, "wild")) {
				$ressource .= " AND wild = 1";
			} else {
				$ressource .= " AND wild = 0";
			}
		}
		
		
		if (isset($_GET['search']) && !empty($_GET['search']) && $_GET['search'] != "") {
		
			$query = $db->prepare("SELECT * FROM decks
								   WHERE isHidden = 0 AND (deck_title LIKE :search OR deck_author LIKE :search) ".$ressource."
								   ORDER BY meta DESC, vote DESC,
								   time DESC LIMIT :limitStart, :limitEnd");
			$arr = array(
					'search' => "%".str_replace('/','',$_GET['search'])."%"
				);
			$x->arrayBinder($query, $arr);
			
			$totalDecks = $db->prepare("SELECT * FROM decks
								   WHERE isHidden = 0 AND (deck_title LIKE :search OR deck_author LIKE :search) ".$ressource);
			$arr = array(
					'search' => "%".str_replace('/','',$_GET['search'])."%"
				);
			$x->arrayBinder($totalDecks, $arr);
			
			
		} else {
		$totalDecks = $db->prepare("SELECT * FROM decks WHERE isHidden = 0");
		
		$query = $db->prepare("SELECT * FROM decks WHERE isHidden = 0
							   ORDER BY  meta DESC, vote DESC,
							   time DESC LIMIT :limitStart, :limitEnd");
		}
		
		$arr = array(
				'limitStart' => $start,
				'limitEnd' => $stop,
			);
		$x->arrayBinderInt($query, $arr);
		
		
		$query->execute();
		
		
		
		
		$totalDecks->execute();
		$totalDecks = $totalDecks->rowCount();
 ?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Scrolldier.com - Decks</title>
	<link rel="icon" type="image/png" href="img/bunny.png">
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="<?php echo($main) ?>css/style.css" />
	<script src="<?php echo($main) ?>jquery.js"></script>
</head>
<body>

	<?php include('inc_/menu.php') ?>
		<div class="container">
			
			
			<div class="decks div-margin">
			
			
			
			<?php if (!empty($_GET['search'])) { ?>
				<p>The search for "<?php echo(str_replace('/','',$_GET['search'])) ?>" gave <?php echo($totalDecks) ?> results</p>
			<?php } else { ?>
				<p>Total decks: <?php echo($totalDecks); ?></p>
			<?php } ?>
			
				<div class="searchbox">
					<form method="post" action="" class="">
						
						<div class="chooseBox clearfix">
							<div class="checkbox">
								<form method="post" action="">
								<ul class="left">
									<?php if (isset($_SESSION['username'])) { ?>
									<li class="left">
										<a class="btn-modern btn-pagina btn-no-margin" href="<?php echo($main) ?>new/deck">New Deck</a><br />
									</li>
									<?php } ?>
									<?php
										
										
										$totalPages = intval($totalDecks / $pageSize)+1;
										
									if ($totalPages != 1) {
									 for ($i = 1; $i <= $totalPages; $i++) {
										
										
										if ($i != $_GET['page']) { ?>
										
											<li class="left"><a class="btn-modern btn-pagina btn-no-margin" href="<?php echo($main) ?>decks/<?php echo($i) ?>/<?php echo($_GET['para']) ?>"><?php echo($i) ?></a></li>
											
											
										<?php }
										
										if ($i == $_GET['page']) { ?>
										
											<li class="left"><a  class="btn-modern btn-pagina btn-no-margin active" href="<?php echo($main) ?>decks/<?php echo($i) ?>/<?php echo($_GET['para']) ?>"><?php echo($i) ?></a></li>
																
										<?php }					
									} 
									} ?>
								</ul>
								<ul class="right">
								 <li>
								 	<input type="search" name="search_box" id="searchTextBox" class="searchText" value="<?php echo($_GET['search']) ?>" placeholder="Search..."/>
								 </li>
								 <li>
								 	<input type="submit" name="submit" value="Search" class="btn-modern btn-pagina searchButton" />
								 </li>
								 
								</ul>
								
								<ul class="right typeIcons">
								  <li>
								      <input id="order_checkbox2" <?php if (strpos($para, "order")) {echo("checked");} if (empty($para)) {echo("checked");} ?> type="checkbox" name="type_order" value="">
								      <label class="checkbox" for="order_checkbox2"><i class="icon-order"></i></label> 
								      
								  </li>
								  <li>  
								      <input id="energy_checkbox2" <?php if (strpos($para, "energy")) {echo("checked");} if (empty($para)) {echo("checked");} ?> type="checkbox"  name="type_energy" value="">
								      <label class="checkbox" for="energy_checkbox2"><i class="icon-energy"></i></label> 
								     
								  </li>
								  <li>
								      <input id="growth_checkbox2" <?php if (strpos($para, "growth")) {echo("checked");}  if (empty($para)) {echo("checked");} ?> type="checkbox"  name="type_growth" value="">
								      <label class="checkbox" for="growth_checkbox2"><i class="icon-growth"></i></label> 
								  </li>
								 <li class="">
								     <input id="decay_checkbox2" <?php if (strpos($para, "decay")) {echo("checked");}  if (empty($para)) {echo("checked");} ?> type="checkbox" name="type_decay" value="">
								     <label class="checkbox" class="" for="decay_checkbox2"><i class="icon-decay"></i></label> 
								 </li>
								 <li class="">
								     <input id="wild_checkbox2" <?php if (strpos($para, "wild")) {echo("checked");} if (empty($para)) {echo("checked");} ?> type="checkbox" name="type_wild" value="">
								     <label class="checkbox" class="" for="wild_checkbox2"><i class="icon-wild"></i></label> 
								 </li>
								 </ul>
								 <ul class="right typeIcons">
									  <li>
									  	<!-- Specific Search -->
									  	<input type="radio" <?php if ($_GET['SearchType'] == "s") {echo("checked");} ?> id="typeS" name="searchType" value="s" />
									  	<label for="typeS">Exact Resource</label>
									  </li>
									  <li>
									  	<!-- Contains -->
									  	<input type="radio" <?php if ($_GET['SearchType'] == "c" || empty($_GET['SearchType'])) {echo("checked");} ?> id="typeC" name="searchType" value="c" />
									  	<label for="typeC">Contains Resource</label>
									  </li>
								 </ul>
								</form>
							</div>
						</div>
						
					</form>
				</div>
				<table>
					<tr class="modern">
						<td><i class="icon-star"></i> </td>
						<td width="300px">Deck title</td>
						<td width="120px"><i class="icon-growth"></i><i class="icon-decay"></i><i class="icon-order"></i><i class="icon-energy"></i></td>
						<td width="50px">Scrolls</td>
						<td>Version</td>
						<td>Author</td>	
						<td><i class="icon-comment"></i></td>
						<td>Age</td>
					</tr>
					<?php while ($deck = $query->fetch(PDO::FETCH_ASSOC)) { ?>
					
					<?php if ($deck['isHidden'] == 0) { ?>
					
					<tr>
						
						<td class="align-center"><?php echo($deck['vote']) ?></td>
						<td><a href="<?php echo($main) ?>deck/<?php echo($deck['id']) ?>"><?php echo($deck['deck_title']) ?></a></td>
						<td>
							<?php if ($deck['growth'] == 1) {
								echo('<i class="icon-growth"></i>');
							}
							
							if ($deck['decay'] == 1) {
								echo('<i class="icon-decay"></i>');
							}
							
							if ($deck['tOrder'] == 1) {
								echo('<i class="icon-order"></i>');
							}
							
							if ($deck['energy'] == 1) {
								echo('<i class="icon-energy"></i>');
							}
							
							if ($deck['wild'] == 1) {
								echo('<i class="icon-wild"></i>');
							}
							 ?>
						</td>
						<td><?php echo($deck['scrolls']) ?></td>
						<td><?php echo($deck['meta']) ?></td>
						<td><?php echo($deck['deck_author']) ?></td>	
						<td><?php echo($x->totalComments($deck['id'], 2)) ?></td>
						<td><?php echo($x->ago($deck['time'])) ?></td>
					</tr>
					<?php } else { ?>
						<?php if ($deck['deck_author'] == $_SESSION['username']) { ?>
							<tr class="isHidden" onclick="location.href='<?php echo($main) ?>deck/<?php echo($deck['id']) ?>'" style="cursor: pointer;">
								
								<td class="align-center"><?php echo($deck['vote']) ?></td>
								<td><?php echo($deck['deck_title']) ?></td>
								<td>
									<?php if ($deck['growth'] == 1) {
										echo('<i class="icon-growth"></i>');
									}
									
									if ($deck['decay'] == 1) {
										echo('<i class="icon-decay"></i>');
									}
									
									if ($deck['tOrder'] == 1) {
										echo('<i class="icon-order"></i>');
									}
									
									if ($deck['energy'] == 1) {
										echo('<i class="icon-energy"></i>');
									}
									
									if ($deck['wild'] == 1) {
										echo('<i class="icon-wild"></i>');
									}
									 ?>
								</td>
								<td><?php echo($deck['scrolls']) ?></td>
								<td><?php echo($deck['meta']) ?></td>
								<td>You</td>	
								<td><?php echo($x->totalComments($deck['id'], 2)) ?></td>
								<td><?php echo($x->ago($deck['time'])) ?></td>
							</tr>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				</table>
								
			</div>
			
			<div class="deckPagina">
				<?php
				$totalPages = intval($totalDecks / $pageSize)+1;
					
					if ($_GET['page'] != 1) { ?>
					
					<a  class="btn-modern btn-pagina btn-no-margin" href="<?php echo($main) ?>decks/<?php echo(intval($_GET['page']-1)) ?>/<?php echo($_GET['para']) ?>">Prev</a>
					
				<?php	}
				
				if ($totalPages != 1) {
				 for ($i = 1; $i <= $totalPages; $i++) {
					
					
					if ($i != $_GET['page']) { ?>
					
						<a  class="btn-modern btn-pagina btn-no-margin" href="<?php echo($main) ?>decks/<?php echo($i) ?>/<?php echo($_GET['para']) ?>"><?php echo($i) ?></a>
						
						
					<?php }
					
					if ($i == $_GET['page']) { ?>
					
						<a  class="btn-modern btn-pagina btn-no-margin active" href="<?php echo($main) ?>decks/<?php echo($i) ?>/<?php echo($_GET['para']) ?>"><?php echo($i) ?></a>
											
					<?php }					
				} 
				} 
				
				if ($_GET['page'] != $totalPages) { ?>
					
					<a  class="btn-modern btn-pagina btn-no-margin" href="<?php echo($main) ?>decks/<?php echo(intval($_GET['page']+1)) ?>/<?php echo($_GET['para']) ?>">Next</a>
					
				<?php	}
				
				?>
			</div>
			
		</div>
		
	<?php include("inc_/footer.php"); ?>
</body>
</html>