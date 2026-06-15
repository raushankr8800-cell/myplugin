<?php
/**
 * Property Tax Database and Calculations Engine
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns average property tax rates, assessment ratios, and county breakdowns for all 50 states
 */
function ust_get_property_tax_data() {
    return [
        'alabama' => ['rate' => 0.0040, 'assessment_ratio' => 0.10, 'counties' => [['name' => 'Jefferson County', 'rate' => 0.0055], ['name' => 'Mobile County', 'rate' => 0.0051], ['name' => 'Baldwin County', 'rate' => 0.0033]]],
        'alaska' => ['rate' => 0.0122, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Anchorage Municipality', 'rate' => 0.0132], ['name' => 'Fairbanks North Star Borough', 'rate' => 0.0125], ['name' => 'Matanuska-Susitna Borough', 'rate' => 0.0118]]],
        'arizona' => ['rate' => 0.0062, 'assessment_ratio' => 0.10, 'counties' => [['name' => 'Maricopa County', 'rate' => 0.0064], ['name' => 'Pima County', 'rate' => 0.0089], ['name' => 'Pinal County', 'rate' => 0.0078]]],
        'arkansas' => ['rate' => 0.0062, 'assessment_ratio' => 0.20, 'counties' => [['name' => 'Pulaski County', 'rate' => 0.0078], ['name' => 'Benton County', 'rate' => 0.0065], ['name' => 'Washington County', 'rate' => 0.0068]]],
        'california' => ['rate' => 0.0075, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Los Angeles County', 'rate' => 0.0082], ['name' => 'San Diego County', 'rate' => 0.0078], ['name' => 'Orange County', 'rate' => 0.0071]]],
        'colorado' => ['rate' => 0.0052, 'assessment_ratio' => 0.067, 'counties' => [['name' => 'Denver County', 'rate' => 0.0057], ['name' => 'El Paso County', 'rate' => 0.0048], ['name' => 'Arapahoe County', 'rate' => 0.0062]]],
        'connecticut' => ['rate' => 0.0215, 'assessment_ratio' => 0.70, 'counties' => [['name' => 'Fairfield County', 'rate' => 0.0182], ['name' => 'Hartford County', 'rate' => 0.0235], ['name' => 'New Haven County', 'rate' => 0.0242]]],
        'delaware' => ['rate' => 0.0058, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'New Castle County', 'rate' => 0.0079], ['name' => 'Sussex County', 'rate' => 0.0031], ['name' => 'Kent County', 'rate' => 0.0052]]],
        'florida' => ['rate' => 0.0086, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Miami-Dade County', 'rate' => 0.0102], ['name' => 'Broward County', 'rate' => 0.0115], ['name' => 'Hillsborough County', 'rate' => 0.0109]]],
        'georgia' => ['rate' => 0.0090, 'assessment_ratio' => 0.40, 'counties' => [['name' => 'Fulton County', 'rate' => 0.0105], ['name' => 'Gwinnett County', 'rate' => 0.0118], ['name' => 'Cobb County', 'rate' => 0.0084]]],
        'hawaii' => ['rate' => 0.0029, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Honolulu County', 'rate' => 0.0029], ['name' => 'Maui County', 'rate' => 0.0025], ['name' => 'Hawaii County', 'rate' => 0.0035]]],
        'idaho' => ['rate' => 0.0063, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Ada County', 'rate' => 0.0072], ['name' => 'Canyon County', 'rate' => 0.0085], ['name' => 'Kootenai County', 'rate' => 0.0055]]],
        'illinois' => ['rate' => 0.0208, 'assessment_ratio' => 0.33, 'counties' => [['name' => 'Cook County', 'rate' => 0.0210], ['name' => 'DuPage County', 'rate' => 0.0215], ['name' => 'Lake County', 'rate' => 0.0262]]],
        'indiana' => ['rate' => 0.0081, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Marion County', 'rate' => 0.0101], ['name' => 'Lake County', 'rate' => 0.0112], ['name' => 'Hamilton County', 'rate' => 0.0078]]],
        'iowa' => ['rate' => 0.0157, 'assessment_ratio' => 0.54, 'counties' => [['name' => 'Polk County', 'rate' => 0.0182], ['name' => 'Linn County', 'rate' => 0.0168], ['name' => 'Scott County', 'rate' => 0.0159]]],
        'kansas' => ['rate' => 0.0143, 'assessment_ratio' => 0.115, 'counties' => [['name' => 'Johnson County', 'rate' => 0.0138], ['name' => 'Sedgwick County', 'rate' => 0.0142], ['name' => 'Shawnee County', 'rate' => 0.0155]]],
        'kentucky' => ['rate' => 0.0085, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Jefferson County', 'rate' => 0.0098], ['name' => 'Fayette County', 'rate' => 0.0099], ['name' => 'Kenton County', 'rate' => 0.0105]]],
        'louisiana' => ['rate' => 0.0056, 'assessment_ratio' => 0.10, 'counties' => [['name' => 'East Baton Rouge Parish', 'rate' => 0.0065], ['name' => 'Orleans Parish', 'rate' => 0.0078], ['name' => 'Jefferson Parish', 'rate' => 0.0062]]],
        'maine' => ['rate' => 0.0128, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Cumberland County', 'rate' => 0.0133], ['name' => 'York County', 'rate' => 0.0122], ['name' => 'Penobscot County', 'rate' => 0.0145]]],
        'maryland' => ['rate' => 0.0105, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Montgomery County', 'rate' => 0.0102], ['name' => 'Prince George\'s County', 'rate' => 0.0135], ['name' => 'Baltimore County', 'rate' => 0.0122]]],
        'massachusetts' => ['rate' => 0.0120, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Middlesex County', 'rate' => 0.0118], ['name' => 'Worcester County', 'rate' => 0.0135], ['name' => 'Essex County', 'rate' => 0.0125]]],
        'michigan' => ['rate' => 0.0148, 'assessment_ratio' => 0.50, 'counties' => [['name' => 'Wayne County', 'rate' => 0.0205], ['name' => 'Oakland County', 'rate' => 0.0152], ['name' => 'Macomb County', 'rate' => 0.0175]]],
        'minnesota' => ['rate' => 0.0111, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Hennepin County', 'rate' => 0.0125], ['name' => 'Ramsey County', 'rate' => 0.0132], ['name' => 'Dakota County', 'rate' => 0.0110]]],
        'mississippi' => ['rate' => 0.0079, 'assessment_ratio' => 0.10, 'counties' => [['name' => 'Hinds County', 'rate' => 0.0102], ['name' => 'Harrison County', 'rate' => 0.0072], ['name' => 'DeSoto County', 'rate' => 0.0084]]],
        'missouri' => ['rate' => 0.0098, 'assessment_ratio' => 0.19, 'counties' => [['name' => 'St. Louis County', 'rate' => 0.0135], ['name' => 'Jackson County', 'rate' => 0.0122], ['name' => 'St. Charles County', 'rate' => 0.0110]]],
        'montana' => ['rate' => 0.0083, 'assessment_ratio' => 0.0135, 'counties' => [['name' => 'Yellowstone County', 'rate' => 0.0089], ['name' => 'Missoula County', 'rate' => 0.0095], ['name' => 'Gallatin County', 'rate' => 0.0075]]],
        'nebraska' => ['rate' => 0.0163, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Douglas County', 'rate' => 0.0192], ['name' => 'Lancaster County', 'rate' => 0.0188], ['name' => 'Sarpy County', 'rate' => 0.0202]]],
        'nevada' => ['rate' => 0.0059, 'assessment_ratio' => 0.35, 'counties' => [['name' => 'Clark County', 'rate' => 0.0062], ['name' => 'Washoe County', 'rate' => 0.0068], ['name' => 'Carson City', 'rate' => 0.0055]]],
        'new-hampshire' => ['rate' => 0.0193, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Hillsborough County', 'rate' => 0.0210], ['name' => 'Rockingham County', 'rate' => 0.0185], ['name' => 'Merrimack County', 'rate' => 0.0222]]],
        'new-jersey' => ['rate' => 0.0223, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Bergen County', 'rate' => 0.0215], ['name' => 'Essex County', 'rate' => 0.0245], ['name' => 'Monmouth County', 'rate' => 0.0201]]],
        'new-mexico' => ['rate' => 0.0079, 'assessment_ratio' => 0.33, 'counties' => [['name' => 'Bernalillo County', 'rate' => 0.0105], ['name' => 'Doña Ana County', 'rate' => 0.0085], ['name' => 'Santa Fe County', 'rate' => 0.0065]]],
        'new-york' => ['rate' => 0.0140, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Nassau County', 'rate' => 0.0175], ['name' => 'Suffolk County', 'rate' => 0.0182], ['name' => 'Westchester County', 'rate' => 0.0205]]],
        'north-carolina' => ['rate' => 0.0080, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Wake County', 'rate' => 0.0085], ['name' => 'Mecklenburg County', 'rate' => 0.0098], ['name' => 'Guilford County', 'rate' => 0.0089]]],
        'north-dakota' => ['rate' => 0.0099, 'assessment_ratio' => 0.045, 'counties' => [['name' => 'Cass County', 'rate' => 0.0110], ['name' => 'Grand Forks County', 'rate' => 0.0122], ['name' => 'Burleigh County', 'rate' => 0.0085]]],
        'ohio' => ['rate' => 0.0159, 'assessment_ratio' => 0.35, 'counties' => [['name' => 'Cuyahoga County', 'rate' => 0.0210], ['name' => 'Franklin County', 'rate' => 0.0195], ['name' => 'Hamilton County', 'rate' => 0.0168]]],
        'oklahoma' => ['rate' => 0.0090, 'assessment_ratio' => 0.11, 'counties' => [['name' => 'Oklahoma County', 'rate' => 0.0105], ['name' => 'Tulsa County', 'rate' => 0.0101], ['name' => 'Cleveland County', 'rate' => 0.0098]]],
        'oregon' => ['rate' => 0.0093, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Multnomah County', 'rate' => 0.0104], ['name' => 'Washington County', 'rate' => 0.0098], ['name' => 'Clackamas County', 'rate' => 0.0088]]],
        'pennsylvania' => ['rate' => 0.0149, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Philadelphia County', 'rate' => 0.0095], ['name' => 'Allegheny County', 'rate' => 0.0182], ['name' => 'Montgomery County', 'rate' => 0.0155]]],
        'rhode-island' => ['rate' => 0.0140, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Providence County', 'rate' => 0.0152], ['name' => 'Kent County', 'rate' => 0.0135], ['name' => 'Washington County', 'rate' => 0.0118]]],
        'south-carolina' => ['rate' => 0.0056, 'assessment_ratio' => 0.04, 'counties' => [['name' => 'Greenville County', 'rate' => 0.0062], ['name' => 'Charleston County', 'rate' => 0.0048], ['name' => 'Richland County', 'rate' => 0.0082]]],
        'south-dakota' => ['rate' => 0.0117, 'assessment_ratio' => 0.85, 'counties' => [['name' => 'Minnehaha County', 'rate' => 0.0132], ['name' => 'Pennington County', 'rate' => 0.0118], ['name' => 'Lincoln County', 'rate' => 0.0145]]],
        'tennessee' => ['rate' => 0.0066, 'assessment_ratio' => 0.25, 'counties' => [['name' => 'Shelby County', 'rate' => 0.0089], ['name' => 'Davidson County', 'rate' => 0.0072], ['name' => 'Knox County', 'rate' => 0.0061]]],
        'texas' => ['rate' => 0.0160, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Harris County', 'rate' => 0.0195], ['name' => 'Dallas County', 'rate' => 0.0191], ['name' => 'Tarrant County', 'rate' => 0.0202]]],
        'utah' => ['rate' => 0.0057, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Salt Lake County', 'rate' => 0.0065], ['name' => 'Utah County', 'rate' => 0.0052], ['name' => 'Davis County', 'rate' => 0.0058]]],
        'vermont' => ['rate' => 0.0180, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Chittenden County', 'rate' => 0.0195], ['name' => 'Rutland County', 'rate' => 0.0175], ['name' => 'Washington County', 'rate' => 0.0188]]],
        'virginia' => ['rate' => 0.0087, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Fairfax County', 'rate' => 0.0098], ['name' => 'Prince William County', 'rate' => 0.0115], ['name' => 'Virginia Beach', 'rate' => 0.0095]]],
        'washington' => ['rate' => 0.0087, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'King County', 'rate' => 0.0092], ['name' => 'Pierce County', 'rate' => 0.0105], ['name' => 'Snohomish County', 'rate' => 0.0098]]],
        'west-virginia' => ['rate' => 0.0057, 'assessment_ratio' => 0.60, 'counties' => [['name' => 'Kanawha County', 'rate' => 0.0062], ['name' => 'Berkeley County', 'rate' => 0.0055], ['name' => 'Monongalia County', 'rate' => 0.0059]]],
        'wisconsin' => ['rate' => 0.0161, 'assessment_ratio' => 1.00, 'counties' => [['name' => 'Milwaukee County', 'rate' => 0.0225], ['name' => 'Dane County', 'rate' => 0.0188], ['name' => 'Waukesha County', 'rate' => 0.0145]]],
        'wyoming' => ['rate' => 0.0056, 'assessment_ratio' => 0.095, 'counties' => [['name' => 'Laramie County', 'rate' => 0.0061], ['name' => 'Natrona County', 'rate' => 0.0058], ['name' => 'Albany County', 'rate' => 0.0068]]],
    ];
}

/**
 * Calculates Property Tax, assessed value, exemptions, monthly tax, and appreciation trend
 */
function ust_calculate_property_tax($home_value, $state_slug, $county_name = '', $homestead = false, $senior = false, $veteran = false) {
    $tax_data = ust_get_property_tax_data();
    
    $state_rate = 0.01; // default fallback 1%
    $assessment_ratio = 1.00; // default full assessment
    if (isset($tax_data[$state_slug])) {
        $state_rate = $tax_data[$state_slug]['rate'];
        $assessment_ratio = $tax_data[$state_slug]['assessment_ratio'];
    }

    // Determine target rate
    $applied_rate = $state_rate;
    $selected_county = 'Average Rate';
    if (!empty($county_name) && isset($tax_data[$state_slug]['counties'])) {
        foreach ($tax_data[$state_slug]['counties'] as $c) {
            if ($c['name'] === $county_name) {
                $applied_rate = $c['rate'];
                $selected_county = $c['name'];
                break;
            }
        }
    }

    // Assessed value before exemptions
    $assessed_value = $home_value * $assessment_ratio;

    // Apply exemptions (standard estimates for exemptions)
    $exempt_deduction = 0;
    if ($homestead) {
        $exempt_deduction += 25000 * $assessment_ratio; // typical homestead exemption
    }
    if ($senior) {
        $exempt_deduction += 10000 * $assessment_ratio; // typical senior exemption
    }
    if ($veteran) {
        $exempt_deduction += 15000 * $assessment_ratio; // typical veteran exemption
    }

    $taxable_value = max(0, $assessed_value - $exempt_deduction);

    // Calculate taxes
    $annual_tax = $taxable_value * $applied_rate;
    $monthly_tax = $annual_tax / 12;

    // 5-Year appreciation projection
    $projections = [];
    $current_value = $home_value;
    for ($i = 1; $i <= 5; $i++) {
        $current_value *= 1.03; // assume 3% annual appreciation
        $current_assessed = $current_value * $assessment_ratio;
        $current_taxable = max(0, $current_assessed - $exempt_deduction);
        $current_tax = $current_taxable * $applied_rate;
        $projections[] = [
            'year' => $i,
            'value' => $current_value,
            'tax' => $current_tax,
            'monthly' => $current_tax / 12
        ];
    }

    return [
        'home_value' => $home_value,
        'assessed_value' => $assessed_value,
        'exemptions' => $exempt_deduction,
        'taxable_value' => $taxable_value,
        'rate' => $applied_rate,
        'selected_county' => $selected_county,
        'annual_tax' => $annual_tax,
        'monthly_tax' => $monthly_tax,
        'projections' => $projections
    ];
}
