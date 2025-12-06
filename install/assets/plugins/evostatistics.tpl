//<?php
/**
 * EvoStatistics
 *
 * <strong>0.1</strong> Счетчики посещений
 *
 * @category    plugin
 * @internal    @events OnWebPageInit,OnManagerWelcomeHome,OnManagerMenuPrerender
 * @internal    @modx_category Content
 * @internal    @properties 
 * @internal    @disabled 0
 * @internal    @installset base
 */
switch ($modx->event->name) {
	case 'OnWebPageInit': {
		
		if (!function_exists('bot_detected')){
			function bot_detected() {
				return (
				isset($_SERVER['HTTP_USER_AGENT'])
				&& preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
				);
			}
		}
		if (!function_exists('get_ip')){
			function get_ip(){
				$value = '';
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$value = $_SERVER['HTTP_CLIENT_IP'];
					} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$value = $_SERVER['HTTP_X_FORWARDED_FOR'];
					} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
					$value = $_SERVER['REMOTE_ADDR'];
				}
				return $value;
			}
		}
		
		$id = $modx->documentIdentifier;	
		if (!$id) return;
		$date = date('d-m-Y');		
		$ip = $modx->db->escape(get_ip());
		$table = $modx->getFullTableName('site_counter');
		
		if (bot_detected()) {
			$bc = $modx->db->query('Select `id`,`bots` from ' . $table . ' where did=' . $id . ' and `date`="' . $date . '"');
			$row = $modx->db->getRow($bc);
			if ($row['id']){
				$bots = $row['bots'] + 1;
				$modx->db->query('Update '.$table.' set `bots`="'.$bots.'" where id='.$row['id']);
				} else{
				$modx->db->insert(['did'=>$id,'date'=>$date,'bots'=>1], $table);		
			}			
			return;	
		}
		
		
		$new = $modx->db->insert(['did'=>$id,'date'=>$date,'ip'=>$ip], $modx->getFullTableName('site_visits'));
		if (!$new) {
			$views = $modx->db->getValue('Select `views` from ' . $table . ' where did=' . $id . ' and `date`="' . $date . '"');
			$views = $views + 1;
			$modx->db->query('update ' . $table . ' set `views`="' . $views . '" where did=' . $id . ' and `date`="' . $date . '"');
			return;
		}
		
		$res = $modx->db->query('Select `id`,`visits`,`views` from ' . $table . ' where did='.$id.' and `date`="'.$date.'"');
		$row = $modx->db->getRow($res);
		if ($row['id']){
			$visits = $row['visits'] + 1;
			$views = $row['views'] +1;			
			$modx->db->query('update ' . $table . ' set `visits`="'.$visits.'", `views`="'.$views.'" where did='.$id.' and `date`="'.$date.'"');
			} else {	
			$modx->db->insert(['did'=>$id,'date'=>$date,'visits'=>1,'views'=>1], $table);
		}		
		break;
	}
	case 'OnManagerMenuPrerender': {
		if(!isset($params['module_id'])) {
			$moduleid = $modx->db->getValue($modx->db->select('id', $modx->getFullTablename('site_modules'), "`modulecode` LIKE '%EvoStatistics%'"));
			} else {
			$moduleid = $params['module_id'];
		}
		$params['menu'] = array_merge($params['menu'], [
		'widgets' => ['counter', 'main', '<i class="fa fa-bar-chart"></i> Счетчик', 'index.php?a=112&id=' . $moduleid, 'Счетчик', 'return false;', 'exec_module', 'main', 0, 90, '']]);
		
		$modx->event->output(serialize($params['menu']));
		break;
	}
	case 'OnManagerWelcomeHome': {
		include(MODX_BASE_PATH.'assets/modules/evostatistics/evostatistics.class.php');
		$es = new evostatistics($modx);	
		$vars = $es->getVars();
		if ($vars['widget'] == 1) {
			$data = $es->getData($vars['period']);
			$data = array_merge($data, $vars);			
			$widgets['evoStatistics'] = [
				'menuindex' =>'-2',
				'id' => 'evoStatistics',
				'cols' => 'col-sm-12',
				'icon' => 'fa-copy',
				'title' => 'Счетчик посещений',
				'body' => '<div class="card-body">			
					<script src="media/script/jquery/jquery.min.js"></script>
					<script src="../assets/modules/evostatistics/js/chart.bundle.min.js"></script>

					<canvas id="counter" style="width: 100%;"></canvas>
					<script>	
						jQuery(document).ready(function() {
							' . $es->drawGraph($data) . '
							$("#counter").createChart();
						});
					</script>
				</div>',
				'hide'=>'0'
			];
			$modx->Event->output(serialize($widgets));
		}
	break;
	}
}


