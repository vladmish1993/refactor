<?php

/*
 * Building a query to select posts with input params
 */
function buildSearchQuery($eventsArray = [])
{
    $today = date('Y-m-d');

    $metaQuery = [];
    $taxQuery = [];

    // begin Building Search Query
    if ($eventsArray) {
        foreach ($eventsArray as $key => $eventQuery) {
            //Query for date
            if ($key == 'date') {
                if (strpos($eventQuery, '%2F')) {
                    $eventQuery = urldecode($eventQuery);
                }
                $eventQuery = str_replace('/', '-', $eventQuery);
                $dateselect = strtotime($eventQuery);
                $selected_date = date('Y-m-d', $dateselect);

                $metaQuery = array(
                    'relation' => 'OR',
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => 'start_date',
                            'value' => $selected_date,
                            'compare' => '<=',
                            'type' => 'DATE'
                        ),
                        array(
                            'key' => 'end_date',
                            'value' => $selected_date,
                            'compare' => '>=',
                            'type' => 'DATE'
                        )
                    ),
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => 'start_date',
                            'value' => $selected_date,
                            'compare' => '=',
                            'type' => 'DATE'
                        ),
                        array(
                            'key' => 'end_date',
                            'value' => $selected_date,
                            'compare' => '=',
                            'type' => 'DATE'
                        )
                    )
                );
            }

            //Query for period
            if ($key == 'when') {
                $eventPeriod = $eventQuery;
                $eventPeriodAfter = '';
                $eventPeriodBefore = '';

                switch ($eventPeriod) {
                    case 'thisWeek';
                        $eventPeriodAfter = strtotime('now');
                        $eventPeriodBefore = strtotime('Sunday this week');
                        break;
                    case 'nextWeek';
                        $eventPeriodAfter = strtotime('Monday next week');
                        $eventPeriodBefore = strtotime('Sunday next week');
                        break;
                    case 'thisMonth';
                        $eventPeriodAfter = strtotime('now');
                        $eventPeriodBefore = strtotime('last day of this month');
                        break;
                    case 'nextMonth';
                        $eventPeriodAfter = strtotime('first day of next month');
                        $eventPeriodBefore = strtotime('last day of next month');
                        break;
                }

                if ($eventPeriod == 'full') {
                    $metaQuery = array(
                        'relation' => 'AND',
                        array(
                            'key' => 'end_date',
                            'value' => $today,
                            'compare' => '>=',
                            'type' => 'DATE'
                        )
                    );
                } else {
                    $eventPeriodAfter = date('Ymd', $eventPeriodAfter);
                    $eventPeriodBefore = date('Ymd', $eventPeriodBefore);
                }
            }

            //Query for place
            if ($key == 'where') {
                $eventLocation = $eventQuery;
                $metaQuery = array(
                    'key' => 'location',
                    'value' => $eventLocation,
                    'compare' => 'LIKE'
                );
            }

            //Query for type of the event
            if ($key == 'type') {
                $cat = $eventQuery;
                $taxQuery = array(
                    array(
                        'taxonomy' => 'event_category',
                        'field' => 'slug',
                        'terms' => $cat
                    )
                );
            }
        }
    } else {
        $metaQuery = array(
            'relation' => 'AND',
            array(
                'key' => 'end_date',
                'value' => $today,
                'compare' => '>=',
                'type' => 'DATE'
            )
        );
    }

    return ['metaQuery' => $metaQuery, 'taxQuery' => $taxQuery];
}