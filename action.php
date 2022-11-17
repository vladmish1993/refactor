<?php
include('functions.php');

$arData = [];
$selectedValues = [];

// Get a list of parameters passed into the URL
$eventuri = $_SERVER['REQUEST_URI'];
$eventbreak = explode('?', $eventuri);
$eventParams = explode('&', $eventbreak[1] ?? '');

// clean the search inputs
$events_array = filter_var_array($_GET, FILTER_SANITIZE_STRING);
$eventsArray = is_array($events_array) ? array_filter($events_array) : [];

// Datepicker
if (array_key_exists('date', $eventsArray)) {
    $selectedValues['date'] = $eventsArray['date'];
} else {
    $selectedValues['date'] = date('d/m/Y');
}

// WHEN Drop Down Field
$whenValues = array(
    'thisWeek' => 'This Week',
    'nextWeek' => 'Next Week',
    'thisMonth' => 'This Month',
    'nextMonth' => 'Next Month',
    'full' => 'Full',
);

$arData['whenValues'] = $whenValues;

if (!empty($eventsArray['when']) && array_key_exists($eventsArray['when'], $whenValues)) {
    $selectedValues['when'] = $eventsArray['when'];
}

//Get locations of events
$args = array(
    'post_type' => 'event',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'orderby' => 'meta_value',
    'meta_key' => 'location',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => 'end_date',
            'value' => date('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE'
        )
    )
);

//Get events
$locations = get_posts($args);
$eventLocations = array();
foreach ($locations as $location) {
    $eventLocations[] = get_field('location', $location);
}

$eventLocations = array_unique($eventLocations);
$arData['eventLocations'] = $eventLocations;

if (!empty($eventsArray['where']) && in_array($eventsArray['where'], $eventLocations)) {
    $selectedValues['where'] = $eventsArray['where'];
}

// Now get all event Categories (except those sub categories above)
$eventCategories = get_terms(
    'event_category',
    array(
        'hide_empty' => true
    )
);

foreach ($eventCategories as $eventCategory) {
    $eventCategorySlug = $eventCategory->slug;
    $eventCategoryName = $eventCategory->name;
    $arData['eventCategoryValues'][$eventCategorySlug] = $eventCategoryName;
}

if (!empty($eventsArray['type']) && array_key_exists($eventsArray['type'], $arData['eventCategoryValues'])) {
    $selectedValues['type'] = $eventsArray['type'];
}

$arData['selectedValues'] = $selectedValues;