<?php
include('Stats.php');
$set = array(181.55,182.14,180.88,180.72,180.37,181.71,181.27,186.35,188.39,188.53);
$stats = new Stats($set);
$time = mktime(12, 0, 0, date('n'), date('j')-10, date('Y'))
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Stats</title>
		<script type='text/javascript' src='https://www.google.com/jsapi'></script>
		<script type='text/javascript'>
			google.load('visualization', '1', {packages:['table']});
			google.setOnLoadCallback(drawTable);
			function drawTable() {
				var data = new google.visualization.DataTable();
				//data.addColumn('number', 'Row');
				data.addColumn('date', 'Date');
				data.addColumn('number', 'Number');
				data.addRows([
					<?php
					foreach($set as $i=>$x) {
						$time = mktime(12, 0, 0, date('n'), date('j')-(9-$i), date('Y'));
						$m = date('m',$time)-1; //JS month index starts at 0
						$d = date('d',$time);
						$y = date('Y',$time);
						$sDate = date('Y-m-d',$time);
						echo "[new Date($y, $m, $d),$x],";
					}
					?>
				]);
				var table = new google.visualization.Table(document.getElementById('table_div'));
				table.draw(data, {showRowNumber: true});
			}
		</script>
	</head>
	<body>
	<div class="container">
		<h1>Stats</h1>
		<div id="table_div"></div>
		<h2>Average</h2>
		<p><?php echo $stats->getAverage(); ?></p>
		<h2>Slope</h2>
		<p>
			<?php 
			$slope = $stats->getSlope(3);
			echo "m=$slope[m], b=$slope[b]";
			?>
		</p>
		<h2>Standard Deviation</h2>

		<p>
			<?php 
			$stdev = $stats->getStandardDeviation();
			echo $stdev;
			?>
		</p>
		<h2>Black Scholes</h2>
		<p>
			<?php
				$expiration = date('Y-m-d',mktime(12, 0, 0, date('n'), date('j')+180, date('Y')));
				echo "trade = $set[9]<br>";
				echo "strike = 120<br>";
				echo "riskFreeInterest = 3.47<br>";
				echo "expiration = $expiration<br>";
				echo "volatility = $stdev<br>";
				$bs = $stats->blackScholes($set[9],120,3.47,$expiration,$stdev);
				echo "call = $bs[call]<br>";
				echo "put = $bs[put]<br>";
			?>
		</p>
	</div><!-- /.container -->
	</body>
</html>

