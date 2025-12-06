<?php
	
	class evostatistics
	{
		protected $module_name;
		protected $table_module;
		protected $post;
		
		public function __construct($modx, $params = null)
		{
			$this->modx = evolutionCMS();
			$this->table_module = $this->modx->getFullTableName("site_modules");
			$this->module_name = "evostatistics";
			$this->post = $_POST;
		}
		
		function getProps()
		{
			$properties = $this->modx->db->getValue(
            "Select `properties` from " .
			$this->table_module .
			' where `modulecode` LIKE "%' .
			$this->module_name .
			'%"'
			);
			$data = json_decode($properties, true);
			return $data;
		}
		
		function setProps()
		{
			$data = $this->post;
			$props = $this->getProps();
			foreach ($data as $key => $value) {
				if (isset($props[$key])) {
					$props[$key][0]["value"] = $value;
				}
			}
			$json = json_encode($props, JSON_UNESCAPED_UNICODE);
			$insert = $this->modx->db->escape($json);
			$this->modx->db->query(
            "Update " .
			$this->table_module .
			' set `properties`="' .
			$insert .
			'" where `modulecode` LIKE "%' .
			$this->module_name .
			'%"'
			);
			header("Location: " . $_SERVER["REQUEST_URI"]);
			exit();
		}
		
		function getVars()
		{
			$input = $this->getProps();
			$data = [];
			$vars = [
            "widget",
            "visitsTitle",
            "visitsColor",
            "viewsTitle",
            "viewsColor",
            "botsTitle",
            "botsColor",
            "period",
            "bots_check",
            "view_bots",
            "rows",
            "skip",
			];
			foreach ($vars as $key) {
				$data[$key] =
                $input[$key][0]["value"] != ""
				? $input[$key][0]["value"]
				: $input[$key][0]["default"];
			}
			return $data;
		}
		
		function getData($period = 7, $begin = "", $did = 0)
		{
			if (!$begin) {
				$begin = date("d-m-Y");
			}
			$data = [];
			for ($i = $period; $i >= 0; $i--) {
				$day = date("d-m-Y", strtotime($begin . " -" . $i . " days"));
				$data["days"][] = $day;
				$and_did = "";
				if ($did != 0) {
					$and_did = " AND did=" . $did;
				}
				$res = $this->modx->db->query(
                'SELECT
				sum(`visits`) as `visits`,
				sum(`views`) as `views`,
				sum(`bots`) as `bots`
				FROM ' .
				$this->modx->getFullTableName("site_counter") .
				' 
				WHERE `date`="' .
				$day .
				'"' .
				$and_did
				);
				
				$row = $this->modx->db->getRow($res);
				
				if (!$row["bots"] or $row["bots"] == "null") {
					$bot = 0;
					} else {
					$bot = $row["bots"];
				}
				$data["bots"][] = $bot;
				
				if (!$row["visits"]) {
					$visit = 0;
					} else {
					$visit = $row["visits"];
				}
				if ($bots_check == 3) {
					$visit = $visit + $bot;
				}
				$data["visits"][] = $visit;
				
				if (!$row["views"] or $row["views"] == "null") {
					$view = 0;
					} else {
					$view = $row["views"];
				}
				if ($bots_check == 3) {
					$view = $view + $bot;
				}
				$data["views"][] = $view;
			}
			
			$md = $this->modx->db->getValue(
            "SELECT `date` FROM " .
			$this->modx->getFullTableName("site_counter") .
			" ORDER BY `id` ASC"
			);
			$mdd = explode("-", $md);
			$data["min_date"] = $mdd[1] . "." . $mdd[0] . "." . $mdd[2];
			$data["max_date"] = date("m.d.Y");
			
			return $data;
		}
		
		function drawGraph($data)
		{
			extract($data);
			$dg =
            "							
			jQuery.fn.createChart = function(options) {
			return this.each(function() {
			var ctx = this.getContext('2d');
			
			var myChart = new Chart.Line(ctx, {
			data: {
			labels: " .
            json_encode($days, JSON_UNESCAPED_UNICODE) .
            ",
			datasets: [{
			label: '" .
            $viewsTitle .
            "',
			fill: false,
			borderColor: '" .
            $viewsColor .
            "',
			backgroundColor: '" .
            $viewsColor .
            "',
			borderWidth: 1.5,
			pointBorderWidth: 0,
			pointRadius: 2,
			borderDash: [2, 2],
			data: " .
            json_encode($views, JSON_UNESCAPED_UNICODE) .
            "
			}, {
			label: '" .
            $visitsTitle .
            "',
			fill: false,
			borderColor: '" .
            $visitsColor .
            "',
			backgroundColor: '" .
            $visitsColor .
            "',
			borderWidth: 1.5,
			pointBorderWidth: 0,
			pointRadius: 2,
			data: " .
            json_encode($visits, JSON_UNESCAPED_UNICODE) .
            "
			}";
			if ($view_bots == 2) {
				$dg .=
                ",{
				label: '" .
                $botsTitle .
                "',
				fill: false,
				borderColor: '" .
                $botsColor .
                "',
				backgroundColor: '" .
                $botsColor .
                "',
				borderWidth: 1.5,
				pointBorderWidth: 0,
				pointRadius: 2,
				data: " .
                json_encode($bots, JSON_UNESCAPED_UNICODE) .
                "
				}";
			}
			$dg .= "]
			},
			options: {
			responsive: true,
			hoverMode: 'index',
			stacked: false,
			title: {
			display: false
			},
			legend: {
			position: 'bottom',
			labels: {
			boxWidth: 12
			}
			},
			scales: {
			xAxes: [{
			gridLines: {
			color: 'rgba(0,0,0,0.05)',
			},
			}],
			yAxes: [$.extend({
			type: 'linear',
			display: true,
			position: 'right',
			id: 'amounts',
			beginAtZero: true,
			gridLines: {
			color: 'rgba(0,0,0,0.05)',
			}
			})],
			}
			}
			});
			});
			};		
			";
			return $dg;
		}
	}
