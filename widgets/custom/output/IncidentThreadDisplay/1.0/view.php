<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
    <rn:block id="top"/>
<?php if ($this->data['attrs']['label']): ?>
    <rn:block id="label">
    <span class="rn_DataLabel"><?=$this->data['attrs']['label'];?> </span>
    </rn:block>
<?php endif; ?>
<?php if($this->data['value']): ?>
    <rn:block id="preList"/>
<?php foreach($this->data['value'] as $thread): ?>
    <?php
        \RightNow\Libraries\Decorator::add($thread, 'Present/IncidentThreadPresenter');
        // This is not a public note, so it should not be displayed.
        if ($thread->IncidentThreadPresenter->isPrivate()) continue;

        $subclass = $thread->IncidentThreadPresenter->isCustomerEntry() ? 'rn_Customer' : '';
    ?>
    <rn:block id="preListItem"/>
    <div class="rn_ThreadHeader <?=$subclass?>">
        <rn:block id="preThreadHeader"/>
        <span class="rn_ThreadAuthor">
            <rn:block id="threadAuthor">
            <!--?=$thread->EntryType->LookupName;?-->
            <?= $thread->IncidentThreadPresenter->getAuthorName() ?>
            <?php if($thread->Channel)
                // printf(\RightNow\Utils\Config::getMessage(VIA_PCT_S_LBL), $thread->Channel->LookupName);
            ?>
            </rn:block>
        </span>
        <span class="rn_ThreadTime">
            <rn:block id="threadTime">
            <?= $thread->IncidentThreadPresenter->formattedCreationTime($this->data['attrs']['highlight']) ?>
            </rn:block>
        </span>
        <rn:block id="postThreadHeader"/>
    </div>
    <div class="rn_ThreadContent">
        <rn:block id="threadContent">
        <?= $thread->IncidentThreadPresenter->formattedEntry($this->data['attrs']['highlight']) ?>
        </rn:block>
    </div>
    <rn:block id="postListItem"/>
<?php endforeach; ?>
    <rn:block id="postList"/>
<?php endif; ?>
    <rn:block id="bottom"/>
</div>
