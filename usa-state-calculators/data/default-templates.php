<?php
/**
 * Upgraded ADP/SmartAsset-Grade default HTML, CSS, and JS Calculator templates
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns default HTML, CSS, and JS based on calculator type and state
 */
function usc_get_default_templates($type, $state_slug) {
    if ($type === 'alimony') {
        if (function_exists('usc_get_alimony_templates')) {
            return usc_get_alimony_templates($state_slug);
        }
    }
    if ($type === 'property-tax') {
        if (function_exists('usc_get_property_tax_templates')) {
            return usc_get_property_tax_templates($state_slug);
        }
    }
    if ($type === 'mortgage') {
        if (function_exists('usc_get_mortgage_templates')) {
            return usc_get_mortgage_templates($state_slug);
        }
    }

    $states = usc_get_states_data();
    $state_name = isset($states[$state_slug]) ? $states[$state_slug]['name'] : 'USA';
    
    $data = [];
    if ($type === 'paycheck') {
        // ADVANCED ADP-GRADE PAYCHECK CALCULATOR HTML
        $data['html'] = '<!-- INFO BOX -->
<div class="info-box">
  📌 Estimate your net take-home pay, payroll taxes (FICA), state taxes, and marginal/effective tax rates in ' . esc_html($state_name) . '.
</div>

<!-- PAY TYPE TOGGLE -->
<div class="unit-row">
  <span style="font-size:12px;font-weight:700;color:var(--re);letter-spacing:.5px">GROSS PAY TYPE</span>
  <div class="unit-sw">
    <button class="unit-btn active" id="btnSalary" onclick="setPayType(\'salary\', this)">SALARY</button>
    <button class="unit-btn" id="btnHourly" onclick="setPayType(\'hourly\', this)">HOURLY</button>
  </div>
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">💵 INCOME DETAILS</label></div>
  
  <!-- GROSS INCOME -->
  <div class="field" id="gross-salary-field">
    <label class="lbl">GROSS ANNUAL SALARY</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="gross-salary" placeholder="e.g. 75000" value="80000">
      <span class="inp-suffix">$</span>
    </div>
    <div class="hint">Annual salary before taxes and deductions</div>
  </div>

  <div id="gross-hourly-fields" style="display:none;">
    <div class="grid2">
      <div class="field">
        <label class="lbl">HOURLY RATE</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="hourly-rate" placeholder="e.g. 25" value="30">
          <span class="inp-suffix">$/hr</span>
        </div>
      </div>
      <div class="field">
        <label class="lbl">HOURS PER WEEK</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="hourly-hours" placeholder="e.g. 40" value="40">
          <span class="inp-suffix">hrs</span>
        </div>
      </div>
    </div>
  </div>

  <!-- PAY FREQUENCY -->
  <div class="field">
    <label class="lbl">PAY FREQUENCY</label>
    <div class="sel-wrap">
      <select class="sel" id="pay-frequency">
        <option value="52">Weekly (52 checks/yr)</option>
        <option value="26" selected>Bi-weekly (26 checks/yr)</option>
        <option value="24">Semi-monthly (24 checks/yr)</option>
        <option value="12">Monthly (12 checks/yr)</option>
      </select>
    </div>
  </div>
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">📋 FEDERAL FILING STATUS & ALLOWANCES</label></div>
  
  <!-- W-4 FORM VERSION SWITCHER -->
  <div class="field">
    <label class="lbl">W-4 FORM VERSION</label>
    <div class="tog-group" style="margin-bottom:12px;">
      <button class="tog-btn active" id="btnW4New" onclick="setW4Version(\'new\', this)">2020 & Later Form</button>
      <button class="tog-btn" id="btnW4Old" onclick="setW4Version(\'old\', this)">Prior to 2020 Form</button>
    </div>
  </div>

  <!-- FILING STATUS -->
  <div class="field">
    <label class="lbl">FEDERAL FILING STATUS</label>
    <div class="tog-group">
      <button class="tog-btn active" data-status="single" onclick="setFilingStatus(\'single\', this)">Single</button>
      <button class="tog-btn" data-status="married" onclick="setFilingStatus(\'married\', this)">Married</button>
      <button class="tog-btn" data-status="head" onclick="setFilingStatus(\'head\', this)">Head of Household</button>
    </div>
  </div>

  <!-- FEDERAL ALLOWANCES (For Pre-2020) -->
  <div class="field" id="fed-allowances-field" style="display:none;">
    <label class="lbl">FEDERAL ALLOWANCES CLAIMED</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="fed-allowances" value="1" min="0">
      <span class="inp-suffix">allowances</span>
    </div>
    <div class="hint">Number of allowances claimed on Form W-4 (legacy)</div>
  </div>

  <!-- STATE SPECIFIC WITHHOLDING -->
  <div class="grid2">
    <div class="field">
      <label class="lbl">STATE ALLOWANCES / EXEMPTIONS</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="state-allowances" value="1" min="0">
        <span class="inp-suffix">allowances</span>
      </div>
      <div class="hint">DE-4 (CA) or IT-2104 (NY) allowances</div>
    </div>
    <div class="field">
      <label class="lbl">EXTRA STATE WITHHOLDING</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="state-extra" value="0" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Additional state tax per pay period</div>
    </div>
  </div>

  <!-- LOCAL CITY TAXES dropdown -->
  <div class="field">
    <label class="lbl">LOCAL CITY / COUNTY TAXES</label>
    <div class="sel-wrap">
      <select class="sel" id="local-tax-type">
        <option value="none">None / Standard Local Taxes</option>
        <option value="nyc">New York City (NYC) Resident (3.078% - 3.876%)</option>
        <option value="philadelphia">Philadelphia (PA) Resident (3.75%)</option>
        <option value="indiana">Indiana County Average (1.75%)</option>
      </select>
    </div>
    <div class="hint">Choose if you reside in a high-withholding local tax jurisdiction</div>
  </div>

  <!-- ADVANCED W-4 ACCORDION -->
  <div id="w4-new-settings" class="det-card" style="background:var(--bg2); border-style:dashed; margin-bottom:15px;">
    <div class="det-title" style="cursor:pointer; display:flex; justify-content:space-between;" onclick="toggleW4Settings()">
      <span>⚙️ ADVANCED W-4 SETTINGS (OPTIONAL)</span>
      <span id="w4-toggle-icon">▾</span>
    </div>
    <div id="w4-settings-fields" style="display:none; margin-top:10px;">
      <div class="field">
        <label class="lbl">
          <input type="checkbox" id="w4-step2" value="1">
          Multiple Jobs / Spouse Works (W-4 Step 2c)
        </label>
      </div>
      <div class="grid2">
        <div class="field">
          <label class="lbl">Dependents Credit (Step 3)</label>
          <div class="inp-wrap">
            <input type="number" class="inp" id="w4-step3" value="0" min="0">
            <span class="inp-suffix">$</span>
          </div>
          <div class="hint">e.g., $2,000 per qualifying child</div>
        </div>
        <div class="field">
          <label class="lbl">Other Annual Income (Step 4a)</label>
          <div class="inp-wrap">
            <input type="number" class="inp" id="w4-step4a" value="0" min="0">
            <span class="inp-suffix">$</span>
          </div>
        </div>
      </div>
      <div class="grid2">
        <div class="field">
          <label class="lbl">Annual Deductions (Step 4b)</label>
          <div class="inp-wrap">
            <input type="number" class="inp" id="w4-step4b" value="0" min="0">
            <span class="inp-suffix">$</span>
          </div>
        </div>
        <div class="field">
          <label class="lbl">Extra Withholding / Pay (Step 4c)</label>
          <div class="inp-wrap">
            <input type="number" class="inp" id="w4-step4c" value="0" min="0">
            <span class="inp-suffix">$</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- DETAILED DEDUCTIONS -->
<div class="sec">
  <div class="sec-head"><label class="lbl">🛡️ PRE-TAX & POST-TAX DEDUCTIONS</label></div>
  
  <div class="grid2">
    <!-- 401K PRE-TAX -->
    <div class="field">
      <label class="lbl">401(K) / 403(B) PRE-TAX</label>
      <div class="grid2" style="grid-template-columns: 80px 1fr; gap:5px;">
        <select class="sel" id="401k-type" style="padding: 10px 20px 10px 8px;">
          <option value="pct">%</option>
          <option value="flat">$</option>
        </select>
        <input type="number" class="inp" id="401k-val" value="6" min="0">
      </div>
    </div>
    <!-- ROTH 401K POST-TAX -->
    <div class="field">
      <label class="lbl">ROTH 401(K) POST-TAX</label>
      <div class="grid2" style="grid-template-columns: 80px 1fr; gap:5px;">
        <select class="sel" id="roth-type" style="padding: 10px 20px 10px 8px;">
          <option value="pct">%</option>
          <option value="flat" selected>$</option>
        </select>
        <input type="number" class="inp" id="roth-val" value="0" min="0">
      </div>
    </div>
  </div>

  <div class="grid3">
    <!-- HEALTH INSURANCE -->
    <div class="field">
      <label class="lbl">MEDICAL INSURANCE</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="pre-tax-med" value="120" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Pre-tax premium / pay</div>
    </div>
    <!-- HSA / FSA -->
    <div class="field">
      <label class="lbl">HSA / FSA CONTR.</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="pre-tax-hsa" value="0" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Pre-tax contribution / pay</div>
    </div>
    <!-- GARNISHMENTS / OTHER -->
    <div class="field">
      <label class="lbl">GARNISHMENTS / MISC</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="post-tax-misc" value="0" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Post-tax child support, union dues</div>
    </div>
  </div>
</div>

<!-- CALCULATE BTN -->
<button class="calc-btn" onclick="calculatePaycheck(true)">
  💵 CALCULATE TAKE-HOME PAY
</button>

<!-- RESULTS container -->
<div class="results" id="results"></div><div id="paycheck-scenarios-container" style="display: none;"></div>';

        // ADVANCED PAYCHECK CALCULATOR JS
        $data['js'] = 'var payType = "salary";
var filingStatus = "single";
var stateSlug = "' . esc_js($state_slug) . '";
var w4Expanded = false;
var w4Version = "new";
var resultsCalculated = false;

var colIndexData = {
    "alabama": 88.0, "alaska": 124.0, "arizona": 106.0, "arkansas": 86.0, "california": 138.5,
    "colorado": 105.0, "connecticut": 116.0, "delaware": 102.0, "florida": 102.8, "georgia": 91.0,
    "hawaii": 184.0, "idaho": 95.0, "illinois": 91.8, "indiana": 90.0, "iowa": 89.0,
    "kansas": 87.0, "kentucky": 89.0, "louisiana": 91.0, "maine": 111.0, "maryland": 114.0,
    "massachusetts": 148.0, "michigan": 92.0, "minnesota": 94.0, "mississippi": 85.3, "missouri": 88.5,
    "montana": 102.0, "nebraska": 91.0, "nevada": 101.0, "new-hampshire": 114.0, "new-jersey": 115.0,
    "new-mexico": 94.0, "new-york": 125.1, "north-carolina": 96.0, "north-dakota": 94.0, "ohio": 91.0,
    "oklahoma": 86.0, "oregon": 115.0, "pennsylvania": 98.0, "rhode-island": 113.0, "south-carolina": 96.0,
    "south-dakota": 92.0, "tennessee": 90.0, "texas": 93.0, "utah": 102.0, "vermont": 114.0,
    "virginia": 102.0, "washington": 115.0, "west-virginia": 88.0, "wisconsin": 96.0, "wyoming": 93.0
};

var usStatesList = [
    {slug: "alabama", name: "Alabama"}, {slug: "alaska", name: "Alaska"}, {slug: "arizona", name: "Arizona"},
    {slug: "arkansas", name: "Arkansas"}, {slug: "california", name: "California"}, {slug: "colorado", name: "Colorado"},
    {slug: "connecticut", name: "Connecticut"}, {slug: "delaware", name: "Delaware"}, {slug: "florida", name: "Florida"},
    {slug: "georgia", name: "Georgia"}, {slug: "hawaii", name: "Hawaii"}, {slug: "idaho", name: "Idaho"},
    {slug: "illinois", name: "Illinois"}, {slug: "indiana", name: "Indiana"}, {slug: "iowa", name: "Iowa"},
    {slug: "kansas", name: "Kansas"}, {slug: "kentucky", name: "Kentucky"}, {slug: "louisiana", name: "Louisiana"},
    {slug: "maine", name: "Maine"}, {slug: "maryland", name: "Maryland"}, {slug: "massachusetts", name: "Massachusetts"},
    {slug: "michigan", name: "Michigan"}, {slug: "minnesota", name: "Minnesota"}, {slug: "mississippi", name: "Mississippi"},
    {slug: "missouri", name: "Missouri"}, {slug: "montana", name: "Montana"}, {slug: "nebraska", name: "Nebraska"},
    {slug: "nevada", name: "Nevada"}, {slug: "new-hampshire", name: "New Hampshire"}, {slug: "new-jersey", name: "New Jersey"},
    {slug: "new-mexico", name: "New Mexico"}, {slug: "new-york", name: "New York"}, {slug: "north-carolina", name: "North Carolina"},
    {slug: "north-dakota", name: "North Dakota"}, {slug: "ohio", name: "Ohio"}, {slug: "oklahoma", name: "Oklahoma"},
    {slug: "oregon", name: "Oregon"}, {slug: "pennsylvania", name: "Pennsylvania"}, {slug: "rhode-island", name: "Rhode Island"},
    {slug: "south-carolina", name: "South Carolina"}, {slug: "south-dakota", name: "South Dakota"}, {slug: "tennessee", name: "Tennessee"},
    {slug: "texas", name: "Texas"}, {slug: "utah", name: "Utah"}, {slug: "vermont", name: "Vermont"},
    {slug: "virginia", name: "Virginia"}, {slug: "washington", name: "Washington"}, {slug: "west-virginia", name: "West Virginia"},
    {slug: "wisconsin", name: "Wisconsin"}, {slug: "wyoming", name: "Wyoming"}
];

function setPayType(type, btn) {
    payType = type;
    document.getElementById("btnSalary").classList.toggle("active", type === "salary");
    document.getElementById("btnHourly").classList.toggle("active", type === "hourly");
    
    if (type === "salary") {
        document.getElementById("gross-salary-field").style.display = "block";
        document.getElementById("gross-hourly-fields").style.display = "none";
    } else {
        document.getElementById("gross-salary-field").style.display = "none";
        document.getElementById("gross-hourly-fields").style.display = "block";
    }
}

function setFilingStatus(status, btn) {
    filingStatus = status;
    document.querySelectorAll("[data-status]").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
}

function setW4Version(version, btn) {
    w4Version = version;
    document.getElementById("btnW4New").classList.toggle("active", version === "new");
    document.getElementById("btnW4Old").classList.toggle("active", version === "old");
    
    if (version === "new") {
        document.getElementById("w4-new-settings").style.display = "block";
        document.getElementById("fed-allowances-field").style.display = "none";
    } else {
        document.getElementById("w4-new-settings").style.display = "none";
        document.getElementById("fed-allowances-field").style.display = "block";
    }
}

function toggleW4Settings() {
    w4Expanded = !w4Expanded;
    document.getElementById("w4-settings-fields").style.display = w4Expanded ? "block" : "none";
    document.getElementById("w4-toggle-icon").innerText = w4Expanded ? "▴" : "▾";
}

function calculatePaycheck(forceShow) {
    if (forceShow === true) {
        resultsCalculated = true;
    }
    if (!resultsCalculated) {
        return;
    }
    var gross = 0;
    var freq = parseFloat(document.getElementById("pay-frequency").value);
    
    if (payType === "salary") {
        gross = parseFloat(document.getElementById("gross-salary").value) || 0;
    } else {
        var rate = parseFloat(document.getElementById("hourly-rate").value) || 0;
        var hours = parseFloat(document.getElementById("hourly-hours").value) || 0;
        gross = rate * hours * 52; 
    }

    var grossPerPay = gross / freq;

    // Granular Pre-Tax Calculations
    var type401k = document.getElementById("401k-type").value;
    var val401k = parseFloat(document.getElementById("401k-val").value) || 0;
    var pre401kPerPay = (type401k === "pct") ? (grossPerPay * (val401k / 100)) : val401k;

    var preMedPerPay = parseFloat(document.getElementById("pre-tax-med").value) || 0;
    var preHsaPerPay = parseFloat(document.getElementById("pre-tax-hsa").value) || 0;
    
    var totalPreTaxDeductions = pre401kPerPay + preMedPerPay + preHsaPerPay;
    var taxableGrossPerPay = Math.max(0, grossPerPay - totalPreTaxDeductions);
    var annualTaxableGross = taxableGrossPerPay * freq;

    // Granular Post-Tax Calculations
    var typeRoth = document.getElementById("roth-type").value;
    var valRoth = parseFloat(document.getElementById("roth-val").value) || 0;
    var postRothPerPay = (typeRoth === "pct") ? (grossPerPay * (valRoth / 100)) : valRoth;

    var postMiscPerPay = parseFloat(document.getElementById("post-tax-misc").value) || 0;
    var totalPostTaxDeductions = postRothPerPay + postMiscPerPay;

    // W-4 parameters
    var w4MultipleJobs = document.getElementById("w4-step2").checked;
    var w4Dependents = parseFloat(document.getElementById("w4-step3").value) || 0;
    var w4OtherIncome = parseFloat(document.getElementById("w4-step4a").value) || 0;
    var w4Deductions = parseFloat(document.getElementById("w4-step4b").value) || 0;
    var w4ExtraWithholding = parseFloat(document.getElementById("w4-step4c").value) || 0;
    var stateAllowances = parseFloat(document.getElementById("state-allowances").value) || 0;
    var stateExtra = parseFloat(document.getElementById("state-extra").value) || 0;
    var fedAllowances = parseFloat(document.getElementById("fed-allowances").value) || 0;

    // 1. Calculate Federal Income Tax (2026 standard brackets lookup)
    var standardDeduction = (filingStatus === "married") ? 29200 : 14600;
    if (filingStatus === "head") standardDeduction = 21900;
    if (w4Version === "new" && w4Deductions > 0) standardDeduction = w4Deductions;

    var taxableFed = 0;
    if (w4Version === "new") {
        taxableFed = Math.max(0, annualTaxableGross + w4OtherIncome - standardDeduction);
    } else {
        var legacyDeduction = (filingStatus === "married") ? 24000 : 12000;
        if (filingStatus === "head") legacyDeduction = 18000;
        taxableFed = Math.max(0, annualTaxableGross - (fedAllowances * 4300) - legacyDeduction);
    }

    var fedTaxAnnual = 0;
    var marginalRate = 10; // Lowest bracket

    if (filingStatus === "single" || filingStatus === "married_separately") {
        if (taxableFed <= 11600) { fedTaxAnnual = taxableFed * 0.10; marginalRate = 10; }
        else if (taxableFed <= 47150) { fedTaxAnnual = 1160 + (taxableFed - 11600) * 0.12; marginalRate = 12; }
        else if (taxableFed <= 100525) { fedTaxAnnual = 5426 + (taxableFed - 47150) * 0.22; marginalRate = 22; }
        else if (taxableFed <= 191950) { fedTaxAnnual = 17168.50 + (taxableFed - 100525) * 0.24; marginalRate = 24; }
        else if (taxableFed <= 243725) { fedTaxAnnual = 39110.50 + (taxableFed - 191950) * 0.32; marginalRate = 32; }
        else if (taxableFed <= 609350) { fedTaxAnnual = 55678.50 + (taxableFed - 243725) * 0.35; marginalRate = 35; }
        else { fedTaxAnnual = 183647.25 + (taxableFed - 609350) * 0.37; marginalRate = 37; }
    } else if (filingStatus === "married") {
        if (taxableFed <= 23200) { fedTaxAnnual = taxableFed * 0.10; marginalRate = 10; }
        else if (taxableFed <= 94300) { fedTaxAnnual = 2320 + (taxableFed - 23200) * 0.12; marginalRate = 12; }
        else if (taxableFed <= 201050) { fedTaxAnnual = 10852 + (taxableFed - 94300) * 0.22; marginalRate = 22; }
        else if (taxableFed <= 383900) { fedTaxAnnual = 34337 + (taxableFed - 201050) * 0.24; marginalRate = 24; }
        else if (taxableFed <= 487450) { fedTaxAnnual = 78221 + (taxableFed - 383900) * 0.32; marginalRate = 32; }
        else if (taxableFed <= 731200) { fedTaxAnnual = 111357 + (taxableFed - 487450) * 0.35; marginalRate = 35; }
        else { fedTaxAnnual = 196669.50 + (taxableFed - 731200) * 0.37; marginalRate = 37; }
    } else {
        if (taxableFed <= 16550) { fedTaxAnnual = taxableFed * 0.10; marginalRate = 10; }
        else if (taxableFed <= 63100) { fedTaxAnnual = 1655 + (taxableFed - 16550) * 0.12; marginalRate = 12; }
        else if (taxableFed <= 100500) { fedTaxAnnual = 7241 + (taxableFed - 63100) * 0.22; marginalRate = 22; }
        else if (taxableFed <= 191900) { fedTaxAnnual = 15469 + (taxableFed - 100500) * 0.24; marginalRate = 24; }
        else if (taxableFed <= 243700) { fedTaxAnnual = 37405 + (taxableFed - 191900) * 0.32; marginalRate = 32; }
        else if (taxableFed <= 609300) { fedTaxAnnual = 53981 + (taxableFed - 243700) * 0.35; marginalRate = 35; }
        else { fedTaxAnnual = 181941 + (taxableFed - 609300) * 0.37; marginalRate = 37; }
    }

    if (w4Version === "new") {
        if (w4MultipleJobs) fedTaxAnnual *= 1.25;
        fedTaxAnnual = Math.max(0, fedTaxAnnual - w4Dependents);
    }
    var fedTaxPerPay = (fedTaxAnnual / freq) + (w4Version === "new" ? w4ExtraWithholding : 0);

    // 2. Social Security & Medicare (FICA) Calculations
    var ssCap = 176100;
    var ssTaxableAnnual = Math.min(annualTaxableGross, ssCap);
    var ssTaxPerPay = (ssTaxableAnnual * 0.062) / freq;

    var medTaxPerPay = taxableGrossPerPay * 0.0145;
    var medSurchargeLimit = (filingStatus === "married") ? 250000 : 200000;
    if (annualTaxableGross > medSurchargeLimit) {
        var excessWage = annualTaxableGross - medSurchargeLimit;
        medTaxPerPay += ((excessWage * 0.009) / freq);
    }

    // 3. State Tax Calculations
    var stateTaxAnnual = 0;
    var localTaxPerPay = 0; 
    var zeroStates = ["texas", "florida", "alaska", "nevada", "south-dakota", "tennessee", "washington", "wyoming", "new-hampshire"];
    var flatStates = {
        "arizona": 0.025, "colorado": 0.044, "georgia": 0.0539, "idaho": 0.058, "illinois": 0.0495,
        "indiana": 0.0305, "kentucky": 0.040, "michigan": 0.0425, "north-carolina": 0.045, "pennsylvania": 0.0307,
        "utah": 0.0455, "mississippi": 0.05
    };

    if (zeroStates.indexOf(stateSlug) !== -1) {
        stateTaxAnnual = 0;
    } else if (flatStates[stateSlug] !== undefined) {
        var stateTaxableBase = Math.max(0, annualTaxableGross - (stateAllowances * 1000));
        stateTaxAnnual = stateTaxableBase * flatStates[stateSlug];
    } else {
        var stateDeduct = (filingStatus === "married") ? 10726 : 5363;
        var stateTaxable = Math.max(0, annualTaxableGross - stateDeduct - (stateAllowances * 1000));
        if (stateSlug === "california") {
            if (stateTaxable <= 10412) stateTaxAnnual = stateTaxable * 0.01;
            else if (stateTaxable <= 24684) stateTaxAnnual = 104.12 + (stateTaxable - 10412) * 0.02;
            else if (stateTaxable <= 38959) stateTaxAnnual = 389.56 + (stateTaxable - 24684) * 0.04;
            else if (stateTaxable <= 54081) stateTaxAnnual = 960.56 + (stateTaxable - 38959) * 0.06;
            else if (stateTaxable <= 68350) stateTaxAnnual = 1867.88 + (stateTaxable - 54081) * 0.08;
            else if (stateTaxable <= 349137) stateTaxAnnual = 3009.40 + (stateTaxable - 68350) * 0.093;
            else if (stateTaxable <= 418961) stateTaxAnnual = 29122.59 + (stateTaxable - 349137) * 0.103;
            else if (stateTaxable <= 698271) stateTaxAnnual = 36314.46 + (stateTaxable - 418961) * 0.113;
            else stateTaxAnnual = 67886.49 + (stateTaxable - 698271) * 0.123;
            
            if (annualTaxableGross > 1000000) stateTaxAnnual += (annualTaxableGross - 1000000) * 0.01;
            localTaxPerPay = (Math.min(annualTaxableGross, 153164) * 0.011) / freq; // CA SDI
        } else if (stateSlug === "new-york") {
            if (stateTaxable <= 8500) stateTaxAnnual = stateTaxable * 0.04;
            else if (stateTaxable <= 11700) stateTaxAnnual = 340 + (stateTaxable - 8500) * 0.045;
            else if (stateTaxable <= 13900) stateTaxAnnual = 484 + (stateTaxable - 11700) * 0.0525;
            else if (stateTaxable <= 80650) stateTaxAnnual = 600 + (stateTaxable - 13900) * 0.0585;
            else if (stateTaxable <= 215400) stateTaxAnnual = 4500 + (stateTaxable - 80650) * 0.0625;
            else if (stateTaxable <= 1077550) stateTaxAnnual = 12921.88 + (stateTaxable - 215400) * 0.0685;
            else stateTaxAnnual = 71979.66 + (stateTaxable - 1077550) * 0.0965;
            
            localTaxPerPay = (Math.min(annualTaxableGross, 85400) * 0.00355) / freq; // NY PFL
        } else if (stateSlug === "new-jersey") {
            var njTaxable = Math.max(0, annualTaxableGross - (stateAllowances * 1000));
            if (njTaxable <= 20000) stateTaxAnnual = njTaxable * 0.014;
            else if (njTaxable <= 35000) stateTaxAnnual = 280 + (njTaxable - 20000) * 0.0175;
            else if (njTaxable <= 40000) stateTaxAnnual = 542.50 + (njTaxable - 35000) * 0.035;
            else if (njTaxable <= 75000) stateTaxAnnual = 717.50 + (njTaxable - 40000) * 0.05525;
            else if (njTaxable <= 500000) stateTaxAnnual = 2651.25 + (njTaxable - 75000) * 0.0637;
            else if (njTaxable <= 1000000) stateTaxAnnual = 29748.75 + (njTaxable - 500000) * 0.0897;
            else stateTaxAnnual = 74598.75 + (njTaxable - 1000000) * 0.1075;
        } else if (stateSlug === "massachusetts") {
            var maTaxable = Math.max(0, annualTaxableGross - (stateAllowances * 1000));
            stateTaxAnnual = maTaxable * 0.05;
            if (annualTaxableGross > 1000000) {
                stateTaxAnnual += (annualTaxableGross - 1000000) * 0.04;
            }
        } else {
            stateTaxAnnual = stateTaxable * 0.048;
        }
    }
    var stateTaxPerPay = (stateTaxAnnual / freq) + stateExtra;

    // Calculate Local City/County taxes
    var localTaxSelected = document.getElementById("local-tax-type").value;
    var selectedLocalTaxPerPay = 0;
    
    if (localTaxSelected === "nyc") {
        var nycTaxAnnual = 0;
        if (annualTaxableGross <= 12000) nycTaxAnnual = annualTaxableGross * 0.03078;
        else if (annualTaxableGross <= 25000) nycTaxAnnual = 369.36 + (annualTaxableGross - 12000) * 0.03762;
        else if (annualTaxableGross <= 50000) nycTaxAnnual = 858.42 + (annualTaxableGross - 25000) * 0.03819;
        else if (annualTaxableGross <= 90000) nycTaxAnnual = 1813.17 + (annualTaxableGross - 50000) * 0.03855;
        else nycTaxAnnual = 3355.17 + (annualTaxableGross - 90000) * 0.03876;
        
        selectedLocalTaxPerPay = nycTaxAnnual / freq;
    } else if (localTaxSelected === "philadelphia") {
        selectedLocalTaxPerPay = (annualTaxableGross * 0.0375) / freq;
    } else if (localTaxSelected === "indiana") {
        selectedLocalTaxPerPay = (annualTaxableGross * 0.0175) / freq;
    }
    localTaxPerPay += selectedLocalTaxPerPay;

    // 4. Net Take-Home
    var totalTaxes = fedTaxPerPay + ssTaxPerPay + medTaxPerPay + stateTaxPerPay + localTaxPerPay;
    var netPay = Math.max(0, grossPerPay - totalTaxes - totalPostTaxDeductions);

    // Advanced Metrics
    var totalTaxAnnual = totalTaxes * freq;
    var effectiveTaxRate = gross > 0 ? ((totalTaxAnnual / gross) * 100) : 0;
    
    // SVG Bar Percentages
    var netRatio = Math.max(0, (netPay / grossPerPay) * 100);
    var fedRatio = Math.max(0, (fedTaxPerPay / grossPerPay) * 100);
    var stateRatio = Math.max(0, (stateTaxPerPay / grossPerPay) * 100);
    var ficaRatio = Math.max(0, ((ssTaxPerPay + medTaxPerPay) / grossPerPay) * 100);
    var localRatio = Math.max(0, (localTaxPerPay / grossPerPay) * 100);
    var deductRatio = Math.max(0, ((totalPreTaxDeductions + totalPostTaxDeductions) / grossPerPay) * 100);

    // Custom Pre-tax optimization advice
    var adviceHtml = "";
    if (val401k === 0) {
        var potential401kSavings = (gross * 0.06) * (marginalRate / 100);
        adviceHtml = `<div class="info-box" style="background:#eff6ff; border-color:#93c5fd; color:#1e40af; margin-top:15px;">
            💡 <strong>Tax Optimization Tip:</strong> You are currently contributing 0% to a pre-tax 401(k). 
            If you contribute <strong>6%</strong> ($${(gross * 0.06).toFixed(0)}/yr), you could lower your taxable income and save approximately 
            <strong>$${potential401kSavings.toFixed(0)}/year</strong> in federal income taxes.
        </div>`;
    } else {
        var actual401kSavings = (pre401kPerPay * freq) * (marginalRate / 100);
        adviceHtml = `<div class="info-box" style="background:#f0fdf4; border-color:#86efac; color:#166534; margin-top:15px;">
            🎉 <strong>Great Job!</strong> Your pre-tax 401(k) contribution of <strong>${val401k}${type401k === "pct" ? "%" : "$"}</strong> 
            is saving you approximately <strong>$${actual401kSavings.toFixed(0)}/year</strong> in federal taxes!
        </div>`;
    }

    var res = document.getElementById("results");
    res.style.display = "block";

    // Dynamic SVG Donut Chart Calculation
    var totalPay = grossPerPay;
    var pNet = (netPay / totalPay) * 100;
    var pFed = (fedTaxPerPay / totalPay) * 100;
    var pState = (stateTaxPerPay / totalPay) * 100;
    var pFica = ((ssTaxPerPay + medTaxPerPay) / totalPay) * 100;
    var pLocal = (localTaxPerPay / totalPay) * 100;
    var pDeduct = ((totalPreTaxDeductions + totalPostTaxDeductions) / totalPay) * 100;

    var donutOffset = 0;
    var donutHtml = `<svg viewBox="0 0 42 42" class="usc-donut-chart" style="width: 100%; max-width: 130px; display: block; margin: 0 auto;">
      <circle cx="21" cy="21" r="15.915" fill="#fff"></circle>
      <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#e5e7eb" stroke-width="4.5"></circle>
    `;
    function addDonutSegment(pct, color) {
        if (pct <= 0) return "";
        var dash = pct.toFixed(1) + " " + (100 - pct).toFixed(1);
        var dashoffset = (100 - donutOffset).toFixed(1);
        donutOffset += pct;
        return `<circle cx="21" cy="21" r="15.915" fill="transparent" stroke="${color}" stroke-width="4.5" stroke-dasharray="${dash}" stroke-dashoffset="${dashoffset}"></circle>`;
    }
    donutHtml += addDonutSegment(pNet, "#22c55e");
    donutHtml += addDonutSegment(pFed, "#3b82f6");
    donutHtml += addDonutSegment(pState, "#fca5a5");
    donutHtml += addDonutSegment(pFica, "#eab308");
    donutHtml += addDonutSegment(pLocal, "#a855f7");
    donutHtml += addDonutSegment(pDeduct, "#94a3b8");
    
    donutHtml += `
      <g>
        <text x="50%" y="48%" style="font-size: 5px; font-weight: 800; text-anchor: middle; fill: #111;">
          ${netRatio.toFixed(0)}%
        </text>
        <text x="50%" y="62%" style="font-size: 2px; font-weight: 600; text-anchor: middle; fill: #6b7280; text-transform: uppercase;">
          Take-Home
        </text>
      </g>
    </svg>`;

    var dropdownOptions = usStatesList.map(function(st) {
        if (st.slug === stateSlug) return "";
        return \'<option value="\' + st.slug + \'">\' + st.name + \'</option>\';
    }).join("");

    res.innerHTML = `
        </div>
        
        <button onclick="savePaycheckScenario()" style="width: 100%; margin-bottom: 15px; padding: 12px; background: var(--re); color: #fff; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px;">💾 SAVE THIS SCENARIO FOR COMPARISON</button>

        <!-- DONUT CHART & TAX RATES SUMMARY -->
        <div class="res-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:12px;">
            <div class="res-card" style="background:#fff; border-color:var(--bdr); display:flex; flex-direction:column; justify-content:center; align-items:center; padding:15px;">
                <div class="res-lbl" style="color:var(--mt); margin-bottom:10px;">📊 ALLOCATION DONUT CHART</div>
                ${donutHtml}
            </div>
            <div class="res-card" style="background:#fff; border-color:var(--bdr); display:flex; flex-direction:column; justify-content:center; padding:15px;">
                <div class="res-lbl" style="color:var(--mt); margin-bottom:8px;">TAX RATES SUMMARY</div>
                <div class="det-row" style="border:none; margin:0; padding:4px 0;"><span class="dk">Marginal Rate</span><span class="dv" style="font-size:15px; color:#111;">${marginalRate}%</span></div>
                <div class="det-row" style="border:none; margin:0; padding:4px 0;"><span class="dk">Effective Rate</span><span class="dv" style="font-size:15px; color:#111;">${effectiveTaxRate.toFixed(2)}%</span></div>
                <div class="det-row" style="border:none; margin:0; padding:4px 0;"><span class="dk">Total Annual Tax</span><span class="dv" style="font-size:15px; color:var(--re); font-weight:700;">$${Math.round(totalTaxAnnual).toLocaleString()}</span></div>
            </div>
        </div>

        <!-- VISUAL SVG INCOME PROPORTION BAR -->
        <div class="det-card" style="margin-bottom:12px;">
            <div class="det-title" style="margin-bottom:8px;">📊 INCOME ALLOCATION PROPORTION</div>
            <div style="height: 24px; border-radius: 8px; overflow: hidden; display: flex; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); margin-bottom:12px;">
                <div style="width: ${netRatio}%; background: #22c55e; height: 100%;" title="Net Pay: ${netRatio.toFixed(0)}%"></div>
                <div style="width: ${fedRatio}%; background: #3b82f6; height: 100%;" title="Federal Tax: ${fedRatio.toFixed(0)}%"></div>
                <div style="width: ${stateRatio}%; background: #fca5a5; height: 100%;" title="State Tax: ${stateRatio.toFixed(0)}%"></div>
                <div style="width: ${ficaRatio}%; background: #eab308; height: 100%;" title="FICA Tax: ${ficaRatio.toFixed(0)}%"></div>
                <div style="width: ${localRatio}%; background: #a855f7; height: 100%;" title="Local Tax: ${localRatio.toFixed(0)}%"></div>
                <div style="width: ${deductRatio}%; background: #94a3b8; height: 100%;" title="Deductions: ${deductRatio.toFixed(0)}%"></div>
            </div>
            <div class="grid2" style="grid-template-columns: repeat(3, 1fr); gap: 6px; font-size:10.5px; font-weight:700;">
                <div style="display:flex; align-items:center; gap:4px;"><span style="width:8px; height:8px; background:#22c55e; display:inline-block; border-radius:50%;"></span> Net Pay: ${netRatio.toFixed(0)}%</div>
                <div style="display:flex; align-items:center; gap:4px;"><span style="width:8px; height:8px; background:#3b82f6; display:inline-block; border-radius:50%;"></span> Fed Tax: ${fedRatio.toFixed(0)}%</div>
                <div style="display:flex; align-items:center; gap:4px;"><span style="width:8px; height:8px; background:#fca5a5; display:inline-block; border-radius:50%;"></span> State Tax: ${stateRatio.toFixed(0)}%</div>
                <div style="display:flex; align-items:center; gap:4px;"><span style="width:8px; height:8px; background:#eab308; display:inline-block; border-radius:50%;"></span> FICA: ${ficaRatio.toFixed(0)}%</div>
                <div style="display:flex; align-items:center; gap:4px;"><span style="width:8px; height:8px; background:#a855f7; display:inline-block; border-radius:50%;"></span> Local: ${localRatio.toFixed(0)}%</div>
                <div style="display:flex; align-items:center; gap:4px;"><span style="width:8px; height:8px; background:#94a3b8; display:inline-block; border-radius:50%;"></span> Deduct: ${deductRatio.toFixed(0)}%</div>
            </div>
        </div>

        <!-- SALARY SUMMARY TABLE -->
        <div class="det-card">
            <div class="det-title">📊 SALARY SUMMARY TABLE</div>
            <table class="comp-table" style="font-size:11.5px; width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--bdr);">
                        <th style="padding:6px 4px;">Category</th>
                        <th style="padding:6px 4px; text-align:right;">Pay Period</th>
                        <th style="padding:6px 4px; text-align:right;">Monthly</th>
                        <th style="padding:6px 4px; text-align:right;">Annual</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Gross Pay</td>
                        <td style="padding:6px 4px; text-align:right;">$${grossPerPay.toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">$${(grossPerPay * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">$${gross.toFixed(2)}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Pre-Tax Deduct.</td>
                        <td style="padding:6px 4px; text-align:right;">-$${totalPreTaxDeductions.toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(totalPreTaxDeductions * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(totalPreTaxDeductions * freq).toFixed(2)}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Federal Tax</td>
                        <td style="padding:6px 4px; text-align:right;">-$${fedTaxPerPay.toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(fedTaxPerPay * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${fedTaxAnnual.toFixed(2)}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">FICA (SS+Med)</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(ssTaxPerPay + medTaxPerPay).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${((ssTaxPerPay + medTaxPerPay) * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${((ssTaxPerPay + medTaxPerPay) * freq).toFixed(2)}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">State Tax</td>
                        <td style="padding:6px 4px; text-align:right;">-$${stateTaxPerPay.toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(stateTaxPerPay * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(stateTaxPerPay * freq).toFixed(2)}</td>
                    </tr>
                    ${localTaxPerPay > 0 ? `
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Local Tax</td>
                        <td style="padding:6px 4px; text-align:right;">-$${localTaxPerPay.toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(localTaxPerPay * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(localTaxPerPay * freq).toFixed(2)}</td>
                    </tr>` : ""}
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Post-Tax Deduct.</td>
                        <td style="padding:6px 4px; text-align:right;">-$${totalPostTaxDeductions.toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(totalPostTaxDeductions * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right;">-$${(totalPostTaxDeductions * freq).toFixed(2)}</td>
                    </tr>
                    <tr style="border-bottom:2px solid var(--re); font-weight:800; background:#f0fdf4;">
                        <td style="padding:6px 4px; color:#166534;">Net Take-Home</td>
                        <td style="padding:6px 4px; text-align:right; color:var(--re);">$${netPay.toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right; color:#166534;">$${(netPay * freq / 12).toFixed(2)}</td>
                        <td style="padding:6px 4px; text-align:right; color:#166534;">$${(netPay * freq).toLocaleString(undefined, {maximumFractionDigits:0})}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="det-card">
            <div class="det-title">📊 ITEM-BY-ITEM DEDUCTIONS LIST</div>
            <div class="det-row"><span class="dk">Gross Wage Per Pay</span><span class="dv">$${grossPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Pre-Tax 401(k) / 403(b)</span><span class="dv">-$${pre401kPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Pre-Tax Medical Premiums</span><span class="dv">-$${preMedPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Pre-Tax HSA/FSA contributions</span><span class="dv">-$${preHsaPerPay.toFixed(2)}</span></div>
            <div class="det-row" style="background:#fafafa;"><span class="dk" style="font-weight:700;">Federal Taxable Wages</span><span class="dv" style="font-weight:700;">$${taxableGrossPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Federal Income Tax</span><span class="dv">-$${fedTaxPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">FICA: Social Security Tax</span><span class="dv">-$${ssTaxPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">FICA: Medicare Tax</span><span class="dv">-$${medTaxPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">State Withholding Tax</span><span class="dv">-$${stateTaxPerPay.toFixed(2)}</span></div>
            ${localTaxPerPay > 0 ? `<div class="det-row"><span class="dk">Local City/County Tax & SDI/PFL</span><span class="dv">-$${localTaxPerPay.toFixed(2)}</span></div>` : ""}
            <div class="det-row"><span class="dk">Post-Tax Roth 401(k)</span><span class="dv">-$${postRothPerPay.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Post-Tax Garnishments/Misc</span><span class="dv">-$${postMiscPerPay.toFixed(2)}</span></div>
            <div class="det-row" style="border-top:2px solid #ccc; padding-top:12px; margin-top:5px;">
                <span class="dk" style="font-weight: 800; color: #111; font-size:14px;">Net Take-Home Pay</span>
                <span class="dv" style="font-weight: 900; color: var(--re); font-size:18px;">$${netPay.toFixed(2)}</span>
            </div>
        </div>

        ${adviceHtml}

        <div class="det-card" style="margin-top: 15px;">
            <div class="det-title" style="margin-bottom: 10px;">🏡 COST OF LIVING ADJUSTER</div>
            <p style="font-size: 12px; color: var(--mt); margin-bottom: 12px;">Contrast your take-home pay purchasing power with other states:</p>
            <div style="display: flex; gap: 8px; justify-content: center; align-items: center; margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight:700; color: #111;">Select State:</label>
                <select id="col-compare-state" class="inp" style="max-width: 200px; padding: 6px 12px;" onchange="calculateColAdjustment()">
                    <option value="">-- Choose State --</option>
                    \${dropdownOptions}
                </select>
            </div>
            <div id="col-comparison-results" style="display:none;"></div>
        </div>

        <!-- COMPARE STATES DYNAMIC CARDS -->
        <div class="det-card" style="margin-top: 15px; text-align: center;">
            <div class="det-title" style="margin-bottom: 10px;">🌎 COMPARE TAXES ACROSS STATES</div>
            <p style="font-size: 12px; color: var(--mt); margin-bottom: 12px;">Compare your take-home pay with zero-income-tax states or neighboring regions:</p>
            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">';
        
        $comp_states = [
            'california' => 'CA',
            'texas'      => 'TX',
            'new-york'   => 'NY',
            'florida'    => 'FL',
        ];
        if (isset($comp_states[$state_slug])) {
            $comp_list = $comp_states;
            unset($comp_list[$state_slug]);
            $comp_list['washington'] = 'WA';
        } else {
            $comp_list = $comp_states;
        }
        foreach ($comp_list as $slug => $abbr) {
            $data['js'] .= '<a href="../' . $slug . '-paycheck-calculator/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">' . $abbr . ' vs ' . esc_html(strtoupper($state_name)) . '</a>';
        }
        
        $data['js'] .= '</div>
        </div>
        <button class="reset-btn" onclick="resetPaycheckCalc()">🔄 CALCULATE AGAIN</button>
        <button class="print-btn" onclick="window.print()">🖨️ PRINT DETAILS REPORT</button>
    `;
    window.currentAnnualNetPay = netPay * freq;
    window.currentEffectiveTaxRate = effectiveTaxRate;
    res.scrollIntoView({behavior: "smooth", block: "start"});
    updatePaycheckUrlParams();
}

function resetPaycheckCalc() {
    resultsCalculated = false;
    document.getElementById("results").style.display = "none";
    document.getElementById("gross-salary").value = "80000";
    document.getElementById("401k-val").value = "6";
    document.getElementById("roth-val").value = "0";
    document.getElementById("pre-tax-med").value = "120";
    document.getElementById("pre-tax-hsa").value = "0";
    document.getElementById("post-tax-misc").value = "0";
    document.getElementById("local-tax-type").value = "none";
    setW4Version("new", document.getElementById("btnW4New"));
    updatePaycheckUrlParams();
    window.scrollTo({top:0, behavior:"smooth"});
}

function updatePaycheckUrlParams() {
    var params = new URLSearchParams();
    params.set("ptyp", payType);
    if (payType === "salary") {
        params.set("sal", document.getElementById("gross-salary").value);
    } else {
        params.set("hr", document.getElementById("hourly-rate").value);
        params.set("hrs", document.getElementById("hourly-hours").value);
    }
    params.set("freq", document.getElementById("pay-frequency").value);
    params.set("w4v", w4Version);
    params.set("fil", filingStatus);
    
    var fedAllow = document.getElementById("fed-allowances").value;
    var stateAllow = document.getElementById("state-allowances").value;
    var stateExtra = document.getElementById("state-extra").value;
    var localTax = document.getElementById("local-tax-type").value;
    
    if (w4Version === "old" && parseFloat(fedAllow) !== 1) params.set("fallow", fedAllow);
    if (parseFloat(stateAllow) !== 1) params.set("sallow", stateAllow);
    if (parseFloat(stateExtra) > 0) params.set("sextra", stateExtra);
    if (localTax !== "none") params.set("loc", localTax);
    
    if (w4Version === "new") {
        var s2 = document.getElementById("w4-step2").checked ? "1" : "0";
        var s3 = document.getElementById("w4-step3").value;
        var s4a = document.getElementById("w4-step4a").value;
        var s4b = document.getElementById("w4-step4b").value;
        var s4c = document.getElementById("w4-step4c").value;
        
        if (s2 === "1") params.set("s2", "1");
        if (parseFloat(s3) > 0) params.set("s3", s3);
        if (parseFloat(s4a) > 0) params.set("s4a", s4a);
        if (parseFloat(s4b) > 0) params.set("s4b", s4b);
        if (parseFloat(s4c) > 0) params.set("s4c", s4c);
    }
    
    params.set("c401kt", document.getElementById("401k-type").value);
    params.set("c401kv", document.getElementById("401k-val").value);
    params.set("rotht", document.getElementById("roth-type").value);
    params.set("rothv", document.getElementById("roth-val").value);
    
    var med = document.getElementById("pre-tax-med").value;
    var hsa = document.getElementById("pre-tax-hsa").value;
    var misc = document.getElementById("post-tax-misc").value;
    
    if (parseFloat(med) > 0) params.set("med", med);
    if (parseFloat(hsa) > 0) params.set("hsa", hsa);
    if (parseFloat(misc) > 0) params.set("misc", misc);
    
    if (resultsCalculated) {
        params.set("calc", "1");
    }
    
    var newRelativePathQuery = window.location.pathname + "?" + params.toString();
    try {
        window.history.replaceState(null, "", newRelativePathQuery);
    } catch(e) {
        console.warn("URL parameters update blocked:", e);
    }
}

function loadPaycheckUrlParams() {
    var params = new URLSearchParams(window.location.search);
    
    if (params.has("ptyp")) {
        var ptyp = params.get("ptyp");
        setPayType(ptyp, document.getElementById(ptyp === "salary" ? "btnSalary" : "btnHourly"));
    }
    if (params.has("sal")) document.getElementById("gross-salary").value = params.get("sal");
    if (params.has("hr")) document.getElementById("hourly-rate").value = params.get("hr");
    if (params.has("hrs")) document.getElementById("hourly-hours").value = params.get("hrs");
    if (params.has("freq")) document.getElementById("pay-frequency").value = params.get("freq");
    
    if (params.has("w4v")) {
        var w4v = params.get("w4v");
        setW4Version(w4v, document.getElementById(w4v === "new" ? "btnW4New" : "btnW4Old"));
    }
    if (params.has("fil")) {
        var fil = params.get("fil");
        var btn = document.querySelector(\'[data-status="\' + fil + \'"]\');
        if (btn) setFilingStatus(fil, btn);
    }
    
    if (params.has("fallow")) document.getElementById("fed-allowances").value = params.get("fallow");
    if (params.has("sallow")) document.getElementById("state-allowances").value = params.get("sallow");
    if (params.has("sextra")) document.getElementById("state-extra").value = params.get("sextra");
    if (params.has("loc")) document.getElementById("local-tax-type").value = params.get("loc");
    
    if (params.has("s2")) document.getElementById("w4-step2").checked = params.get("s2") === "1";
    if (params.has("s3")) document.getElementById("w4-step3").value = params.get("s3");
    if (params.has("s4a")) document.getElementById("w4-step4a").value = params.get("s4a");
    if (params.has("s4b")) document.getElementById("w4-step4b").value = params.get("s4b");
    if (params.has("s4c")) document.getElementById("w4-step4c").value = params.get("s4c");
    
    if (params.has("s2") || params.has("s3") || params.has("s4a") || params.has("s4b") || params.has("s4c")) {
        openW4Panel();
    }
    
    if (params.has("c401kt")) document.getElementById("401k-type").value = params.get("c401kt");
    if (params.has("c401kv")) document.getElementById("401k-val").value = params.get("c401kv");
    if (params.has("rotht")) document.getElementById("roth-type").value = params.get("rotht");
    if (params.has("rothv")) document.getElementById("roth-val").value = params.get("rothv");
    
    if (params.has("med")) document.getElementById("pre-tax-med").value = params.get("med");
    if (params.has("hsa")) document.getElementById("pre-tax-hsa").value = params.get("hsa");
    if (params.has("misc")) document.getElementById("post-tax-misc").value = params.get("misc");
    
    if (params.get("calc") === "1") {
        calculatePaycheck(true);
    }
}

function openW4Panel() {
    var fields = document.getElementById("w4-settings-fields");
    var icon = document.getElementById("w4-toggle-icon");
    if (fields && icon) {
        fields.style.display = "block";
        icon.textContent = "▴";
        w4Expanded = true;
    }
}

function savePaycheckScenario() {
    var scenarios = JSON.parse(localStorage.getItem("usc_paycheck_scenarios") || "[]");
    
    var gross = 0;
    if (payType === "salary") {
        gross = parseFloat(document.getElementById("gross-salary").value) || 0;
    } else {
        var rate = parseFloat(document.getElementById("hourly-rate").value) || 0;
        var hours = parseFloat(document.getElementById("hourly-hours").value) || 40;
        gross = rate * hours * 52;
    }
    
    var freqSelect = document.getElementById("pay-frequency");
    var freqLabel = freqSelect.options[freqSelect.selectedIndex].text.split(" (")[0];
    
    var annualNet = window.currentAnnualNetPay || 0;
    var effectiveRate = window.currentEffectiveTaxRate || 0;
    
    var label = "$" + Math.round(gross/1000) + "k " + payType.charAt(0).toUpperCase() + payType.slice(1);
    
    var newScenario = {
        id: Date.now(),
        gross: gross,
        payType: payType,
        filingStatus: filingStatus,
        freqLabel: freqLabel,
        annualNet: annualNet,
        effectiveRate: effectiveRate,
        label: label
    };
    
    scenarios.push(newScenario);
    if (scenarios.length > 3) {
        scenarios.shift();
    }
    
    localStorage.setItem("usc_paycheck_scenarios", JSON.stringify(scenarios));
    renderSavedPaycheckScenarios();
}

function deletePaycheckScenario(id) {
    var scenarios = JSON.parse(localStorage.getItem("usc_paycheck_scenarios") || "[]");
    scenarios = scenarios.filter(function(s) { return s.id !== id; });
    localStorage.setItem("usc_paycheck_scenarios", JSON.stringify(scenarios));
    renderSavedPaycheckScenarios();
}

function renderSavedPaycheckScenarios() {
    var container = document.getElementById("paycheck-scenarios-container");
    if (!container) return;
    
    var scenarios = JSON.parse(localStorage.getItem("usc_paycheck_scenarios") || "[]");
    if (scenarios.length === 0) {
        container.innerHTML = "";
        container.style.display = "none";
        return;
    }
    
    var tableHeaders = scenarios.map(function(s) {
        return \'<th style="padding:10px 6px; text-align:right; min-width: 100px;"><div style="font-weight:700; font-size:12px; color:#111;">\' + s.label + \'</div><button onclick="deletePaycheckScenario(\' + s.id + \')" style="border:none; background:none; color:#ef4444; font-size:10px; cursor:pointer; padding:2px 0; font-weight:700;">[Remove]</button></th>\';
    }).join("");
    
    var grossRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">$\' + Math.round(s.gross).toLocaleString() + \'</td>\';
    }).join("");
    
    var statusRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">\' + s.filingStatus.charAt(0).toUpperCase() + s.filingStatus.slice(1) + \'</td>\';
    }).join("");
    
    var freqRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">\' + s.freqLabel + \'</td>\';
    }).join("");
    
    var netRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right; font-weight:700; color:#16a34a;">$\' + Math.round(s.annualNet).toLocaleString() + \'</td>\';
    }).join("");
    
    var rateRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">\' + s.effectiveRate.toFixed(2) + \'%</td>\';
    }).join("");

    container.innerHTML = \'<div class="det-card" style="border: 2px solid var(--re); border-top: 5px solid var(--re); background: var(--bg2); margin-top:20px;"><div class="det-title" style="color:var(--re); font-size:14px; display:flex; align-items:center; gap:6px;">💾 SAVED SCENARIOS COMPARISON</div><p style="font-size:11.5px; color:var(--mt); margin-top:4px; margin-bottom:12px;">Compare up to 3 saved configurations side-by-side. New saves replace the oldest.</p><div style="overflow-x:auto;"><table class="comp-table" style="width:100%; font-size:11px; border-collapse:collapse; min-width: 400px;"><thead><tr style="border-bottom:1.5px solid var(--bdr);"><th style="padding:10px 6px; text-align:left;">Parameters</th>\' + tableHeaders + \'</tr></thead><tbody><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Gross Annual Wages</td>\' + grossRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Filing Status</td>\' + statusRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Pay Frequency</td>\' + freqRow + \'</tr><tr style="border-bottom:1px solid var(--bdr); background: var(--soft);"><td style="padding:8px 6px; font-weight:700; text-align:left; color:#16a34a;">Annual Take-Home</td>\' + netRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Effective Tax Rate</td>\' + rateRow + \'</tr></tbody></table></div></div>\';
    container.style.display = "block";
}

function calculateColAdjustment() {
    var select = document.getElementById("col-compare-state");
    if (!select) return;
    var targetSlug = select.value;
    if (targetSlug === "") {
        document.getElementById("col-comparison-results").style.display = "none";
        return;
    }
    var targetName = select.options[select.selectedIndex].text;
    
    var currentCOL = colIndexData[stateSlug] || 100.0;
    var targetCOL = colIndexData[targetSlug] || 100.0;
    
    var currentAnnualNet = window.currentAnnualNetPay || 0;
    if (currentAnnualNet <= 0) return;
    
    var equivalentAnnualNet = currentAnnualNet * (targetCOL / currentCOL);
    var diff = equivalentAnnualNet - currentAnnualNet;
    
    var resultDiv = document.getElementById("col-comparison-results");
    if (!resultDiv) return;
    
    var comparisonHtml = \'<div style="margin-top:10px; padding:10px; background:#fff; border-radius:6px; border:1px solid var(--bdr); text-align:left;"><div style="font-size:12px; font-weight:700; color:#111; margin-bottom:4px;">Purchasing Power in \' + targetName + \':</div><p style="font-size:11.5px; color:var(--mt); margin:0 0 8px 0; line-height:1.4;">To maintain the same standard of living, you would need a net take-home pay of <strong>$\' + Math.round(equivalentAnnualNet).toLocaleString() + \'/yr</strong> in \' + targetName + \' (Cost of Living Index: \' + targetCOL.toFixed(1) + \' vs \' + currentCOL.toFixed(1) + \' in our state).</p><div style="font-size:12px; font-weight:800; color:\' + (diff > 0 ? \'#dc2626\': \'#16a34a\') + \';">\' + (diff > 0 ? \'⚠️ You need $\' + Math.round(diff).toLocaleString() + \' more net pay per year there.\' : \'✅ You save $\' + Math.round(Math.abs(diff)).toLocaleString() + \' in living expenses per year there!\') + \'</div></div>\';
    resultDiv.innerHTML = comparisonHtml;
    resultDiv.style.display = "block";
}

function initPaycheckCalculator() {
    loadPaycheckUrlParams();
    renderSavedPaycheckScenarios();
    var inputs = document.querySelectorAll(".usc-calculator-container input, .usc-calculator-container select");
    inputs.forEach(function(input) {
        input.addEventListener("input", function() { calculatePaycheck(false); });
        input.addEventListener("change", function() { calculatePaycheck(false); });
    });
}
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initPaycheckCalculator);
} else {
    initPaycheckCalculator();
}';
    } elseif ($type === 'child-support') {
        // ADVANCED COURT-GRADE CHILD SUPPORT CALCULATOR HTML
        $data['html'] = '<!-- INFO BOX -->
<div class="info-box">
  📌 Calculate child support payments based on the ' . esc_html($state_name) . ' guidelines, factoring in custodial time, health support credits, childcare, and mandatory spousal support adjustments.
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">👪 FAMILY & CUSTODY SPLIT</label></div>
  
  <!-- NUMBER OF CHILDREN -->
  <div class="field">
    <label class="lbl">NUMBER OF CHILDREN</label>
    <div class="sel-wrap">
      <select class="sel" id="num-children">
        <option value="1">1 Child</option>
        <option value="2" selected>2 Children</option>
        <option value="3">3 Children</option>
        <option value="4">4 Children</option>
        <option value="5">5 or more Children</option>
      </select>
    </div>
  </div>

  <!-- OTHER CHILDREN LEGALLY SUPPORTED -->
  <div class="field">
    <label class="lbl">OTHER CHILDREN LEGALLY SUPPORTED</label>
    <div class="sel-wrap">
      <select class="sel" id="other-children-supported">
        <option value="0" selected>0 Other Children</option>
        <option value="1">1 Other Child</option>
        <option value="2">2 Other Children</option>
        <option value="3">3 or more Other Children</option>
      </select>
    </div>
    <div class="hint">Other children you are legally obligated to support (reduces percentage in Texas)</div>
  </div>

  <!-- OBLIGOR PARENT TOGGLE -->
  <div class="field">
    <label class="lbl">WHO IS THE PAYING PARENT?</label>
    <div class="tog-group">
      <button class="tog-btn active" data-obligor="father" onclick="setObligor(\'father\', this)">Father</button>
      <button class="tog-btn" data-obligor="mother" onclick="setObligor(\'mother\', this)">Mother</button>
    </div>
  </div>

  <!-- SHARED PHYSICAL CUSTODY NIGHTS SLIDER -->
  <div class="slider-field" style="margin-top: 15px;">
    <div class="slider-top">
      <label class="lbl" style="margin:0">CUSTODY NIGHTS WITH PAYING PARENT (Per Year)</label>
      <span class="slider-val" id="nightsVal" style="background:#dbeafe; border-color:#93c5fd; color:#1e40af;">73 Nights (20%)</span>
    </div>
    <input type="range" class="slider" id="nightsSlider" min="0" max="365" value="73" oninput="updateNightsSlider(this)">
    <div class="slider-marks"><span>0 nights (Sole Custody)</span><span>110 nights</span><span>182 nights (Shared 50/50)</span><span>365 nights</span></div>
    <div class="hint">Nights per year the children sleep at paying parent\'s residence. Over 73 nights triggers adjustments in many states.</div>
  </div>
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">💰 MONTHLY INCOME & COURT ADJUSTMENTS</label></div>

  <div class="grid2">
    <!-- PAYING PARENT INCOME -->
    <div class="field">
      <label class="lbl">PAYING PARENT GROSS INCOME</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="paying-income" placeholder="e.g. 5500" value="5500">
        <span class="inp-suffix">$/mo</span>
      </div>
      <div class="hint">Wages before taxes</div>
    </div>

    <!-- RECEIVING PARENT INCOME -->
    <div class="field" id="receiving-parent-income-field">
      <label class="lbl">RECEIVING PARENT GROSS INCOME</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="receiving-income" placeholder="e.g. 3500" value="3500">
        <span class="inp-suffix">$/mo</span>
      </div>
      <div class="hint">Wages before taxes</div>
    </div>
  </div>

  <!-- MANDATORY COURT-ORDERED DEDUCTIONS -->
  <div class="grid2">
    <div class="field">
      <label class="lbl">ALIMONY / SPOUSAL SUPPORT PAID</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="alimony-paid" value="0" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Monthly alimony paid to other spouses</div>
    </div>
    <div class="field">
      <label class="lbl">OTHER CHILD SUPPORT OBLIGATIONS</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="other-support" value="0" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Monthly support paid for other kids</div>
    </div>
  </div>

  <div class="grid3">
    <!-- HEALTH INSURANCE PREMIUM -->
    <div class="field">
      <label class="lbl">CHILD HEALTH PREMIUM</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="health-cost" value="180" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Monthly premium cost</div>
    </div>
    <!-- WORK RELATED CHILDCARE -->
    <div class="field">
      <label class="lbl">WORK CHILDCARE COST</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="childcare-cost" value="0" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Monthly daycare costs</div>
    </div>
    <!-- RETIREMENT / UNION DUES -->
    <div class="field">
      <label class="lbl">RETIREMENT / UNION DUES</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="union-cost" value="50" min="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Monthly mandatory dues / pay</div>
    </div>
  </div>

  <!-- COURT DEVIATION ADJUSTMENT -->
  <div class="field" style="margin-top: 15px;">
    <label class="lbl">COURT DEVIATION ADJUSTMENT (+/-)</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="court-deviation" value="0">
      <span class="inp-suffix">$/mo</span>
    </div>
    <div class="hint">Custom monthly offset (positive or negative) approved by court</div>
  </div>
</div>

<!-- CALCULATE BTN -->
<button class="calc-btn" onclick="calculateChildSupport(true)">
  👪 ESTIMATE CHILD SUPPORT
</button>

<!-- RESULTS container -->
<div class="results" id="results"></div><div id="child-support-scenarios-container" style="display: none;"></div>';

        // ADVANCED CHILD SUPPORT CALCULATOR JS
        $js_template = <<<'JS'
var obligorParent = "father";
var stateSlug = "{{STATE_SLUG}}";
var resultsCalculated = false;

function initChildSupportCalculator() {
    loadCSUrlParams();
    renderSavedCSScenarios();
    var percentageStates = ["texas", "nevada", "wisconsin", "alaska", "mississippi"];
    if (percentageStates.indexOf(stateSlug) !== -1) {
        var recField = document.getElementById("receiving-parent-income-field");
        if (recField) recField.style.display = "none";
    }
    var slider = document.getElementById("nightsSlider");
    if (slider) {
        slider.style.setProperty("--p", (slider.value / 365) * 100 + "%");
    }
    
    // Attach live calculations
    var inputs = document.querySelectorAll(".usc-calculator-container input, .usc-calculator-container select");
    inputs.forEach(function(input) {
        input.addEventListener("input", function() { calculateChildSupport(false); });
        input.addEventListener("change", function() { calculateChildSupport(false); });
    });
}
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initChildSupportCalculator);
} else {
    initChildSupportCalculator();
}

function setObligor(parent, btn) {
    obligorParent = parent;
    document.querySelectorAll("[data-obligor]").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
}

function updateNightsSlider(el) {
    var v = parseInt(el.value);
    var pct = Math.round((v / 365) * 100);
    document.getElementById("nightsVal").innerText = v + " Nights (" + pct + "%)";
    el.style.setProperty("--p", (v / 365) * 100 + "%");
    calculateChildSupport(false);
}

function calculateChildSupport(forceShow) {
    if (forceShow === true) {
        resultsCalculated = true;
    }
    if (!resultsCalculated) {
        return;
    }
    var pIncome = parseFloat(document.getElementById("paying-income").value) || 0;
    var rIncome = parseFloat(document.getElementById("receiving-income").value) || 0;
    var children = parseInt(document.getElementById("num-children").value) || 1;
    var health = parseFloat(document.getElementById("health-cost").value) || 0;
    var daycare = parseFloat(document.getElementById("childcare-cost").value) || 0;
    var alimony = parseFloat(document.getElementById("alimony-paid").value) || 0;
    var otherSupport = parseFloat(document.getElementById("other-support").value) || 0;
    var union = parseFloat(document.getElementById("union-cost").value) || 0;
    
    var nights = parseInt(document.getElementById("nightsSlider").value) || 0;
    var hPct = nights / 365;

    var otherKidsSupported = parseInt(document.getElementById("other-children-supported").value) || 0;
    var deviation = parseFloat(document.getElementById("court-deviation").value) || 0;

    var monthlySupport = 0;
    var modelUsed = "";
    var breakdownHtml = "";
    
    // Calculated net disposable incomes
    var pNet = 0;
    var rNet = 0;

    // 1. TEXAS PERCENTAGE MODEL
    if (stateSlug === "texas") {
        modelUsed = "Texas Percentage of Net Resources Model";
        
        // Calculate Net Resources (Gross Income minus taxes (estimate 22%), alimony, other support, union dues)
        pNet = Math.max(0, pIncome * 0.78 - alimony - otherSupport - union);
        
        // Cap net resources at Texas state maximum cap ($9,200/mo)
        var cappedNet = Math.min(pNet, 9200);

        var pct = 0.20; 
        if (otherKidsSupported === 0) {
            if (children === 1) pct = 0.20;
            else if (children === 2) pct = 0.25;
            else if (children === 3) pct = 0.30;
            else if (children === 4) pct = 0.35;
            else pct = 0.40;
        } else if (otherKidsSupported === 1) {
            if (children === 1) pct = 0.16;
            else if (children === 2) pct = 0.20;
            else if (children === 3) pct = 0.252;
            else if (children === 4) pct = 0.303;
            else pct = 0.35;
        } else if (otherKidsSupported === 2) {
            if (children === 1) pct = 0.14;
            else if (children === 2) pct = 0.186;
            else if (children === 3) pct = 0.225;
            else if (children === 4) pct = 0.272;
            else pct = 0.318;
        } else { // 3+ other kids
            if (children === 1) pct = 0.12;
            else if (children === 2) pct = 0.16;
            else if (children === 3) pct = 0.20;
            else if (children === 4) pct = 0.24;
            else pct = 0.28;
        }

        monthlySupport = cappedNet * pct;
        
        // Shared Custody parenting discount
        if (nights >= 110) {
            var discount = (nights / 365) * 0.45; // Max 45% discount for split parenting
            monthlySupport = monthlySupport * (1 - discount);
        }

        // Adjust for medical/dental cost sharing credit
        monthlySupport = Math.max(0, monthlySupport - (health * 0.5));
        
        rNet = rIncome * 0.78; // Dummy for table

        breakdownHtml = `
            <div class="det-row"><span class="dk">Capped Net Resources Limit</span><span class="dv">$${cappedNet.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Child Percentage rate</span><span class="dv">${(pct*100).toFixed(1)}%</span></div>
            <div class="det-row"><span class="dk">Other Children Supported</span><span class="dv">${otherKidsSupported} children</span></div>
            <div class="det-row"><span class="dk">Shared Nights Discount</span><span class="dv">${(nights >= 110) ? Math.round(discount*100) + "%" : "None"}</span></div>
        `;
    } 
    // 2. CALIFORNIA ALGEBRAIC MODEL
    else if (stateSlug === "california") {
        modelUsed = "California Algebraic Guideline Formula";
        
        // Net Disposable Incomes (Gross minus taxes (approx 20%), alimony, union dues, retirement)
        pNet = Math.max(0, pIncome * 0.80 - alimony - otherSupport - union);
        rNet = Math.max(0, rIncome * 0.80);
        
        var combinedNet = pNet + rNet;
        var K = 0.25;
        if (combinedNet <= 800) {
            K = 0.20 + (combinedNet / 8000);
        } else if (combinedNet <= 6666) {
            K = 0.25;
        } else if (combinedNet <= 10000) {
            K = 0.20;
        } else {
            K = 0.15;
        }

        var childMultiplier = 1.0;
        if (children === 2) childMultiplier = 1.6;
        else if (children === 3) childMultiplier = 2.0;
        else if (children === 4) childMultiplier = 2.3;
        else if (children >= 5) childMultiplier = 2.5;

        // CS = K * (HN - (H% * (HN + SN))) * multiplier
        // HN = High earner net, SN = Low earner net.
        var HN = (pNet > rNet) ? pNet : rNet;
        var SN = (pNet > rNet) ? rNet : pNet;
        var H_percent = (pNet > rNet) ? hPct : (1 - hPct);

        var baseCS = K * (HN - (H_percent * (HN + SN)));
        monthlySupport = baseCS * childMultiplier;

        // If paying parent is the low earner, reverse the support direction or adjust
        if (pNet < rNet) {
            monthlySupport = -monthlySupport;
        }

        // Add healthcare/childcare proportional add-ons
        var pSharePct = combinedNet > 0 ? (pNet / combinedNet) : 0.5;
        monthlySupport += ((health + daycare) * pSharePct);
        monthlySupport = Math.max(0, monthlySupport);

        breakdownHtml = `
            <div class="det-row"><span class="dk">California K Factor</span><span class="dv">${K.toFixed(3)}</span></div>
            <div class="det-row"><span class="dk">Children Multiplier</span><span class="dv">x${childMultiplier.toFixed(1)}</span></div>
            <div class="det-row"><span class="dk">Add-on Cost Share (${(pSharePct*100).toFixed(0)}%)</span><span class="dv">+$${((health+daycare)*pSharePct).toFixed(2)}</span></div>
        `;
    } 
    // 3. GENERAL INCOME SHARES MODEL
    else {
        modelUsed = "Income Shares Model (Shared Custody Guidelines)";
        pNet = Math.max(0, pIncome * 0.78 - alimony - otherSupport - union);
        rNet = Math.max(0, rIncome * 0.78);

        var combinedNet = pNet + rNet;
        var pSharePct = combinedNet > 0 ? (pNet / combinedNet) : 0.5;

        var baseObligation = 0;
        if (combinedNet <= 2000) baseObligation = combinedNet * 0.17 * children;
        else if (combinedNet <= 5000) baseObligation = (340 + (combinedNet - 2000) * 0.14) * children;
        else if (combinedNet <= 10000) baseObligation = (760 + (combinedNet - 5000) * 0.12) * children;
        else baseObligation = (1360 + (combinedNet - 10000) * 0.10) * children;

        var totalObligation = baseObligation + health + daycare;
        monthlySupport = totalObligation * pSharePct;

        if (nights > 110) {
            var nightsAdjustment = (nights / 365) * 0.55; 
            monthlySupport = Math.max(0, monthlySupport - (baseObligation * nightsAdjustment));
        }

        breakdownHtml = `
            <div class="det-row"><span class="dk">Basic Obligation (Combined)</span><span class="dv">$${baseObligation.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Total Joint Child Expenses</span><span class="dv">$${totalObligation.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Shared Nights Adjustment</span><span class="dv">${(nights > 110) ? "Active (>110 nights)" : "None"}</span></div>
        `;
    }

    // Apply Court Deviation offset and clamp to 0
    monthlySupport = Math.max(0, monthlySupport + deviation);

    var res = document.getElementById("results");
    res.style.display = "block";

    // Proportional Income Share Donut Chart Calculation
    var combinedNet = pNet + rNet;
    var pSharePct = combinedNet > 0 ? (pNet / combinedNet) : 0.5;
    var rSharePct = 1 - pSharePct;
    var pctP = pSharePct * 100;
    var pctR = rSharePct * 100;

    var donutCSOffset = 0;
    var donutCSHtml = "";
    if (combinedNet > 0) {
        donutCSHtml = `<svg viewBox="0 0 42 42" class="usc-donut-chart" style="width: 100%; max-width: 130px; display: block; margin: 0 auto;">
          <circle cx="21" cy="21" r="15.915" fill="#fff"></circle>
          <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#e5e7eb" stroke-width="4.5"></circle>
        `;
        function addDonutCSSegment(pct, color) {
            if (pct <= 0) return "";
            var dash = pct.toFixed(1) + " " + (100 - pct).toFixed(1);
            var dashoffset = (100 - donutCSOffset).toFixed(1);
            donutCSOffset += pct;
            return `<circle cx="21" cy="21" r="15.915" fill="transparent" stroke="${color}" stroke-width="4.5" stroke-dasharray="${dash}" stroke-dashoffset="${dashoffset}"></circle>`;
        }
        donutCSHtml += addDonutCSSegment(pctP, "#dc2626"); // Red (Paying)
        donutCSHtml += addDonutCSSegment(pctR, "#3b82f6"); // Blue (Receiving)
        
        donutCSHtml += `
          <g>
            <text x="50%" y="48%" style="font-size: 5px; font-weight: 800; text-anchor: middle; fill: #111;">
              ${pctP.toFixed(0)}%
            </text>
            <text x="50%" y="62%" style="font-size: 2.5px; font-weight: 600; text-anchor: middle; fill: #6b7280; text-transform: uppercase;">
              Paying Share
            </text>
          </g>
        </svg>`;
    }

    res.innerHTML = `
        <div class="hero-grid">
            <div class="hero-card">
                <div class="hero-lbl">ESTIMATED CHILD SUPPORT</div>
                <div class="hero-val">$${monthlySupport.toFixed(2)}</div>
                <div class="hero-sub">Estimated Monthly Support Obligation</div>
            </div>
            <div class="hero-card">
                <div class="hero-lbl">ANNUAL TOTAL</div>
                <div class="hero-val">$${(monthlySupport * 12).toLocaleString(undefined, {maximumFractionDigits:0})}</div>
                <div class="hero-sub">Estimated Annual Support</div>
            </div>
        </div>
        
        <button onclick="saveCSScenario()" style="width: 100%; margin-bottom: 15px; padding: 12px; background: var(--re); color: #fff; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px;">💾 SAVE THIS SCENARIO FOR COMPARISON</button>

        <!-- WEEKLY / DAILY BREAKDOWN GRID -->
        <div class="res-grid" style="grid-template-columns: 1fr 1fr 1fr; margin-bottom:12px;">
            <div class="res-card" style="padding:10px; background:#fff; border-color:var(--bdr);">
                <div class="res-lbl" style="font-size:9px;">MONTHLY</div>
                <div class="res-val" style="font-size:16px;">$${monthlySupport.toFixed(2)}</div>
            </div>
            <div class="res-card" style="padding:10px; background:#fff; border-color:var(--bdr);">
                <div class="res-lbl" style="font-size:9px;">WEEKLY EQUIV.</div>
                <div class="res-val" style="font-size:16px;">$${(monthlySupport / 4.33).toFixed(2)}</div>
            </div>
            <div class="res-card" style="padding:10px; background:#fff; border-color:var(--bdr);">
                <div class="res-lbl" style="font-size:9px;">DAILY EQUIV.</div>
                <div class="res-val" style="font-size:16px;">$${(monthlySupport / 30.4).toFixed(2)}</div>
            </div>
        </div>

        <!-- INCOME SHARE DONUT CHART -->
        ${donutCSHtml !== "" ? `
        <div class="det-card" style="text-align:center;">
            <div class="det-title" style="margin-bottom:8px;">📊 INCOME SHARE SPLIT CHART</div>
            ${donutCSHtml}
            <div class="grid2" style="grid-template-columns: 1fr 1fr; gap:6px; font-size:11px; font-weight:700; margin-top:10px;">
                <div style="display:flex; align-items:center; justify-content:center; gap:4px;"><span style="width:8px; height:8px; background:#dc2626; display:inline-block; border-radius:50%;"></span> Paying Parent: ${pctP.toFixed(0)}%</div>
                <div style="display:flex; align-items:center; justify-content:center; gap:4px;"><span style="width:8px; height:8px; background:#3b82f6; display:inline-block; border-radius:50%;"></span> Receiving Parent: ${pctR.toFixed(0)}%</div>
            </div>
        </div>
        ` : ""}

        <!-- SIDE-BY-SIDE PARENTAL COMPARISON GRID -->
        <div class="det-card">
            <div class="det-title">⚖️ SIDE-BY-SIDE FINANCIAL ANALYSIS</div>
            <table class="comp-table" style="font-size:11.5px;">
                <thead>
                    <tr>
                        <th>Financial Item</th>
                        <th>Paying Parent</th>
                        <th>Receiving Parent</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gross Monthly Salary</td>
                        <td>$${pIncome.toFixed(2)}</td>
                        <td>$${rIncome.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td>Net Disposable Income</td>
                        <td>$${pNet.toFixed(2)}</td>
                        <td>$${rNet.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td>Parenting Nights / Split</td>
                        <td>${nights} nights (${(hPct*100).toFixed(0)}%)</td>
                        <td>${365 - nights} nights (${(100 - (hPct*100)).toFixed(0)}%)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="det-card">
            <div class="det-title">📊 COURT GUIDELINE CALCULATIONS BREAKDOWN</div>
            <div class="det-row"><span class="dk">Calculation Model</span><span class="dv">${modelUsed}</span></div>
            <div class="det-row"><span class="dk">Paying Parent Net resources</span><span class="dv">$${pNet.toFixed(2)}/mo</span></div>
            ${breakdownHtml}
            <div class="det-row"><span class="dk">Mandatory Alimony/Exemptions</span><span class="dv">-$${alimony.toFixed(2)}</span></div>
            <div class="det-row"><span class="dk">Health & Daycare Add-ons</span><span class="dv">$${(health + daycare).toFixed(2)}</span></div>
            ${deviation !== 0 ? `<div class="det-row"><span class="dk">Custom Court Deviation Offset</span><span class="dv">${deviation > 0 ? "+" : ""}$${deviation.toFixed(2)}</span></div>` : ""}
            <div class="det-row" style="border-top:2px solid #ccc; padding-top:12px; margin-top:5px;">
                <span class="dk" style="font-weight: 800; color: #111; font-size:14px;">Total Monthly Payment</span>
                <span class="dv" style="font-weight: 900; color: var(--re); font-size:18px;">$${monthlySupport.toFixed(2)}</span>
            </div>
        </div>

        <!-- ATTORNEY CTA -->
        <div class="det-card" style="background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%); border-color:#f59e0b; text-align:center;">
            <div style="font-size:15px; font-weight:800; color:#b45309; margin-bottom:5px;">⚖️ NEED PROFESSIONAL LEGAL HELP?</div>
            <p style="font-size:12px; color:#78350f; margin-bottom:12px;">Child support disputes can be complex. Speak to a family lawyer in <strong>{{STATE_NAME}}</strong> to protect your rights and custody interests.</p>
            <a href="https://www.google.com/search?q=family+lawyer={{STATE_SLUG}}" target="_blank" class="calc-btn" style="display:inline-block; max-width:260px; margin:0 auto; padding:10px 15px; font-size:13px; text-decoration:none; text-align:center; background:#d97706; box-shadow: 0 4px 10px rgba(217,119,6,0.25);">Find a Family Lawyer in {{STATE_NAME}}</a>
        </div>

        <!-- COMPARE STATES DYNAMIC CARDS -->
        <div class="det-card" style="margin-top: 15px; text-align: center;">
            <div class="det-title" style="margin-bottom: 10px;">🌎 COMPARE CHILD SUPPORT GUIDELINES</div>
            <p style="font-size: 12px; color: var(--mt); margin-bottom: 12px;">Compare calculations and guideline systems with other jurisdictions:</p>
            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">${(function(){
                var comp = { "california":"CA", "texas":"TX", "new-york":"NY", "florida":"FL", "washington":"WA" };
                var cur = "{{STATE_SLUG}}";
                var out = "";
                Object.keys(comp).forEach(function(s){
                    if (s === cur) return;
                    out += '<a href="../' + s + '-child-support-calculator/" class="usc-compare-btn" style="text-decoration:none; padding:6px 12px; font-size:11.5px; border:1.5px solid var(--bdr); border-radius:8px; color:#111; font-weight:700;">' + comp[s] + ' vs {{STATE_NAME_UPPER}}</a>';
                });
                return out;
            })()}</div>
        </div>

        <div class="disc">⚠️ <strong>Disclaimer:</strong> This calculation uses standardized state formulas. Actual awards are decided by local state family courts and may deviate based on custody visitation details, special needs, and extra deductions.</div>
        
        <button class="reset-btn" onclick="resetChildSupportCalc()">🔄 CALCULATE AGAIN</button>
        <button class="print-btn" onclick="window.print()">🖨️ PRINT DETAILS REPORT</button>
    `;
    window.currentCSMonthlySupport = monthlySupport;
    res.scrollIntoView({behavior: "smooth", block: "start"});
    updateCSUrlParams();
}

function resetChildSupportCalc() {
    resultsCalculated = false;
    document.getElementById("results").style.display = "none";
    document.getElementById("paying-income").value = "5500";
    document.getElementById("receiving-income").value = "3500";
    document.getElementById("health-cost").value = "180";
    document.getElementById("childcare-cost").value = "0";
    document.getElementById("alimony-paid").value = "0";
    document.getElementById("other-support").value = "0";
    document.getElementById("union-cost").value = "50";
    document.getElementById("nightsSlider").value = "73";
    document.getElementById("other-children-supported").value = "0";
    document.getElementById("court-deviation").value = "0";
    updateNightsSlider(document.getElementById("nightsSlider"));
    updateCSUrlParams();
    window.scrollTo({top:0, behavior:"smooth"});
}

function updateCSUrlParams() {
    var params = new URLSearchParams();
    params.set("kid", document.getElementById("num-children").value);
    params.set("oth", document.getElementById("other-children-supported").value);
    params.set("pay", document.getElementById("paying-income").value);
    params.set("rec", document.getElementById("receiving-income").value);
    params.set("hth", document.getElementById("health-cost").value);
    params.set("care", document.getElementById("childcare-cost").value);
    params.set("al", document.getElementById("alimony-paid").value);
    params.set("othsp", document.getElementById("other-support").value);
    params.set("un", document.getElementById("union-cost").value);
    params.set("ngt", document.getElementById("nightsSlider").value);
    params.set("dev", document.getElementById("court-deviation").value);
    params.set("obl", obligorParent);
    
    if (resultsCalculated) {
        params.set("calc", "1");
    }
    
    var newRelativePathQuery = window.location.pathname + "?" + params.toString();
    try {
        window.history.replaceState(null, "", newRelativePathQuery);
    } catch(e) {
        console.warn("URL parameters update blocked:", e);
    }
}

function loadCSUrlParams() {
    var params = new URLSearchParams(window.location.search);
    
    if (params.has("kid")) document.getElementById("num-children").value = params.get("kid");
    if (params.has("oth")) document.getElementById("other-children-supported").value = params.get("oth");
    if (params.has("pay")) document.getElementById("paying-income").value = params.get("pay");
    if (params.has("rec")) document.getElementById("receiving-income").value = params.get("rec");
    if (params.has("hth")) document.getElementById("health-cost").value = params.get("hth");
    if (params.has("care")) document.getElementById("childcare-cost").value = params.get("care");
    if (params.has("al")) document.getElementById("alimony-paid").value = params.get("al");
    if (params.has("othsp")) document.getElementById("other-support").value = params.get("othsp");
    if (params.has("un")) document.getElementById("union-cost").value = params.get("un");
    if (params.has("ngt")) {
        var ngt = params.get("ngt");
        document.getElementById("nightsSlider").value = ngt;
        updateNightsSlider(document.getElementById("nightsSlider"));
    }
    if (params.has("dev")) document.getElementById("court-deviation").value = params.get("dev");
    if (params.has("obl")) {
        var obl = params.get("obl");
        setObligor(obl, document.getElementById(obl === "father" ? "btnObligorFather" : "btnObligorMother"));
    }
    
    if (params.get("calc") === "1") {
        calculateChildSupport(true);
    }
}

function saveCSScenario() {
    var scenarios = JSON.parse(localStorage.getItem("usc_cs_scenarios") || "[]");
    
    var payingIncome = parseFloat(document.getElementById("paying-income").value) || 0;
    var receivingIncome = parseFloat(document.getElementById("receiving-income").value) || 0;
    var numKids = document.getElementById("num-children").value;
    var monthlySupportVal = window.currentCSMonthlySupport || 0;
    
    var label = "$" + Math.round(payingIncome/1000) + "k vs $" + Math.round(receivingIncome/1000) + "k, " + numKids + " " + (numKids == 1 ? "kid" : "kids");
    
    var newScenario = {
        id: Date.now(),
        payingIncome: payingIncome,
        receivingIncome: receivingIncome,
        numKids: numKids,
        obligorParent: obligorParent,
        nights: document.getElementById("nightsSlider").value,
        monthlySupport: monthlySupportVal,
        label: label
    };
    
    scenarios.push(newScenario);
    if (scenarios.length > 3) {
        scenarios.shift();
    }
    
    localStorage.setItem("usc_cs_scenarios", JSON.stringify(scenarios));
    renderSavedCSScenarios();
}

function deleteCSScenario(id) {
    var scenarios = JSON.parse(localStorage.getItem("usc_cs_scenarios") || "[]");
    scenarios = scenarios.filter(function(s) { return s.id !== id; });
    localStorage.setItem("usc_cs_scenarios", JSON.stringify(scenarios));
    renderSavedCSScenarios();
}

function renderSavedCSScenarios() {
    var container = document.getElementById("child-support-scenarios-container");
    if (!container) return;
    
    var scenarios = JSON.parse(localStorage.getItem("usc_cs_scenarios") || "[]");
    if (scenarios.length === 0) {
        container.innerHTML = "";
        container.style.display = "none";
        return;
    }
    
    var tableHeaders = scenarios.map(function(s) {
        return '<th style="padding:10px 6px; text-align:right; min-width: 100px;"><div style="font-weight:700; font-size:12px; color:#111;">' + s.label + '</div><button onclick="deleteCSScenario(' + s.id + ')" style="border:none; background:none; color:#ef4444; font-size:10px; cursor:pointer; padding:2px 0; font-weight:700;">[Remove]</button></th>';
    }).join("");
    
    var payIncomeRow = scenarios.map(function(s) {
        return '<td style="padding:8px 6px; text-align:right;">$' + Math.round(s.payingIncome).toLocaleString() + '</td>';
    }).join("");
    
    var recIncomeRow = scenarios.map(function(s) {
        return '<td style="padding:8px 6px; text-align:right;">$' + Math.round(s.receivingIncome).toLocaleString() + '</td>';
    }).join("");
    
    var nightsRow = scenarios.map(function(s) {
        return '<td style="padding:8px 6px; text-align:right;">' + s.nights + ' nights</td>';
    }).join("");
    
    var obligorRow = scenarios.map(function(s) {
        return '<td style="padding:8px 6px; text-align:right;">' + s.obligorParent.charAt(0).toUpperCase() + s.obligorParent.slice(1) + '</td>';
    }).join("");
    
    var supportRow = scenarios.map(function(s) {
        return '<td style="padding:8px 6px; text-align:right; font-weight:700; color:var(--re);">$' + Math.round(s.monthlySupport).toLocaleString() + '</td>';
    }).join("");

    container.innerHTML = '<div class="det-card" style="border: 2px solid var(--re); border-top: 5px solid var(--re); background: var(--bg2); margin-top:20px;"><div class="det-title" style="color:var(--re); font-size:14px; display:flex; align-items:center; gap:6px;">💾 SAVED SCENARIOS COMPARISON</div><p style="font-size:11.5px; color:var(--mt); margin-top:4px; margin-bottom:12px;">Compare up to 3 saved configurations side-by-side. New saves replace the oldest.</p><div style="overflow-x:auto;"><table class="comp-table" style="width:100%; font-size:11px; border-collapse:collapse; min-width: 400px;"><thead><tr style="border-bottom:1.5px solid var(--bdr);"><th style="padding:10px 6px; text-align:left;">Parameters</th>' + tableHeaders + '</tr></thead><tbody><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Payer Monthly Income</td>' + payIncomeRow + '</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Recipient Monthly Income</td>' + recIncomeRow + '</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Payer Nights Share</td>' + nightsRow + '</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Paying Parent</td>' + obligorRow + '</tr><tr style="border-bottom:1px solid var(--bdr); background: var(--soft);"><td style="padding:8px 6px; font-weight:700; text-align:left; color:var(--re);">Monthly Support</td>' + supportRow + '</tr></tbody></table></div></div>';
    container.style.display = "block";
}
JS;

        $data['js'] = str_replace(
            ['{{STATE_SLUG}}', '{{STATE_NAME}}', '{{STATE_NAME_UPPER}}'],
            [esc_js($state_slug), esc_html($state_name), esc_html(strtoupper($state_name))],
            $js_template
        );
    } elseif ($type === 'mortgage') {
        $data['html'] = '<!-- Mortgage HTML -->
<div class="info-box">📌 Calculate your monthly mortgage payment including principal, interest, taxes, and insurance (PITI) in ' . esc_html($state_name) . '.</div>
<div class="sec">
  <div class="sec-head"><label class="lbl">🏠 Home Details</label></div>
  <div class="field">
    <label class="lbl">Home Price</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="home-price" value="400000">
      <span class="inp-suffix">$</span>
    </div>
  </div>
  <div class="grid2">
    <div class="field">
      <label class="lbl">Down Payment</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="down-payment" value="80000">
        <span class="inp-suffix">$</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">Interest Rate</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="interest-rate" value="6.5" step="0.1">
        <span class="inp-suffix">%</span>
      </div>
    </div>
  </div>
  <div class="field">
    <label class="lbl">Loan Term</label>
    <div class="sel-wrap">
      <select class="sel" id="loan-term">
        <option value="30" selected>30-Year Fixed</option>
        <option value="15">15-Year Fixed</option>
      </select>
    </div>
  </div>
</div>
<button class="calc-btn" onclick="calculateMortgage()">🏠 Calculate Monthly Payment</button>
<div class="results" id="results"></div>';

        $data['js'] = 'function calculateMortgage() {
    var price = parseFloat(document.getElementById("home-price").value) || 0;
    var down = parseFloat(document.getElementById("down-payment").value) || 0;
    var rate = parseFloat(document.getElementById("interest-rate").value) || 0;
    var term = parseFloat(document.getElementById("loan-term").value) || 30;

    var principal = price - down;
    var monthlyRate = (rate / 100) / 12;
    var totalPayments = term * 12;

    var monthlyPayment = 0;
    if (monthlyRate > 0) {
        monthlyPayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, totalPayments)) / (Math.pow(1 + monthlyRate, totalPayments) - 1);
    } else {
        monthlyPayment = principal / totalPayments;
    }

    var res = document.getElementById("results");
    res.style.display = "block";
    res.innerHTML = "<div class=\"det-card\" style=\"text-align:center;\"><div class=\"det-title\">💰 Estimated Monthly Payment</div><div class=\"det-val\">$" + monthlyPayment.toFixed(2) + "</div><p>Principal & Interest only.</p></div>";
}';

    } elseif ($type === 'tax') {
        $data['html'] = '<!-- Tax HTML -->
<div class="info-box">📌 Estimate your federal and state tax liability for ' . esc_html($state_name) . '.</div>
<div class="sec">
  <div class="sec-head"><label class="lbl">📈 Income & Filing Status</label></div>
  <div class="field">
    <label class="lbl">Annual Income</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="tax-income" value="75000">
      <span class="inp-suffix">$</span>
    </div>
  </div>
  <div class="field">
    <label class="lbl">Filing Status</label>
    <div class="sel-wrap">
      <select class="sel" id="tax-status">
        <option value="single" selected>Single</option>
        <option value="married">Married Filing Jointly</option>
      </select>
    </div>
  </div>
</div>
<button class="calc-btn" onclick="calculateTax()">📈 Calculate Tax Liability</button>
<div class="results" id="results"></div>';

        $data['js'] = 'function calculateTax() {
    var income = parseFloat(document.getElementById("tax-income").value) || 0;
    var status = document.getElementById("tax-status").value;

    var fedTax = income * 0.15;
    var stateTax = income * 0.04;
    var takeHome = income - fedTax - stateTax;

    var res = document.getElementById("results");
    res.style.display = "block";
    res.innerHTML = "<div class=\"det-card\"><div class=\"det-title\" style=\"text-align:center;\">📊 Tax Summary</div><div class=\"usc-state-calc-row\" style=\"margin-top:10px;\"><span>Federal Tax (Est):</span><strong>$" + fedTax.toFixed(2) + "</strong></div><div class=\"usc-state-calc-row\"><span>State Tax (Est):</span><strong>$" + stateTax.toFixed(2) + "</strong></div><div class=\"usc-state-calc-row\" style=\"border-top:1px solid #eee; padding-top:10px; margin-top:10px;\"><span>Est. Take-Home:</span><strong>$" + takeHome.toFixed(2) + "</strong></div></div>";
}';

    } elseif ($type === 'auto-loan') {
        $data['html'] = '<!-- Auto Loan HTML -->
<div class="info-box">📌 Calculate your auto loan payments and interest in ' . esc_html($state_name) . '.</div>
<div class="sec">
  <div class="sec-head"><label class="lbl">🚗 Vehicle Details</label></div>
  <div class="field">
    <label class="lbl">Vehicle Price</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="car-price" value="25000">
      <span class="inp-suffix">$</span>
    </div>
  </div>
  <div class="grid2">
    <div class="field">
      <label class="lbl">Down Payment / Trade-in</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="car-down" value="3000">
        <span class="inp-suffix">$</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">Interest Rate</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="car-rate" value="5.5" step="0.1">
        <span class="inp-suffix">%</span>
      </div>
    </div>
  </div>
  <div class="field">
    <label class="lbl">Loan Term (Months)</label>
    <div class="sel-wrap">
      <select class="sel" id="car-term">
        <option value="60" selected>60 Months (5 Years)</option>
        <option value="48">48 Months (4 Years)</option>
        <option value="72">72 Months (6 Years)</option>
      </select>
    </div>
  </div>
</div>
<button class="calc-btn" onclick="calculateCarLoan()">🚗 Calculate Payment</button>
<div class="results" id="results"></div>';

        $data['js'] = 'function calculateCarLoan() {
    var price = parseFloat(document.getElementById("car-price").value) || 0;
    var down = parseFloat(document.getElementById("car-down").value) || 0;
    var rate = parseFloat(document.getElementById("car-rate").value) || 0;
    var term = parseFloat(document.getElementById("car-term").value) || 60;

    var loanAmount = price - down;
    var monthlyRate = (rate / 100) / 12;

    var monthlyPayment = 0;
    if (monthlyRate > 0) {
        monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, term)) / (Math.pow(1 + monthlyRate, term) - 1);
    } else {
        monthlyPayment = loanAmount / term;
    }

    var res = document.getElementById("results");
    res.style.display = "block";
    res.innerHTML = "<div class=\"det-card\" style=\"text-align:center;\"><div class=\"det-title\">🚗 Estimated Monthly Payment</div><div class=\"det-val\">$" + monthlyPayment.toFixed(2) + "</div></div>";
}';

    } elseif ($type === 'insurance') {
        $data['html'] = '<!-- Insurance HTML -->
<div class="info-box">📌 Estimate your monthly insurance premiums in ' . esc_html($state_name) . '.</div>
<div class="sec">
  <div class="sec-head"><label class="lbl">🛡️ Insurance Details</label></div>
  <div class="field">
    <label class="lbl">Coverage Type</label>
    <div class="sel-wrap">
      <select class="sel" id="ins-type">
        <option value="auto" selected>Auto Insurance</option>
        <option value="home">Homeowners Insurance</option>
        <option value="health">Health Insurance</option>
      </select>
    </div>
  </div>
  <div class="field">
    <label class="lbl">Coverage Level</label>
    <div class="sel-wrap">
      <select class="sel" id="ins-level">
        <option value="standard" selected>Standard / Basic</option>
        <option value="premium">Premium / Full Coverage</option>
      </select>
    </div>
  </div>
</div>
<button class="calc-btn" onclick="calculateInsurance()">🛡️ Calculate Premium</button>
<div class="results" id="results"></div>';

        $data['js'] = 'function calculateInsurance() {
    var type = document.getElementById("ins-type").value;
    var level = document.getElementById("ins-level").value;

    var basePremium = 100;
    if (type === "home") basePremium = 150;
    if (type === "health") basePremium = 300;

    if (level === "premium") basePremium *= 1.5;

    var res = document.getElementById("results");
    res.style.display = "block";
    res.innerHTML = "<div class=\"det-card\" style=\"text-align:center;\"><div class=\"det-title\">🛡️ Estimated Monthly Premium</div><div class=\"det-val\">$" + basePremium.toFixed(2) + "</div></div>";
}';
    }

    // Default global stylesheet (using user design tokens)
    $data['css'] = '/* Theme specific styles */
:root {
  --or: #f97316;
  --re: #dc2626;
  --grad: linear-gradient(135deg,#dc2626 0%,#f97316 100%);
  --soft: linear-gradient(135deg,#fef2f2 0%,#fff7ed 100%);
  --bdr: #e5e7eb;
  --mt: #6b7280;
  --bg: #ffffff;
  --bg2: #f9fafb;
}';

    return $data;
}
