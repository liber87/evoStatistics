<?php 
	include(MODX_BASE_PATH.'assets/modules/evostatistics/evostatistics.class.php');
	$es = new evostatistics($modx);	
	$vars = $es->getVars();	
	$did = $modx->documentObject['id'];
	$data = $es->getData($vars['period'], '', $did);
	$data = array_merge($data, $vars);
?>
<script src="media/script/jquery/jquery.min.js"></script>
<script src="../assets/modules/evostatistics/js/chart.bundle.min.js"></script>

<canvas id="counter" style="width: 100%;"></canvas>
<script>	
	jQuery(document).ready(function() {
		<?php 
			$dg = $es->drawGraph($data);	
			echo $dg;
		?>
		$('#counter').createChart();
	});
</script>