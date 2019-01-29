
	<div class="clearfix" itemtype="http://schema.org/Product" itemscope>
		<div itemprop="Name">
			<?php echo $this->Html->link($this->Html->image('donate.png'), array('controller' => 'projects', 'action' => 'discover', 'project_type'=> 'donate' , 'admin' => false), array('class'=> 'zoom-plus js-no-pjax','title' => __l(Configure::read('project.alt_name_for_donate_singular_caps')), 'escape' => false));?>
			<h3 class="h4 zoom-plus">
				<?php echo $this->Html->link(__l(Configure::read('project.alt_name_for_donate_singular_caps')), array('controller' => 'projects', 'action' => 'discover', 'project_type'=> 'donate' , 'admin' => false), array('class'=> 'text-uppercase clr-grn txt js-no-pjax','title' => __l(Configure::read('project.alt_name_for_donate_singular_caps'))));?>
			</h3>
		</div>
		<p class="h4" itemprop="description"><?php echo sprintf(__l("In %s %s, amount is immediately paid to the %s owner and %s gets no %s."), Configure::read('project.alt_name_for_donate_singular_small'), Configure::read('project.alt_name_for_project_plural_small'), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_donor_singular_small'), Configure::read('project.alt_name_for_reward_plural_small')); ?> </p>
	</div>