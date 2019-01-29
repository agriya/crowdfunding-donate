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
class Donate extends AppModel
{
    public $name = 'Donate';
    var $useTable = 'project_donate_fields';
    public $displayField = 'id';
    public $actsAs = array(
        'Sluggable' => array(
            'label' => array(
                'name'
            )
        ) ,
    );
    //$validate set in __construct for multi-language support
    //The Associations below have been created with all possible keys, those that are not needed can be removed
    public $belongsTo = array(
        'DonateProjectCategory' => array(
            'className' => 'Donate.DonateProjectCategory',
            'foreignKey' => 'donate_project_category_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'counterCache' => true,
            'counterScope' => '',
        ) ,
        'DonateProjectStatus' => array(
            'className' => 'Donate.DonateProjectStatus',
            'foreignKey' => 'donate_project_status_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'counterCache' => true,
            'counterScope' => '',
        ) ,
        'Project' => array(
            'className' => 'Projects.Project',
            'foreignKey' => 'project_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
    function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        $this->_permanentCacheAssociations = array(
            'Project'
        );
        $this->validate = array(
            'needed_amount' => array(
                'rule3' => array(
                    'rule' => array(
                        'minMaxAmount',
                        'needed_amount',
                    ) ,
                    'message' => sprintf(__l('The amount between %s to %s') , Configure::read('Donate.minimum_amount') , Configure::read('Donate.maximum_amount'))
                ) ,
                'rule2' => array(
                    'rule' => array(
                        'comparison',
                        '>=',
                        1
                    ) ,
                    'allowEmpty' => false,
                    'message' => __l('Must be greater than zero')
                ) ,
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                )
            ) ,
            'project_funding_end_date' => array(
                'rule2' => array(
                    'rule' => array(
                        'comparison',
                        '>=',
                        date('Y-m-d') ,
                    ) ,
                    'message' => sprintf(__l('%s funding end date should be greater than to today') , Configure::read('project.alt_name_for_project_singular_caps'))
                ) ,
                'rule1' => array(
                    'rule' => 'date',
                    'message' => __l('Enter valid date')
                )
            ) ,
            'donate_project_category_id' => array(
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                )
            )
        );
    }
    function minMaxAmount($field1, $field = null) 
    {
        return ($this->data[$this->name][$field] >= Configure::read('Project.minimum_amount') && $this->data[$this->name][$field] <= Configure::read('Project.maximum_amount'));
    }
    function updateProjectStatus($project_fund_id) 
    {
        $projectFund = $this->Project->ProjectFund->find('first', array(
            'conditions' => array(
                'ProjectFund.id' => $project_fund_id
            ) ,
            'contain' => array(
                'Project' => array(
                    'Donate',
                ) ,
            ) ,
            'recursive' => 2
        ));
        if ($projectFund['Project']['collected_amount'] == $projectFund['Project']['needed_amount'] && empty($projectFund['Project']['Donate']['is_allow_over_funding'])) {
            $this->updateStatus(ConstDonateProjectStatus::FundingClosed, $projectFund['Project']['id']);
        }
        if ($projectFund['Project']['collected_amount'] < $projectFund['Project']['needed_amount'] && ($projectFund['Project']['collected_amount']) >= $projectFund['Project']['needed_amount']) {
            $this->updateAll(array(
                'Donate.project_donate_goal_reached_date' => '"' . date('Y-m-d H:i:s') . '"'
            ) , array(
                'Donate.id' => $projectFund['Project']['Donate']['id']
            ));
        }
    }
    function updateStatus($to_project_status_id, $project_id) 
    {
        $project = $this->Project->find('first', array(
            'conditions' => array(
                'Project.id = ' => $project_id,
            ) ,
            'contain' => array(
                'Donate',
                'User',
                'ProjectType',
                'Attachment',
            ) ,
            'recursive' => 0,
        ));
        $_data = array();
        $_data['Donate']['donate_project_status_id'] = $to_project_status_id;
        if ($to_project_status_id == ConstDonateProjectStatus::FundingClosed || $to_project_status_id == ConstDonateProjectStatus::FundingExpired) {
            $_data['Donate']['project_donate_goal_reached_date'] = date('Y-m-d H:i:s');
        }
        if ($to_project_status_id == ConstDonateProjectStatus::FundingExpired || $to_project_status_id == ConstDonateProjectStatus::FundingClosed) {
            $_data['Project']['project_cancelled_date'] = date('Y-m-d H:i:s');
        }
        $_data['Donate']['id'] = $project['Donate']['id'];
        $this->save($_data, false);
        $tmp_project = $this->
        {
            'processStatus' . $to_project_status_id}($project);
            $_data = array();
            $_data['from_project_status_id'] = $project['Donate']['donate_project_status_id'];
            $_data['to_project_status_id'] = $to_project_status_id;
            $this->postActivity($project, ConstProjectActivities::StatusChange, $_data);
            //Expired only hide in activities
            if ($to_project_status_id == 5) {
                // update activities record hide from public
                $this->Project->Message->updateActivitiesHideFromPublic($project_id);
            }
        }
        function processStatus2($project) 
        {
            // Open For Funding //
            if (isPluginEnabled('SocialMarketing')) {
                App::import('Model', 'SocialMarketing.UserFollower');
                $this->UserFollower = new UserFollower();
                $this->UserFollower->send_follow_mail($_SESSION['Auth']['User']['id'], 'added', $project);
            }
            $data['Project']['project_start_date'] = date('Y-m-d');
            $data['Project']['id'] = $project['Project']['id'];
            $this->Project->save($data);
            $total_needed_amount = $project['User']['total_needed_amount']+$project['Project']['needed_amount'];
            $this->Project->updateAll(array(
                'User.total_needed_amount' => $total_needed_amount
            ) , array(
                'User.id' => $project['User']['id']
            ));
            $this->Project->postOnSocialNetwork($project);
            $data = array();
            $data['User']['id'] = $project['Project']['user_id'];
            $data['User']['is_idle'] = 0;
            $data['User']['is_project_posted'] = 1;
            $this->Project->User->save($data);
        }
        function processStatus3($project) 
        {
            // Funding Closed //
            
        }
        function processStatus4($project) 
        {
            // Open For Idea //
            $data = array();
            $data['User']['id'] = $project['Project']['user_id'];
            $data['User']['is_idle'] = 0;
            $data['User']['is_project_posted'] = 1;
            $this->Project->User->save($data);
        }
        function processStatus5() 
        {
            // Funding Expired //
            
        }
		public function deductFromCollectedAmount($project) 
		{
			$donates = $this->find('all', array(
				'conditions' => array(
					'Donate.project_id' => $project['Project']['id'],
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
			if (in_array($projectDetails[$project['Project']['id']]['id'], array(
							ConstDonateProjectStatus::FundingExpired
					))) {
				return false;
			} else {
				return true;
			}
		}
		public function getCategoryConditions($category = null, $is_slug = true) 
		{	
			if(!empty($is_slug)) {
				App::import('Model', 'Donate.DonateProjectCategory');
				$this->DonateProjectCategory = new DonateProjectCategory();
				$category = $this->DonateProjectCategory->find('first', array(
					'conditions' => array(
						'DonateProjectCategory.slug' => $category
					) ,
					'recursive' => -1
				));
				$response['category_details'] = $category['DonateProjectCategory'];
				$response['conditions'] = array(
					'Donate.donate_project_category_id' => $category['DonateProjectCategory']['id']
				);
			} else {
				$response['conditions'] = array(
					'Donate.donate_project_category_id' => $category
				);
			}
			return $response;
		}
		public function onProjectCategories($is_slug = false)  
		{
			$fields = array(
				'DonateProjectCategory.slug',
				'DonateProjectCategory.name'
			);
			if(!$is_slug) {
				$fields = array(
					'DonateProjectCategory.id',
					'DonateProjectCategory.name'
				);
			}	
			$donateProjectCategory = $this->DonateProjectCategory->find('list', array(
				'conditions' => array(
					'DonateProjectCategory.is_approved' => 1
				) ,
				'fields' => $fields,
				'order' => array(
					'DonateProjectCategory.name' => 'ASC'
				) ,
			));
			$response['donateCategories'] = $donateProjectCategory;
			return $response;
		}
		public function isAllowToPublish($project_id) 
		{
			$project = $this->find('count', array(
				'conditions' => array(
					'Donate.project_id' => $project_id,
					'Donate.donate_project_status_id' => array(
						ConstDonateProjectStatus::OpenForIdea,
						ConstDonateProjectStatus::OpenForDonating
					)
				)
			));
			$response['is_allow_to_publish'] = 1;
			return $response;
		}
		public function isAllowToProcessPayment($project_id) 
		{
			$project = $this->find('count', array(
				'conditions' => array(
					'Donate.project_id' => $project_id,
					'Donate.donate_project_status_id' => ConstDonateProjectStatus::Pending,
					'Project.is_paid' => 0,
				) ,
				'recursive' => 0
			));
			if (!empty($project)) {
				$response['is_allow_process_payment'] = 1;
				return $response;
			}
		}
		public function isAllowToViewProject($project, $funded_users, $followed_user) 
		{
			$response['is_allow_to_view_project'] = 1;
			if (($project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::Pending || $project['Donate']['donate_project_status_id'] == ConstDonateProjectStatus::FundingExpired) && (!$funded_users) && (!$followed_user) && (!$_SESSION['Auth']['User']['id'] || ($_SESSION['Auth']['User']['id'] && $_SESSION['Auth']['User']['id'] != $project['Project']['user_id'] && $_SESSION['Auth']['User']['role_id'] != ConstUserTypes::Admin))) {
				$response['is_allow_to_view_project'] = 0;
			}
			return $response;
		}
		public function onProjectViewMessageDisplay($project) 
		{
			$donate = $this->find('first', array(
				'conditions' => array(
					'Donate.donate_project_status_id' => array(
						ConstDonateProjectStatus::OpenForIdea,
						ConstDonateProjectStatus::OpenForDonating,
						ConstDonateProjectStatus::FundingClosed,
					) ,
					'Donate.project_id' => $project['Project']['id']
				) ,
				'fields' => array(
					'Donate.project_id'
				)
			));
			$response['is_comment_allow'] = 0;
			if (!empty($donate)) {
				$response['is_comment_allow'] = 1;
			}
			return $response;
		}
		public function getUserOpenProjectCount($user_id){
			$donate_count = $this->Project->find('count',array(
    			'conditions' => array(
    					'Donate.donate_project_status_id' => ConstDonateProjectStatus::OpenForDonating,
    					'Project.user_id' => $user_id,
    			) ,
    			'contain' => array(
                    'Donate'
                ) ,
                'recursive' => 0
    		));
			return $donate_count;
		}
    }
?>