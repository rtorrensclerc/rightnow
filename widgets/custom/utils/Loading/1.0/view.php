<? $init_started = ($this->data['attrs']['init_started'])?'active':'inactive'; ?>
<? $scope        = ($this->data['attrs']['scope'] === 'global')?'global':'local'; ?>
<? $tone         = ($this->data['attrs']['tone'] === 'dark')?'dark':'clear'; ?>

<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> rn_Loading_Backlight <?= $init_started ?> <?= $scope ?> <?= $tone ?>">
  <div class="color">
  </div>
  <div class="icon">
  </div>
</div>