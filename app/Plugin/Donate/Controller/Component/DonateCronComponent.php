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
class DonateCronComponent extends Component
{
    public function main() 
    {
        App::import('Model', 'Donate.Donate');
        $this->Donate = new Donate();
        App::import('Model', 'Project');
        $this->Project = new Project();
        $projects = $this->Donate->find('all', array(
            'conditions' => array(
                'Project.is_draft' => 0,
                'Donate.donate_project_status_id' => array(
                    ConstDonateProjectStatus::OpenForDonating,
                ) ,
            ) ,
            'contain' => array(
                'Project'
            ) ,
            'recursive' => 0
        ));
        foreach($projects as $project) {
            if (($project['Project']['collected_amount'] >= $project['Project']['needed_amount'] && empty($project['Donate']['is_allow_over_donating'])) || ($project['Project']['collected_amount'] >= $project['Project']['needed_amount'] && strtotime($project['Project']['project_end_date'] . ' 23:55:59') <= strtotime(date('Y-m-d H:i:s'))) || (strtotime($project['Project']['project_end_date'] . ' 23:55:59') <= strtotime(date('Y-m-d H:i:s')))) {
                if (empty($project['Project']['project_fund_count'])) {
                    $this->Donate->updateStatus(ConstDonateProjectStatus::FundingExpired, $project['Project']['id']);
                } else {
                    $this->Donate->updateStatus(ConstDonateProjectStatus::FundingClosed, $project['Project']['id']);
                }
            }
        }
    }
}
