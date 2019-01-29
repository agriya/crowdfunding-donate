<?php /* SVN: $Id: admin_index.ctp 2897 2010-09-02 11:26:34Z beautlin_108ac10 $ */ ?>
<?php
  //for small pie chart
      $project_percentage = $project_stat = '';
      $all = $total_projects;
      $project_percentage .= ($project_percentage != '') ? ',' : '';
      $project_stat .= (!empty($project_stat)) ? '|'.__l("Pending") : __l("Pending");
      $project_percentage .= round((empty($pending_project_count)) ? 0 : ( ($pending_project_count / $all) * 100 ));

      $project_percentage .= ($project_percentage != '') ? ',' : '';
      $project_stat .= (!empty($project_stat)) ? '|'.__l('Open for Idea') : __l('Open for Idea');
      $project_percentage .= round((empty($open_for_idea)) ? 0 : ( ($open_for_idea / $all) * 100 ));

      $project_percentage .= ($project_percentage != '') ? ',' : '';
      $project_stat .= (!empty($project_stat)) ? '|'.__l('Open for Funding') : __l('Open for Funding');
      $project_percentage .= round((empty($opened_project_count)) ? 0 : ( ($opened_project_count / $all) * 100 ));

	  $project_percentage .= ($project_percentage != '') ? ',' : '';
      $project_stat .= (!empty($project_stat)) ? '|'.__l("Expired") : __l("Expired");
      $project_percentage .= round((empty($expired_project_count)) ? 0 : ( ($expired_project_count / $all) * 100 ));

      $project_percentage .= ($project_percentage != '') ? ',' : '';
      $project_stat .= (!empty($project_stat)) ? '|'.__l("Closed") : __l("Closed");
      $project_percentage .= round((empty($closed_project_count)) ? 0 : ( ($closed_project_count / $all) * 100 ));
?>
<div class="main-admn-usr-lst js-response">	
	<div class="thumbnail navbar-btn no-border row">			
		<?php echo $this->element('svg_chart');?>
		<div class="col-md-3 center-block img-thumbnail marg-top-30 clearfix">	
			<h3>
				<?php echo sprintf(__l('%s Status'), Configure::read('project.alt_name_for_project_singular_caps')); ?>
			</h3>
			<div class="pull-left">
				<?php echo $this->Html->image('http://chart.googleapis.com/chart?cht=p&amp;chd=t:'.$project_percentage.'&amp;chs=120x120&amp;chco=E49F18|49C8F5|8F91DB|78A595|557D36&amp;chf=bg,s,FF000000'); ?>					
			</div>
			<div class="pull-left text-info">
				<?php
				$percentage = $label = '';
				$percentage .= ($percentage != '') ? ',' : '';
				$label .= (!empty($project_stat)) ? '|'.__l("Successful") : __l("Successful");
				$percentage .= round((empty($successful_projects)) ? 0 : ( ($successful_projects / $closed_project_count) * 100 ));
				$percentage .= ($project_percentage != '') ? ',' : '';
				$label .= (!empty($project_stat)) ? '|'.__l("Failed") : __l("Failed");
				$percentage .= round((empty($failed_projects)) ? 0 : ( ($failed_projects / $closed_project_count) * 100 ));
				?>
				<div>
					<span>
						<?php echo __l('Private Info'); ?> <i title="<?php echo sprintf(__l('This info is private. You can able to set Genuine/Not Genuine for Funding Closed %s'), Configure::read('project.alt_name_for_project_plural_caps')); ?>"  data-placement="left" class="js-tooltip fa fa-question-circle"></i>
					</span>
					<div><?php echo __l('Genuine') . ': '.$successful_projects; ?></div>
					<div><?php echo __l('Not Genuine') . ': '.$failed_projects; ?> <i title="<?php echo __l('Funding closed, but project owner did fraudulently'); ?>"  data-placement="left" class="fa fa-question-circlejs-tooltip"></i></div>
				</div>
			</div>			
		</div>			
	</div>

	<div class="bg-primary row">
		<ul class="list-inline sec-1 navbar-btn">
			<?php
				foreach($formFieldSteps AS $key => $value) { 
					if(!empty($step_count[$key])) {
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center adm-usr">'.$this->Html->cInt($step_count[$key], false).'</span><span>' .__l($value). '</span>', array('controller'=>'donates','action'=>'index','project_status_id' => ConstDonateProjectStatus::PendingAction, 'step'=> $key), array('escape' => false));?>
				</div>
			</li>
			<?php
					}
				}
			?>
		</ul>
	</div>
	<div class="clearfix">
		<div class="navbar-btn">
			<ul class="list-unstyled clearfix">
				<li class="pull-left"> 
					<p><?php echo $this->element('paging_counter');?></p>
				</li>
			</ul>
		</div>
		<?php echo $this->Form->create('Project' , array('class' => 'clearfix js-shift-click js-no-pjax','action' => 'update')); ?>
		<?php echo $this->Form->input('r', array('type' => 'hidden', 'value' => $this->request->url)); ?>

		<div class="table-responsive">
			<table class="table table-striped table-bordered">
				<thead>
					<tr class="js-even">        
						<th class="col-xs-2">
						</th>
						<th  class="text-left"><div><?php echo $this->Paginator->sort('name', __l('Name'));?></div></th>
						<th  class="text-left"><div><?php echo __l('Completed Step');?></div></th>
						<th class="text-left"><div><?php echo $this->Paginator->sort('User.username', __l('Posted By'));?></div></th>
						<th class="text-right"><div><?php echo $this->Paginator->sort('needed_amount', __l('Needed')).' ('.Configure::read('site.currency').')';?></div></th>
						<th  class="text-center"><div><?php echo $this->Paginator->sort('name', __l('Posted On'));?></div></th>
					</tr>
				</thead>
				<tbody class="h6">
					<?php
					$i = 0;
					if (!empty($projects)):
					foreach ($projects as $project):
					?>
					<tr>
						<td>
							<div class="btn-group">
							<a href="#" title="Actions"  class="btn btn-success js-no-pjax"><?php echo __l('Approve?'); ?></a>
							<?php
							$redirect_url = Router::url(array(
							'controller' => 'projects',
							'action' => 'pending_approval_steps',
							$project['Project']['id']
							), true);
							?>
							<?php
							if(empty($project['Project']['is_draft'])){
							?>
							<a class="btn btn-success js-approve-link js-tooltip js-no-pjax"  data-target="#js-ajax" data-toggle="modal" title="<?php echo __l('Approve');?>" href="<?php echo $this->Html->cText($redirect_url, false); ?>"><i class="fa fa-cog"> </i><span class="caret"></span></a>
							<?php } ?>
							<div class="dropdown-menu js-pending-list clearfix" id="dropdown-<?php echo $i; ?>">
							<div class="text-center"><?php echo $this->Html->image('ajax-follow-loader.gif', array('class'=>'js-loader')); ?></div>
							</div>
							</div>
						</td>
						<td>
							<i title="<?php echo !empty($project['Donate']['DonateProjectStatus'])?$this->Html->cText($project['Donate']['DonateProjectStatus']['name'], false):'Drafted';?>" class="fa fa-square pull-left project-status-<?php echo $this->Html->cInt($project['Donate']['donate_project_status_id'], false);?>"></i>
							<?php echo $this->Html->link($this->Html->cText($project['Project']['name'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], 'admin' => false), array('class'=>'htruncate col-xs-6','title' => $this->Html->cText($project['Project']['name'], false), 'escape' => false));?>
						</td>
						<td>
							<?php
							$current_step = max(array_keys(unserialize($project['Project']['tracked_steps'])));
							if(!empty($formFieldSteps[$current_step])) {
							echo __l("Step ") . $current_step . ": " . $formFieldSteps[$current_step];
							}
							?>
						</td>
						<td>
							<div class="media">
								<div class="pull-left tbl-img">
									<?php echo $this->Html->getUserAvatar($project['User'], 'micro_thumb', false, '', 'admin');?>
								 </div>
								 <div class="tbl-cnt media-body">
									<span title="<?php echo $this->Html->cText($project['User']['username'], false); ?>"><?php echo $this->Html->getUserLink($project['User']); ?></span>
								</div>
							</div>
						</td>
						<td class="text-right"><?php echo $this->Html->cCurrency($project['Project']['needed_amount']);?></td>
						<td class="text-center"><?php echo $this->Html->cDateTimeHighlight($project['Project']['created']);?></td>
					</tr>
					<?php
					$i++;
					endforeach;
					else:
					?>
					<tr class="js-even">
						<td colspan="22"><i class="fa fa-exclamation-triangle"></i> <?php echo sprintf(__l('No %s available'), Configure::read('project.alt_name_for_donate_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps'));?></td>
					</tr>
					<?php
					endif;
					?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="page-sec navbar-btn">
		<div class="row">
			<div class="col-xs-12 col-sm-6 pull-right">
				<?php if(!empty($pagination)) :?>
				<?php echo $this->element('paging_links'); ?>
				<?php endif; ?>
			</div>
			<?php
			echo $this->Form->end();
			?>
		</div>
	</div>
</div>
<div class="modal fade" id="js-ajax">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"> <a href="#" class="btn btn-danger js-no-pjax" data-dismiss="modal"><?php echo __l('Close'); ?></a> </div>
		</div>
	</div>
</div>
</div>
<span id="tooltip_text" class="invisible"></span>
