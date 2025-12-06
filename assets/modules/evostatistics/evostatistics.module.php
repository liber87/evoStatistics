<?php 
	
	include(MODX_BASE_PATH.'assets/modules/evostatistics/evostatistics.class.php');
	$es = new evostatistics($modx);		
	if (isset($_POST['send_settings'])) $es->setProps($_POST);
	$vars = $es->getVars();	
		
?>
<html>
	<head>
		<title>Evo Statistics</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />		
		<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$modx->config['modx_charset'];?>" />
		<link rel="stylesheet" type="text/css" href="<?=$modx->config['site_manager_url'];?>media/style/default/css/styles.min.css" />
		<link rel="stylesheet" type="text/css" href="../assets/modules/evostatistics/css/air-datepicker.css" />
		<style>
			.text-primary,td{font-size: 0.8125rem !important; cursor:ponter;}
			.graph{background:green; margin:10px 0; height:10px; width:100%;}
			.center{text-align:center;vertical-align:middle !important;}
			.pag span{color: #212529;    background-color: #dae0e5; margin: 0.2em; border-color: #d3d9df;     padding: 0.46153846em 1em; top: 9px; position: relative;}
			.fancySearchRow input{padding: 0.25rem;}
			.sectionBody .displayparams, .sectionBody .permissiongroup{    border-spacing: 0px;}
			.grid{border:none;}
			#displayparams td{vertical-align:middle;}
		</style>
		<script src="../assets/modules/evostatistics/js/jquery.min.js"></script>
		<script src="media/script/tabpane.js" type="text/javascript"></script>
		<script src="../assets/modules/evostatistics/js/chart.bundle.min.js"></script>
		<script src="../assets/modules/evostatistics/js/fancyTable.min.js"></script>
		<script src="../assets/modules/evostatistics/js/air-datepicker.js"></script>
	</head>
	<body class="sectionBody">
		<div id="actions" style="display:none;">
			<div class="btn-group">				
				<a id="Button5" class="btn btn-success" href="javascript:;" onclick="$('#settingsForm').submit();">
					<i class="fa fa-floppy-o"></i><span>Сохранить</span>
				</a>				
			</div>
		</div>
		<h1><i class="fa fa-th"></i>Счетчик посещений</h1>
		
		<div class="tab-pane " id="counterPane">
			<script type="text/javascript">
				tpResources = new WebFXTabPane(document.getElementById('counterPane'));
			</script>
						
			<div class="tab-page" id="tabGraphics">
				<h2 class="tab"><i class="fa fa-bar-chart"></i> График</h2>
				
				<script type="text/javascript">tpResources.addTabPage(document.getElementById('tabGraphics'));</script>
				<div class="tab-body">
					<form method="post" action="">					
						<input type="text" class="form-control range_date" name="graph_range" value="<?php echo $_POST['graph_range'];?>" style="width:75%;" placeholder="Выберите диапазон"><button class="btn btn-success" style="width:25%;">Посмотреть</button>
					</form>
					<?php	
					
					
					
					if ($_POST['graph_range']){
						$dd = explode(' - ',$_POST['graph_range']);
						$d1 = explode('.',$dd[0]);
						
						$d2 = explode('.',$dd[1]);
						
						$date1 = new DateTime($d1[2].'-'.$d1[1].'-'.$d1[0]);
						$date2 = new DateTime($d2[2].'-'.$d2[1].'-'.$d2[0]);
						
						$interval = $date1->diff($date2);
						$period = $interval->days;
						
						$begin = $d2[0] . '-' . $d2[1]. '-' .$d2[2];
						
					}
					$data = $es->getData($period, $begin);
					$data = array_merge($data, $vars);
					?>
					<script>	
						jQuery(document).ready(function() {
							<?php 
								$dg = $es->drawGraph($data);	
								echo $dg;
							?>
				
							new AirDatepicker('.range_date', {
								range: true,
								multipleDatesSeparator: ' - ',
								minDate: '" <?php echo $min_date;?> "',
								maxDate: '" <?php echo $max_date;?> "'
							});
							new AirDatepicker('.table_choiсe',{
								minDate: '" <?php echo $min_date;?> "',
								maxDate: '" <?php echo $max_date;?> "'
							});
							
							
							change = false;
							
							$('#settingsForm input').keyup(function(){
								change = true;
							});
							$('#settingsForm select').change(function(){
								change = true;
							});
							
							$('#viewsTable').fancyTable({
								pagination: true,
								globalSearch:true,
								perPage: 10
							});
							$('#counter').createChart();
							setInterval(function(){
								if ($('#tabSettings').is(':visible')){
									if (change) $('#actions').show();
								} else {
									$('#actions').hide();								
								}
							}, 1000);
					});
					</script>
					<canvas id='counter' style="width: 100%;"></canvas>
					
				</div>
			</div>
			
			<div class="tab-page" id="tabDays">
				<h2 class="tab"><i class="fa fa-th-list"></i> Постраничная статистика</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById('tabDays'));</script>
				<div class="tab-body-">
					<div style="">					
						<form method="post" action="">					
							<input type="text" class="form-control table_choiсe" name="table_choiсe" value="<?php echo $_POST['table_choiсe'];?>" style="width:75%;" placeholder="Выберите дату"><button class="btn btn-success" style="width:25%;">Посмотреть</button>
						</form>
					</div>
					<table class="grid" cellpadding="1" cellspacing="1" id="viewsTable">
						<thead>
							<tr>
								<td class="gridHeader" width="5%"> </td>
								<td class="gridHeader" width="5%">ID</td>
								<td class="gridHeader" width="75%">Название страницы</td>
								<td class="gridHeader" width="5%">Визиты</td>
								<td class="gridHeader" width="5%">Просмотры</td>
								<td class="gridHeader" width="5%">Боты</td>
								
							</tr>
						
							
							<?php								
								$data = date('d-m-Y');
								if($_POST['table_choiсe']){
									$da = explode('-', $_POST['date']);
									$data = $da[2].'-'.$da[1].'-'.$da[0];
								}
								
								$site_start = $modx->getConfig('site_start');
								$error_page = $modx->getConfig('error_page');
								
								if ($site_start != $error_page) $exception = ' and `did`!="'.$error_page.'" ';
								
								
								$res = $modx->db->query('Select `did`,`date`,`visits`,`views`,`bots`,`pagetitle` from ' . $modx->getFullTableName('site_counter') . ' as `counter`
								left  join ' . $modx->getFullTableName('site_content') . ' as `c` ON `c`.id = `counter`.`did`
								where `date`="'.$data.'" ' . $exception . ' and `visits`>0 ORDER BY `visits` DESC');
								
								$count = $modx->db->getValue('Select sum(`visits`) from ' . $modx->getFullTableName('site_counter') . '
								where `date`="'.$data.'" ' . $exception . ' and `visits`> 0');
																																
								$n=1;
								$out = '';
								$visits = 0;
								$views = 0;
								$bots = 0;
								
								while($item = $modx->db->getRow($res)){
									if ($item['visits']<=$skip) continue;
									$summ = $item['visits'] + $item['bots'];
									$width = ($item['visits']/$count)*100;
									$out.= '
									<tr>
										<td>'.$n.'</td>
										<td>'.$item['did'].'</td>
										<td>'.$item['pagetitle'].'
										<div class="graph" style="width:'.$width.'%"></div>									
										</td>
										<td class="center">'.$item['visits'].'</td>
										<td class="center">'.$item['views'].'</td>
										<td class="center">'.$item['bots'].'</td>									
									</tr>
									';
									$n++;
									$visits = $visits + $item['visits'];
									$views = $views + $item['views'];
									$bots = $bots + $item['bots'];
								}
								echo '
								<tr>
									<td></td>
									<td></td>
									<td></td>
									<td class="center">'.$visits.'</td>
									<td class="center">'.$views.'</td>
									<td class="center">'.$bots.'</td>									
								</tr>
							</thead>
						<tbody>
								';
								echo $out;
							?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="tab-page" id="tabSettings">
				<h2 class="tab"><i class="fa fa-th-list"></i> Настройки</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById('tabSettings'));</script>
				<div class="displayparamrow">
					<div id="displayparams">
						<form action="" method="post" id="settingsForm">
							<input type="hidden" name="send_settings" value="1">
							<table width="100%" cellpadding="0" cellspacing="0" border="0" class="displayparams grid">
								<thead>
									<tr>
										<td>Параметр</td>
										<td>Значение</td>
										
									</tr>
								</thead>
								<tbody>
									
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Показывать виджет на главной</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<select name="widget">
												<option value="0">Нет</option>
												<option value="1" <?php if ($widget) echo 'selected="selected"';?>>Да</option>
											</select>
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Сколько дней показывать по умолчанию</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="period" value="<? echo $period;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Количество строк в таблице</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="rows" value="<? echo $rows;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Пропуск страниц с визитами меньше</span>
											<span class="paramDesc">
												<br>
												<small>В таблице, на график не распространяется</small>
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="skip" value="<? echo $skip;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Заголовок для визитов</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="visitsTitle" value="<? echo $visitsTitle;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Цвет линии для визитов</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="visitsColor" value="<? echo $visitsColor;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Заголовок для просмотров</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="viewsTitle" value="<? echo $viewsTitle;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Цвет линии для просмотров</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="viewsColor" value="<? echo $viewsColor;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Что делать с ботами</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<select name="view_bots" data-value="<?php echo $bots;?>"> 
												<option value="1" <?php if($view_bots==1) echo 'selected="selected"';?>>Не показывать</option>
												<option value="2" <?php if($view_bots==2) echo 'selected="selected"';?>>Показывать отдельно</option>
												<option value="3" <?php if($view_bots==3) echo 'selected="selected"';?>>Суммировать</option>
											</select>
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Заголовок для ботов</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="botsTitle" value="<? echo $botsTitle;?>">
										</td>			
									</tr>
									<tr>
										<td class="labelCell" width="20%">
											<span class="paramLabel">Цвет линии для ботов</span>
											<span class="paramDesc">
											</span>
										</td>
										<td class="inputCell relative" width="80%">
											<input type="text" name="botsColor" value="<? echo $botsColor;?>">
										</td>			
									</tr>
								</tbody>
							</table>
						</form>
					</div>
				</div>
			</div>				
		</div>
	</body>
</html>
