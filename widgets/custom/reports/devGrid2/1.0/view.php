<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<div class="container">
    <div class="row">
      <table>
        <thead>
            <? if(count($this->data['result']['headers']) > 0): ?>
            <? foreach($this->data['result']['headers'] as $header):?>
                <? if($header['data_type'] != '3'):?>
                    <th><?=$header['heading'];?></th>
                <? else:?>
                    <th class="numeric"><?=$header['heading'];?></th>
                <? endif;?>
            <? endforeach;?>
            <? endif;?>
            </tr>
        </thead>
        <? if(count($this->data['result']['data']) > 0): ?>
            <tbody>
            <? for($i = 0; $i < count($this->data['result']['data']); $i++): ?>
                <tr>
                <? for($j = 0; $j < count($this->data['result']['headers']); $j++):?>
                    <? if($this->data['result']['headers'][$j]['data_type'] == 99):?>
                        <td data-title="<?=$this->data['result']['headers'][$j]['heading'];?>" class="url"><a href="<?=$this->data['attrs']['url_per_col'].$this->data['result']['data'][$i][$j]?>"><?=($this->data['result']['data'][$i][$j] !== '' && $this->data['result']['data'][$i][$j] !== null && $this->data['result']['data'][$i][$j] !== false) ? $this->data['result']['data'][$i][$j] : '&nbsp;' ?></a></td>
                    <? else:?>
                        <td data-title='<?=$this->data['result']['headers'][$j]['heading'];?>' class="data"><?=($this->data['result']['data'][$i][$j] !== '' && $this->data['result']['data'][$i][$j] !== null && $this->data['result']['data'][$i][$j] !== false) ? $this->data['result']['data'][$i][$j] : '&nbsp;' ?></td>
                    <? endif;?>
                <? endfor;?>
                </tr>
            <? endfor;?>
            </tbody>
        <? endif;?>
    </table>
    </div>
    <div class="rn_IntegerPaginator">
          <ul class="pagination">
            <li><a class="back">&laquo;</a></li>
            <? if($this->data['total_pages'] > 0):?>
                <? for($pageNumber = 1; $pageNumber <= $this->data['total_pages']; $pageNumber++):?>
                    <? if ($this->isCurrentPage($pageNumber, 1)): ?>
                        <li class="paginator<?=$pageNumber?>"><a class="pageSelected page<?=$pageNumber?>"><?=$pageNumber;?></a></li>
                    <? elseif (!$this->shouldShowPageNumber($pageNumber, 1, $this->data['js']['total_pages'])): ?>
                        <li class="rn_HiddenPaginator paginator<?=$pageNumber?>"><a class="page<?=$pageNumber?>"><?=$pageNumber;?></a></li>
                        <? if ($this->shouldShowHellip($pageNumber, 1, $this->data['js']['total_pages'])): ?>
                            <li><span class="rn_PageHellip">&hellip;</span></li>
                        <? endif;?>
                    <? else:?>
                        <li class="paginator<?=$pageNumber?>"><a class="page<?=$pageNumber?>"><?=$pageNumber;?></a></li>
                        <? if ($this->shouldShowHellip($pageNumber, 1, $this->data['js']['total_pages'])): ?>
                            <li><span class="rn_PageHellip">&hellip;</span></li>
                        <? endif;?>
                    <? endif;?>
                <? endfor;?>
            <? endif;?>
            <li><a class="forward">&raquo;</a></li>
          </ul>
    </div>
</div>
</div>


