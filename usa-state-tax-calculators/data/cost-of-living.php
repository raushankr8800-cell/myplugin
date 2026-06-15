<?php
/**
 * Cost of Living Index data (US average = 100).
 * Approximate composite indices used for the Cost of Living Adjuster widget.
 */
if (!defined('ABSPATH')) exit;

function ust_get_col_index() {
    return [
        'alabama' => 88.3, 'alaska' => 125.0, 'arizona' => 108.4, 'arkansas' => 90.0,
        'california' => 138.5, 'colorado' => 105.6, 'connecticut' => 113.1, 'delaware' => 101.1,
        'florida' => 102.8, 'georgia' => 91.0, 'hawaii' => 186.0, 'idaho' => 98.2,
        'illinois' => 94.9, 'indiana' => 91.0, 'iowa' => 90.3, 'kansas' => 87.5,
        'kentucky' => 93.9, 'louisiana' => 91.0, 'maine' => 111.5, 'maryland' => 116.5,
        'massachusetts' => 146.5, 'michigan' => 91.5, 'minnesota' => 94.1, 'mississippi' => 86.3,
        'missouri' => 88.9, 'montana' => 102.6, 'nebraska' => 90.8, 'nevada' => 101.3,
        'new-hampshire' => 109.8, 'new-jersey' => 114.1, 'new-mexico' => 94.0, 'new-york' => 125.1,
        'north-carolina' => 96.5, 'north-dakota' => 94.6, 'ohio' => 93.7, 'oklahoma' => 86.0,
        'oregon' => 114.5, 'pennsylvania' => 101.7, 'rhode-island' => 110.7, 'south-carolina' => 96.5,
        'south-dakota' => 92.9, 'tennessee' => 90.3, 'texas' => 92.9, 'utah' => 103.2,
        'vermont' => 115.3, 'virginia' => 100.9, 'washington' => 114.9, 'west-virginia' => 90.5,
        'wisconsin' => 95.0, 'wyoming' => 92.6,
    ];
}
