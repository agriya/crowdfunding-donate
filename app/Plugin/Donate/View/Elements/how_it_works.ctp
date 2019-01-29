<?php
  if (isPluginEnabled('Idea')) {
    $is_ideaEnabled = 1;
  }
  $payment_methods_donate = '';
  $paypal_flag = 0;
  $paypal_branch_class = '';
  $paypal_fee_donate = '';
  $paypal_text = '';
  $paypal_text2 = '';
  if(isPluginEnabled('Paypal')) {
		$payment_methods_donate = 'PayPal payment';
		if (Configure::read('Project.payment_gateway_fee_id') == ConstPaymentGatewayFee::Seller) {
			$paypal_fee_donate = __l(', payment gateway fee approx 2.90% charged runtime by PayPal');
		} elseif (Configure::read('Project.payment_gateway_fee_id') == ConstPaymentGatewayFee::SiteAndSeller) {
			$paypal_fee_donate = __l(', payment gateway fee approx 2.90%(shared with admin) charged runtime by PayPal');
		}
    $paypal_flag = 1;
    $paypal_branch_class = 'class="branch last-list"';
    $paypal_text = 'site capture fund and ';
    $paypal_text2 = 'site will capture fund and ';
  }
  if(isPluginEnabled('Sudopay')) {
	  if (!empty($supported_gateways)) {
		  $payment_methods_donate.= implode(' / ', $supported_gateways);
	  }
  }
  if(isPluginEnabled('Wallet')) {
		if(!empty($payment_methods_donate)){
			$payment_methods_donate.= ' / ';
		}
		$payment_methods_donate.= 'Wallet';
  }
?>
	<div class="clearfix"></div>
    <div class="donate clearfix">
      <div class="page-header clearfix mspace no-pad">
			<?php
				if(empty($this->request->params['plugin']) && $this->request->params['controller'] == 'nodes') {
			?>
					<h4 class="text-b no-mar"><?php echo __l(Configure::read('project.alt_name_for_donate_singular_caps'));?></h4>
			<?php
				} else if($this->request->params['plugin'] == 'projects' && $this->request->params['controller'] == 'projects') {
			?>
					<h3 class="h2 roboto-bold text-center"><?php echo __l('How It Works');?>
						<sup><?php echo $this->Html->image('quesion-circle.png', array('alt' => __l('[Image: Quesion Circle]'))); ?> </sup>
					</h3>
					<p class="h3 text-center marg-btom-30"><?php echo __l('People immediately pay to you. Can\'t offer rewards.');?></p>
			<?php
				}
			?>
		</div>
      <div class="col-sm-6 top-mspace">
        <div class="project_guideline">
          <ul class="project-guideline-block list-unstyled primaryNav project-owner">
            <li class="home"><span class="btn btn-success"><?php echo __l(Configure::read('project.alt_name_for_donate_project_owner_singular_caps')); ?> </span>
              <ul class="list-unstyled">
                <?php
                if(!empty($is_ideaEnabled)) {
                ?>
                  <li><span><?php echo sprintf(__l('Adds an %s'), 'Idea'); ?> </span></li>
                  <li>
                    <span>
                      <?php
                        echo sprintf(__l('Admin moves the %s for %s'), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_donate_present_continuous_small'));
                      ?>
                    </span>
					<ul class="list-unstyled  first">
					  <li class ="offset"><span><?php echo sprintf(__l('Expired (No %s for %s) '), Configure::read('project.alt_name_for_donate_present_continuous_small'), Configure::read('project.alt_name_for_project_singular_small')); ?> </span></li>
					</ul>
                  </li>
                <?php } else { ?>
                  <li><span><?php echo sprintf(__l('Adds a %s'), Configure::read('project.alt_name_for_project_singular_caps')); ?> </span>
					<ul class="list-unstyled  first">
					  <li class ="offset"><span><?php echo sprintf(__l('Expired (No %s for %s) '), Configure::read('project.alt_name_for_donate_present_continuous_small'), Configure::read('project.alt_name_for_project_singular_small')); ?> </span></li>
					</ul>
				  </li>
                <?php } ?>
                  <li class="branch last-list">
                    <span>
                    <?php
                      echo sprintf(__l('%s funds a %s'), Configure::read('project.alt_name_for_donor_singular_caps'), Configure::read('project.alt_name_for_project_singular_small'));
                      if(!empty($payment_methods_donate)) {
                        echo ' through '. $payment_methods_donate;
                      }
                    ?>
                    </span>
                    <?php if(!empty($paypal_flag)) {?>
                      <ul class="list-unstyled  first">
                        <li class ="offset"><span><?php echo sprintf(__l('Receiver %s, Marketplace Receiver site'), Configure::read('project.alt_name_for_donate_project_owner_singular_small')); ?> </span></li>
                      </ul>
                    <?php } ?>
                  </li>
                  <li <?php echo $paypal_branch_class; ?>><span><?php echo sprintf(__l('Immediate Payment'), Configure::read('project.alt_name_for_project_singular_caps')) . ' <span class="show">' . sprintf($paypal_text.__l('transfer amount to %s after deduct the site commission'), Configure::read('project.alt_name_for_donate_project_owner_singular_small')) . '</span>'; ?></span></li>
                  <li>
                    <span>
                      <?php
                      echo __l('Amount Received = Fund Amount - Site Fee');
                      if (isPluginEnabled('Paypal') && Configure::read('Project.payment_gateway_fee_id') != ConstPaymentGatewayFee::Site) {
                        echo __l(' - Payment Gateway Fee');
                      }
                      echo '<br /><span class="show">'.__l('Site fee ') . Configure::read('Project.fund_commission_percentage') . '% ' . $paypal_fee_donate .'</span>';
                      ?>
                    </span>
                  </li>
                  <li><span><?php echo sprintf(__l('%s Closed'), Configure::read('project.alt_name_for_project_singular_caps')); ?> </span></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-sm-6 top-mspace">
        <div class="project_guideline">
          <ul class="project-guideline-block list-unstyled primaryNav project-owner">
            <li class="home"><span class="btn btn-success"><?php echo __l(Configure::read('project.alt_name_for_donor_singular_caps')); ?> </span>
              <ul class="list-unstyled">
                <?php
                if(!empty($is_ideaEnabled)) {
                ?>
                  <li>
                    <span>
                      <?php
                        echo sprintf(__l('Votes an %s'), __l('Idea'));
                      ?>
                    </span>
                  </li>
                  <li>
                    <span>
                      <?php
                        echo sprintf(__l('Admin moves the %s for %s'), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_donate_present_continuous_small'));
                      ?>
                    </span>
                  </li>
                <?php } ?>
                  <li>
                    <span>
                      <?php
                        echo sprintf(__l('Funds a %s'), Configure::read('project.alt_name_for_project_singular_small'));
                        if(!empty($payment_methods_donate)) {
                          echo ' through '. $payment_methods_donate;
                        }
                      ?>
                    </span>
                    <?php if(!empty($paypal_flag)) {?>
                      <ul class="list-unstyled  first">
                        <li class ="offset"><span><?php echo __l('Makes the payment through PayPal adaptive payment') . ' <span class="show">' . __l('No site fee collected.') . '</span>'; ?> </span></li>
                      </ul>
                    <?php } ?>
                  </li>
                  <li <?php echo $paypal_branch_class; ?>><span><?php echo sprintf(__l('Immediate Payment'), Configure::read('project.alt_name_for_project_singular_caps')) . '<span class="show">' . sprintf($paypal_text2.__l('transfer amount to %s'), Configure::read('project.alt_name_for_donate_project_owner_singular_small')) . '</span>'; ?> </span></li>
			  </ul>
            </li>
          </ul>
        </div>
      </div>
    </div>