<div class="main-admn-usr-lst js-response">
	<div class="projectFunds index js-response">
		<div class="clearfix navbar-btn donates">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#"><i class="fa fa-th-list fa-fw"></i> <?php echo __l('List'); ?></a></li>
			</ul>
			<div class="clearfix">
				<div class="pull-left"><?php echo $this->element('paging_counter');?></div>
				<div class="pull-right">
					<?php echo $this->Form->create('ProjectFund' ,array('url' => array('controller' => 'project_funds','action' => 'index','project_type'=>'Donate')), array('type' => 'get', 'class' => 'form-search')); ?>
					<?php echo $this->Form->input('q', array('label' => false,' placeholder' => __l('Search'), 'class' => 'search-query mob-clr')); ?>
					<div class="hide">
					<?php echo $this->Form->submit(__l('Search'));?>
					</div>
					<?php echo $this->Form->end(); ?>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th class="text-left"><div><?php echo Configure::read('project.alt_name_for_donor_singular_caps');?></div></th>
							<th class="text-center"><div><?php echo $this->Paginator->sort('User.username', Configure::read('project.alt_name_for_donor_singular_caps'));?></div></th>
							<th class="text-right"><div><?php echo __l('Paid Amount') . ' ('.Configure::read('site.currency').')';?></div></th>
							<th class="text-right"><div><?php echo $this->Paginator->sort('amount', sprintf(__l('Amount to %s'), Configure::read('project.alt_name_for_donate_project_owner_singular_caps'))).'
							('.Configure::read('site.currency').')';?></div></th>
							<th class="text-right"><div><?php echo $this->Paginator->sort('site_fee', __l('Site Commission')).' ('.Configure::read('site.currency').')';?></div></th>
							<th class="text-center"><div><?php echo $this->Paginator->sort('created', sprintf(__l('%s On'), Configure::read('project.alt_name_for_donate_past_tense_caps')));?></div></th>
							<th><div><?php echo $this->Paginator->sort('Status', __l('Status'));?></div></th>
						</tr>
					</thead>
					<tbody class="h6">
						<?php
						if (!empty($projectFunds)):
						$donate_amount = $site_fee_amount = $paid_amount = 0;
						foreach ($projectFunds as $projectFund):
						$donate_amount += $projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee'];
						$site_fee_amount += $projectFund['ProjectFund']['site_fee'];
						$paid_amount += $projectFund['ProjectFund']['amount'];
						?>
						<tr>
							<td class="text-left">
								<div class="clearfix">
								<?php $project_status = $projectFund['Project']['Donate']['DonateProjectStatus']['name']; ?>
								<i title="<?php echo $this->Html->cText($project_status, false);?>" class="fa fa-square project-status-<?php echo $this->Html->cInt($projectFund['Project']['Donate']['donate_project_status_id'], false);?>"></i>
								<?php echo $this->Html->link($this->Html->cText($projectFund['Project']['name']), array('controller'=> 'projects', 'action'=>'view', $projectFund['Project']['slug'],'admin' => false), array('escape' => false,'title'=>$this->Html->cText($projectFund['Project']['name'],false)));?>
								</div>
							</td>
							<td>
								<ul class="list-inline tbl">
									<li class="tbl-img">
										<?php echo $this->Html->getUserAvatar($projectFund['User'], 'micro_thumb',true, '', 'admin');?>
									</li>
									<li class="tbl-cnt">
										<p>
											<?php echo $this->Html->getUserLink($projectFund['User']); ?>
										</p>
									</li>
								</ul>
							</td>
							<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount']);?>
							</td>
							<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee']); ?></td>
							<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['site_fee']);?></td>
							<td class="text-center"><?php echo $this->Html->cDateTimeHighlight($projectFund['ProjectFund']['created']);?></td>
							<td><?php echo sprintf(__l('%s'), Configure::read('project.alt_name_for_donate_past_tense_caps')); ?></td>
						</tr>
						<?php
						endforeach;
						else:
						?>
						<tr>
							<td colspan="9" class="text-center"><i class="fa fa-exclamation-triangle fa-fw"></i><?php echo sprintf(__l('No %s Funds available'), Configure::read('project.alt_name_for_project_singular_caps')); ?></td>
						</tr>
						<?php
						endif;
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="page-sec navbar-btn">
		<div class="row">
			<?php if (!empty($projectFunds)) : ?>
				<div class="col-xs-12 col-sm-6 pull-right">
					<?php  echo $this->element('paging_links'); ?>
				</div>
			<?php endif;?>
		</div>
	</div>
</div>