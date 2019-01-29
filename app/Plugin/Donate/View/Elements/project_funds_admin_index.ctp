<?php
	$q = !empty($this->request->data['ProjectFund']['q']) ? $this->request->data['ProjectFund']['q'] : '';
	$type = !empty($this->request->params['named']['type']) ? $this->request->params['named']['type'] : '';
	echo $this->requestAction(array('controller' => 'donates', 'action' => 'fund_index', 'admin' => true, 'type' => $type, 'q' => $q), array('return'));
?>