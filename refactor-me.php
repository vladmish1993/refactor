<?php
include('action.php');
?>
<!-- // Build Search Form -->
<div class="feature-filters feature-filters--block">
    <p>Events Filters</p>
    <div class="grid-x grid-padding-x">

        <!--Date-->
        <div class="cell medium-4">
            <label for="datepicker" class="show-for-sr">label</label>
            <input id="datepicker" value="<?= $arData['selectedValues']['date']; ?>" class="hasDatepicker"/>
        </div>

        <!--When-->
        <div class="cell medium-4">
            <label for="seewhatson" class="show-for-sr">When it's on</label>
            <select name="period" class="field" id="seewhatson">
                <option value="">When</option>
                <? foreach ($arData['whenValues'] as $whenKey => $whenDate): ?>
                    <option value="<?= $whenKey ?>" <?= array_key_exists('when', $arData['selectedValues']) && $whenKey == $arData['selectedValues']['when'] ? 'selected="selected"' : '' ?>><?= $whenDate ?></option>
                <? endforeach; ?>
            </select>
        </div>

        <!--Where-->
        <div class="cell medium-4">
            <label for="placestogo" class="show-for-sr">Where it's on</label>
            <select name="location" class="field" id="placestogo">
                <option value="">Where</option>
                <? foreach ($arData['eventLocations'] as $eventLocation): ?>
                    <option value="<?= $eventLocation ?>" <?= array_key_exists('where', $arData['selectedValues']) && $eventLocation == $arData['selectedValues']['where'] ? 'selected="selected"' : '' ?>><?= $eventLocation ?></option>
                <? endforeach; ?>
            </select>
        </div>

        <!--Type-->
        <div class="cell medium-4">
            <label for="eventsbytype" class="show-for-sr">Event Type</label>
            <select name="categoryID" class="field" id="eventsbytype">
                <option value="">Type</option>
                <? foreach ($arData['eventCategoryValues'] as $eventCategorySlug => $eventCategoryName): ?>
                    <option value="<?= $eventCategorySlug ?>" <?= array_key_exists('type', $arData['selectedValues']) && $eventCategorySlug == $arData['selectedValues']['type'] ? 'selected="selected"' : '' ?>><?= $eventCategoryName ?></option>
                <? endforeach; ?>
            </select>
        </div>

        <!--Buttons-->
        <div class="cell medium-4">
            <button class="cmd-filter cmd-filter--small event-filter" type="button">Filter Items</button>
            <button class="cmd-filter cmd-filter--small btn-clear" type="button">Clear Filters</button>
        </div>
    </div>
</div>
<!-- // Build Search Form -->