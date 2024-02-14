<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
    <span id="rn_<?= $this->instanceID ?>_Label" class="rn_Label rn_DataLabel">
    <?= $this->data['attrs']['label_input'] ?>
    </span>

    <div class="rn_DataValue">
    <?php if ($this->data['attrs']['display_type'] === 'textarea'): ?>

        <?= $this->data['attrs']['value'] ?>

    <? elseif($this->data['attrs']['display_type'] === 'checkbox'): ?>

        <?= ($this->data["attrs"]["value"] == 1)?'Sí':'No' ?>

    <? elseif($this->data['attrs']['display_type'] === 'number'): ?>

        <?= ($this->data['attrs']['value'])?$this->data['attrs']['value']:0 ?>

    <? else: ?>

        <?= $this->data['attrs']['value'] ?>

    <?php endif; ?>
    </div>
</div>
