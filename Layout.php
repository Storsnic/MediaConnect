<?php
/* @var $filters Filters */
/* @var $this CController */
use app\modules\tracker\helpers\AFilterHtml;
?>
<form id="settingsForm" method="GET" action="<?= $action ?>">
    <?php if ($isAdmin) {
        $this->renderPartial('setting_admin', compact('filters'));
    } ?>
    <div class="settingsColumn">
        <div class="filter">
            <span class="settingsFilterTitle"><strong>Параметры сворачивания парковок</strong></span>
            <div class="range">
                <span class="settingsTitle">Максимальная дистанция в метрах между конечными парковками:</span>
                <span class="colorval settingsVal"><?= $filters->maxDistBigPark->getValue() ?></span>
                <span class="colorval settingsValM">м</span>
                <?php AFilterHtml::inputRange($filters->maxDistBigPark, 'maxDistBigPark'); ?>
            </div>
            <div class="range">
                <span class="settingsTitle">Максимальная дистанция каждого элемента в метрах:</span>
                <span class="colorval settingsVal"><?= $filters->maxDistRemoveBigPark->getValue(); ?></span>
                <span class="colorval settingsValM">м</span>
                <?php AFilterHtml::inputRange($filters->maxDistRemoveBigPark, 'maxDistRemoveBigPark'); ?>
            </div>
        </div>
        <div class="filter">
            <?php AFilterHtml::checkbox($filters->violApptAndCall, 'violApptAndCall'); ?> 
            <span class="settingsFilterTitle">
                <strong>Встречи и звонки в нарушениях</strong>
            </span>
        </div>
        
    </div>
    <div class="settingsColumn settingsRightColumn">
        <?php 
            if($filters->getActiveTimedot()){
                $val = $filters->valTimedot->getValue();
                $m = 'мин';
                $timedot = 'true';
            }else{
                $val = $filters->valDistdot->getValue();
                $m = 'м';
                $timedot = 'false';
            }
        ?>
        <!-- Частота точек -->
        <div class="filter">
            <input type="hidden" name="timedot" data-default="<?= ($filters->valTimedot->getDefault() ? 'true' : 'false') ?>" data-value="<?= $timedot ?>" value="<?= $timedot ?>" />
            <span class="settingsFilterTitle"><strong>Частота точек с данными:</strong> 
                <span class="colorval settingsVal"><?= $val ?></span> 
                <span class="colorval settingsValM"><?= $m ?></span> 
            </span>
            
            <div class="range" data-valm="м">
                <span class="settingsTitle">По расстоянию</span> 
                <?php AFilterHtml::inputRange($filters->valDistdot, 'valDistdot'); ?>
            </div>
            <div class="range" data-valm="мин">
                <span class="settingsTitle">По времени</span> 
                <?php AFilterHtml::inputRange($filters->valTimedot, 'valTimedot'); ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div id="buttons">
        <a id="userSetting">Сбросить</a> 
        <a id="defaultSetting">Настроить по умолчанию</a>
        <input class="settingsSubmit" type="submit" value="Сохранить">
    </div>
</form>
