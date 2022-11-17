<?php

/** ****************************************************************************
 *
 *  CODING ASSESSMENT - REFACTORING
 *
 *  The code below was taken from a real legacy website we inherited
 *  For this exercise the challenge is simple and open ended
 *  Simply make this easier to read, understand and maintain.
 *
 *  You may refactor as much or as little as you think necessary
 *  ASSUMPTIONS
 *   - Assume the queries give you the arrays/objects as demonstrated so far
 *   - WE will assume your code works - this exercise is about code structuring.
 *
 * ****************************************************************************/




// Set Defaults
$today          = date('Y-m-d');

// Get a list of parameters passed into the URL
$eventuri       = $_SERVER['REQUEST_URI'];
$eventbreak     = explode('?', $eventuri);
$eventParams    = explode('&', $eventbreak[1] ?? '');

// clean the search inputs
$events_array = filter_var_array($_GET, FILTER_SANITIZE_STRING);
$eventsArray = is_array($events_array) ? array_filter($events_array) : [];

// Build up list of OPtions for various search form fields
// --------------------------------------------------------

// WHEN Drop Down Field
$when = array (
	'thisWeek' => 'This Week',
	'nextWeek' => 'Next Week',
	'thisMonth' => 'This Month',
	'nextMonth' => 'Next Month',
	'full' => 'Full',
);

// Now get all event Categories (except those sub categories above)
$taxonomies = get_terms(
	'event_category',
	array(
		'hide_empty' => true
	)
);


?>



	<!-- // Build Search Form -->
	<div class="feature-filters feature-filters--block">
		<p>Events Filters</p>
		<div class="grid-x grid-padding-x">

			<div class="cell medium-4">
				<label for="datepicker" class="show-for-sr">label</label>
				<input id="datepicker" value="<?php CheckDateExists($eventsArray); ?>" class="hasDatepicker" />
			</div>

			<div class="cell medium-4">
				<label for="seewhatson" class="show-for-sr">When it's on</label>
				<select name="period" class="field" id="seewhatson">
					<option value="">When</option>
					<?php
					foreach ($when as $whenkey => $whendate) {
						$selected    = '';
						if (empty($eventsArray['when'])) {
							echo '<option value="' . $whenkey . '" >' . $whendate . '</option>';
						} else {
							$eventPeriod = $eventsArray['when'];
							$selected = ($whenkey == $eventPeriod)  ? 'selected="selected"' : '';
							echo '<option value        ="' . $whenkey . '" ' . $selected . '>' . $whendate . '</option>';
						}
					}
					?>
				</select>
			</div>

			<div class="cell medium-4">
				<label for="placestogo" class="show-for-sr">Where it's on</label>
				<select name="location" class="field" id="placestogo">
					<option value="">Where</option>
					<?php
					$args = array(
						'post_type'         => 'event',
						'posts_per_page'    => -1,
						'fields'            => 'ids',
						'orderby'           => 'meta_value',
						'meta_key'          => 'location',
						'order'             => 'ASC',
						'meta_query'        => array(
							array(
								'key'       => 'end_date',
								'value'     => $today,
								'compare'   => '>=',
								'type'      => 'DATE'
							)
						)
					);
					$locations = get_posts($args);
					$eventLocations = array();
					foreach ($locations as $location) {
						$eventLocations[] = get_field('location', $location);
					}

					foreach (array_unique($eventLocations) as $eventLocation) {
						$searchedLocation = '';
						if (array_key_exists('where', $eventsArray)) {
							$searchedLocation = $eventsArray['where'];
						}
						$selected = '';

						if ($searchedLocation == $eventLocation) {
							$selected = 'selected="selected"';
						}

						echo '<option value="' . $eventLocation . '" '.$selected.'>' . $eventLocation . '</option>';
					}
					?>
				</select>
			</div>

			<div class="cell medium-4">
				<label for="eventsbytype" class="show-for-sr">Event Type</label>
				<select name="categoryID" class="field" id="eventsbytype">
					<option value="">Type</option>
					<?php
					foreach ($taxonomies as $term) {
						$searchedType = '';
						$termSlug = $term->slug;
						if (array_key_exists('type', $eventsArray)) {
							$searchedType = $eventsArray['type'];
						}

						$selected = '';

						if ($searchedType == $termSlug) {
							$selected = 'selected="selected"';
						}

						echo '<option value="'.$term->slug.'" '.$selected.'>'.$term->name.'</option>';
					}
					?>
				</select>
			</div>

			<div class="cell medium-4">
				<button class="cmd-filter cmd-filter--small event-filter" type="button">Filter Items</button>
				<button class="cmd-filter cmd-filter--small btn-clear" type="button">Clear Filters</button>
			</div>


		</div>
	</div>
	<!-- // Build Search Form -->


<?php

function CheckDateExists($eventsArray)
{
	if (array_key_exists('date', $eventsArray)) {
		foreach ($eventsArray as $key => $eventQuery) {
			if ($key == 'date') {
				echo $eventQuery;
			}
		}
	} else {
		echo date('d/m/Y');
	}
}



// begin Building Search Query
$selected_date = '';
$eventPeriodAfter = '';
$eventPeriodBefore = '';
$dateQuery = '';
$taxQuery = '';
if ($eventsArray) {
	foreach ($eventsArray as $key => $eventQuery) {
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
						'key'     => 'start_date',
						'value'   => $selected_date,
						'compare' => '<=',
						'type'    => 'DATE'
					),
					array(
						'key'     => 'end_date',
						'value'   => $selected_date,
						'compare' => '>=',
						'type'    => 'DATE'
					)
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'start_date',
						'value'   => $selected_date,
						'compare' => '=',
						'type'    => 'DATE'
					),
					array(
						'key'     => 'end_date',
						'value'   => $selected_date,
						'compare' => '=',
						'type'    => 'DATE'
					)
				)
			);
		}

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
						'key'       => 'end_date',
						'value'     => $today,
						'compare'   => '>=',
						'type'      => 'DATE'
					)
				);
			} else {
				$eventPeriodAfter = date('Ymd', $eventPeriodAfter);
				$eventPeriodBefore = date('Ymd', $eventPeriodBefore);
			}
		}

		if ($key == 'where') {
			$eventLocation = $eventQuery;
			$metaQuery = array(
				'key'       => 'location',
				'value'     => $eventLocation,
				'compare'   => 'LIKE'
			);
		}

		if ($key == 'type') {
			$cat = $eventQuery;
			$taxQuery = array (
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
			'key'       => 'end_date',
			'value'     => $today,
			'compare'   => '>=',
			'type'      => 'DATE'
		)
	);
}
