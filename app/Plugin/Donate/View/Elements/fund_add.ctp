<?php
if(!empty($response_data['donate'])){
$donate = $response_data['donate'];
}
$class = '';
	if (strlen($project['Project']['name']) > 40) {
		$class .= ' title-double-line';
	}
?>
<div>
<section data-offset-top="10" data-spy="" class=" row <?php echo $class; ?>">
    <div class="clearfix">
      <div class="col-sm-1 payment-img marg-top-30"> <?php echo $this->Html->link($this->Html->showImage('Project', $project['Attachment'], array('dimension' => 'medium_thumb', 'alt' => sprintf(__l('[Image: %s]'), $this->Html->cText($project['Project']['name'], false)), 'title' => $this->Html->cText($project['Project']['name'], false), 'class' => 'js-tooltip'),array('aspect_ratio'=>1)), array('controller' => 'projects', 'action' => 'view',  $project['Project']['slug'], 'admin' => false), array('escape' => false)); ?> </div>
      <div class="col-sm-7 pull-left">
        <h3><?php echo $this->Html->link($this->Html->filterSuspiciousWords($this->Html->cText($project['Project']['name'], false), $project['Project']['detected_suspicious_words']), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug']), array('escape' => false));?></h3>
        <p> <?php echo __l('A') . ' '; ?>
          <?php
          $response = Cms::dispatchEvent('View.Project.displaycategory', $this, array(
            'data' => $project
          ));
          if (!empty($response->data['content'])) {
            echo $this->Html->cHtml($response->data['content']);
          }
        ?>
          <?php echo sprintf(__l('%s in '), Configure::read('project.alt_name_for_project_singular_small')) . ' '; ?>
          <?php
          if (!empty($project['City']['name'])) {
            echo $this->Html->cText($project['City']['name'], false) . ', ';
          }
          if (!empty($project['Country']['name'])) {
            echo $this->Html->cText($project['Country']['name']);
          }
        ?>
          <?php echo __l(' by '); ?><?php echo $this->Html->link($this->Html->cText($project['User']['username']), array('controller' => 'users', 'action' => 'view', $project['User']['username']), array('escape' => false));?>

        </p>
      </div>
    </div>
  </section>
</div>
<div class="projectFunds form clearfix row">
    <div>
				<?php
					if (isset($this->request->data['ProjectFund']['wallet']) && $this->request->data['ProjectFund']['payment_gateway_id'] == ConstPaymentGateways::SudoPay && !empty($sudopay_gateway_settings) && $sudopay_gateway_settings['is_payment_via_api'] == ConstBrandType::VisibleBranding) {
						echo $this->element('sudopay_button', array('data' => $sudopay_data, 'cache' => array('config' => 'sec')), array('plugin' => 'Sudopay'));
					} else{
				 ?>
		<div class="col-xs-12">
            <fieldset>
            <legend><?php echo sprintf(__l('%s Amount'),Configure::read('project.alt_name_for_donate_singular_caps')); ?></legend>
            <div>
            <?php
				echo $this->Form->input('latitude',array('type' => 'hidden', 'id'=>'latitude'));
				echo $this->Form->input('longitude',array('type' => 'hidden', 'id'=>'longitude'));
				echo $this->Form->input('project_id',array('type'=>'hidden'));
				if (!empty($donate['Donate']['donate_type_id'])&&($donate['Donate']['donate_type_id'] == ConstDonateTypes::Reward)) {
					echo $this->Form->input('amount',array('type' =>'hidden'));
				}
				if (!empty($donate['Donate']['donate_type_id'])&&($donate['Donate']['donate_type_id'] == ConstDonateTypes::Fixed )) {
					echo $this->Form->input('amount',array('readonly'=>true,'label' => sprintf(__l('%s amount'), Configure::read('project.alt_name_for_donate_singular_caps')) .' ('.Configure::read('site.currency').')'));
				} else {
					echo $this->Form->input('amount',array('label' => sprintf(__l('%s amount'), Configure::read('project.alt_name_for_donate_singular_caps')) .' ('.Configure::read('site.currency').')'));
				}?>
            </div>
            </fieldset>
		</div>
           	<div class="col-md-12">
                 <fieldset>
        			<legend><?php echo __l('Personalize your Donation'); ?></legend>
        			<div class="group-block personal-radio">
        			<?php echo $this->Form->input('is_anonymous',array('type' =>'radio','default'=>ConstAnonymous::None,'options'=>$radio_options,'legend'=>false));?>
        			</div>
        		 </fieldset>
            </div>
		<div class="col-md-12">
		   <div class="row">
	              <?php echo $this->element('donate-faq', array('cache' => array('config' => 'sec')),array('plugin'=>'Donate')); ?>
	            </div>
	        </div>
	    </div>
	  	</div>
    	<div class="col-xs-12">
            	 <legend><?php echo __l('Select Payment Type'); ?></legend>
                  <?php  echo $this->element('payment-get_gateways', array('model'=>'ProjectFund','type'=>'is_enable_for_donate','is_enable_wallet'=>1, 'project_type'=>$project['ProjectType']['name'],'user_id' =>$project['Project']['user_id'], 'cache' => array('config' => 'sec')));?>
            	
        </div>
		<?php } ?>
	