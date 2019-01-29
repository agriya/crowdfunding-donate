<?php
	$width = 620;
	$collapse_class = 'in';
	if ($this->request->params['isAjax']) {
		$collapse_class ="in";
	}
?>
<div class="col-xs-12">
	<div class="js-donates col-xs-12 donate-projects">
		<div class="accordion-group">
			<div class="accordion-heading">
				<div class="no-bor clearfix box-head">
					<h5 class="no-mar">
						<span class="pull-left mspace">
							<?php echo $this->Html->image('donate-projects.png', array('alt' => sprintf(__l('[Image: %s]'), Configure::read('project.alt_name_for_donate_singular_caps')))); ?>							
							<span class="h4 mspace"><?php echo __l(Configure::read('project.alt_name_for_donate_singular_caps')).' '. __l(Configure::read('project.alt_name_for_project_plural_caps')); ?></span>
						</span>
						<div class="pull-right">						
							<div class="pull-left space">
								<div class="dropdown">
									<a class="dropdown-toggle js-no-pjax js-overview" data-toggle="dropdown" href="#">
										<i class="fa fa-wrench grayc"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-right">
										<li<?php echo (!empty($this->request->params['named']['select_range_id']) && $this->request->params['named']['select_range_id'] == 'lastDays') ? ' class="active"' : ''; ?>><a class='js-link {"data_load":"js-donates"}' title="<?php echo __l('Last 7 days'); ?>"  href="<?php echo Router::url('/', true)."donate_charts/chart_projects/select_range_id:lastDays";?>"><?php echo __l('Last 7 days'); ?></a> </li>
										<li<?php echo (!empty($this->request->params['named']['select_range_id']) && $this->request->params['named']['select_range_id'] == 'lastWeeks') ? ' class="active"' : ''; ?>><a class='js-link {"data_load":"js-donates"}' title="<?php echo __l('Last 4 weeks'); ?>" href="<?php echo Router::url('/', true)."donate_charts/chart_projects/select_range_id:lastWeeks";?>"><?php echo __l('Last 4 weeks'); ?></a> </li>
										<li<?php echo (!empty($this->request->params['named']['select_range_id']) && $this->request->params['named']['select_range_id'] == 'lastMonths') ? ' class="active"' : ''; ?>><a class='js-link {"data_load":"js-donates"}' title="<?php echo __l('Last 3 months'); ?>" href="<?php echo Router::url('/', true)."donate_charts/chart_projects/select_range_id:lastMonths";?>"><?php echo __l('Last 3 months'); ?></a> </li>
										<li<?php echo (!empty($this->request->params['named']['select_range_id']) && $this->request->params['named']['select_range_id'] == 'lastYears') ? ' class="active"' : ''; ?>><a class='js-link {"data_load":"js-donates"}' title="<?php echo __l('Last 3 years'); ?>"  href="<?php echo Router::url('/', true)."donate_charts/chart_projects/select_range_id:lastYears";?>"><?php echo __l('Last 3 years'); ?></a> </li>
									</ul>
								</div>
							</div>
							<div class="pull-left">
								<a class="accordion-toggle js-toggle-icon js-no-pjax clearfix pull-right js-no-pjax space" href="#donates" data-parent="#accordion-admin-dashboard" data-toggle="collapse">
									<i class="fa fa-chevron-down grayc"></i>
								</a>
							</div>					
						</div>
					</h5>
				</div>
			</div>
			<div id="donates" class="accordion-body collapse over-hide <?php echo $collapse_class;?>">
				<div class="accordion-inner">
					<?php
						$div_class = "js-load-line-graph ";
					?>
					<div class="row">
						<section class="col-md-6 border-right border-right">
							<div class="<?php echo $div_class;?> text-center {'chart_width':'<?php echo $width; ?>', 'chart_type':'LineChart', 'data_container':'donate_projects_line_data', 'chart_container':'donate_projects_line_chart', 'chart_title':'<?php echo __l(Configure::read('project.alt_name_for_project_plural_caps')); ?>', 'chart_y_title': '<?php echo __l(Configure::read('project.alt_name_for_project_plural_caps'));?>'}">
								<div id="donate_projects_line_chart"></div>
								<div class="hide">
								<table id="donate_projects_line_data" class="table table-striped table-bordered table-condensed table-hover">
									<thead>
										<tr>
											<th>Period</th>
											<?php foreach($chart_projects_periods as $_period): ?>
												<th><?php echo $_period['display']; ?></th>
											<?php endforeach; ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach($chart_projects_data as $display_name => $chart_data): ?>
											<tr>
												<th><?php echo $display_name; ?></th>
												<?php foreach($chart_data as $val): ?>
													<td><?php echo $val; ?></td>
												<?php endforeach; ?>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								</div>
							</div>
						</section>
						<hr class="visible-xs">
						<section class="col-md-6">
							<div class="<?php echo $div_class;?> text-center {'chart_width':'<?php echo $width; ?>', 'chart_type':'LineChart', 'data_container':'donate_project_fund_line_data', 'chart_container':'donate_project_fund_line_chart', 'chart_title':'<?php echo sprintf(__l('%s Funded'),Configure::read('project.alt_name_for_project_singular_caps')) ;?>', 'chart_y_title': '<?php echo sprintf(__l('%s Funded'),Configure::read('project.alt_name_for_project_singular_caps'));?>'}">
								<div id="donate_project_fund_line_chart"></div>
								<div class="hide">
								<table id="donate_project_fund_line_data" class="table table-striped table-bordered table-condensed table-hover">
									<thead>
										<tr>
											<th>Period</th>
											<?php foreach($chart_project_funds_periods as $_period): ?>
												<th><?php echo $_period['display']; ?></th>
											<?php endforeach; ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach($chart_project_funds_data as $display_name => $chart_data): ?>
											<tr>
												<th><?php echo $display_name; ?></th>
												<?php foreach($chart_data as $val): ?>
													<td><?php echo $val; ?></td>
												<?php endforeach; ?>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								</div>
							</div>
						</section>
						<hr class="visible-xs">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>