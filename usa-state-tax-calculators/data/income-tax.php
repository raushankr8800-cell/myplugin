<?php
/**
 * Income Tax Database and Calculations Engine
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns standard deductions, bracket thresholds, and rates for federal and all 50 states
 */
function ust_get_income_tax_data() {
    return [
        'federal' => [
            'standard_deduction' => [
                'single' => 15750,
                'married' => 31500,
                'head' => 23625,
            ],
            'brackets' => [
                'single' => [
                    ['limit' => 11925, 'rate' => 0.10],
                    ['limit' => 48475, 'rate' => 0.12],
                    ['limit' => 103350, 'rate' => 0.22],
                    ['limit' => 197300, 'rate' => 0.24],
                    ['limit' => 250525, 'rate' => 0.32],
                    ['limit' => 626350, 'rate' => 0.35],
                    ['limit' => 999999999999, 'rate' => 0.37],
                ],
                'married' => [
                    ['limit' => 23850, 'rate' => 0.10],
                    ['limit' => 96950, 'rate' => 0.12],
                    ['limit' => 206700, 'rate' => 0.22],
                    ['limit' => 394600, 'rate' => 0.24],
                    ['limit' => 501050, 'rate' => 0.32],
                    ['limit' => 626350, 'rate' => 0.35],
                    ['limit' => 999999999999, 'rate' => 0.37],
                ],
                'head' => [
                    ['limit' => 17000, 'rate' => 0.10],
                    ['limit' => 64850, 'rate' => 0.12],
                    ['limit' => 103350, 'rate' => 0.22],
                    ['limit' => 197300, 'rate' => 0.24],
                    ['limit' => 250500, 'rate' => 0.32],
                    ['limit' => 626350, 'rate' => 0.35],
                    ['limit' => 999999999999, 'rate' => 0.37],
                ]
            ]
        ],
        'states' => [
            'alabama' => ['type' => 'graduated', 'deduction' => 3000, 'brackets' => [['limit' => 500, 'rate' => 0.02], ['limit' => 3000, 'rate' => 0.04], ['limit' => 999999999999, 'rate' => 0.05]]],
            'alaska' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'arizona' => ['type' => 'flat', 'deduction' => 14600, 'flat_rate' => 0.025],
            'arkansas' => ['type' => 'graduated', 'deduction' => 2340, 'brackets' => [['limit' => 5000, 'rate' => 0.02], ['limit' => 10000, 'rate' => 0.03], ['limit' => 999999999999, 'rate' => 0.044]]],
            'california' => ['type' => 'graduated', 'deduction' => 5363, 'brackets' => [['limit' => 10412, 'rate' => 0.01], ['limit' => 24684, 'rate' => 0.02], ['limit' => 38959, 'rate' => 0.04], ['limit' => 54081, 'rate' => 0.06], ['limit' => 68350, 'rate' => 0.08], ['limit' => 349137, 'rate' => 0.093], ['limit' => 418961, 'rate' => 0.103], ['limit' => 698271, 'rate' => 0.113], ['limit' => 999999999999, 'rate' => 0.123]]],
            'colorado' => ['type' => 'flat', 'deduction' => 14600, 'flat_rate' => 0.044],
            'connecticut' => ['type' => 'graduated', 'deduction' => 15000, 'brackets' => [['limit' => 10000, 'rate' => 0.03], ['limit' => 50000, 'rate' => 0.05], ['limit' => 100000, 'rate' => 0.055], ['limit' => 200000, 'rate' => 0.06], ['limit' => 250000, 'rate' => 0.065], ['limit' => 999999999999, 'rate' => 0.0699]]],
            'delaware' => ['type' => 'graduated', 'deduction' => 3250, 'brackets' => [['limit' => 5000, 'rate' => 0.022], ['limit' => 10000, 'rate' => 0.039], ['limit' => 20000, 'rate' => 0.048], ['limit' => 25000, 'rate' => 0.052], ['limit' => 60000, 'rate' => 0.0555], ['limit' => 999999999999, 'rate' => 0.066]]],
            'florida' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'georgia' => ['type' => 'flat', 'deduction' => 12000, 'flat_rate' => 0.0539],
            'hawaii' => ['type' => 'graduated', 'deduction' => 2200, 'brackets' => [['limit' => 4800, 'rate' => 0.032], ['limit' => 9600, 'rate' => 0.055], ['limit' => 19200, 'rate' => 0.068], ['limit' => 36000, 'rate' => 0.076], ['limit' => 48000, 'rate' => 0.0825], ['limit' => 999999999999, 'rate' => 0.11]]],
            'idaho' => ['type' => 'flat', 'deduction' => 14600, 'flat_rate' => 0.058],
            'illinois' => ['type' => 'flat', 'deduction' => 2425, 'flat_rate' => 0.0495],
            'indiana' => ['type' => 'flat', 'deduction' => 1000, 'flat_rate' => 0.0305],
            'iowa' => ['type' => 'flat', 'deduction' => 13850, 'flat_rate' => 0.038],
            'kansas' => ['type' => 'graduated', 'deduction' => 3500, 'brackets' => [['limit' => 15000, 'rate' => 0.031], ['limit' => 30000, 'rate' => 0.0525], ['limit' => 999999999999, 'rate' => 0.057]]],
            'kentucky' => ['type' => 'flat', 'deduction' => 2980, 'flat_rate' => 0.04],
            'louisiana' => ['type' => 'graduated', 'deduction' => 4500, 'brackets' => [['limit' => 12500, 'rate' => 0.0185], ['limit' => 50000, 'rate' => 0.035], ['limit' => 999999999999, 'rate' => 0.0425]]],
            'maine' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 24500, 'rate' => 0.058], ['limit' => 58050, 'rate' => 0.0675], ['limit' => 999999999999, 'rate' => 0.0715]]],
            'maryland' => ['type' => 'graduated', 'deduction' => 2550, 'brackets' => [['limit' => 3000, 'rate' => 0.03], ['limit' => 100000, 'rate' => 0.0475], ['limit' => 250000, 'rate' => 0.055], ['limit' => 999999999999, 'rate' => 0.0575]]],
            'massachusetts' => ['type' => 'flat', 'deduction' => 4400, 'flat_rate' => 0.05],
            'michigan' => ['type' => 'flat', 'deduction' => 5600, 'flat_rate' => 0.0425],
            'minnesota' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 30070, 'rate' => 0.0535], ['limit' => 98760, 'rate' => 0.068], ['limit' => 181230, 'rate' => 0.0785], ['limit' => 999999999999, 'rate' => 0.0985]]],
            'mississippi' => ['type' => 'flat', 'deduction' => 2300, 'flat_rate' => 0.047],
            'missouri' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 10000, 'rate' => 0.02], ['limit' => 80000, 'rate' => 0.04], ['limit' => 999999999999, 'rate' => 0.048]]],
            'montana' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 22000, 'rate' => 0.047], ['limit' => 999999999999, 'rate' => 0.059]]],
            'nebraska' => ['type' => 'graduated', 'deduction' => 7900, 'brackets' => [['limit' => 3760, 'rate' => 0.0246], ['limit' => 22350, 'rate' => 0.0351], ['limit' => 35740, 'rate' => 0.0501], ['limit' => 999999999999, 'rate' => 0.0584]]],
            'nevada' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'new-hampshire' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'new-jersey' => ['type' => 'graduated', 'deduction' => 1000, 'brackets' => [['limit' => 20000, 'rate' => 0.014], ['limit' => 35000, 'rate' => 0.0175], ['limit' => 40000, 'rate' => 0.035], ['limit' => 75000, 'rate' => 0.05525], ['limit' => 500000, 'rate' => 0.0637], ['limit' => 999999999999, 'rate' => 0.1075]]],
            'new-mexico' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 5500, 'rate' => 0.015], ['limit' => 11000, 'rate' => 0.032], ['limit' => 16000, 'rate' => 0.047], ['limit' => 999999999999, 'rate' => 0.059]]],
            'new-york' => ['type' => 'graduated', 'deduction' => 8000, 'brackets' => [['limit' => 8500, 'rate' => 0.04], ['limit' => 11700, 'rate' => 0.045], ['limit' => 13900, 'rate' => 0.0525], ['limit' => 21400, 'rate' => 0.0585], ['limit' => 80650, 'rate' => 0.0625], ['limit' => 215400, 'rate' => 0.0685], ['limit' => 1077550, 'rate' => 0.0965], ['limit' => 999999999999, 'rate' => 0.109]]],
            'north-carolina' => ['type' => 'flat', 'deduction' => 12750, 'flat_rate' => 0.045],
            'north-dakota' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 44725, 'rate' => 0.00], ['limit' => 999999999999, 'rate' => 0.025]]],
            'ohio' => ['type' => 'graduated', 'deduction' => 0, 'brackets' => [['limit' => 26050, 'rate' => 0.0138], ['limit' => 46100, 'rate' => 0.0275], ['limit' => 100000, 'rate' => 0.0325], ['limit' => 999999999999, 'rate' => 0.035]]],
            'oklahoma' => ['type' => 'graduated', 'deduction' => 8350, 'brackets' => [['limit' => 1000, 'rate' => 0.0025], ['limit' => 2500, 'rate' => 0.0075], ['limit' => 3750, 'rate' => 0.0175], ['limit' => 4900, 'rate' => 0.0275], ['limit' => 7200, 'rate' => 0.0375], ['limit' => 999999999999, 'rate' => 0.0475]]],
            'oregon' => ['type' => 'graduated', 'deduction' => 2745, 'brackets' => [['limit' => 4150, 'rate' => 0.0475], ['limit' => 10400, 'rate' => 0.0675], ['limit' => 125000, 'rate' => 0.0875], ['limit' => 999999999999, 'rate' => 0.099]]],
            'pennsylvania' => ['type' => 'flat', 'deduction' => 0, 'flat_rate' => 0.0307],
            'rhode-island' => ['type' => 'graduated', 'deduction' => 10050, 'brackets' => [['limit' => 73450, 'rate' => 0.0375], ['limit' => 166950, 'rate' => 0.0475], ['limit' => 999999999999, 'rate' => 0.0599]]],
            'south-carolina' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 3200, 'rate' => 0.00], ['limit' => 16000, 'rate' => 0.03], ['limit' => 999999999999, 'rate' => 0.064]]],
            'south-dakota' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'tennessee' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'texas' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'utah' => ['type' => 'flat', 'deduction' => 0, 'flat_rate' => 0.0455],
            'vermont' => ['type' => 'graduated', 'deduction' => 14600, 'brackets' => [['limit' => 45600, 'rate' => 0.0335], ['limit' => 110450, 'rate' => 0.066], ['limit' => 230000, 'rate' => 0.076], ['limit' => 999999999999, 'rate' => 0.0875]]],
            'virginia' => ['type' => 'graduated', 'deduction' => 8500, 'brackets' => [['limit' => 3000, 'rate' => 0.02], ['limit' => 5000, 'rate' => 0.03], ['limit' => 17000, 'rate' => 0.05], ['limit' => 999999999999, 'rate' => 0.0575]]],
            'washington' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
            'west-virginia' => ['type' => 'graduated', 'deduction' => 0, 'brackets' => [['limit' => 10000, 'rate' => 0.0236], ['limit' => 25000, 'rate' => 0.0315], ['limit' => 40000, 'rate' => 0.0354], ['limit' => 60000, 'rate' => 0.0472], ['limit' => 999999999999, 'rate' => 0.0512]]],
            'wisconsin' => ['type' => 'graduated', 'deduction' => 12760, 'brackets' => [['limit' => 14320, 'rate' => 0.035], ['limit' => 28640, 'rate' => 0.044], ['limit' => 315310, 'rate' => 0.053], ['limit' => 999999999999, 'rate' => 0.0765]]],
            'wyoming' => ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0],
        ]
    ];
}

/**
 * Calculates Federal Tax, FICA, State Tax, and Net Income
 */
function ust_calculate_income_tax($gross_income, $filing_status, $state_slug, $pretax_deductions = 0) {
    $tax_data = ust_get_income_tax_data();
    $filing_status = in_array($filing_status, ['single', 'married', 'head']) ? $filing_status : 'single';
    
    // Deductions
    $pretax = max(0, floatval($pretax_deductions));
    $taxable_fed = max(0, $gross_income - $pretax);

    // 1. FICA Deductions
    // Social Security (6.2% up to $176,100)
    $ss_limit = 176100;
    $ss_tax = min($gross_income, $ss_limit) * 0.062;
    // Medicare (1.45%, plus 0.9% additional over $200k for Single/Head, $250k for Married)
    $med_threshold = ($filing_status === 'married') ? 250000 : 200000;
    $med_tax = $gross_income * 0.0145;
    if ($gross_income > $med_threshold) {
        $med_tax += ($gross_income - $med_threshold) * 0.009;
    }
    $total_fica = $ss_tax + $med_tax;

    // 2. Federal Income Tax
    $fed_deduction = isset($tax_data['federal']['standard_deduction'][$filing_status]) ? $tax_data['federal']['standard_deduction'][$filing_status] : 15000;
    $fed_taxable_income = max(0, $fed_taxable = $taxable_fed - $fed_deduction);
    $fed_tax = 0;
    $prev_limit = 0;
    $fed_brackets = $tax_data['federal']['brackets'][$filing_status];
    foreach ($fed_brackets as $bracket) {
        $limit = $bracket['limit'];
        $rate = $bracket['rate'];
        if ($fed_taxable_income > $limit) {
            $fed_tax += ($limit - $prev_limit) * $rate;
            $prev_limit = $limit;
        } else {
            $fed_tax += ($fed_taxable_income - $prev_limit) * $rate;
            break;
        }
    }

    // 3. State Income Tax
    $state_tax = 0;
    $state_taxable_income = 0;
    $state_deduction = 0;
    if (isset($tax_data['states'][$state_slug])) {
        $state_info = $tax_data['states'][$state_slug];
        if ($state_info['type'] === 'flat') {
            $state_deduction = $state_info['deduction'];
            $state_taxable_income = max(0, $taxable_fed - $state_deduction);
            $state_tax = $state_taxable_income * $state_info['flat_rate'];
        } elseif ($state_info['type'] === 'graduated') {
            $state_deduction = $state_info['deduction'];
            // For married couples, double the standard deduction
            if ($filing_status === 'married') {
                $state_deduction *= 2;
            }
            $state_taxable_income = max(0, $taxable_fed - $state_deduction);
            $prev_limit = 0;
            foreach ($state_info['brackets'] as $bracket) {
                $limit = $bracket['limit'];
                // For married, double brackets (if not flat)
                if ($filing_status === 'married' && $limit !== 999999999999) {
                    $limit *= 2;
                }
                $rate = $bracket['rate'];
                if ($state_taxable_income > $limit) {
                    $state_tax += ($limit - $prev_limit) * $rate;
                    $prev_limit = $limit;
                } else {
                    $state_tax += ($state_taxable_income - $prev_limit) * $rate;
                    break;
                }
            }
        }
    }

    // Calculations
    $total_deductions = $total_fica + $fed_tax + $state_tax + $pretax;
    $net_income = max(0, $gross_income - $total_deductions);
    $effective_tax_rate = ($gross_income > 0) ? (($fed_tax + $state_tax + $total_fica) / $gross_income) * 100 : 0;

    return [
        'gross_income' => $gross_income,
        'pretax_deductions' => $pretax,
        'fed_deduction' => $fed_deduction,
        'state_deduction' => $state_deduction,
        'fed_taxable' => $fed_taxable_income,
        'state_taxable' => $state_taxable_income,
        'ss_tax' => $ss_tax,
        'med_tax' => $med_tax,
        'fica_tax' => $total_fica,
        'fed_tax' => $fed_tax,
        'state_tax' => $state_tax,
        'total_deductions' => $total_deductions,
        'net_income' => $net_income,
        'effective_rate' => $effective_tax_rate,
    ];
}

