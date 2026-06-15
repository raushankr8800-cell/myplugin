<?php
/**
 * Sales Tax Database and Calculations Engine
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns average sales tax rates, state baseline rates, and major counties/cities details for all 50 states
 */
function ust_get_sales_tax_data() {
    return [
        'alabama' => ['rate' => 0.0400, 'avg_local' => 0.0529, 'counties' => [['name' => 'Jefferson County (Birmingham)', 'rate' => 0.1000], ['name' => 'Mobile County (Mobile)', 'rate' => 0.1000], ['name' => 'Montgomery County (Montgomery)', 'rate' => 0.0850]]],
        'alaska' => ['rate' => 0.0000, 'avg_local' => 0.0182, 'counties' => [['name' => 'Juneau City & Borough', 'rate' => 0.0500], ['name' => 'Kenai Peninsula Borough', 'rate' => 0.0300], ['name' => 'Ketchikan Gateway Borough', 'rate' => 0.0400]]],
        'arizona' => ['rate' => 0.0560, 'avg_local' => 0.0277, 'counties' => [['name' => 'Maricopa County (Phoenix)', 'rate' => 0.0860], ['name' => 'Pima County (Tucson)', 'rate' => 0.0870], ['name' => 'Pinal County', 'rate' => 0.0720]]],
        'arkansas' => ['rate' => 0.0650, 'avg_local' => 0.0294, 'counties' => [['name' => 'Pulaski County (Little Rock)', 'rate' => 0.0900], ['name' => 'Benton County', 'rate' => 0.0850], ['name' => 'Washington County', 'rate' => 0.0875]]],
        'california' => ['rate' => 0.0725, 'avg_local' => 0.0160, 'counties' => [['name' => 'Los Angeles County', 'rate' => 0.0950], ['name' => 'San Francisco County', 'rate' => 0.08625], ['name' => 'San Diego County', 'rate' => 0.0775]]],
        'colorado' => ['rate' => 0.0290, 'avg_local' => 0.0491, 'counties' => [['name' => 'Denver County', 'rate' => 0.0881], ['name' => 'El Paso County (Colorado Springs)', 'rate' => 0.0825], ['name' => 'Arapahoe County', 'rate' => 0.0775]]],
        'connecticut' => ['rate' => 0.0635, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0635]]],
        'delaware' => ['rate' => 0.0000, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide (No Sales Tax)', 'rate' => 0.0000]]],
        'florida' => ['rate' => 0.0600, 'avg_local' => 0.0102, 'counties' => [['name' => 'Miami-Dade County', 'rate' => 0.0700], ['name' => 'Orange County (Orlando)', 'rate' => 0.0650], ['name' => 'Hillsborough County (Tampa)', 'rate' => 0.0750]]],
        'georgia' => ['rate' => 0.0400, 'avg_local' => 0.0338, 'counties' => [['name' => 'Fulton County (Atlanta)', 'rate' => 0.0890], ['name' => 'Gwinnett County', 'rate' => 0.0600], ['name' => 'DeKalb County', 'rate' => 0.0800]]],
        'hawaii' => ['rate' => 0.0400, 'avg_local' => 0.0044, 'counties' => [['name' => 'Honolulu County (Oahu)', 'rate' => 0.0450], ['name' => 'Hawaii County (Big Island)', 'rate' => 0.0450], ['name' => 'Maui County', 'rate' => 0.0400]]],
        'idaho' => ['rate' => 0.0600, 'avg_local' => 0.0003, 'counties' => [['name' => 'Ada County (Boise)', 'rate' => 0.0600], ['name' => 'Kootenai County', 'rate' => 0.0600], ['name' => 'Valley County (Resort)', 'rate' => 0.0700]]],
        'illinois' => ['rate' => 0.0625, 'avg_local' => 0.0261, 'counties' => [['name' => 'Cook County (Chicago)', 'rate' => 0.1025], ['name' => 'DuPage County', 'rate' => 0.0825], ['name' => 'Lake County', 'rate' => 0.0750]]],
        'indiana' => ['rate' => 0.0700, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0700]]],
        'iowa' => ['rate' => 0.0600, 'avg_local' => 0.0094, 'counties' => [['name' => 'Polk County (Des Moines)', 'rate' => 0.0700], ['name' => 'Linn County (Cedar Rapids)', 'rate' => 0.0700], ['name' => 'Scott County (Davenport)', 'rate' => 0.0700]]],
        'kansas' => ['rate' => 0.0650, 'avg_local' => 0.0225, 'counties' => [['name' => 'Johnson County (Overland Park)', 'rate' => 0.0960], ['name' => 'Sedgwick County (Wichita)', 'rate' => 0.0750], ['name' => 'Wyandotte County (Kansas City)', 'rate' => 0.09125]]],
        'kentucky' => ['rate' => 0.0600, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0600]]],
        'louisiana' => ['rate' => 0.0445, 'avg_local' => 0.0510, 'counties' => [['name' => 'East Baton Rouge Parish', 'rate' => 0.0995], ['name' => 'Orleans Parish (New Orleans)', 'rate' => 0.0945], ['name' => 'Jefferson Parish', 'rate' => 0.0920]]],
        'maine' => ['rate' => 0.0550, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0550]]],
        'maryland' => ['rate' => 0.0600, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0600]]],
        'massachusetts' => ['rate' => 0.0625, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0625]]],
        'michigan' => ['rate' => 0.0600, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0600]]],
        'minnesota' => ['rate' => 0.06875, 'avg_local' => 0.0065, 'counties' => [['name' => 'Hennepin County (Minneapolis)', 'rate' => 0.08025], ['name' => 'Ramsey County (St. Paul)', 'rate' => 0.08125], ['name' => 'Dakota County', 'rate' => 0.07375]]],
        'mississippi' => ['rate' => 0.0700, 'avg_local' => 0.0007, 'counties' => [['name' => 'Hinds County (Jackson)', 'rate' => 0.0800], ['name' => 'Harrison County', 'rate' => 0.0700], ['name' => 'DeSoto County', 'rate' => 0.0700]]],
        'missouri' => ['rate' => 0.04225, 'avg_local' => 0.0416, 'counties' => [['name' => 'St. Louis County', 'rate' => 0.0860], ['name' => 'Jackson County (Kansas City)', 'rate' => 0.0885], ['name' => 'St. Charles County', 'rate' => 0.0795]]],
        'montana' => ['rate' => 0.0000, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide (No Sales Tax)', 'rate' => 0.0000]]],
        'nebraska' => ['rate' => 0.0550, 'avg_local' => 0.0147, 'counties' => [['name' => 'Douglas County (Omaha)', 'rate' => 0.0700], ['name' => 'Lancaster County (Lincoln)', 'rate' => 0.0725], ['name' => 'Sarpy County', 'rate' => 0.0700]]],
        'nevada' => ['rate' => 0.0685, 'avg_local' => 0.0138, 'counties' => [['name' => 'Clark County (Las Vegas)', 'rate' => 0.08375], ['name' => 'Washoe County (Reno)', 'rate' => 0.08265], ['name' => 'Carson City', 'rate' => 0.0760]]],
        'new-hampshire' => ['rate' => 0.0000, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide (No Sales Tax)', 'rate' => 0.0000]]],
        'new-jersey' => ['rate' => 0.06625, 'avg_local' => -0.0003, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.06625], ['name' => 'Urban Enterprise Zone (UEZ)', 'rate' => 0.033125]]],
        'new-mexico' => ['rate' => 0.04875, 'avg_local' => 0.0284, 'counties' => [['name' => 'Bernalillo County (Albuquerque)', 'rate' => 0.0775], ['name' => 'Santa Fe County (Santa Fe)', 'rate' => 0.084375], ['name' => 'Doña Ana County (Las Cruces)', 'rate' => 0.083125]]],
        'new-york' => ['rate' => 0.0400, 'avg_local' => 0.0453, 'counties' => [['name' => 'New York City (NYC)', 'rate' => 0.08875], ['name' => 'Nassau County', 'rate' => 0.08625], ['name' => 'Suffolk County', 'rate' => 0.08625]]],
        'north-carolina' => ['rate' => 0.0475, 'avg_local' => 0.0225, 'counties' => [['name' => 'Wake County (Raleigh)', 'rate' => 0.0725], ['name' => 'Mecklenburg County (Charlotte)', 'rate' => 0.0725], ['name' => 'Durham County', 'rate' => 0.0750]]],
        'north-dakota' => ['rate' => 0.0500, 'avg_local' => 0.0198, 'counties' => [['name' => 'Cass County (Fargo)', 'rate' => 0.0750], ['name' => 'Grand Forks County', 'rate' => 0.0725], ['name' => 'Burleigh County (Bismarck)', 'rate' => 0.0650]]],
        'ohio' => ['rate' => 0.0575, 'avg_local' => 0.0149, 'counties' => [['name' => 'Cuyahoga County (Cleveland)', 'rate' => 0.0800], ['name' => 'Franklin County (Columbus)', 'rate' => 0.0750], ['name' => 'Hamilton County (Cincinnati)', 'rate' => 0.0780]]],
        'oklahoma' => ['rate' => 0.0450, 'avg_local' => 0.0449, 'counties' => [['name' => 'Oklahoma County (Oklahoma City)', 'rate' => 0.08625], ['name' => 'Tulsa County (Tulsa)', 'rate' => 0.08517], ['name' => 'Cleveland County (Norman)', 'rate' => 0.0875]]],
        'oregon' => ['rate' => 0.0000, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide (No Sales Tax)', 'rate' => 0.0000]]],
        'pennsylvania' => ['rate' => 0.0600, 'avg_local' => 0.0034, 'counties' => [['name' => 'Philadelphia County', 'rate' => 0.0800], ['name' => 'Allegheny County (Pittsburgh)', 'rate' => 0.0700], ['name' => 'Other Counties (Standard)', 'rate' => 0.0600]]],
        'rhode-island' => ['rate' => 0.0700, 'avg_local' => 0.0000, 'counties' => [['name' => 'Statewide rate (Standard)', 'rate' => 0.0700]]],
        'south-carolina' => ['rate' => 0.0600, 'avg_local' => 0.0150, 'counties' => [['name' => 'Charleston County', 'rate' => 0.0900], ['name' => 'Richland County (Columbia)', 'rate' => 0.0800], ['name' => 'Greenville County', 'rate' => 0.0600]]],
        'south-dakota' => ['rate' => 0.0420, 'avg_local' => 0.0190, 'counties' => [['name' => 'Minnehaha County (Sioux Falls)', 'rate' => 0.0650], ['name' => 'Pennington County (Rapid City)', 'rate' => 0.0650], ['name' => 'Brown County', 'rate' => 0.0620]]],
        'tennessee' => ['rate' => 0.0700, 'avg_local' => 0.0255, 'counties' => [['name' => 'Davidson County (Nashville)', 'rate' => 0.0925], ['name' => 'Shelby County (Memphis)', 'rate' => 0.0975], ['name' => 'Knox County (Knoxville)', 'rate' => 0.0925]]],
        'texas' => ['rate' => 0.0625, 'avg_local' => 0.0195, 'counties' => [['name' => 'Harris County (Houston)', 'rate' => 0.0825], ['name' => 'Dallas County (Dallas)', 'rate' => 0.0825], ['name' => 'Travis County (Austin)', 'rate' => 0.0825]]],
        'utah' => ['rate' => 0.0610, 'avg_local' => 0.0109, 'counties' => [['name' => 'Salt Lake County', 'rate' => 0.0725], ['name' => 'Utah County (Provo)', 'rate' => 0.0725], ['name' => 'Weber County (Ogden)', 'rate' => 0.0725]]],
        'vermont' => ['rate' => 0.0600, 'avg_local' => 0.0030, 'counties' => [['name' => 'Chittenden County (Burlington)', 'rate' => 0.0700], ['name' => 'Rutland County', 'rate' => 0.0600], ['name' => 'Windsor County', 'rate' => 0.0600]]],
        'virginia' => ['rate' => 0.0530, 'avg_local' => 0.0045, 'counties' => [['name' => 'Northern Virginia region', 'rate' => 0.0600], ['name' => 'Hampton Roads region', 'rate' => 0.0600], ['name' => 'Richmond City', 'rate' => 0.0600], ['name' => 'Other Counties', 'rate' => 0.0530]]],
        'washington' => ['rate' => 0.0650, 'avg_local' => 0.0288, 'counties' => [['name' => 'King County (Seattle)', 'rate' => 0.1025], ['name' => 'Pierce County (Tacoma)', 'rate' => 0.1030], ['name' => 'Spokane County', 'rate' => 0.0900]]],
        'west-virginia' => ['rate' => 0.0600, 'avg_local' => 0.0057, 'counties' => [['name' => 'Kanawha County (Charleston)', 'rate' => 0.0700], ['name' => 'Monongalia County (Morgantown)', 'rate' => 0.0700], ['name' => 'Cabell County (Huntington)', 'rate' => 0.0700]]],
        'wisconsin' => ['rate' => 0.0500, 'avg_local' => 0.0043, 'counties' => [['name' => 'Milwaukee County', 'rate' => 0.0590], ['name' => 'Dane County (Madison)', 'rate' => 0.0550], ['name' => 'Brown County (Green Bay)', 'rate' => 0.0550]]],
        'wyoming' => ['rate' => 0.0400, 'avg_local' => 0.0144, 'counties' => [['name' => 'Laramie County (Cheyenne)', 'rate' => 0.0600], ['name' => 'Natrona County (Casper)', 'rate' => 0.0500], ['name' => 'Teton County (Jackson)', 'rate' => 0.0600]]]
    ];
}
