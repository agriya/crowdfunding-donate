<?php $link = Configure::read('project.alt_name_for_donate_singular_caps'); ?>
<?php $collected_percentage = ($project['Project']['collected_percentage']) ? $project['Project']['collected_percentage'] : 0 ;?>
<div class="progress list-group-item-text">
	<div class="progress-bar progress-bar-success" role="progressbar" style="width:<?php echo ($collected_percentage > 100) ? '100%' : $collected_percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($collected_percentage, false).'%'; ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo $collected_percentage;?>"></div>
</div>
<div class="row">
	<?php if($project['Donate']['donate_project_status_id'] != ConstDonateProjectStatus::Pending): ?>
		<div class="col-xs-4">	
			<h3 class="h3 navbar-btn list-group-item-text htruncate">
				<strong><?php echo $this->Html->cInt($collected_percentage);?><?php echo '%';?></strong>
			</h3>
			<p class="grayc h6 list-group-item-heading htruncate"><?php echo __l('funded') ?></p>
		</div>
	<?php endif; ?>
	<div class="col-xs-4">
		<div class="row">
			<h3 class="h3 navbar-btn list-group-item-text htruncate">
				<strong><?php echo $this->Html->siteCurrencyFormat($this->Html->cCurrency($project['Project']['collected_amount'],false)); ?></strong>
			</h3>
			<p class="grayc h6 list-group-item-heading htruncate"><?php echo Configure::read('project.alt_name_for_donate_past_tense_small') ?></p>
		</div>
	</div>
	<div class="col-xs-4 text-right">
		<?php
		$days = $display_text = '';
		if ($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::FundingClosed):
		$display_text = __l('ended on');
		if (empty($project['Donate']['is_allow_over_donating'])):
		$days = $this->Html->cDateTimeHighlight($project['Donate']['project_donate_goal_reached_date'], false);
		else:
		$days = $this->Html->cDateTimeHighlight($project['Project']['project_end_date'], false);
		endif;
		elseif ($project['Project']['collected_amount'] >= $project['Project']['needed_amount']):
		$display_text = __l('funded');
		$days = $this->Html->cDateTimeHighlight($project['Donate']['project_donate_goal_reached_date'], false);
		elseif($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::FundingExpired):
		$display_text = __l('expired');
		$days = $this->Html->cDateTimeHighlight($project['Project']['project_end_date'], false);
		else:
		if ($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::OpenForDonating):
		$display_text = (round($project[0]['enddate']) >0) ? __l('days to go') : __l('hours to go');
		if ($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::OpenForDonating):
		if(!empty($project[0]['enddate']) && round($project[0]['enddate']) >0):
		$days = $this->Html->cInt($project[0]['enddate'], false);
		else:
		$countdown = 1;
		$days = intval(strtotime($project['Project']['project_end_date'] . ' 23:59:59') - time());
		endif;
		endif;
		endif;
		endif;
		?>
		<h3 class="h3 navbar-btn list-group-item-text htruncate">
			<?php if (!empty($countdown)): ?>
			<div class="js-countdown">&nbsp;</div>
			<span class="js-time hide"><?php echo $this->Html->cInt($days, false);?></span>
			<?php else: ?>
			<span class="c" title="<?php echo $days;?>"><?php echo $days;?></span>
			<?php endif; ?>
		</h3>
		<p class="grayc h6 list-group-item-heading htruncate"><?php echo $display_text; ?></p>
	</div>
</div>
