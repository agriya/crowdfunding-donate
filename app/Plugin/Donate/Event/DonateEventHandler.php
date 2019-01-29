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
class DonateEventHandler extends Object implements CakeEventListener
{
    /**
     * implementedEvents
     *
     * @return array
     */
    public function implementedEvents() 
    {
        return array(
            'View.Project.displaycategory' => array(
                'callable' => 'onCategorydisplay'
            ) ,
            'View.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox'
            ) ,
            'Controller.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox'
            ) ,
            'Behavior.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox',
            ) ,
            'View.Project.onCategoryListing' => array(
                'callable' => 'onCategoryListingRender',
            ) ,
            'View.Project.projectStatusValue' => array(
                'callable' => 'getProjectStatusValue'
            ) ,
            'Model.Project.beforeAdd' => array(
                'callable' => 'onProjectValidation',
            ) ,
            'Controller.Projects.afterAdd' => array(
                'callable' => 'onProjectAdd',
            ) ,
            'Controller.Projects.afterEdit' => array(
                'callable' => 'onProjectEdit',
            ) ,
            'Controller.ProjectFunds.beforeAdd' => array(
                'callable' => 'isAllowAddFund',
            ) ,
            'Controller.ProjectFunds.beforeValidation' => array(
                'callable' => 'onProjectFundValidation',
            ) ,
            'Controller.ProjectFunds.afterAdd' => array(
                'callable' => 'onProjectFundAdd',
            ) ,
            'Controller.Project.openFunding' => array(
                'callable' => 'onOpenFunding',
            ) ,
            'Model.Project.openFunding' => array(
                'callable' => 'onOpenFunding',
            ) ,
            'Controller.ProjectType.projectIds' => array(
                'callable' => 'onMessageDisplay',
            ) ,
            'Controller.ProjectType.ClosedProjectIds' => array(
                'callable' => 'getClosedProjectIds',
            ) ,
            'Controller.ProjectType.getConditions' => array(
                'callable' => 'getConditions',
            ) ,
            'Controller.ProjectType.getContain' => array(
                'callable' => 'getContain',
            ) ,
            'Controller.ProjectType.getProjectTypeStatus' => array(
                'callable' => 'getProjectTypeStatus',
            ) ,
            'View.Project.howitworks' => array(
                'callable' => 'howitworks',
                'priority' => 2
            ) ,
            'View.AdminDasboard.onActionToBeTaken' => array(
                'callable' => 'onActionToBeTakenRender'
            ) ,
            'Controller.FeatureProject.getConditions' => array(
                'callable' => 'getFeatureProjectList'
            ) ,
        );
    }
    /**
     * onCategoryListing
     *
     * @param CakeEvent $event
     * @return void
     */
    public function onCategoryListingRender($event) 
    {
        $content = '';
        if (!empty($event->data['data']['project_type']) && $event->data['data']['project_type'] == Configure::read('project.alt_name_for_donate_singular_small')) {
            $view = $event->subject();
            App::import('Model', 'Donate.DonateProjectCategory');
            $this->DonateProjectCategory = new DonateProjectCategory();
            $projectCategories = $this->DonateProjectCategory->find('all', array(
                'fields' => array(
                    'DonateProjectCategory.name',
                    'DonateProjectCategory.slug'
                ) ,
                'limit' => 10,
                'order' => 'DonateProjectCategory.name asc'
            ));
            if (!empty($projectCategories)) {
                $content = '<h4>' . __l('Filter by Category') . '</h4>
        	     <ul class="nav navbar-nav nav-tabs nav-stacked">';
                foreach($projectCategories as $project_category) {
                    $class = (!empty($event->data['data']['category']) && $event->data['data']['category'] == $project_category['DonateProjectCategory']['slug']) ? ' class="active"' : null;
                    $content.= '<li' . $class . '>' . $view->Html->link($project_category['DonateProjectCategory']['name'], array(
                        'controller' => 'projects',
                        'action' => 'index',
                        'category' => $project_category['DonateProjectCategory']['slug'],
                        'project_type' => Configure::read('project.alt_name_for_donate_singular_small') ,
                    ) , array(
                        'title' => $project_category['DonateProjectCategory']['name']
                    )) . '</li>';
                }
                $content.= '</ul>';
            }
        }
        $event->data['content'] = $content;
    }
    public function onProjectValidation($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        $error = array();
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Donate) {
            App::import('Model', 'Donate.Donate');
            $this->Donate = new Donate();
            $this->Donate->set($data);
            if (!$this->Donate->validates()) {
                $error = $this->Donate->validationErrors;
            }
        }
        $event->data['error']['Donate'] = $error;
    }
    public function onProjectAdd($event) 
    {
        $controller = $event->subject();
        $data = $event->data['data'];
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Donate) {
            $donate = $controller->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $data['Project']['id']
                ) ,
                'contain' => array(
                    'Donate.id',
                    'Donate.donate_project_status_id',
                ) ,
                'recursive' => 0,
            ));
            if (!empty($donate) && !empty($donate['Donate']['id'])) {
                $data['Donate']['id'] = $donate['Donate']['id'];
            }
            if (empty($donate['Donate']['donate_project_status_id'])) {
                if (!$data['Project']['is_draft']) {
                    $data['Donate']['donate_project_status_id'] = ConstDonateProjectStatus::Pending;
                } else {
                    $data['Donate']['donate_project_status_id'] = 0;
                }
            }
            $data['Donate']['project_id'] = $data['Project']['id'];
            $data['Donate']['user_id'] = $controller->Auth->user('id');
            $controller->Project->Donate->save($data);
        }
    }
    public function onProjectEdit($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Donate) {
            App::import('Model', 'Donate.Donate');
            $this->Donate = new Donate();
            $donate_data = $this->Donate->find('first', array(
                'conditions' => array(
                    'Donate.project_id' => $data['Project']['id']
                ) ,
                'recursive' => -1
            ));
            if (!empty($data['Project']['publish']) && empty($donate_data['Donate']['donate_project_status_id'])) {
                $data['Donate']['donate_project_status_id'] = ConstDonateProjectStatus::Pending;
            }
            $data['Donate']['id'] = $donate_data['Donate']['id'];
            $this->Donate->save($data);
        }
    }
    public function isAllowAddFund($event) 
    {
        $project = $event->data['data'];
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Donate) {
            App::import('Model', 'Donate.Donate');
            $this->Donate = new Donate();
            $donate_data = $this->Donate->find('first', array(
                'conditions' => array(
                    'Donate.project_id' => $project['Project']['id']
                ) ,
                'recursive' => -1
            ));
            if (strtotime(date('Y-m-d 23:59:59', strtotime($project['Project']['project_end_date']))) > time() && !($donate_data['Donate']['is_allow_over_donating']) && $project['Project']['needed_amount'] <= $project['Project']['collected_amount']) {
                $event->data['error'] = sprintf(__l('%s has been not allowed overfunding') , Configure::read('project.alt_name_for_project_singular_caps'));
            } else {
                $event->data['donate'] = $donate_data;
            }
        }
    }
    public function onProjectFundValidation($event) 
    {
        $data = $event->data['data'];
        App::import('Model', 'Project.Project');
        $this->Project = new Project();
        $project = $this->Project->find('first', array(
            'conditions' => array(
                'Project.id' => $data['ProjectFund']['project_id']
            ) ,
            'contain' => array(
                'Donate'
            ) ,
            'recursive' => 0
        ));
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Donate) {
            $validationErrors = '';
            
            if (($data['ProjectFund']['amount'] > $project['Project']['needed_amount']-$project['Project']['collected_amount']) && !($project['Donate']['is_allow_over_donating'])) {
                $validationErrors['amount'] = __l('The amount should be less than needed amount.');
            } else if (!empty($project['Donate']['donate_type_id']) && !empty($project['Donate']['min_amount_to_fund'])) {
                if ($project['Project']['needed_amount']%$data['ProjectFund']['amount'] != 0 && $project['Donate']['donate_type_id'] == ConstDonateTypes::Multiple) {
                    $validationErrors['amount'] = __l('Amount should be multiple of ' . $project['Donate']['min_amount_to_fund'] . ".");
                } else if ($project['Donate']['min_amount_to_fund'] > $data['ProjectFund']['amount'] && ($project['Donate']['donate_type_id'] == ConstDonateTypes::Minimum)) {
                    $validationErrors['amount'] = __l('The amount should not be less than ') . Configure::read('site.currency') . $project['Donate']['min_amount_to_fund'];
                } else if ($project['Donate']['min_amount_to_fund'] != $data['ProjectFund']['amount'] && ($project['Donate']['donate_type_id'] == ConstDonateTypes::Fixed)) {
                    $validationErrors['amount'] = __l('The amount should be equal to ') . Configure::read('site.currency') . $project['Donate']['min_amount_to_fund'];
                }
            }
            $event->data['error'] = $validationErrors;
        }
    }
    public function onProjectFundAdd($event) 
    {
        $data = $event->data['data'];
    }
    public function onOpenFunding($event) 
    {
        $controller = $event->subject();
        if (is_object($controller->Project)) {
            $obj = $controller->Project;
        } else {
            $obj = $controller;
        }
        $event_data = $event->data['data'];
        $type = $event->data['type'];
        $project = $obj->find('first', array(
            'conditions' => array(
                'Project.id' => $event_data['project_id']
            ) ,
            'contain' => array(
                'Donate'
            ) ,
            'recursive' => 0
        ));
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Donate) {
            if (isPluginEnabled('Idea') && ($type == 'approve' || $type == 'vote')) {
                if ($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::Pending) {
                    $obj->Donate->updateStatus(ConstDonateProjectStatus::OpenForIdea, $event_data['project_id']);
                    $event->data['message'] = __l('Idea has been opened for voting');
                } else {
                    $event->data['error_message'] = __l('Idea has been already opened for voting');
                }
            } else {
                if ($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::Pending || $project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::OpenForIdea) {
					$obj->Donate->updateStatus(ConstDonateProjectStatus::OpenForDonating, $event_data['project_id']);
                    $event->data['message'] = sprintf(__l('Project has been opened for %s') , Configure::read('project.alt_name_for_donor_present_continuous'));
                } else {
                    $event->data['error_message'] = sprintf(__l('Project has been already opened for %s') , Configure::read('project.alt_name_for_donor_present_continuous'));
                }
            }
        }
    }
    public function onCategorydisplay($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        $class = '';
		if(isset($event->data['class'])){
			$class = $event->data['class'];
		}
        $extra_arr = array();
        if (!empty($event->data['target'])) {
            $extra_arr['target'] = '_blank';
        }
        $return = '';
        if ($data['ProjectType']['id'] == ConstProjectTypes::Donate) {
            App::import('Model', 'Donate.Donate');
            $Donate = new Donate;
            $donate = $Donate->find('first', array(
                'conditions' => array(
                    'Donate.project_id' => $data['Project']['id']
                ) ,
                'contain' => array(
                    'DonateProjectCategory'
                )
            ));
            if (!empty($donate['DonateProjectCategory'])) {
                if ($class == 'categoryname') {
                    $return = $donate['DonateProjectCategory']['name'];
                } else {
                    if ($donate['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::OpenForIdea) {
                        $return = $obj->Html->link($donate['DonateProjectCategory']['name'], array(
                            'controller' => 'projects',
                            'action' => 'index',
                            'category' => $donate['DonateProjectCategory']['slug'],
                            'project_type' => 'donate',
                            'idea' => 'idea'
                        ) , array_merge(array(
                            'title' => $donate['DonateProjectCategory']['name'],
                            'class' => 'text-danger' .$class
                        ) , $extra_arr));
                    } else {
                        $return = $obj->Html->link($donate['DonateProjectCategory']['name'], array(
                            'controller' => 'projects',
                            'action' => 'index',
                            'category' => $donate['DonateProjectCategory']['slug'],
                            'project_type' => Configure::read('project.alt_name_for_donate_singular_small') ,
                        ) , array_merge(array(
                            'title' => $donate['DonateProjectCategory']['name'],
                            'class' => 'text-danger' .$class
                        ) , $extra_arr));
                    }
                }
            }
            $event->data['content'] = $return;
        }
    }
    public function onMessageDisplay($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        App::import('Model', 'Donate.Donate');
        $Donate = new Donate;
        $projectIds = $Donate->find('list', array(
            'conditions' => array(
                'Donate.donate_project_status_id' => array(
                    ConstDonateProjectStatus::OpenForDonating
                ) ,
                'Donate.user_id' => $obj->Auth->user('id') ,
            ) ,
            'fields' => array(
                'Donate.project_id'
            )
        ));
        $projectIds = array_unique(array_merge($projectIds, $data));
        $event->data['ids'] = $projectIds;
        $event->data['projectStatus'] = $this->__getProjectStatus($projectIds);
    }
    public function __getProjectStatus($projectIds) 
    {
        App::import('Model', 'Donate.Donate');
        $Donate = new Donate;
        $donates = $Donate->find('all', array(
            'conditions' => array(
                'Donate.project_id' => $projectIds,
            ) ,
            'contain' => array(
                'DonateProjectStatus'
            ) ,
            'recursive' => 0
        ));
        $projectDetails = array();
        foreach($donates as $key => $donate) {
            $projectDetails[$donate['Donate']['project_id']] = $donate['DonateProjectStatus'];
        }
        return $projectDetails;
    }
    public function getProjectStatusValue($event) 
    {
        $projectStatusIds = $event->data['status_id'];
        $projectTypeId = $event->data['project_type_id'];
        if ($projectTypeId == ConstProjectTypes::Donate) {
            $donateProjectStatus = array(
                ConstDonateProjectStatus::Pending => __l('Pending') ,
                ConstDonateProjectStatus::OpenForDonating => sprintf(__l('Open for %s') , Configure::read('project.alt_name_for_donate_present_continuous_small')) ,
                ConstDonateProjectStatus::FundingClosed => __l('Funding Closed') ,
                ConstDonateProjectStatus::FundingExpired => sprintf(__l('%s Expired') , Configure::read('project.alt_name_for_project_singular_caps')) ,
                ConstDonateProjectStatus::OpenForIdea => __l('Open for voting')
            );
            if (array_key_exists($projectStatusIds, $donateProjectStatus)) {
                $event->data['response'] = $donateProjectStatus[$projectStatusIds];
            } else {
                $event->data['response'] = 0;
            }
        }
    }
    public function onMessageInbox($event) 
    {
        $obj = $event->subject();
        $projectStatus = $event->data['projectStatus'];
        $project = $event->data['project'];
        if (!empty($project['Project']['project_type_id']) && $project['Project']['project_type_id'] == ConstProjectTypes::Donate) {
            $projectStatusNew = $this->__getProjectStatus($project['Project']['id']);
            if (!empty($event->data['type']) && $event->data['type'] == 'status') {
                $event->data['is_allow_to_cancel_project'] = 0;
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::OpenForIdea
                ))) {
                    $event->data['is_allow_to_vote'] = 1;
                    $event->data['is_allow_to_move_for_funding'] = 1;
                } elseif (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_move_for_voting'] = 1;
                    $event->data['is_allow_to_move_for_funding'] = 1;
                    if (isPluginEnabled('Idea')) {
                        $event->data['is_show_vote'] = 1;
                    }
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::FundingClosed
                ))) {
                    $event->data['is_allow_to_change_status'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::Pending
                ))) {
                    $event->data['is_affiliate_status_pending'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::FundingClosed
                ))) {
                    $event->data['is_not_show_you_here'] = 1;
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::Pending,
                    ConstDonateProjectStatus::OpenForIdea
                ))) {
                    $event->data['is_show_project_funding_tab'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::OpenForDonating
                ))) {
                    $event->data['is_allow_to_fund'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    0,
                    ConstDonateProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_edit_fund'] = 1;
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::FundingExpired
                ))) {
                    $event->data['is_allow_to_follow'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::OpenForIdea,
                    ConstDonateProjectStatus::OpenForDonating
                ))) {
                    $event->data['is_allow_to_share'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_pay_listing_fee'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstDonateProjectStatus::Pending
                ))) {
                    $event->data['is_pending_status'] = 1;
                }
            }
            if (empty($projectStatus)) {
                $event->data['projectStatus'] = $projectStatusNew;
            } else {
                $event->data['projectStatus'] = $projectStatusNew+$projectStatus;
            }
        }
    }
    public function getClosedProjectIds($event) 
    {
        $obj = $event->subject();
        $project_ids = $event->data['project_ids'];
        $status_id = ConstDonateProjectStatus::FundingClosed;
        $conditions = array();
        $conditions['Donate.project_id'] = $project_ids;
        $conditions['Donate.donate_project_status_id'] = $status_id;
        $tmp_project_ids = $this->__getProjectIds($conditions);
        $conditions = array();
        $conditions['Donate.user_id'] = $obj->Auth->user('id');
        $conditions['Donate.donate_project_status_id'] = $status_id;
        $tmp1_project_ids = $this->__getProjectIds($conditions);
        $event->data['project_ids'] = array_unique(array_merge($tmp_project_ids, $tmp1_project_ids));
    }
    private function __getProjectIds($conditions) 
    {
        App::import('Model', 'Donate.Donate');
        $donate = new Donate();
        $projectIds = $donate->find('list', array(
            'conditions' => $conditions,
            'fields' => array(
                'Donate.project_id'
            )
        ));
        return $projectIds;
    }
    public function getConditions($event) 
    {
        if (!empty($event->data['data'])) {
            $data = $event->data['data'];
        }
        if (!empty($event->data['type'])) {
            $type = $event->data['type'];
        }
        if (!empty($event->data['page'])) {
            $page = $event->data['page'];
        }
        if (!empty($data) && $data['ProjectType']['id'] == ConstProjectTypes::Donate) {
            if ($type == 'idea') {
                $event->data['conditions'] = array(
                    'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForIdea
                );
            } elseif ($type == 'open') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForDonating
                );
            } elseif ($type == 'search') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForIdea,
                    'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForDonating,
                );
            } elseif ($type == 'closed') {
                $event->data['conditions'] = array(
                    'Donate.donate_project_status_id' => ConstDonateProjectStatus::FundingClosed
                );
            } elseif ($type == 'notclosed') {
                $event->data['conditions'] = array(
                    'Donate.donate_project_status_id !=' => ConstDonateProjectStatus::FundingClosed
                );
            }
        } elseif (!empty($page)) {
            if ($type == 'idea') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForIdea
                );
            } elseif ($type == 'myprojects') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id NOT' => array(
                        ConstDonateProjectStatus::Pending,
                        ConstDonateProjectStatus::OpenForIdea,
                        ConstDonateProjectStatus::FundingExpired
                    )
                );
            } elseif ($type == 'search') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id NOT' => array(
                        ConstDonateProjectStatus::Pending,
                    )
                );
            } elseif ($type == 'open') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => array(
                        ConstDonateProjectStatus::OpenForDonating,
                    )
                );
            } elseif ($type == 'project_count') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => array(
                        ConstDonateProjectStatus::OpenForDonating,
                        ConstDonateProjectStatus::FundingClosed,
                    )
                );
            } elseif ($type == 'all_project_count') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id NOT' => array(
                        ConstDonateProjectStatus::OpenForIdea,
                    )
                );
            } elseif ($type == 'idea_count') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => array(
                        ConstDonateProjectStatus::OpenForIdea
                    )
                );
            } elseif ($type == 'count') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => array(
                        ConstDonateProjectStatus::OpenForDonating,
                        ConstDonateProjectStatus::FundingClosed,
                        ConstDonateProjectStatus::OpenForIdea
                    )
                );
            } elseif ($type == 'city_count') {
                $event->data['conditions']['OR'][] = array(
                    'Donate.donate_project_status_id' => array(
                        ConstDonateProjectStatus::OpenForDonating,
                    )
                );
            } elseif ($type == 'iphone') {
                $event->data['conditions']['AND'][] = array(
                    'Donate.donate_project_status_id' => array(
                        ConstDonateProjectStatus::OpenForDonating,
                    )
                );
            }
        }
    }
    public function getContain($event) 
    {
        $obj = $event->subject();
        switch ($event->data['type']) {
            case 1:
                $event->data['contain']['Donate'] = array(
                    'DonateProjectCategory',
                    'DonateProjectStatus',
                );
                break;

            case 2:
                $event->data['contain']['Donate'] = array(
                    'fields' => array(
                        'id'
                    )
                );
                break;
        }
    }
    public function getProjectTypeStatus($event) 
    {
        $obj = $event->subject();
        $project = $event->data['project'];
        if (!empty($project['Donate'])) {
            $data = array();
            $data['Project_funding_text'] = sprintf(__l('%s amount') , Configure::read('project.alt_name_for_donate_present_continuous_caps'));
            $data['Project_funded_text'] = Configure::read('project.alt_name_for_donate_past_tense_small');
            $data['Project_fund_button_lable'] = Configure::read('project.alt_name_for_donate_singular_caps');
            $data['Project_status_name'] = $project['Donate']['DonateProjectStatus']['name'];
            if ($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::OpenForDonating) {
                if ($obj->Auth->user('id') != $project['Project']['user_id']) {
                    $data['Project_fund_button_status'] = true;
                    $data['Project_fund_button_url'] = Router::url(array(
                        'controller' => 'project_funds',
                        'action' => 'add',
                        $project['Project']['id']
                    ) , true);
                } else {
                    $data['Project_fund_button_status'] = false;
                }
            } else {
                $data['Project_fund_button_status'] = false;
            }
            if ((strtotime($project['Project']['project_end_date']) < strtotime(date('Y-m-d'))) && ($project['Project']['needed_amount'] != $project['Project']['collected_amount'])) {
                $data['Project_status'] = -1;
            } else if ($project['Project']['needed_amount'] == $project['Project']['collected_amount']) {
                $data['Project_status'] = 1;
            } else {
                $data['Project_status'] = 0;
            }
            $data['Donated'] = $project['Project']['collected_amount'];
            $data['Category_name'] = $project['DonateProjectCategory']['name'];
            $event->data['data'] = $data;
        }
    }
    public function howitworks($event) 
    {
        $view = $event->subject();
        App::import('Model', 'PaymentGatewaySetting');
        $this->PaymentGatewaySetting = new PaymentGatewaySetting();
        $arrDonateWallet = $this->PaymentGatewaySetting->find('first', array(
            'conditions' => array(
                'PaymentGatewaySetting.payment_gateway_id' => ConstPaymentGateways::Wallet,
                'PaymentGatewaySetting.name' => 'is_enable_for_donate'
            ) ,
            'recursive' => 0
        ));
        if ($arrDonateWallet['PaymentGateway']['is_test_mode']) {
            $data['is_donate_wallet_enabled'] = $arrDonateWallet['PaymentGatewaySetting']['test_mode_value'];
        } else {
            $data['is_donate_wallet_enabled'] = $arrDonateWallet['PaymentGatewaySetting']['live_mode_value'];
        }
        if (isPluginEnabled('Sudopay')) {
            App::import('Model', 'Sudopay.SudopayPaymentGateway');
            $this->SudopayPaymentGateway = new SudopayPaymentGateway();
            $supported_gateways = $this->SudopayPaymentGateway->find('list', array(
                'fields' => array(
                    'SudopayPaymentGateway.sudopay_gateway_name'
                ) ,
                'recursive' => -1,
            ));
            $data['supported_gateways'] = $supported_gateways;
        }
        echo $view->element('Donate.how_it_works', $data);
    }
    public function onActionToBeTakenRender($event) 
    {
        $view = $event->subject();
        App::import('Model', 'User');
        $user = new User();
        App::import('Model', 'Donate.Donate');
        $donate = new Donate();
        $data['donate_pending_for_approval_count'] = $donate->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Donate,
                'Project.is_pending_action_to_admin = ' => 1
            ) ,
            'recursive' => -1
        ));
        $data['donate_user_flagged_count'] = $user->Project->find('count', array(
            'conditions' => array(
                'Project.is_user_flagged' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        ));
        $data['donate_system_flagged_count'] = $user->Project->find('count', array(
            'conditions' => array(
                'Project.is_system_flagged' => 1,
                'Project.project_type_id' => ConstProjectTypes::Donate
            ) ,
            'recursive' => -1
        ));
        $event->data['content']['PendingProject'].= $view->element('Donate.admin_action_taken_pending', $data);
        $event->data['content']['FlaggedProjects'].= $view->element('Donate.admin_action_taken', $data);
    }
    public function getFeatureProjectList($event) 
    {
        $controller = $event->subject();
		$conditions = array();
		$conditions['Project.is_active'] = 1;
		$conditions['Project.is_draft'] = 0;
		$conditions['Project.is_admin_suspended'] = '0';
		$conditions['Project.project_end_date >= '] = date('Y-m-d');
		$conditions['Project.project_type_id'] = ConstProjectTypes::Donate;
		
		$conditions['NOT'] = array( 'Donate.donate_project_status_id' => array(
                        ConstDonateProjectStatus::Pending,
						ConstDonateProjectStatus::FundingExpired
                    ));
		
		$contain = array(
			'Attachment',
			'Donate'
		);
		$order = array(
			'Project.is_featured' => 'desc',
			'Project.id' => 'desc'
		);            
		$donate = $controller->Project->find('all', array(
			'conditions' => $conditions,
			'contain' => $contain,
			'recursive' => 3,
			'order' => $order,
			'limit' => 4
		));
		$event->data['content']['Donate'] = $donate;
    }
}
?>