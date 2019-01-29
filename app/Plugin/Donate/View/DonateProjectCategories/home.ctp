<?php
  if (!empty($projectCategories)) {
    $projectCategories = array_chunk($projectCategories, 6);
?>
  <h6><?php echo __l('Browse Categories');?></h6>

    <ul class="grid_3 alpha clearfix">
  <?php foreach ($projectCategories[0] as $projectCategorie): ?>
      <li><?php echo $this->Html->link($projectCategorie['DonateProjectCategory']['name'], array('controller' => 'projects', 'action' => 'index', 'category' => $projectCategorie['DonateProjectCategory']['slug']), array('title' => $projectCategorie['DonateProjectCategory']['name']));?></li>
  <?php endforeach; ?>
    </ul>
  <?php if(!empty($projectCategories[1])) {?>
    <ul class="grid_4 alpha clearfix">
  <?php foreach ($projectCategories[1] as $projectCategorie): ?>
      <li><?php echo $this->Html->link($projectCategorie['DonateProjectCategory']['name'], array('controller' => 'projects', 'action' => 'index', 'category' => $projectCategorie['DonateProjectCategory']['slug']), array('title' => $projectCategorie['DonateProjectCategory']['name']));?></li>
  <?php endforeach; ?>
    </ul>
  <?php } ?>

<?php } ?>