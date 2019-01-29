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
require_once 'constants.php';
CmsNav::add('Projects', array(
    'title' => 'Projects',
    'url' => array(
        'controller' => 'projects',
        'action' => 'index',
    ) ,
    'weight' => 30,
    'data-bootstro-step' => "4",
    'data-bootstro-content' => __l("To monitor the summary, price point statistics of site and also to manage all projects posted in the site.") ,
    'icon-class' => 'file',
    'children' => array(
        'Donate Projects' => array(
            'title' => sprintf(__l('%s %s') , Configure::read('project.alt_name_for_donate_singular_caps') , Configure::read('project.alt_name_for_project_plural_caps')) ,
            'url' => array(
                'controller' => 'donates',
                'action' => 'index'
            ) ,
            'weight' => 50,
        ) ,
    ) ,
));
CmsNav::add('masters', array(
    'title' => 'Masters',
    'weight' => 200,
    'children' => array(
        'Donate Projects' => array(
            'title' => Configure::read('project.alt_name_for_donate_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps') ,
            'url' => '',
            'weight' => 600,
        ) ,
        'Donate Project Categories' => array(
            'title' => sprintf(__l('%s %s Categories') , Configure::read('project.alt_name_for_donate_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')) ,
            'url' => array(
                'controller' => 'donate_project_categories',
                'action' => 'index',
            ) ,
            'weight' => 610,
        ) ,
        'Donate Project Statuses' => array(
            'title' => sprintf(__l('%s %s Statuses') , Configure::read('project.alt_name_for_donate_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')) ,
            'url' => array(
                'controller' => 'donate_project_statuses',
                'action' => 'index',
            ) ,
            'weight' => 620,
        ) ,
    )
));
CmsNav::add('payments', array(
    'title' => __l('Payments') ,
    'weight' => 50,
    'children' => array(
        'Projects Funded' => array(
            'title' => __l('Projects Funded') ,
            'url' => '',
            'weight' => 300,
        ) ,
        'Donate Projects Funded' => array(
            'title' => sprintf(__l('%s') , Configure::read('project.alt_name_for_donate_plural_caps')) ,
            'url' => array(
                'controller' => 'donates',
                'action' => 'funds'
            ) ,
            'weight' => 320,
        ) ,
    )
));
$defaultModel = array(
    'Project' => array(
        'hasOne' => array(
            'Donate' => array(
                'className' => 'Donate.Donate',
                'foreignKey' => 'project_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
    ) ,
    'ProjectFund' => array(
        'hasOne' => array(
            'DonateFund' => array(
                'className' => 'Donate.DonateFund',
                'foreignKey' => 'project_fund_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
    ) ,
);
CmsHook::bindModel($defaultModel);
