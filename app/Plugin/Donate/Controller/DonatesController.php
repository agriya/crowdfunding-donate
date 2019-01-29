<?php
/**
 * CrowdFunding
 *
 * PHP version 5
 *
 * @category   PHP
 * @package    Crowdfunding
 * @subpackage Core
 * @author     Agriya <info@agriya.com>
 * @copyright  2018 Agriya Infoway Private Ltd
 * @license    http://www.agriya.com/ Agriya Infoway Licence
 * @link       http://www.agriya.com
 */
class DonatesController extends AppController
{
    public $name = 'Donates';
    public function beforeFilter() 
    {
        $this->Security->disabledFields = array(
            'Project.id',
        );
        parent::beforeFilter();
    }
    public function overview() 
    {
        $user_id = $this->Auth->user('id');
        if (!empty($user_id)) {
            $periods = array(
                'day' => array(
                    'display' => __l('Today') ,
                    'conditions' => array(
                        'Project.created =' => date('Y-m-d', strtotime('now')) ,
                    )
                ) ,
                'week' => array(
                    'display' => __l('This Week') ,
                    'conditions' => array(
                        'Project.created =' => date('Y-m-d', strtotime('now -7 days')) ,
                    )
                ) ,
                'month' => array(
                    'display' => __l('This Month') ,
                    'conditions' => array(
                        'Project.created =' => date('Y-m-d', strtotime('now -30 days')) ,
                    )
                ) ,
                'total' => array(
                    'display' => __l('Total') ,
                    'conditions' => array()
                )
            );
            $models[] = array(
                'Transaction' => array(
                    'display' => __l('Cleared') ,
                    'projectconditions' => array(
                        'Project.user_id' => $user_id,
                        'Donate.donate_project_status_id' => array(
                            ConstDonateProjectStatus::FundingClosed,
                            ConstDonateProjectStatus::OpenForDonating,
                        )
                    ) ,
                    'alias' => 'Cleared',
                    'type' => 'cInt',
                    'isSub' => 'Project',
                    'class' => 'highlight-cleared'
                )
            );
            $models[] = array(
                'Transaction' => array(
                    'display' => __l('Pipeline') ,
                    'projectconditions' => array(
                        'Project.user_id' => $user_id,
                        'Donate.donate_project_status_id' => array(
                            ConstDonateProjectStatus::Pending,
                            ConstDonateProjectStatus::OpenForDonating,
                            ConstDonateProjectStatus::OpenForIdea,
                        )
                    ) ,
                    'alias' => 'Pipeline',
                    'type' => 'cInt',
                    'isSub' => 'Projects',
                    'class' => 'highlight-pipeline'
                )
            );
            foreach($models as $unique_model) {
                foreach($unique_model as $model => $fields) {
                    foreach($periods as $key => $period) {
                        if ($fields['alias'] == 'Cleared') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::ProjectBacked
                            ));
                        } elseif ($fields['alias'] == 'Pipeline') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::ProjectBacked
                            ));
                        } elseif ($fields['alias'] == 'PipelineReverse') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::Refunded
                            ));
                        } elseif ($fields['alias'] == 'Lost') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::Refunded
                            ));
                        }
                        $conditions = $period['conditions'];
                        if (!empty($fields['conditions'])) {
                            $conditions = array_merge($periods[$key]['conditions'], $fields['conditions']);
                        }
                        $projectConditions = array(
                            'Project.user_id' => $this->Auth->user('id')
                        );
                        if (!empty($fields['projectconditions'])) {
                            $projectConditions = $fields['projectconditions'];
                        }
                        $project_list = $this->Donate->Project->find('list', array(
                            'conditions' => $projectConditions,
                            'fields' => array(
                                'Project.id',
                            ) ,
                            'recursive' => 1
                        ));
                        $conditions['ProjectFund.project_id'] = $project_list;
                        $conditions['Transaction.class'] = 'ProjectFund';
                        $aliasName = !empty($fields['alias']) ? $fields['alias'] : $model;
                        $result = $this->Donate->Project->Transaction->find('first', array(
                            'fields' => array(
                                'SUM(Transaction.amount) as amount',
                            ) ,
                            'conditions' => $conditions,
                            'recursive' => 1
                        ));
                        $this->set($aliasName . $key, $result[0]['amount']);
                    }
                }
            }
        }
        $this->set(compact('periods', 'models'));
    }
    public function myprojects() 
    {
        $this->pageTitle = sprintf(__l('%s - %s') , Configure::read('project.alt_name_for_donate_singular_caps') , Configure::read('project.alt_name_for_project_plural_caps'));
        $conditions['Project.project_type_id'] = ConstProjectTypes::Donate;
        $conditions['Project.user_id'] = $this->Auth->user('id');
        $order = array(
            'Project.project_end_date' => 'asc'
        );
        if (!$this->Auth->user('id')) {
            if ($this->RequestHandler->prefers('json')){
                $this->set('iphone_response', array("message" =>__l('Invalid request') , "error" => 1));
            }else{
                throw new NotFoundException(__l('Invalid request'));
            }
        }
        if (!empty($this->request->params['named']['status'])) {
            if ($this->request->params['named']['status'] == 'draft') {
                $conditions['Project.is_draft'] = 1;
            } elseif ($this->request->params['named']['status'] == 'pending') {
                $conditions['Donate.donate_project_status_id'] = ConstDonateProjectStatus::Pending;
            } elseif ($this->request->params['named']['status'] == 'idea') {
                $conditions['Donate.donate_project_status_id'] = ConstDonateProjectStatus::OpenForIdea; 
            } elseif ($this->request->params['named']['status'] == 'open_for_donating') {
                $conditions['Donate.donate_project_status_id'] = ConstDonateProjectStatus::OpenForDonating;
            } elseif ($this->request->params['named']['status'] == 'closed') {
                $conditions['Donate.donate_project_status_id'] = ConstDonateProjectStatus::FundingClosed;
            } elseif ($this->request->params['named']['status'] == 'expired') {
                $conditions['Donate.donate_project_status_id'] = ConstDonateProjectStatus::FundingExpired;
                unset($conditions['Project.project_end_date >= ']);
            } elseif ($this->request->params['named']['status'] != 'all') {
                $conditions['Donate.donate_project_status_id'] = $this->request->params['named']['status'];
            }
        } 
	//Todo: Need to change for default status 
	/*else {
            $conditions['Donate.donate_project_status_id'] = ConstDonateProjectStatus::OpenForDonating;
        }*/
        $contain = array(
            'Project' => array(
                'ProjectType',
                'User' => array(
                    'UserAvatar'
                ) ,
                'Message' => array(
                    'conditions' => array(
                        'Message.is_activity' => 0,
                        'Message.is_sender' => 0
                    ) ,
                ) ,
                'Attachment',
                'Transaction',
            ) ,
            'DonateProjectStatus',
        );
        if (isPluginEnabled('Idea')) {
            $contain['Project']['ProjectRating'] = array(
                'conditions' => array(
                    'ProjectRating.user_id' => $this->Auth->user('id') ,
                )
            );
        }
        if (!isPluginEnabled('Idea')) {
            $conditions['Donate.donate_project_status_id !='] = ConstDonateProjectStatus::OpenForIdea;
        }
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'order' => $order,
            'recursive' => 3,
            'limit' => 20,
        );
        $projects = $this->paginate();
        $this->set('projects', $projects);
        if ($this->RequestHandler->prefers('json') && !empty($this->request->query['key'])) {
            $event_data['contain'] = $contain;
            $event_data['conditions'] = $conditions;
            $event_data['order'] = $order;
            $event_data['limit'] = 20;
            $event_data['model'] = "Donate";
            $event_data = Cms::dispatchEvent('Controller.Donate.myprojects', $this, array(
                'data' => $event_data
            ));
        }
        $donateStatuses = $this->Donate->DonateProjectStatus->find('list', array(
            'recursive' => -1
        ));
        if (!isPluginEnabled('Idea')) {
            unset($donateStatuses[ConstDonateProjectStatus::OpenForIdea]);
        }
        $this->set(compact('donateStatuses'));
        $projectStatuses = array();
        foreach($donateStatuses as $key => $status) {
            $status_condition = array(
                'Donate.donate_project_status_id ' => $key,
                'Project.user_id' => $this->Auth->user('id')
            );
            $project_status = $this->Donate->Project->find('count', array(
                'conditions' => $status_condition,
                'contain' => array(
                    'Donate'
                ) ,
                'recursive' => 0
            ));
            $projectStatuses[$key] = $project_status;
        }
        $this->set('system_drafted', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_draft = ' => 1,
                'Project.user_id' => $this->Auth->user('id') ,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('projectStatuses', $projectStatuses);
        $this->set('draft_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_draft = ' => 1,
                'Project.user_id' => $this->Auth->user('id') ,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('all_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_active' => 1,
                'Project.user_id' => $this->Auth->user('id') ,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        if (!empty($this->request->params['named']['from'])) {
            $this->render('project_filter');
        }
        $countDetail = $this->Donate->Project->getAdminRejectApproveCount(ConstProjectTypes::Donate, ConstDonateProjectStatus::Pending, 'Donate', 'Donate.donate_project_status_id');
        $this->set('formFieldSteps', $countDetail['formFieldSteps']);
        $this->set('rejectedCount', $countDetail['rejectedCount']);
        $this->set('approvedCount', $countDetail['approvedCount']);
        $this->set('rejectedProjectIds', $countDetail['rejectedProjectIds']);
        $this->set('approvedProjectIds', $countDetail['approvedProjectIds']);
    }
    public function myfunds() 
    {
        $this->pageTitle = __l('My Donations');
        $conditions = array();
        $this->loadModel('Projects.ProjectFund');
        $conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Donate;
        $conditions['ProjectFund.user_id'] = $this->Auth->user('id');
        if (isset($this->request->params['named']['status'])) {
            if ($this->request->params['named']['status'] == 'refunded') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Expired;
            } else if ($this->request->params['named']['status'] == 'paid') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaidToOwner;
            } else if ($this->request->params['named']['status'] == 'cancelled') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Canceled;
            }
        }
        $this->set('fund_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id' => $this->Auth->user('id') ,
                'ProjectFund.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('refunded_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id = ' => $this->Auth->user('id') ,
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Expired,
                'ProjectFund.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('paid_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id = ' => $this->Auth->user('id') ,
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaidToOwner,
                'ProjectFund.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('cancelled_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id = ' => $this->Auth->user('id') ,
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Canceled,
                'ProjectFund.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $contain = array(
            'User' => array(
                'UserAvatar'
            ) ,
            'Project' => array(
                'User' => array(
                    'fields' => array(
                        'User.username',
                        'User.id'
                    )
                ) ,
                'Donate' => array(
                    'DonateProjectStatus'
                ) ,
                'Attachment',
            )
        );
        $paging_array = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'recursive' => 3,
            'order' => array(
                'ProjectFund.id' => 'desc'
            )
        );
        $limit = 20;
        if (!empty($limit)) {
            $paging_array['limit'] = $limit;
        }
        $this->paginate = $paging_array;
        $this->set('projectFunds', $this->paginate('ProjectFund'));
		$this->set('all_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id' => $this->Auth->user('id') ,
                'ProjectFund.project_type_id' => ConstProjectTypes::Donate
            )
        )));
        $conditions['ProjectFund.is_given'] = 1;
        $conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Donate;
        $this->set('given_count', $this->ProjectFund->find('count', array(
            'conditions' => $conditions
        )));
        if (!empty($this->request->params['named']['from'])) {
            $this->render('myfunds');
        }
    }
    function admin_index() 
    {
        $this->_redirectGET2Named(array(
            'filter_id',
            'category_id',
            'q'
        ));
		if (!empty($this->request->data['Project']['q'])) {
			$this->request->params['named']['q'] = $this->request->data['Project']['q'];
		}
		App::import('Model', 'Projects.FormFieldStep');
		$FormFieldStep = new FormFieldStep();
		$formFieldSteps = $FormFieldStep->find('list', array(
				'conditions' => array(
						'FormFieldStep.project_type_id' => ConstProjectTypes::Donate,
						'FormFieldStep.is_splash' => 1
				) ,
				'fields' => array(
						'FormFieldStep.order',
						'FormFieldStep.name'
				) ,
				'recursive' => -1
		));
		$this->set('formFieldSteps', $formFieldSteps);
        $this->pageTitle = Configure::read('project.alt_name_for_donate_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps');
        $conditions = array();
        $conditions['Project.project_type_id'] = ConstProjectTypes::Donate;
        // check the filer passed through named parameter
        if (isset($this->request->params['named']['filter_id'])) {
            $this->request->data['Project']['filter_id'] = $this->request->params['named']['filter_id'];
        }
        if (!empty($this->request->data['Project']['filter_id'])) {
            if ($this->request->data['Project']['filter_id'] == ConstMoreAction::Suspend) {
                $conditions['Project.is_admin_suspended'] = 1;
                $this->pageTitle.= ' - ' . __l('Suspended');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Active) {
                $conditions['Project.is_active'] = 1;
                $this->pageTitle.= ' - ' . __l('Active');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Inactive) {
                $conditions['Project.is_active'] = 0;
                $this->pageTitle.= ' - ' . __l('Inactive');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Flagged) {
                $conditions['Project.is_system_flagged'] = 1;
                $this->pageTitle.= ' - ' . __l('System Flagged');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::UserFlagged) {
                $conditions['Project.is_user_flagged'] = 1;
                $this->pageTitle.= ' - ' . __l('User Flagged');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Drafted) {
                $conditions['Project.is_draft'] = 1;
                $this->pageTitle.= ' - ' . __l('Drafted');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Featured) {
                $conditions['Project.is_featured'] = 1;
                $this->pageTitle.= ' - ' . __l('Featured');
            }
            $this->request->params['named']['filter_id'] = $this->request->data['Project']['filter_id'];
        }
        if (!empty($this->request->data['Project']['project_status_id'])) {
            $this->request->params['named']['project_status_id'] = $this->request->data['Project']['project_status_id'];
            $conditions['Donate.donate_project_status_id'] = $this->request->data['Project']['project_status_id'];
        } elseif (!empty($this->request->params['named']['project_status_id'])) {
            $this->request->data['Project']['project_status_id'] = $this->request->params['named']['project_status_id'];
            $conditions['Donate.donate_project_status_id'] = $this->request->data['Project']['project_status_id'];
        } elseif (!empty($this->request->params['named']['is_allow_over_donating'])) {
            $this->request->data['Donate']['is_allow_over_donating'] = $this->request->params['named']['is_allow_over_donating'];
            $conditions['Project.is_allow_over_donating'] = $this->request->data['Donate']['is_allow_over_donating'];
        } elseif (!empty($this->request->params['named']['transaction_type_id']) && $this->request->params['named']['transaction_type_id'] == ConstTransactionTypes::ListingFee) {
            $this->pageTitle.= ' - ' . __l('Listing Fee Paid');
            $this->request->data['Project']['transaction_type_id'] = $this->request->params['named']['transaction_type_id'];
            $foreigns = $this->Donate->Project->Transaction->find('list', array(
                'conditions' => array(
                    'Transaction.class = ' => 'Project',
                    'Transaction.transaction_type_id = ' => ConstTransactionTypes::ListingFee,
                    'Project.project_type_id' => ConstProjectTypes::Donate
                ) ,
                'fields' => array(
                    'Transaction.foreign_id'
                ) ,
                'recursive' => 0
            ));
            $conditions['Project.id'] = $foreigns;
        }
        if (!empty($this->request->data['Project']['project_status_id']) or !empty($this->request->data['Project']['project_status_id'])) {
            switch ($conditions['Donate.donate_project_status_id']) {
                case ConstDonateProjectStatus::Pending:
                    $this->pageTitle.= ' - ' . __l('Pending');
                    break;

                case ConstDonateProjectStatus::OpenForDonating:
                    $this->pageTitle.= ' - ' . sprintf(__l('Open for %s') , Configure::read('project.alt_name_for_donate_present_continuous_caps'));
                    break;

                case ConstDonateProjectStatus::OpenForIdea:
                    $this->pageTitle.= ' - ' . __l('Open for Voting');
                    break;

                case ConstDonateProjectStatus::FundingClosed:
                    $this->pageTitle.= ' - ' . __l('Funding Closed');
                    break;

                case ConstDonateProjectStatus::FundingExpired:
                    $this->pageTitle.= ' - ' . __l('Funding Expired');
                    break;

                case ConstDonateProjectStatus::PendingAction:
                    $this->pageTitle.= ' - ' . __l('Pending Action to Admin');
                    break;

                default:
                    break;
            }
        }
        if (isset($this->request->params['named']['q'])) {
            $conditions['AND']['OR'][]['Project.name LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['User.username LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $this->pageTitle.= sprintf(__l(' - Search - %s') , $this->request->params['named']['q']);
            $this->request->data['Project']['q'] = $this->request->params['named']['q'];
        }
        if (!empty($this->request->params['named']['type']) && $this->request->params['named']['type'] == 'listing_fee') {
            $conditions['Project.fee_amount !='] = '0.00';
        }
        if (!empty($this->request->params['named']['project_flag_category_id'])) {
            $project_flag = $this->Donate->Project->ProjectFlag->find('list', array(
                'conditions' => array(
                    'ProjectFlag.project_flag_category_id' => $this->request->params['named']['project_flag_category_id'],
                    'Project.project_type_id' => ConstProjectTypes::Donate
                ) ,
                'fields' => array(
                    'ProjectFlag.id',
                    'ProjectFlag.project_id'
                ) ,
                'recursive' => -1
            ));
            $conditions['Project.id'] = $project_flag;
        }
        if (!empty($this->request->params['named']['project_category_id'])) {
            $conditions['Donate.donate_project_category_id'] = $this->request->params['named']['project_category_id'];
            $user = $this->Donate->DonateProjectCategory->find('first', array(
                'conditions' => array(
                    'DonateProjectCategory.id' => $this->request->params['named']['project_category_id']
                ) ,
                'fields' => array(
                    'DonateProjectCategory.id',
                    'DonateProjectCategory.name'
                ) ,
                'recursive' => -1
            ));
            if (empty($user)) {
                throw new NotFoundException(__l('Invalid request'));
            }
            $this->pageTitle.= ' - ' . $user['DonateProjectCategory']['name'];
        } elseif (!empty($this->request->params['named']['user_id'])) {
            $user = $this->{$this->modelClass}->User->find('first', array(
                'conditions' => array(
                    'User.id' => $this->request->params['named']['user_id']
                ) ,
                'fields' => array(
                    'User.id',
                    'User.username'
                ) ,
                'recursive' => -1
            ));
            if (empty($user)) {
                throw new NotFoundException(__l('Invalid request'));
            }
            $conditions['Project.user_id'] = $this->request->params['named']['user_id'];
            $this->pageTitle.= ' - ' . $user['User']['username'];
        }
        $contain = array(
            'User',
            'ProjectType',
            'Attachment',
            'Donate' => array(
                'DonateProjectStatus',
                'DonateProjectCategory'
            ) ,
            'Ip' => array(
                'City' => array(
                    'fields' => array(
                        'City.name',
                    )
                ) ,
                'State' => array(
                    'fields' => array(
                        'State.name',
                    )
                ) ,
                'Country' => array(
                    'fields' => array(
                        'Country.name',
                        'Country.iso_alpha2',
                    )
                ) ,
                'fields' => array(
                    'Ip.ip',
                    'Ip.latitude',
                    'Ip.longitude',
                    'Ip.host'
                )
            ) ,
        );
        if (!empty($this->request->data['Project']['project_status_id']) && $this->request->data['Project']['project_status_id'] == ConstDonateProjectStatus::PendingAction) {
            $conditions['Project.is_pending_action_to_admin'] = 1;
            unset($conditions['Donate.donate_project_status_id']);
            App::import('Model', 'Projects.FormFieldStep');
            $FormFieldStep = new FormFieldStep();
            $splashStep = $FormFieldStep->find('first', array(
                'conditions' => array(
                    'FormFieldStep.project_type_id' => ConstProjectTypes::Donate,
                    'FormFieldStep.is_splash' => 1
                ) ,
                'fields' => array(
                    'FormFieldStep.order'
                ) ,
                'recursive' => -1
            ));
            $this->set('splashStep', $splashStep['FormFieldStep']['order']);
        }
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'order' => array(
                'Project.id' => 'desc'
            ) ,
            'recursive' => 3
        );
        /// Status Based on Count
        $this->set('opened_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::OpenForDonating,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('idea_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::OpenForIdea,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('pending_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::Pending,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('allow_overfunding', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.is_allow_over_donating = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('closed_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::FundingClosed,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('open_for_idea', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::OpenForIdea,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('paid_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Donate,
                'Project.is_paid' => 1
            ) ,
            'recursive' => 0
        )));
        $this->set('expired_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::FundingExpired,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        // total openid users list
        $this->set('suspended', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_admin_suspended = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('user_flagged', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_user_flagged = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('system_flagged', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_system_flagged = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('system_drafted', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_draft = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('successful_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_successful = ' => 1,
                'Donate.donate_project_status_id' => ConstDonateProjectStatus::FundingClosed,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('failed_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_successful = ' => 0,
                'Donate.donate_project_status_id' => ConstDonateProjectStatus::FundingClosed,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('active_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_active' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('inactive_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_active' => 0,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('featured_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_featured' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('total_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('projects', $this->paginate('Project'));
        $filters = $this->Donate->Project->isFilterOptions;
        $moreActions = $this->Donate->Project->moreActions;
        if (empty($this->request->data['Project']['project_status_id']) || $this->request->data['Project']['project_status_id'] != ConstDonateProjectStatus::FundingClosed) {
            unset($moreActions[ConstMoreAction::Successful]);
            unset($moreActions[ConstMoreAction::Failed]);
        }
        $projectStatuses = $this->Donate->DonateProjectStatus->find('list', array(
            'conditions' => array(
                'DonateProjectStatus.is_active' => 1
            )
        ));
        $this->set('moreActions', $moreActions);
        $this->set('filters', $filters);
        $this->set('projectStatuses', $projectStatuses);
        if (!empty($this->request->data['Project']['project_status_id']) && $this->request->data['Project']['project_status_id'] == ConstDonateProjectStatus::PendingAction) {
            $this->set('step_count', $this->Donate->Project->getStepCount(ConstProjectTypes::Donate));
            $this->render('admin_index_pending');
        }
    }
    public function admin_funds() 
    {
        $this->_redirectPOST2Named(array(
            'q'
        ));
        $this->loadModel('Projects.ProjectFund');
        $this->pageTitle = sprintf(__l('%s %s Funds') , Configure::read('project.alt_name_for_donate_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'));
        $conditions = array();
        $project_ids = $this->Donate->find('list', array(
            'conditions' => array(
                'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForDonating
            ) ,
            'fields' => array(
                'Donate.project_id'
            ) ,
            'recursive' => -1
        ));
        $conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Donate;
        if (!empty($this->request->params['named']['project'])) {
            $conditions['ProjectFund.project_id'] = $this->request->params['named']['project'];
            $project_name = $this->ProjectFund->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $this->request->params['named']['project'],
                ) ,
                'fields' => array(
                    'Project.name',
                ) ,
                'recursive' => -1,
            ));
            $this->pageTitle.= ' - ' . $project_name['Project']['name'];
        }
        if (!empty($this->request->params['named']['project_id'])) {
            $conditions['ProjectFund.project_id'] = $this->request->params['named']['project_id'];
            $project_name = $this->ProjectFund->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $this->request->params['named']['project_id'],
                ) ,
                'fields' => array(
                    'Project.name',
                ) ,
                'recursive' => -1,
            ));
            $this->pageTitle.= ' - ' . $project_name['Project']['name'];
        } elseif (!empty($this->request->params['named']['user_id'])) {
            $conditions['ProjectFund.user_id'] = $this->request->params['named']['user_id'];
            $user = $this->{$this->modelClass}->User->find('first', array(
                'conditions' => array(
                    'User.id' => $this->request->params['named']['user_id']
                ) ,
                'fields' => array(
                    'User.id',
                    'User.username'
                ) ,
                'recursive' => -1
            ));
            if (empty($user)) {
                throw new NotFoundException(__l('Invalid request'));
            }
            $this->pageTitle.= ' - ' . $user['User']['username'];
        }
        if (!empty($this->request->params['named']['q'])) {
            $conditions['AND']['OR'][]['User.username LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['Project.name LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['Project.description LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['Project.short_description LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $this->pageTitle.= sprintf(__l(' - Search - %s') , $this->request->params['named']['q']);
            $this->request->data['ProjectFund']['q'] = $this->request->params['named']['q'];
        }
        $contain = array(
            'Project' => array(
                'Donate' => array(
                    'DonateProjectStatus'
                )
            ) ,
            'User',
        );
        if (isPluginEnabled('ProjectRewards')) {
            $contain['ProjectReward'] = array();
        }
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'order' => array(
                'ProjectFund.id' => 'desc'
            ) ,
            'recursive' => 3
        );
        $this->set('projectFunds', $this->paginate('ProjectFund'));
    }
    public function admin_donate_svg() 
    {
        $this->loadModel('Projects.FormFieldStep');
        $formFieldStep = $this->FormFieldStep->find('count', array(
            'conditions' => array(
                'FormFieldStep.is_splash' => 1,
                'FormFieldStep.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        ));
        $this->set('formFieldStep', $formFieldStep);
        /// Status Based on Count
        $this->set('opened_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForDonating,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('pending_action_to_admin_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.is_pending_action_to_admin' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        )));
        $this->set('pending_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::Pending,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('closed_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::FundingClosed,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('open_for_idea', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::OpenForIdea,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->set('paid_projects', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Donate,
                'Project.is_paid' => 1
            ) ,
            'recursive' => 0
        )));
        $this->set('expired_project_count', $this->Donate->Project->find('count', array(
            'conditions' => array(
                'Donate.donate_project_status_id = ' => ConstDonateProjectStatus::FundingExpired,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => 0
        )));
        $this->layout = "ajax";
    }
}
?>