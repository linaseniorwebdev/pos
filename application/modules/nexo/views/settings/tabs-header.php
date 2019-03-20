<ul class="nav nav-tabs">
    <?php foreach( $tabs as $name => $tab ):?>
        <li <?php echo $activeTab === $name ? 'class="active"' : '';?>><a href="<?php echo @$baseUrl . '?tab=' . $name;?>"><?php echo $tab;?></a></li>
    <?php endforeach;?>
</ul>
<br>