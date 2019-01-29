<p>	
	<span class="btn btn-sm btn-success bdr-rad-7px open-fund-pad">
		<i class="fa fa-usd fa-fw font-size-15"></i>
	</span>  
	<span class="panel-title list-group-item-text list-group-item-heading clr-black vertical-center roboto-regular">
		<?php
			$projectStatus = array();
			$response = Cms::dispatchEvent('View.ProjectType.GetProjectStatus', $this, array(
					'projectStatus' => $projectStatus,
					'project' => $project,
					'type'=> 'status'
				));
			$projectStatus = $response->data['projectStatus'];
			$status_response = Cms::dispatchEvent('View.Project.projectStatusValue', $this, array(
										  'status_id' => $projectStatus[$project['Project']['id']]['id'],
										  'project_type_id' => $project['Project']['project_type_id']
										));
			if($status_response->data['response']){
				$reason =  $status_response->data['response'];
				echo $this->Html->cText($reason);
			}
			else{
				echo __l('Draft');
			}
		?>
	</span>
</p>