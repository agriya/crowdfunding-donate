<div class="main-admn-usr-lst js-response">
	<?php if(empty($this->request->params['named']['view_type'])) : ?>
	<div class="clearfix donates">		 
		<div class="navbar-btn">
			<h3>
				<i class="fa fa-th-list fa-fw"></i> <?php echo __l('List');?>
			</h3>
			<?php
			$placeholder = __l('Search');
			if (!empty($this->request->params['named']['q'])) {
			$placeholder = $this->request->params['named']['q'];
			}
			?>
			<ul class="list-unstyled clearfix">
				<li class="pull-left"> 
					<p><?php echo $this->element('paging_counter');?></p>
				</li>
				<li class="pull-right"> 
					<div class="form-group srch-adon">
						<?php echo $this->Form->create('Donate' ,array('url' => array('controller' => 'donates','action' => 'funds')), array('type' => 'get', 'class' => 'form-search')); ?>
						<span class="form-control-feedback" id="basic-addon1"><i class="fa fa-search text-default"></i></span>
						<?php echo $this->Form->input('q', array('label' => false,' placeholder' => __l('Search'), 'class' => 'form-control')); ?>
						<div class="hide">
						<?php echo $this->Form->submit(__l('Search'));?>
						</div>
						<?php echo $this->Form->end(); ?>
					</div>
				</li>
			</ul>
		</div>		
		<?php endif; ?>		
		<div class="table-responsive">
			<table class="table table-striped">
				<thead class="h5">
					<tr>
						<th class="text-left"><div><?php echo __l(Configure::read('project.alt_name_for_project_singular_caps'));?></div></th>
						<th><div><?php echo $this->Paginator->sort('User.username', __l(Configure::read('project.alt_name_for_donor_singular_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<th class="text-center"><div><?php echo __l('Paid Amount') . ' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('amount', sprintf(__l('Amount to %s'), Configure::read('project.alt_name_for_donate_project_owner_singular_caps')), array('class' => 'js-no-pjax js-filter')).'
						('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('site_fee', __l('Site Commission'), array('class' => 'js-no-pjax js-filter')).' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('created', sprintf(__l('%s On'), Configure::read('project.alt_name_for_donate_past_tense_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
					</tr>
				</thead>
				<tbody class="h5">
					<?php
					if (!empty($projectFunds)):
					$donate_amount = $site_fee_amount = $paid_amount = 0;
					foreach ($projectFunds as $projectFund):
					$donate_amount += $projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee'];
					$site_fee_amount += $projectFund['ProjectFund']['site_fee'];
					$paid_amount += $projectFund['ProjectFund']['amount'];
					?>
					<?php if(!empty($projectFund['Project']['Donate'])){ ?>
					<tr>
						<td class="text-left">
						<div class="clearfix htruncate">
						<?php $project_status = !empty($projectFund['Project']['Donate']['DonateProjectStatus']['name']) ? $projectFund['Project']['Donate']['DonateProjectStatus']['name'] : ''; ?>
						<i title="<?php echo $this->Html->cText($project_status, false);?>" class="fa fa-square project-status-<?php echo $this->Html->cInt($projectFund['Project']['Donate']['donate_project_status_id'], false);?>"></i>
						<?php echo $this->Html->link($this->Html->cText($projectFund['Project']['name']), array('controller'=> 'projects', 'action'=>'view', $projectFund['Project']['slug'],'admin' => false), array('escape' => false,'title'=>$this->Html->cText($projectFund['Project']['name'],false)));?>
						</div>
						</td>
						<td class="text-left">
							<div class="media">
								<div class="pull-left">
									<p>
										<?php echo $this->Html->getUserAvatar($projectFund['User'], 'micro_thumb',true, '', 'admin');?>
									</p>
								</div>
								<div class="media-body">
									<p>
										<?php echo $this->Html->getUserLink($projectFund['User']); ?>
									</p>
								</div>
							</div>
						</td>
						<td class="text-center"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount']);?></td>
						<td class="text-center"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee']); ?></td>
						<td class="text-center"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['site_fee']);?></td>
						<td class="text-center"><?php echo $this->Html->cDateTimeHighlight($projectFund['ProjectFund']['created']);?></td>
					</tr>
					<?php } ?>
					<?php
					endforeach;
					else:
					?>
					<tr>
						<td colspan="9"><i class="fa fa-exclamation-triangle"></i> <?php echo sprintf(__l('No %s Funds available'), Configure::read('project.alt_name_for_project_singular_caps')); ?></td>
					</tr>
					<?php
					endif;
					?>
				</tbody>
			</table>
		</div>			
	</div>
	<div class="page-sec navbar-btn">
		<?php if (!empty($projectFunds)) : ?>
		<div class="row">
			<div class="col-xs-12 col-sm-6 pull-right">
				<?php  echo $this->element('paging_links'); ?>
			</div>
		</div>
		<?php endif;?>
	</div>
</div>