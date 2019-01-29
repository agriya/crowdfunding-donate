<?php /* SVN: $Id: index.ctp 2901 2010-09-02 11:49:34Z sakthivel_135at10 $ */ ?>
<?php if (!$this->request->params['isAjax']) { ?>
  <div class="js-response donate space hor-mspace">
<?php } ?>
<div class="clearfix space" id="js-donate-scroll" itemtype="http://schema.org/Product" itemscope>
  <div class="donate-status text-b" itemprop="Name">
    <span class="ver-space"><?php echo $this->Html->image('donates.png', array('class'=>'right-mspace-xs','width' => 50, 'height' => 50)); ?></span><span class="no-mar h3 text-success"><?php echo Configure::read('project.alt_name_for_donate_singular_caps'); ?></span>
  </div>  
</div>
  <div class="clearfix hor-space">
    <ul class="filter-list-block list-inline">
      <li><?php echo $this->Html->link('<span class="badge badge-info"><span><strong>' . $this->Html->cInt($all_count, false) . '</strong></span></span><span class="show">' . __l('All') . '</span>', array('controller' => 'donates', 'action' => 'myprojects', 'status' => 'all'), array('class' => 'js-filter js-no-pjax', 'escape' => false)); ?></li>
		<?php $approvedCountInfo = count($formFieldSteps) > 1 ?' / '.__l('Admin Approved').' ('.$this->Html->cInt($approvedCount,false).')':'';
		  $countIcon = !empty($formFieldSteps)?'<i class="fa fa-info-circle sfont js-tooltip" title="'.__l('Admin Rejected').' ('.$this->Html->cInt($rejectedCount,false).')'.$approvedCountInfo.'"></i>':'';
		  ?>
      <?php 
			$badge_color = array('warning', 'primary', 'darkgreen', 'lightblue', 'danger');
			$color = 0;
			foreach($donateStatuses as $key => $status) {
			  $countInfo = ($key == ConstDonateProjectStatus::Pending)?$countIcon:'';
		  ?>
        <li>
          <?php echo $this->Html->link('<span class="badge badge-'.$badge_color[$color].'"><span><strong>' . $this->Html->cInt($projectStatuses[$key], false) . $countInfo .'</strong></span></span><span  class="show" pro-status-' . $key . '">' . $this->Html->cText(__l($status), false) . '</span>', array('controller' => 'donates', 'action' => 'myprojects', 'status' => $key), array('class' => 'js-filter js-no-pjax', 'escape' => false));?>
        </li>
      <?php
				$color++;
			} ?>
      <li>
        <?php echo $this->Html->link('<span class="badge badge-black"><span><strong>' . $this->Html->cInt($draft_count, false) . '</strong></span></span><span class="show">' . __l('Drafted') . '</span>', array('controller' => 'donates', 'action' => 'myprojects', 'status' => 'draft'), array('class' => 'js-filter js-no-pjax', 'escape' => false)); ?>
      </li>
    </ul>
  </div>
  <?php  echo $this->element('paging_counter'); ?>
  <div class="table-responsive">
  <table class="table table-striped table-bordered table-condensed table-hover panel">
    <tr>
    <?php if (empty($this->request->params['named']['status']) || (!in_array($this->request->params['named']['status'], array(ConstDonateProjectStatus::FundingClosed, ConstDonateProjectStatus::FundingExpired)))): ?>
      <th rowspan="2" class="text-center table-action-width"><?php echo __l('Actions');?></th>
      <?php endif; ?>
      <th rowspan="2" class="text-left"><div class="js-filter"><?php echo $this->Paginator->sort('Project.name', __l('Name'), array('url' => array('controller' => 'donates', 'action' => 'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <th rowspan="2" class="text-center">
        <div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.collected_amount', __l('Collected Amount') ,array('url' => array('controller' => 'donates', 'action' => 'myprojects'), 'class' => 'js-no-pjax')) . ' (' . Configure::read('site.currency') . ')'; ?></div> / <div class="js-filter js-no-pjax"><?php echo $this->Paginator->sort('Project.needed_amount', __l('Needed'),array('url' => array('controller' => 'donates', 'action' => 'myprojects'), 'class' => 'js-no-pjax'));?></div>
      </th>
      <?php if (!empty($this->request->params['named']['status']) && ($this->request->params['named']['status'] == 'goal')): ?>
        <th rowspan="2" class="text-right"><div class="js-filter text-left"><?php echo $this->Paginator->sort('Project.commission_amount', __l('Received amount') , array('url'=>array('controller'=>'donates','action'=>'myprojects'), 'class' => 'js-no-pjax')).' ('.Configure::read('site.currency').')';?></div></th>
      <?php endif; ?>
      <th rowspan="2" class="text-center">
        <div class="js-filter text-center"><?php echo $this->Paginator->sort( 'Project.project_fund_count', Configure::read('project.alt_name_for_donor_plural_caps') ,array('url'=>array('controller'=>'donates','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div>
      </th>
      <?php
        if( !empty($this->request->params['named']['status']) && ($this->request->params['named']['status'] == 'goal')):
          $colspan = 3;
        else:
          $colspan = 2;
        endif;
      ?>
      <th rowspan="2" class="text-center">
        <div><?php echo sprintf(__l('%s Date'), Configure::read('project.alt_name_for_donate_present_continuous_caps') ); ?><div class="js-filter"><?php echo $this->Paginator->sort('Project.project_start_date', __l('Start') , array('url'=>array('controller'=>'donates','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div>/<div class="js-filter js-no-pjax"><?php echo $this->Paginator->sort('Project.project_end_date', __l('End') ,array('url'=>array('controller'=>'donates','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></div>
      </th>
	  <?php if(isPluginEnabled('ProjectUpdates')): ?>
      <th rowspan="2" class="text-center">
        <div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.blog_count', __l('Updates') , array('url' => array('controller' => 'donates', 'action' => 'myprojects'), 'class' => 'js-no-pjax'));?></div>
      </th>
	  <?php endif;?>
      <?php  if(isPluginEnabled('ProjectFollowers')): ?>
        <th rowspan="2" class="text-center">
          <div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.project_follower_count', __l('Followers') , array('url'=>array('controller'=>'donates','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div>
        </th>
      <?php endif; ?>
      <th rowspan="2" class="text-center">
        <div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.message_count', __l('Comments') , array('url'=>array('controller'=>'donates','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div>
      </th>
    </tr>
      <?php if( !empty($this->request->params['named']['status']) && $this->request->params['named']['status'] == 'goal'): ?>
    <tr>
        <th><div class="js-filter"><?php echo $this->Paginator->sort('Project.project_fund_goal_reached_date', __l('Goal Reached Date') , array('url'=>array('controller'=>'donates','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
    </tr>
      <?php endif; ?>
	  <tr>
	  </tr>
    <?php
      if (!empty($projects)):
        $i = 0;
        foreach ($projects as $project):
          if(!empty($project['Project']['project_end_date'])):
          $time_strap= strtotime($project['Project']['project_end_date']) -strtotime( date('Y-m-d'));
          $days = floor($time_strap /(60*60*24));
          if($days > 0){
          $project[0]['enddate'] =$days;
          }else{
          $project[0]['enddate'] =0;
          }
          endif;
    ?>
    <tr>
    <?php if (empty($this->request->params['named']['status']) || (!in_array($project['Donate']['donate_project_status_id'], array(ConstDonateProjectStatus::FundingClosed, ConstDonateProjectStatus::FundingExpired)))): ?>
      <td class="text-center">
        <?php if (!in_array($project['Donate']['donate_project_status_id'], array(ConstDonateProjectStatus::FundingClosed, ConstDonateProjectStatus::FundingExpired))) { ?>
        <div class="dropdown">
          <a href="#" title="Actions" data-toggle="dropdown" class="fa fa-cog fa-lg dropdown-toggle js-no-pjax"><span class="hide">Action</span></a>
          <ul class="list-unstyled dropdown-menu text-left clearfix">
            <?php if ($project['Project']['is_draft'] || $project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::Pending || $project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::OpenForIdea || ($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::OpenForDonating && Configure::read('Project.is_allow_project_owner_to_edit_project_in_open_status'))): ?>
              <li><?php echo $this->Html->link('<i class="fa fa-pencil-square-o fa-fw"></i>'.__l('Edit'), array('controller' => 'projects', 'action' => 'edit', $project['Project']['id']), array('class' => 'edit js-edit', 'title' => __l('Edit'),'escape'=>false)); ?></li>
            <?php endif; ?>
            <?php if (in_array($project['Donate']['donate_project_status_id'], array(ConstDonateProjectStatus::OpenForIdea, ConstDonateProjectStatus::OpenForDonating)) && isPluginEnabled('SocialMarketing')) { ?>
              <li><?php echo $this->Html->link('<i class="fa fa-share fa-fw"></i>'.__l('Share'), array('controller'=>'social_marketings','action'=>'publish', $project['Project']['id'],'type'=>'facebook', 'publish_action' => 'add'), array( 'title' => __l('Share'),'escape'=>false)); ?></li>
            <?php } ?>
			<?php if($project['Project']['is_draft'] || $project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::Pending){ ?>
		  <li><?php echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.__l('Delete'), Router::url(array('controller'=>'projects','action' => 'delete', $project['Project']['id']),true).'?redirect_to='.$this->request->url,array('class' => 'js-confirm', 'escape'=>false,'title' => __l('Delete')));?></li>
		  <?php } ?>
          </ul>
        </div>
        <?php } ?>
      </td>
      <?php endif; ?>
	  <?php if ((in_array($project['Donate']['donate_project_status_id'], array(ConstDonateProjectStatus::FundingClosed, ConstDonateProjectStatus::FundingExpired)))&&($this->request->params['named']['status']=='all')): ?>

      <td class="text-center"></td>
	  <?php endif;?>
      <td class="text-left">
        <?php
          if ($is_wallet_enabled) {
            $project_status = $project['DonateProjectStatus']['name'];
          } else {
            $project_status = str_replace("Refunded","Voided",$project['DonateProjectStatus']['name']);
          }
        ?>
        <i title="<?php echo $this->Html->cText($project['DonateProjectStatus']['name'], false);?>" class="fa fa-square fa-fw fa-lg project-status-<?php echo $this->Html->cInt($project['Donate']['donate_project_status_id'], false);?>"></i>
		<?php if(!empty($formFieldSteps) && in_array($project['Project']['id'], $rejectedProjectIds)):?>
			<i class="fa fa-info-circle sfont js-tooltip" title="<?php echo __l('Admin Rejected'); ?>"></i>
		<?php endif; ?>
		<?php if(!empty($formFieldSteps) && count($formFieldSteps) > 1 && in_array($project['Project']['id'], $approvedProjectIds)):?>
			<i class="fa fa-info-circle sfont js-tooltip greenc" title="<?php echo __l('Admin Approved'); ?>"></i>
		<?php endif; ?>
        <?php echo $this->Html->link($this->Html->cText($project['Project']['name'],false) , array('controller'=>'projects' , 'action'=>'view' , $project['Project']['slug'] , 'admin'=>false) , array('class' => 'cboxelement', 'escape' => false,'title'=> $this->Html->cText($project['Project']['name'],false)));?>
      </td>
      <td class="text-right">
        <?php $collected_percentage = ($project['Project']['collected_percentage']) ? $project['Project']['collected_percentage'] : 0; ?>
        <div class="progress progress-bar-success">
          <div style="width:<?php echo ($collected_percentage > 100) ? '100%' : $collected_percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($collected_percentage, false).'%'; ?>" class="progress-bar"></div>
        </div>
        <p class="text-center no-mar"><?php echo $this->Html->cCurrency($project['Project']['collected_amount']); ?> / <?php echo $this->Html->cCurrency($project['Project']['needed_amount']); ?></p>
      </td>
      <?php if( !empty($this->request->params['named']['status']) && ($this->request->params['named']['status'] == 'goal')): ?>
        <td class="text-right"><?php echo $this->Html->cCurrency($project['Project']['collected_amount'] - $project['Project']['commission_amount']); ?></td>
      <?php endif; ?>
      <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_fund_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#backers', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_fund_count'], false))); ?></td>
      <td class="text-center">
        <?php
          if (empty($project['Project']['project_start_date']) || $project['Project']['project_start_date'] == '0000-00-00')   {
            echo '-';
          } else {
        ?>
        <div class="clearfix">
          <div class="progress-block clearfix">
            <?php
              $project_progress_precentage = 0;
              if(strtotime($project['Project']['project_start_date']) < strtotime(date('Y-m-d H:i:s'))) {
                if($project['Project']['project_end_date'] !==   NULL) {
                  $days_till_now = (strtotime(date("Y-m-d")) - strtotime(date($project['Project']['project_start_date']))) / (60 * 60 * 24);
                  $total_days = (strtotime(date($project['Project']['project_end_date'])) - strtotime(date($project['Project']['project_start_date']))) / (60 * 60 * 24);
                  if($total_days) {
                    $project_progress_precentage = round((($days_till_now/$total_days) * 100));
                  } else {
                    $project_progress_precentage = round((($days_till_now) * 100));
                  }
                  if($project_progress_precentage > 100) {
                    $project_progress_precentage = 100;
                  }
                } else {
                  $project_progress_precentage = 100;
                }
              }
            ?>
            <?php if ($project['Project']['project_end_date']) ?>
              <div class="progress">
                <div style="width:<?php echo ($project_progress_precentage > 100) ? '100%' : $project_progress_precentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($project_progress_precentage, false).'%'; ?>" class="progress-bar"></div>
              </div>
              <p class="progress-value clearfix"><span><?php echo $this->Html->cDateTimeHighlight($project['Project']['project_start_date']);?></span>&nbsp;/&nbsp;<span><?php echo (!is_null($project['Project']['project_end_date']))? $this->Html->cDateTimeHighlight($project['Project']['project_end_date']): ' - ';?></span></p>
          </div>
        </div>
      <?php } ?>
    </td>
    <?php if(Configure::read('Project.is_project_comment_enabled') && !Configure::read('Project.is_fb_project_comment_enabled')):  ?>
      <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_comment_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#comments', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_comment_count'], false))); ?></td>
    <?php endif; ?>
	<?php if(isPluginEnabled('ProjectUpdates')): ?>
    <td class="text-center">
      <?php
        if (!empty($project['Project']['feed_url'])) {
          echo $this->Html->link($this->Html->cInt($project['Project']['project_feed_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#updates', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title'=> $this->Html->cInt($project['Project']['project_feed_count'], false)));
        } else {
          echo $this->Html->link($this->Html->cInt($project['Project']['blog_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#updates', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['blog_count'], false)));
        }
      ?>
    </td>
	<?php endif;?>
    <?php if(isPluginEnabled('ProjectFollowers')): ?>
      <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_follower_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#followers', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_follower_count'], false)));?></td>
    <?php endif; ?>
    <td class="text-center"><?php echo $this->Html->link($this->Html->cInt(count($project['Project']['Message']), false),array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], 'admin' => false, '#comments'), array('escape' => false, 'title' => $this->Html->cInt(count($project['Project']['Message']), false)));?></td>
    <?php if( !empty($this->request->params['named']['status']) && $this->request->params['named']['status'] == 'goal'): ?>
      <td class="text-center"><?php echo ($project['Donate']['project_fund_goal_reached_date'])?$this->Html->cDate($project['Project']['project_fund_goal_reached_date']):' ';?></td>
    <?php endif; ?>
  </tr>
  <?php
      endforeach;
    else:
  ?>
  <tr><td colspan="22" class="panel">
  	  <div class="text-center no-items">
		<p><?php echo sprintf(__l('No %s %s available'), Configure::read('project.alt_name_for_donate_singular_caps'), Configure::read('project.alt_name_for_project_plural_caps')); ?></p>
	  </div>
  </td></tr>
  <?php
    endif;
  ?>
</table>
</div>
<?php if (!empty($projects)) { ?>
   <div class="clearfix">
    <div class="pull-right paging js-pagination js-no-pjax {'scroll':'js-donate-scroll'}">
      <?php  echo $this->element('paging_links'); ?>
    </div>
  </div>
<?php } ?>
<?php if (!$this->request->params['isAjax']) { ?>
  </div>
<?php } ?>