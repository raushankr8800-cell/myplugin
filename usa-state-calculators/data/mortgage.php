<?php
/**
 * Mortgage Calculator Template and Content Generator
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns default HTML, CSS, and JS for the Mortgage Calculator based on state
 */
function usc_get_mortgage_templates($state_slug) {
    $states = usc_get_states_data();
    $state_name = isset($states[$state_slug]) ? $states[$state_slug]['name'] : 'USA';

    $data = [];

    // 1. MORTGAGE CALCULATOR HTML
    $data['html'] = '<!-- mortgage-v1 -->
<!-- INFO BOX -->
<div class="info-box">
  📌 Estimate your monthly mortgage payments (PITI) in ' . esc_html($state_name) . '. Input home price, customize down payment, tax rates, homeowners insurance, and analyze closing costs, extra payments payoff schedules, and DTI affordability.
</div>

<!-- MORTGAGE DETAILS -->
<div class="sec">
  <div class="sec-head"><label class="lbl">🏠 LOAN INFORMATION</label></div>

  <div class="field">
    <label class="lbl">HOME PURCHASE PRICE <span class="usc-tooltip-wrap" data-tooltip="The purchase price of the home you plan to buy.">ℹ️</span></label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="mortgage-home-price" placeholder="e.g. 350000" oninput="syncDownpaymentValues(\'percent\')">
      <span class="inp-suffix">$</span>
    </div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">DOWN PAYMENT ($) <span class="usc-tooltip-wrap" data-tooltip="The upfront cash payment for the home.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="mortgage-downpayment-cash" placeholder="e.g. 70000" oninput="syncDownpaymentValues(\'cash\')">
        <span class="inp-suffix">$</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">DOWN PAYMENT (%) <span class="usc-tooltip-wrap" data-tooltip="Down payment represented as a percentage of home purchase price. 20% or more avoids PMI.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="mortgage-downpayment-pct" placeholder="e.g. 20" min="0" max="100" oninput="syncDownpaymentValues(\'percent\')">
        <span class="inp-suffix">%</span>
      </div>
    </div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">INTEREST RATE (%) <span class="usc-tooltip-wrap" data-tooltip="The annual interest rate for your home loan.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="mortgage-interest-rate" step="0.01" value="6.5">
        <span class="inp-suffix">%</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">LOAN TERM <span class="usc-tooltip-wrap" data-tooltip="The duration of time you have to repay the home loan.">ℹ️</span></label>
      <div class="inp-wrap select-wrap">
        <select id="mortgage-loan-term" class="inp" onchange="calculateMortgage(false)">
          <option value="30">30-Year Fixed</option>
          <option value="20">20-Year Fixed</option>
          <option value="15">15-Year Fixed</option>
          <option value="10">10-Year Fixed</option>
        </select>
      </div>
    </div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">PAYMENT FREQUENCY <span class="usc-tooltip-wrap" data-tooltip="Choose between standard monthly payments and accelerated bi-weekly payments. Accelerated bi-weekly pays half the monthly amount every two weeks, adding an extra month\'s payment each year.">ℹ️</span></label>
      <div class="inp-wrap select-wrap">
        <select id="mortgage-payment-frequency" class="inp" onchange="calculateMortgage(false)">
          <option value="monthly">Monthly</option>
          <option value="biweekly">Accelerated Bi-Weekly</option>
        </select>
      </div>
    </div>
  </div>
</div>

<!-- TAXES & FEES -->
<div class="sec">
  <div class="sec-head"><label class="lbl">📋 TAXES, INSURANCE & FEES</label></div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">PROPERTY TAX RATE (%) <span class="usc-tooltip-wrap" data-tooltip="Average annual property tax rate for this location.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="mortgage-tax-rate" step="0.001">
        <span class="inp-suffix">%</span>
      </div>
      <div class="hint" id="mortgage-tax-hint"></div>
    </div>
    <div class="field">
      <label class="lbl">HOME INSURANCE ($/YR) <span class="usc-tooltip-wrap" data-tooltip="Annual hazard/homeowners insurance premium.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="mortgage-insurance" step="1">
        <span class="inp-suffix">$/yr</span>
      </div>
      <div class="hint" id="mortgage-insurance-hint"></div>
    </div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">HOA FEES ($/MO) <span class="usc-tooltip-wrap" data-tooltip="Monthly Homeowners Association fees (if any).">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="mortgage-hoa" value="0">
        <span class="inp-suffix">$/mo</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">CREDIT SCORE TIER <span class="usc-tooltip-wrap" data-tooltip="FICO Credit Score tier. Higher credit score reduces estimated PMI cost.">ℹ️</span></label>
      <div class="inp-wrap select-wrap">
        <select id="mortgage-credit-tier" class="inp" onchange="calculateMortgage(false)">
          <option value="excellent">Excellent (740+)</option>
          <option value="good" selected>Good (670-739)</option>
          <option value="fair">Fair (580-669)</option>
          <option value="poor">Poor (<580)</option>
        </select>
      </div>
    </div>
  </div>
</div>

<!-- EXTRA PAYMENTS ACCORDION -->
<div class="exemption-accordion-header" onclick="toggleAccordion(\'mortgage-extra-payments\', \'mortgage-extra-arrow\')">
  <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: var(--or);">
    <span>⚙️ EXTRA PAYMENTS (PAYOFF SIMULATOR)</span>
    <span style="font-size: 10px; font-weight: normal; color: var(--mt);">(OPTIONAL)</span>
  </div>
  <span id="mortgage-extra-arrow" style="font-size: 12px; color: var(--or); transition: transform 0.2s ease; display: inline-block;">▶</span>
</div>

<div id="mortgage-extra-payments" style="display:none; margin-top:12px; border: 1.5px solid var(--bdr); border-left: 3px solid var(--or); padding: 16px; border-radius: 8px; background: var(--bg2);">
  <div class="grid2">
    <div class="field">
      <label class="lbl">EXTRA MONTHLY PAYMENT ($) <span class="usc-tooltip-wrap" data-tooltip="Additional principal paid every single month.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="extra-monthly" value="0">
        <span class="inp-suffix">$</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">EXTRA ANNUAL PAYMENT ($) <span class="usc-tooltip-wrap" data-tooltip="Additional principal paid once every year.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="extra-annual" value="0">
        <span class="inp-suffix">$</span>
      </div>
    </div>
  </div>
  <div class="grid2">
    <div class="field">
      <label class="lbl">ONE-TIME PAYMENT ($) <span class="usc-tooltip-wrap" data-tooltip="A single lump-sum payment added to the principal balance.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="extra-onetime" value="0">
        <span class="inp-suffix">$</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">ON PAY_MONTH (1-based) <span class="usc-tooltip-wrap" data-tooltip="Which month from start to apply the one-time extra payment (e.g. month 12).">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="extra-onetime-month" value="12" min="1">
        <span class="inp-suffix">month</span>
      </div>
    </div>
  </div>
</div>

<!-- DTI RATIO ACCORDION -->
<div class="exemption-accordion-header" style="margin-top:10px;" onclick="toggleAccordion(\'mortgage-dti-panel\', \'mortgage-dti-arrow\')">
  <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: var(--or);">
    <span>📊 DEBT-TO-INCOME (DTI) AFFORDABILITY</span>
    <span style="font-size: 10px; font-weight: normal; color: var(--mt);">(OPTIONAL)</span>
  </div>
  <span id="mortgage-dti-arrow" style="font-size: 12px; color: var(--or); transition: transform 0.2s ease; display: inline-block;">▶</span>
</div>

<div id="mortgage-dti-panel" style="display:none; margin-top:12px; border: 1.5px solid var(--bdr); border-left: 3px solid var(--or); padding: 16px; border-radius: 8px; background: var(--bg2);">
  <div class="grid2">
    <div class="field">
      <label class="lbl">GROSS MONTHLY INCOME ($) <span class="usc-tooltip-wrap" data-tooltip="Total pre-tax income earned monthly by all borrowers.">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="dti-income" placeholder="e.g. 8000">
        <span class="inp-suffix">$</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">OTHER MONTHLY DEBTS ($) <span class="usc-tooltip-wrap" data-tooltip="Total monthly minimum payments for auto loans, credit cards, student loans (exclude mortgage).">ℹ️</span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="dti-debts" value="0">
        <span class="inp-suffix">$</span>
      </div>
    </div>
  </div>
</div>

<!-- CALCULATE BTN -->
<button class="calc-btn" onclick="calculateMortgage(true)">
  🧮 CALCULATE MORTGAGE
</button>

<!-- RESULTS container -->
<div class="results" id="results"></div>
<!-- SAVED SCENARIOS WIDGET -->
<div id="mortgage-scenarios-container" style="margin-top: 24px; display: none;"></div>';

    // 2. MORTGAGE CALCULATOR JS
    $data['js'] = 'var stateSlug = "' . esc_js($state_slug) . '";
var stateName = "' . esc_js($state_name) . '";
var resultsCalculated = false;

// Complete State Mortgage defaults database
var mortgageStateDictionary = {
    "alabama": { "homeValue": 220000, "taxRate": 0.40, "insurance": 1600, "closingCostPct": 2.0 },
    "alaska": { "homeValue": 350000, "taxRate": 1.04, "insurance": 1300, "closingCostPct": 2.5 },
    "arizona": { "homeValue": 430000, "taxRate": 0.51, "insurance": 1400, "closingCostPct": 2.0 },
    "arkansas": { "homeValue": 200000, "taxRate": 0.62, "insurance": 1800, "closingCostPct": 2.0 },
    "california": { "homeValue": 780000, "taxRate": 0.71, "insurance": 1700, "closingCostPct": 1.5 },
    "colorado": { "homeValue": 540000, "taxRate": 0.51, "insurance": 2400, "closingCostPct": 2.0 },
    "connecticut": { "homeValue": 380000, "taxRate": 1.96, "insurance": 1500, "closingCostPct": 2.5 },
    "delaware": { "homeValue": 370000, "taxRate": 0.43, "insurance": 1100, "closingCostPct": 3.0 },
    "florida": { "homeValue": 400000, "taxRate": 0.80, "insurance": 3600, "closingCostPct": 2.5 },
    "georgia": { "homeValue": 320000, "taxRate": 0.90, "insurance": 1700, "closingCostPct": 2.0 },
    "hawaii": { "homeValue": 840000, "taxRate": 0.29, "insurance": 1300, "closingCostPct": 2.0 },
    "idaho": { "homeValue": 450000, "taxRate": 0.49, "insurance": 1200, "closingCostPct": 2.0 },
    "illinois": { "homeValue": 270000, "taxRate": 2.08, "insurance": 1600, "closingCostPct": 2.5 },
    "indiana": { "homeValue": 230000, "taxRate": 0.83, "insurance": 1400, "closingCostPct": 2.0 },
    "iowa": { "homeValue": 210000, "taxRate": 1.52, "insurance": 1500, "closingCostPct": 2.0 },
    "kansas": { "homeValue": 220000, "taxRate": 1.34, "insurance": 2200, "closingCostPct": 2.0 },
    "kentucky": { "homeValue": 200000, "taxRate": 0.82, "insurance": 1600, "closingCostPct": 2.0 },
    "louisiana": { "homeValue": 210000, "taxRate": 0.56, "insurance": 2300, "closingCostPct": 2.0 },
    "maine": { "homeValue": 360000, "taxRate": 1.24, "insurance": 1200, "closingCostPct": 2.0 },
    "maryland": { "homeValue": 400000, "taxRate": 1.05, "insurance": 1400, "closingCostPct": 2.5 },
    "massachusetts": { "homeValue": 600000, "taxRate": 1.14, "insurance": 1600, "closingCostPct": 2.0 },
    "michigan": { "homeValue": 230000, "taxRate": 1.38, "insurance": 1600, "closingCostPct": 2.0 },
    "minnesota": { "homeValue": 330000, "taxRate": 1.02, "insurance": 1800, "closingCostPct": 2.0 },
    "mississippi": { "homeValue": 180000, "taxRate": 0.65, "insurance": 1900, "closingCostPct": 2.0 },
    "missouri": { "homeValue": 240000, "taxRate": 0.93, "insurance": 1800, "closingCostPct": 2.0 },
    "montana": { "homeValue": 450000, "taxRate": 0.74, "insurance": 1500, "closingCostPct": 2.0 },
    "nebraska": { "homeValue": 250000, "taxRate": 1.63, "insurance": 2200, "closingCostPct": 2.0 },
    "nevada": { "homeValue": 430000, "taxRate": 0.59, "insurance": 1300, "closingCostPct": 2.0 },
    "new-hampshire": { "homeValue": 440000, "taxRate": 1.93, "insurance": 1200, "closingCostPct": 2.0 },
    "new-jersey": { "homeValue": 500000, "taxRate": 2.23, "insurance": 1400, "closingCostPct": 3.0 },
    "new-mexico": { "homeValue": 290000, "taxRate": 0.67, "insurance": 1400, "closingCostPct": 2.0 },
    "new-york": { "homeValue": 460000, "taxRate": 1.40, "insurance": 1600, "closingCostPct": 3.5 },
    "north-carolina": { "homeValue": 320000, "taxRate": 0.77, "insurance": 1500, "closingCostPct": 2.0 },
    "north-dakota": { "homeValue": 250000, "taxRate": 0.95, "insurance": 1700, "closingCostPct": 2.0 },
    "ohio": { "homeValue": 220000, "taxRate": 1.53, "insurance": 1300, "closingCostPct": 2.0 },
    "oklahoma": { "homeValue": 200000, "taxRate": 0.85, "insurance": 2300, "closingCostPct": 2.0 },
    "oregon": { "homeValue": 500000, "taxRate": 0.93, "insurance": 1300, "closingCostPct": 2.0 },
    "pennsylvania": { "homeValue": 270000, "taxRate": 1.49, "insurance": 1200, "closingCostPct": 3.0 },
    "rhode-island": { "homeValue": 430000, "taxRate": 1.40, "insurance": 1500, "closingCostPct": 2.0 },
    "south-carolina": { "homeValue": 290000, "taxRate": 0.52, "insurance": 1700, "closingCostPct": 2.0 },
    "south-dakota": { "homeValue": 300000, "taxRate": 1.08, "insurance": 1800, "closingCostPct": 2.0 },
    "tennessee": { "homeValue": 310000, "taxRate": 0.66, "insurance": 1700, "closingCostPct": 2.0 },
    "texas": { "homeValue": 300000, "taxRate": 1.60, "insurance": 2700, "closingCostPct": 2.0 },
    "utah": { "homeValue": 510000, "taxRate": 0.52, "insurance": 1200, "closingCostPct": 2.0 },
    "vermont": { "homeValue": 380000, "taxRate": 1.73, "insurance": 1200, "closingCostPct": 2.0 },
    "virginia": { "homeValue": 380000, "taxRate": 0.87, "insurance": 1300, "closingCostPct": 2.0 },
    "washington": { "homeValue": 600000, "taxRate": 0.87, "insurance": 1400, "closingCostPct": 2.0 },
    "west-virginia": { "homeValue": 160000, "taxRate": 0.55, "insurance": 1400, "closingCostPct": 2.0 },
    "wisconsin": { "homeValue": 270000, "taxRate": 1.61, "insurance": 1300, "closingCostPct": 2.0 },
    "wyoming": { "homeValue": 330000, "taxRate": 0.55, "insurance": 1200, "closingCostPct": 2.0 }
};

// Global trend arrays for interactive tooltips
var globalBalanceTrend = [];
var globalInterestTrend = [];

function initMortgageCalculator() {
    var stateData = mortgageStateDictionary[stateSlug] || { "homeValue": 350000, "taxRate": 1.0, "insurance": 1500, "closingCostPct": 2.0 };
    
    var homePriceInput = document.getElementById("mortgage-home-price");
    if (homePriceInput) {
        homePriceInput.value = stateData.homeValue;
        syncDownpaymentValues(\'percent\');
    }
    
    var taxInput = document.getElementById("mortgage-tax-rate");
    if (taxInput) {
        taxInput.value = stateData.taxRate;
    }
    var taxHint = document.getElementById("mortgage-tax-hint");
    if (taxHint) {
        taxHint.textContent = "State Average: " + stateData.taxRate.toFixed(2) + "%";
    }

    var insuranceInput = document.getElementById("mortgage-insurance");
    if (insuranceInput) {
        insuranceInput.value = stateData.insurance;
    }
    var insHint = document.getElementById("mortgage-insurance-hint");
    if (insHint) {
        insHint.textContent = "State Average: $" + stateData.insurance.toLocaleString() + "/yr";
    }

    populateCompareDropdown();

    // Bind events for live calculation and URL updates
    var inputs = document.querySelectorAll(".usc-calculator-container input, .usc-calculator-container select");
    inputs.forEach(function(input) {
        input.addEventListener("input", function() { calculateMortgage(false); });
        input.addEventListener("change", function() { calculateMortgage(false); });
    });

    loadMortgageUrlParams();
    renderSavedMortgageScenarios();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMortgageCalculator);
} else {
    initMortgageCalculator();
}

function toggleAccordion(id, arrowId) {
    var element = document.getElementById(id);
    var arrow = document.getElementById(arrowId);
    if (element && arrow) {
        if (element.style.display === "none") {
            element.style.display = "block";
            arrow.style.transform = "rotate(90deg)";
        } else {
            element.style.display = "none";
            arrow.style.transform = "rotate(0deg)";
        }
    }
}

function syncDownpaymentValues(trigger) {
    var price = parseFloat(document.getElementById("mortgage-home-price").value) || 0;
    var cashInput = document.getElementById("mortgage-downpayment-cash");
    var pctInput = document.getElementById("mortgage-downpayment-pct");

    if (price <= 0) return;

    if (trigger === "cash") {
        var cashVal = parseFloat(cashInput.value) || 0;
        var pctVal = (cashVal / price) * 100;
        pctInput.value = pctVal.toFixed(2);
    } else {
        var pctVal = parseFloat(pctInput.value) || 0;
        if (pctVal === 0 && pctInput.value === "") {
            pctVal = 20; // Default 20% down
            pctInput.value = "20";
        }
        var cashVal = price * (pctVal / 100);
        cashInput.value = Math.round(cashVal);
    }
    calculateMortgage(false);
}

function drawMortgageDonutChart(pi, tax, ins, pmi, hoa) {
    var canvas = document.getElementById("housing-donut-chart");
    if (!canvas) return;
    var ctx = canvas.getContext("2d");
    if (!ctx) return;

    var x = canvas.width / 2;
    var y = canvas.height / 2;
    var radius = 70;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    var total = pi + tax + ins + pmi + hoa;
    if (total === 0) {
        ctx.beginPath();
        ctx.arc(x, y, radius, 0, 2 * Math.PI);
        ctx.lineWidth = 20;
        ctx.strokeStyle = "#e5e7eb";
        ctx.stroke();
        return;
    }

    var segments = [
        { val: pi, col: "#4f46e5" },  // P&I - Indigo
        { val: tax, col: "#f97316" }, // Tax - Orange
        { val: ins, col: "#10b981" }, // Ins - Emerald
        { val: pmi, col: "#dc2626" }, // PMI - Red
        { val: hoa, col: "#8b5cf6" }  // HOA - Violet
    ];

    var startAngle = -0.5 * Math.PI;
    segments.forEach(function(seg) {
        if (seg.val <= 0) return;
        var angle = 2 * Math.PI * (seg.val / total);
        ctx.beginPath();
        ctx.arc(x, y, radius, startAngle, startAngle + angle);
        ctx.lineWidth = 20;
        ctx.strokeStyle = seg.col;
        ctx.stroke();
        startAngle += angle;
    });

    // Label inside hole
    ctx.fillStyle = "#111111";
    ctx.font = "bold 15px sans-serif";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText("$" + Math.round(total).toLocaleString(), x, y - 8);

    ctx.fillStyle = "#6b7280";
    ctx.font = "normal 9px sans-serif";
    ctx.fillText("Monthly PITI", x, y + 12);
}

function drawAmortizationChart(remainingBalanceData, cumulativeInterestData, highlightIndex) {
    var canvas = document.getElementById("amortization-canvas");
    if (!canvas) return;
    var ctx = canvas.getContext("2d");
    if (!ctx) return;

    var width = canvas.width;
    var height = canvas.height;
    var paddingLeft = 40;
    var paddingRight = 15;
    var paddingTop = 15;
    var paddingBottom = 25;

    ctx.clearRect(0, 0, width, height);

    var maxVal = Math.max(remainingBalanceData[0], cumulativeInterestData[cumulativeInterestData.length - 1]);
    if (maxVal <= 0) maxVal = 100;

    // Draw horizontal grid lines
    ctx.strokeStyle = "#e5e7eb";
    ctx.lineWidth = 1;
    for (var i = 0; i < 4; i++) {
        var y = paddingTop + (height - paddingTop - paddingBottom) * (i / 3);
        ctx.beginPath();
        ctx.moveTo(paddingLeft, y);
        ctx.lineTo(width - paddingRight, y);
        ctx.stroke();
    }

    function getX(idx) {
        return paddingLeft + (width - paddingLeft - paddingRight) * (idx / (remainingBalanceData.length - 1));
    }
    function getY(val) {
        return height - paddingBottom - (height - paddingTop - paddingBottom) * (val / maxVal);
    }

    // Line 1: Remaining Balance (Indigo)
    ctx.beginPath();
    ctx.strokeStyle = "#4f46e5";
    ctx.lineWidth = 3;
    ctx.moveTo(getX(0), getY(remainingBalanceData[0]));
    for (var j = 1; j < remainingBalanceData.length; j++) {
        ctx.lineTo(getX(j), getY(remainingBalanceData[j]));
    }
    ctx.stroke();

    // Line 2: Cumulative Interest (Red)
    ctx.beginPath();
    ctx.strokeStyle = "#dc2626";
    ctx.lineWidth = 2.5;
    ctx.moveTo(getX(0), getY(cumulativeInterestData[0]));
    for (var j = 1; j < cumulativeInterestData.length; j++) {
        ctx.lineTo(getX(j), getY(cumulativeInterestData[j]));
    }
    ctx.stroke();

    // Draw hover details if highlightIndex is active
    if (highlightIndex !== undefined && highlightIndex >= 0 && highlightIndex < remainingBalanceData.length) {
        var hx = getX(highlightIndex);

        // Vertical line
        ctx.beginPath();
        ctx.strokeStyle = "#9ca3af";
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 4]);
        ctx.moveTo(hx, paddingTop);
        ctx.lineTo(hx, height - paddingBottom);
        ctx.stroke();
        ctx.setLineDash([]); // Reset dash

        // Dot for Balance
        var hy1 = getY(remainingBalanceData[highlightIndex]);
        ctx.beginPath();
        ctx.fillStyle = "#4f46e5";
        ctx.arc(hx, hy1, 5, 0, 2 * Math.PI);
        ctx.fill();
        ctx.strokeStyle = "#ffffff";
        ctx.lineWidth = 1.5;
        ctx.stroke();

        // Dot for Interest
        var hy2 = getY(cumulativeInterestData[highlightIndex]);
        ctx.beginPath();
        ctx.fillStyle = "#dc2626";
        ctx.arc(hx, hy2, 5, 0, 2 * Math.PI);
        ctx.fill();
        ctx.strokeStyle = "#ffffff";
        ctx.lineWidth = 1.5;
        ctx.stroke();
    }

    // Axis Labels
    ctx.fillStyle = "#6b7280";
    ctx.font = "9px sans-serif";
    ctx.textAlign = "center";
    ctx.fillText("Start", getX(0), height - 8);
    
    var midIndex = Math.floor(remainingBalanceData.length / 2);
    ctx.fillText("Yr " + Math.round(midIndex), getX(midIndex), height - 8);
    ctx.fillText("End", getX(remainingBalanceData.length - 1), height - 8);

    // Y-axis ticks
    ctx.textAlign = "right";
    ctx.textBaseline = "middle";
    for (var i = 0; i < 4; i++) {
        var val = maxVal * (1 - i / 3);
        var y = paddingTop + (height - paddingTop - paddingBottom) * (i / 3);
        ctx.fillText("$" + Math.round(val/1000) + "k", paddingLeft - 5, y);
    }
}

function handleChartHover(evt) {
    var canvas = document.getElementById("amortization-canvas");
    var tooltip = document.getElementById("amortization-tooltip");
    if (!canvas || !tooltip || globalBalanceTrend.length === 0) return;

    var rect = canvas.getBoundingClientRect();
    var scaleX = canvas.width / rect.width;
    
    // Get mouse X relative to canvas drawing coords
    var mouseX = (evt.clientX - rect.left) * scaleX;
    
    var paddingLeft = 40;
    var paddingRight = 15;
    var contentWidth = canvas.width - paddingLeft - paddingRight;
    var ratio = (mouseX - paddingLeft) / contentWidth;
    
    var idx = Math.round(ratio * (globalBalanceTrend.length - 1));
    if (idx >= 0 && idx < globalBalanceTrend.length) {
        drawAmortizationChart(globalBalanceTrend, globalInterestTrend, idx);

        tooltip.innerHTML = `
            <div style="font-weight:700;margin-bottom:3px;border-bottom:1px solid #475569;padding-bottom:2px;">Year ${idx}</div>
            <div><span style="color:#818cf8;margin-right:4px;">●</span>Balance: $${Math.round(globalBalanceTrend[idx]).toLocaleString()}</div>
            <div><span style="color:#f87171;margin-right:4px;">●</span>Interest: $${Math.round(globalInterestTrend[idx]).toLocaleString()}</div>
        `;
        
        // Position tooltip inside the container
        var tooltipWidth = tooltip.offsetWidth;
        var tooltipHeight = tooltip.offsetHeight;
        var posX = evt.clientX - rect.left;
        var posY = evt.clientY - rect.top;

        // Prevent overflow
        if (posX + tooltipWidth + 15 > rect.width) {
            posX = posX - tooltipWidth - 15;
        } else {
            posX = posX + 15;
        }
        posY = posY - tooltipHeight - 10;
        if (posY < 0) posY = 10;

        tooltip.style.left = posX + "px";
        tooltip.style.top = posY + "px";
        tooltip.style.display = "block";
    } else {
        tooltip.style.display = "none";
        drawAmortizationChart(globalBalanceTrend, globalInterestTrend);
    }
}

function setupChartEventListeners() {
    var canvas = document.getElementById("amortization-canvas");
    var tooltip = document.getElementById("amortization-tooltip");
    if (!canvas) return;

    canvas.addEventListener("mousemove", handleChartHover);
    canvas.addEventListener("mouseleave", function() {
        if (tooltip) tooltip.style.display = "none";
        drawAmortizationChart(globalBalanceTrend, globalInterestTrend);
    });

    canvas.addEventListener("touchmove", function(e) {
        if (e.touches.length > 0) {
            handleChartHover(e.touches[0]);
        }
    });
    canvas.addEventListener("touchend", function() {
        if (tooltip) tooltip.style.display = "none";
        drawAmortizationChart(globalBalanceTrend, globalInterestTrend);
    });
}

function calculateMortgage(forceShow) {
    if (forceShow === true) {
        resultsCalculated = true;
    }
    
    // Sync URL Parameters silently on change
    updateMortgageUrlParams();

    if (!resultsCalculated) return;

    var homePrice = parseFloat(document.getElementById("mortgage-home-price").value) || 0;
    var downpaymentCash = parseFloat(document.getElementById("mortgage-downpayment-cash").value) || 0;
    var interestRate = parseFloat(document.getElementById("mortgage-interest-rate").value) || 0;
    var termYears = parseInt(document.getElementById("mortgage-loan-term").value) || 30;
    var isBiweekly = document.getElementById("mortgage-payment-frequency").value === "biweekly";

    var taxRate = parseFloat(document.getElementById("mortgage-tax-rate").value) || 0;
    var annualInsurance = parseFloat(document.getElementById("mortgage-insurance").value) || 0;
    var monthlyHOA = parseFloat(document.getElementById("mortgage-hoa").value) || 0;
    var creditTier = document.getElementById("mortgage-credit-tier").value;

    var loanAmount = Math.max(0, homePrice - downpaymentCash);

    // Calculate baseline Monthly P&I (used for both monthly and accelerated bi-weekly payment)
    var monthlyPI = 0;
    var termMonths = termYears * 12;
    var monthlyRate = (interestRate / 100) / 12;

    if (loanAmount > 0) {
        if (monthlyRate > 0) {
            monthlyPI = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / (Math.pow(1 + monthlyRate, termMonths) - 1);
        } else {
            monthlyPI = loanAmount / termMonths;
        }
    }

    var monthlyTax = (homePrice * (taxRate / 100)) / 12;
    var monthlyInsurance = annualInsurance / 12;

    // PMI calculation
    var monthlyPMI = 0;
    var downpaymentPct = homePrice > 0 ? (downpaymentCash / homePrice) * 100 : 0;
    if (downpaymentPct < 20.0 && loanAmount > 0) {
        var pmiRate = 0.70; // Good credit default
        if (creditTier === "excellent") pmiRate = 0.40;
        else if (creditTier === "fair") pmiRate = 1.10;
        else if (creditTier === "poor") pmiRate = 1.50;
        monthlyPMI = (loanAmount * (pmiRate / 100)) / 12;
    }

    var totalMonthly = monthlyPI + monthlyTax + monthlyInsurance + monthlyPMI + monthlyHOA;

    // Display values depending on frequency
    var displayPayment = totalMonthly;
    var displayLabel = "ESTIMATED MONTHLY PAYMENT";
    var displaySub = "Principal + Interest + Taxes + Ins + PMI";

    if (isBiweekly) {
        displayPayment = (monthlyPI / 2) + (monthlyTax + monthlyInsurance + monthlyPMI + monthlyHOA) / 2;
        displayLabel = "ESTIMATED BI-WEEKLY PAYMENT";
        displaySub = `Bi-Weekly P&I ($${Math.round(monthlyPI / 2).toLocaleString()}) + Escrows ($${Math.round((monthlyTax + monthlyInsurance + monthlyPMI + monthlyHOA)/2).toLocaleString()})`;
    }

    // Extra payments inputs
    var extraMonthly = parseFloat(document.getElementById("extra-monthly").value) || 0;
    var extraAnnual = parseFloat(document.getElementById("extra-annual").value) || 0;
    var extraOnetime = parseFloat(document.getElementById("extra-onetime").value) || 0;
    var extraOnetimeMonth = parseInt(document.getElementById("extra-onetime-month").value) || 0;

    // Amortization Schedule Calculation
    var remainingBalance = loanAmount;
    var totalInterestPaid = 0;
    var yearSummary = [];
    var balanceTrend = [loanAmount];
    var interestTrend = [0];

    // Compute baseline monthly without extra payments first
    var baselineBalance = loanAmount;
    var baselineInterestPaid = 0;
    var baselineMonths = termMonths;
    for (var m = 1; m <= termMonths; m++) {
        if (baselineBalance <= 0) break;
        var baselineInt = baselineBalance * monthlyRate;
        var baselinePrin = Math.min(baselineBalance, monthlyPI - baselineInt);
        baselineInterestPaid += baselineInt;
        baselineBalance -= baselinePrin;
    }

    remainingBalance = loanAmount;
    var actualMonths = 0;
    var currentYearPrin = 0;
    var currentYearInt = 0;

    // Determine simulation frequency constants
    var totalPeriods = isBiweekly ? termYears * 26 : termYears * 12;
    var ratePerPeriod = isBiweekly ? (interestRate / 100) / 26 : (interestRate / 100) / 12;
    var paymentPerPeriod = isBiweekly ? monthlyPI / 2 : monthlyPI;
    var periodsPerYear = isBiweekly ? 26 : 12;

    for (var m = 1; m <= totalPeriods; m++) {
        if (remainingBalance <= 0) break;

        var intPay = remainingBalance * ratePerPeriod;
        var prinPay = paymentPerPeriod - intPay;
        if (prinPay < 0) prinPay = 0;

        var totalPrincipalToPay = prinPay;
        if (isBiweekly) {
            totalPrincipalToPay += extraMonthly / 2;
            if (m === Math.round(extraOnetimeMonth * 2.16)) {
                totalPrincipalToPay += extraOnetime;
            }
            if (m % 26 === 0) {
                totalPrincipalToPay += extraAnnual;
            }
        } else {
            totalPrincipalToPay += extraMonthly;
            if (m === extraOnetimeMonth) {
                totalPrincipalToPay += extraOnetime;
            }
            if (m % 12 === 0) {
                totalPrincipalToPay += extraAnnual;
            }
        }

        totalPrincipalToPay = Math.min(remainingBalance, totalPrincipalToPay);
        totalInterestPaid += intPay;
        remainingBalance -= totalPrincipalToPay;

        currentYearPrin += totalPrincipalToPay;
        currentYearInt += intPay;

        actualMonths = isBiweekly ? Math.ceil(m / 2.16) : m;

        if (m % periodsPerYear === 0 || remainingBalance <= 0) {
            var yrNum = Math.ceil(m / periodsPerYear);
            yearSummary.push({
                year: yrNum,
                principal: currentYearPrin,
                interest: currentYearInt,
                balance: remainingBalance
            });
            balanceTrend.push(remainingBalance);
            interestTrend.push(totalInterestPaid);
            currentYearPrin = 0;
            currentYearInt = 0;
        }
    }

    // Store for interactive chart tooltips
    globalBalanceTrend = balanceTrend;
    globalInterestTrend = interestTrend;

    var monthsSaved = baselineMonths - actualMonths;
    var interestSaved = Math.max(0, baselineInterestPaid - totalInterestPaid);

    // Amortization Table Rows
    var amortizationRows = "";
    yearSummary.forEach(function(yr) {
        amortizationRows += `
            <tr style="border-bottom:1px solid var(--bdr);">
                <td style="padding:6px 4px;font-weight:700;">Year ${yr.year}</td>
                <td style="padding:6px 4px;text-align:right;">$${Math.round(yr.principal).toLocaleString()}</td>
                <td style="padding:6px 4px;text-align:right;">$${Math.round(yr.interest).toLocaleString()}</td>
                <td style="padding:6px 4px;text-align:right;font-weight:700;">$${Math.round(yr.balance).toLocaleString()}</td>
            </tr>
        `;
    });

    // Savings impact alert
    var extraSavingsHtml = "";
    var hasExtra = (extraMonthly > 0 || extraAnnual > 0 || extraOnetime > 0);
    if (monthsSaved > 0 && (isBiweekly || hasExtra)) {
        var yearsSaved = Math.floor(monthsSaved / 12);
        var remainingM = monthsSaved % 12;
        var timeSavedText = "";
        if (yearsSaved > 0) timeSavedText += yearsSaved + " year" + (yearsSaved > 1 ? "s" : "");
        if (remainingM > 0) timeSavedText += (timeSavedText ? " and " : "") + remainingM + " month" + (remainingM > 1 ? "s" : "");

        extraSavingsHtml = `
            <div class="det-card savings-banner" style="background:#f0fdf4; border:1.5px solid #bbf7d0; display:flex; align-items:center; gap:12px; margin-bottom:15px; padding:16px;">
                <span style="font-size:24px;">🚀</span>
                <div>
                    <div style="font-weight:800; color:#166534; font-size:13px; text-transform:uppercase;">Compounding & Savings Impact</div>
                    <div style="font-size:12.5px; color:#15803d; margin-top:2px; line-height:1.4;">
                        Your selections save you <strong style="font-size:14px; color:#166534;">$${Math.round(interestSaved).toLocaleString()}</strong> in interest and shave <strong style="font-size:14px; color:#166534;">${timeSavedText}</strong> off your loan term compared to a standard monthly amortized loan!
                    </div>
                </div>
            </div>
        `;
    }

    // DTI Affordability logic
    var dtiHtml = "";
    var grossIncome = parseFloat(document.getElementById("dti-income").value) || 0;
    if (grossIncome > 0) {
        var otherDebts = parseFloat(document.getElementById("dti-debts").value) || 0;
        var frontDti = (totalMonthly / grossIncome) * 100;
        var backDti = ((totalMonthly + otherDebts) / grossIncome) * 100;

        var frontStatus = frontDti <= 28 ? "<span style=\'color:#16a34a; font-weight:700;\'>Excellent (<= 28%)</span>" : "<span style=\'color:#dc2626; font-weight:700;\'>High (Limit: 28%)</span>";
        var backStatus = backDti <= 36 ? "<span style=\'color:#16a34a; font-weight:700;\'>Excellent (<= 36%)</span>" : "<span style=\'color:#dc2626; font-weight:700;\'>High (Limit: 36%)</span>";
        
        var dtiGrade = "";
        var dtiBg = "";
        var dtiBorder = "";
        var dtiTextCol = "";
        if (frontDti <= 28 && backDti <= 36) {
            dtiGrade = "🏡 FULLY AFFORDABLE";
            dtiBg = "#f0fdf4";
            dtiBorder = "#bbf7d0";
            dtiTextCol = "#15803d";
        } else if (frontDti <= 36 && backDti <= 43) {
            dtiGrade = "⚠️ STRETCHED BUDGET";
            dtiBg = "#fffbeb";
            dtiBorder = "#fde68a";
            dtiTextCol = "#b45309";
        } else {
            dtiGrade = "🚨 HIGH RISK BUDGET";
            dtiBg = "#fef2f2";
            dtiBorder = "#fca5a5";
            dtiTextCol = "#b91c1c";
        }

        dtiHtml = `
            <div class="det-card" style="background:${dtiBg}; border: 1.5px solid ${dtiBorder}; padding:16px;">
                <div style="font-weight: 800; color: ${dtiTextCol}; font-size: 13.5px; margin-bottom: 8px;">${dtiGrade}</div>
                <p style="font-size: 12px; color: ${dtiTextCol}; margin: 0 0 12px 0; line-height:1.4;">
                    Lenders use the <strong>28/36 rule</strong> to grade affordability:
                </p>
                <div style="font-size: 11.5px; color: #111; line-height:1.5;">
                    • <strong>Front-End DTI (Housing Costs):</strong> ${frontDti.toFixed(1)}% — ${frontStatus}<br>
                    • <strong>Back-End DTI (Total Debts):</strong> ${backDti.toFixed(1)}% — ${backStatus}
                </div>
            </div>
        `;
    }

    // Closing Cost Estimator
    var stateCCData = mortgageStateDictionary[stateSlug] || { closingCostPct: 2.0 };
    var totalCC = loanAmount * (stateCCData.closingCostPct / 100);
    
    var originationFee = loanAmount * 0.01;
    var titleIns = loanAmount * 0.005;
    var appraisalCost = 550;
    var recordingTaxes = loanAmount * 0.002;
    var prepaidsEscrow = totalCC - (originationFee + titleIns + appraisalCost + recordingTaxes);
    if (prepaidsEscrow < 0) prepaidsEscrow = 0;

    var closingCostsHtml = `
        <div class="det-card">
            <div class="det-title">💸 ESTIMATED PURCHASE CLOSING COSTS</div>
            <p style="font-size:12px; color:var(--mt); margin:0 0 12px 0;">
                Closing costs typically range from 2% to 4% of the loan amount. In ${stateName}, they average about <strong>${stateCCData.closingCostPct.toFixed(1)}%</strong>:
            </p>
            <table class="comp-table" style="font-size:11px; width:100%; border-collapse:collapse; text-align:left; margin-bottom:8px;">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--bdr);">
                        <th style="padding:6px 4px;">Closing Cost Component</th>
                        <th style="padding:6px 4px; text-align:right;">Estimated Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Lender Origination & Underwriting Fee (1%)</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(originationFee).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Title Search & Title Insurance (0.5%)</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(titleIns).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Home Appraisal & Credit Report Fees</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(appraisalCost).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Government Recording Fees & Taxes</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(recordingTaxes).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Prepaid Interest, Insurance & Tax Escrows</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(prepaidsEscrow).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:2px solid var(--re); background:var(--soft); font-weight:700;">
                        <td style="padding:6px 4px;">Total Estimated Cash Closing Costs</td>
                        <td style="padding:6px 4px; text-align:right; color:var(--re);">$${Math.round(totalCC).toLocaleString()}</td>
                    </tr>
                </tbody>
            </table>
            <div style="font-size:10px; color:var(--mt); font-style:italic;">
                * Actual closing costs vary depending on title companies, mortgage brokers, and local transfer fees.
            </div>
        </div>
    `;

    // Dynamic Outputs render
    var res = document.getElementById("results");
    res.style.display = "block";

    res.innerHTML = `
        <div class="hero-grid">
            <div class="hero-card">
                <div class="hero-lbl">${displayLabel}</div>
                <div class="hero-val">$${Math.round(displayPayment).toLocaleString()}</div>
                <div class="hero-sub">${displaySub}</div>
            </div>
            <div class="hero-card">
                <div class="hero-lbl">TOTAL INTEREST TO PAY</div>
                <div class="hero-val">$${Math.round(totalInterestPaid).toLocaleString()}</div>
                <div class="hero-sub">Over the life of the loan</div>
            </div>
        </div>

        <button onclick="saveMortgageScenario()" style="width: 100%; margin-bottom: 15px; padding: 12px; background: #4f46e5; color: #fff; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px;">💾 SAVE THIS SCENARIO FOR COMPARISON</button>

        ${extraSavingsHtml}

        <div class="det-card">
            <div class="det-title">📊 PITI BREAKDOWN</div>
            <div style="display: flex; justify-content: center; align-items: center; margin: 20px 0;">
                <canvas id="housing-donut-chart" width="200" height="200" style="max-width:200px; max-height:200px;"></canvas>
            </div>
            <table class="comp-table" style="font-size:11.5px; width:100%; border-collapse:collapse; text-align:left; margin-bottom:8px;">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--bdr);">
                        <th style="padding:6px 4px;">Payment Component</th>
                        <th style="padding:6px 4px; text-align:right;">Monthly Cost</th>
                        <th style="padding:6px 4px; text-align:right;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;"><span style="width:8px; height:8px; background:#4f46e5; display:inline-block; border-radius:50%; margin-right:6px;"></span> Mortgage P&I</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(monthlyPI).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#4f46e5;">${((monthlyPI/totalMonthly)*100).toFixed(1)}%</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;"><span style="width:8px; height:8px; background:#f97316; display:inline-block; border-radius:50%; margin-right:6px;"></span> Property Tax</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(monthlyTax).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#f97316;">${((monthlyTax/totalMonthly)*100).toFixed(1)}%</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;"><span style="width:8px; height:8px; background:#10b981; display:inline-block; border-radius:50%; margin-right:6px;"></span> Home Insurance</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(monthlyInsurance).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#10b981;">${((monthlyInsurance/totalMonthly)*100).toFixed(1)}%</td>
                    </tr>
                    ${monthlyPMI > 0 ? `
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;"><span style="width:8px; height:8px; background:#dc2626; display:inline-block; border-radius:50%; margin-right:6px;"></span> PMI (Private Mortgage Ins.)</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(monthlyPMI).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#dc2626;">${((monthlyPMI/totalMonthly)*100).toFixed(1)}%</td>
                    </tr>` : ""}
                    ${monthlyHOA > 0 ? `
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;"><span style="width:8px; height:8px; background:#8b5cf6; display:inline-block; border-radius:50%; margin-right:6px;"></span> HOA Fees</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(monthlyHOA).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#8b5cf6;">${((monthlyHOA/totalMonthly)*100).toFixed(1)}%</td>
                    </tr>` : ""}
                    <tr style="border-bottom:2px solid var(--re); background:var(--soft); font-size:12px; font-weight:700;">
                        <td style="padding:6px 4px;">Total Estimated Monthly Payment</td>
                        <td style="padding:6px 4px; text-align:right; color:#111;">$${Math.round(totalMonthly).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#111;">100.0%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="det-card">
            <div class="det-title">📈 AMORTIZATION BALANCE TREND</div>
            <p style="font-size:12px; color:var(--mt); margin:0 0 16px 0;">
                See how your loan balance declines (<span style="color:#4f46e5;font-weight:700;">Remaining Principal</span>) compared to the (<span style="color:#dc2626;font-weight:700;">Cumulative Interest Paid</span>) over time (Hover for details):
            </p>
            <div style="position: relative; display: inline-block; width: 100%; max-width: 360px; margin: 20px auto; text-align: center;">
                <canvas id="amortization-canvas" width="360" height="180" style="width: 100%; height: auto; display: block;"></canvas>
                <div id="amortization-tooltip" class="usc-chart-tooltip" style="position: absolute; display: none; background: rgba(15, 23, 42, 0.9); color: #fff; padding: 6px 10px; border-radius: 4px; font-size: 11px; pointer-events: none; z-index: 10; box-shadow: 0 4px 6px rgba(0,0,0,0.15); text-align: left; line-height: 1.3; white-space: nowrap;"></div>
            </div>
            
            <div style="max-height: 250px; overflow-y: auto; border: 1.5px solid var(--bdr); border-radius: 8px; margin-top: 15px;">
                <table class="comp-table" style="font-size:11px; width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="position: sticky; top: 0; background: var(--bg2); border-bottom: 1.5px solid var(--bdr); z-index: 2;">
                        <tr>
                            <th style="padding:6px 4px;">Timeline</th>
                            <th style="padding:6px 4px; text-align:right;">Principal Paid</th>
                            <th style="padding:6px 4px; text-align:right;">Interest Paid</th>
                            <th style="padding:6px 4px; text-align:right;">Ending Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${amortizationRows}
                    </tbody>
                </table>
            </div>
        </div>

        ${closingCostsHtml}

        ${dtiHtml}

        <!-- DYNAMIC COMPARISON WIDGET -->
        <div class="det-card" style="margin-top: 15px;">
            <div class="det-title" style="margin-bottom: 10px;">🌎 STATE-BY-STATE MORTGAGE COMPARISON</div>
            <p style="font-size: 12px; color: var(--mt); margin-bottom: 12px;">
                Compare how much mortgage you would pay for the same loan amount (<strong>$${Math.round(loanAmount).toLocaleString()}</strong>) in another US state:
            </p>
            <div style="display: flex; gap: 8px; justify-content: center; align-items: center; margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight:700; color: #111;">Compare with:</label>
                <select id="compare-mortgage-select" class="inp" style="max-width: 200px; padding: 6px 12px;" onchange="onCompareMortgageChange()">
                    <!-- Populated dynamically -->
                </select>
            </div>
            <div id="mortgage-comparison-results" style="display:none;"></div>
        </div>

        <!-- LINK BUTTONS -->
        <div class="det-card" style="margin-top: 15px; text-align: center;">
            <div class="det-title" style="margin-bottom: 10px;">🌎 COMPARE RATES ACROSS STATES</div>
            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                <a href="../mortgage-calculator-california/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">California</a>
                <a href="../mortgage-calculator-texas/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">Texas</a>
                <a href="../mortgage-calculator-florida/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">Florida</a>
                <a href="../mortgage-calculator-new-york/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">New York</a>
            </div>
        </div>

        <button class="reset-btn" onclick="resetMortgageCalculator()">🔄 CALCULATE AGAIN</button>
        <button class="print-btn" onclick="window.print()">🖨️ PRINT DETAILS REPORT</button>
    `;

    // Populate comparison dropdown now that it is in the DOM
    populateCompareDropdown();

    // Draw Canvas Charts
    drawMortgageDonutChart(monthlyPI, monthlyTax, monthlyInsurance, monthlyPMI, monthlyHOA);
    drawAmortizationChart(balanceTrend, interestTrend);
    setupChartEventListeners();

    res.scrollIntoView({behavior: "smooth", block: "start"});
}

function resetMortgageCalculator() {
    var stateData = mortgageStateDictionary[stateSlug] || { "homeValue": 350000, "taxRate": 1.0, "insurance": 1500, "closingCostPct": 2.0 };
    document.getElementById("mortgage-home-price").value = stateData.homeValue;
    syncDownpaymentValues(\'percent\');
    document.getElementById("mortgage-interest-rate").value = "6.5";
    document.getElementById("mortgage-loan-term").value = "30";
    document.getElementById("mortgage-payment-frequency").value = "monthly";
    document.getElementById("mortgage-tax-rate").value = stateData.taxRate;
    document.getElementById("mortgage-insurance").value = stateData.insurance;
    document.getElementById("mortgage-hoa").value = "0";
    document.getElementById("mortgage-credit-tier").value = "good";

    document.getElementById("extra-monthly").value = "0";
    document.getElementById("extra-annual").value = "0";
    document.getElementById("extra-onetime").value = "0";
    document.getElementById("extra-onetime-month").value = "12";

    document.getElementById("dti-income").value = "";
    document.getElementById("dti-debts").value = "0";

    resultsCalculated = false;
    var res = document.getElementById("results");
    if (res) {
        res.innerHTML = "";
        res.style.display = "none";
    }

    // Close options panels
    document.getElementById("mortgage-extra-payments").style.display = "none";
    document.getElementById("mortgage-extra-arrow").style.transform = "rotate(0deg)";
    document.getElementById("mortgage-dti-panel").style.display = "none";
    document.getElementById("mortgage-dti-arrow").style.transform = "rotate(0deg)";

    updateMortgageUrlParams();

    window.scrollTo({top:0, behavior:"smooth"});
}

function populateCompareDropdown() {
    var select = document.getElementById("compare-mortgage-select");
    if (!select) return;
    select.innerHTML = \'<option value="">-- Select State --</option>\';

    var stateSlugs = Object.keys(mortgageStateDictionary).sort();
    stateSlugs.forEach(function(slug) {
        var opt = document.createElement("option");
        opt.value = slug;
        opt.textContent = slug.toUpperCase().replace("-", " ");
        select.appendChild(opt);
    });
}

function onCompareMortgageChange() {
    var select = document.getElementById("compare-mortgage-select");
    var container = document.getElementById("mortgage-comparison-results");
    if (!select || !container) return;

    var targetSlug = select.value;
    if (targetSlug === "") {
        container.innerHTML = "";
        container.style.display = "none";
        return;
    }

    var targetData = mortgageStateDictionary[targetSlug];
    var currentData = mortgageStateDictionary[stateSlug] || { "taxRate": 1.0, "insurance": 1500 };

    var homePrice = parseFloat(document.getElementById("mortgage-home-price").value) || 0;
    var downpaymentCash = parseFloat(document.getElementById("mortgage-downpayment-cash").value) || 0;
    var loanAmount = Math.max(0, homePrice - downpaymentCash);
    var interestRate = parseFloat(document.getElementById("mortgage-interest-rate").value) || 0;
    var termYears = parseInt(document.getElementById("mortgage-loan-term").value) || 30;

    var currentTaxRate = parseFloat(document.getElementById("mortgage-tax-rate").value) || currentData.taxRate;
    var currentAnnualInsurance = parseFloat(document.getElementById("mortgage-insurance").value) || currentData.insurance;

    // Monthly P&I
    var monthlyRate = (interestRate / 100) / 12;
    var termMonths = termYears * 12;
    var monthlyPI = 0;
    if (loanAmount > 0) {
        if (monthlyRate > 0) {
            monthlyPI = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / (Math.pow(1 + monthlyRate, termMonths) - 1);
        } else {
            monthlyPI = loanAmount / termMonths;
        }
    }

    // Current State PITI
    var currentMonthlyTax = (homePrice * (currentTaxRate / 100)) / 12;
    var currentMonthlyInsurance = currentAnnualInsurance / 12;
    var currentMonthlyPMI = 0;
    var downpaymentPct = homePrice > 0 ? (downpaymentCash / homePrice) * 100 : 0;
    if (downpaymentPct < 20.0 && loanAmount > 0) {
        var creditTier = document.getElementById("mortgage-credit-tier").value;
        var pmiRate = 0.70;
        if (creditTier === "excellent") pmiRate = 0.40;
        else if (creditTier === "fair") pmiRate = 1.10;
        else if (creditTier === "poor") pmiRate = 1.50;
        currentMonthlyPMI = (loanAmount * (pmiRate / 100)) / 12;
    }
    var currentMonthlyHOA = parseFloat(document.getElementById("mortgage-hoa").value) || 0;
    var currentTotalMonthly = monthlyPI + currentMonthlyTax + currentMonthlyInsurance + currentMonthlyPMI + currentMonthlyHOA;

    // Target State PITI
    var targetTaxRate = targetData.taxRate;
    var targetMonthlyTax = (homePrice * (targetTaxRate / 100)) / 12;
    var targetMonthlyInsurance = targetData.insurance / 12;
    var targetMonthlyPMI = currentMonthlyPMI;
    var targetMonthlyHOA = currentMonthlyHOA;
    var targetTotalMonthly = monthlyPI + targetMonthlyTax + targetMonthlyInsurance + targetMonthlyPMI + targetMonthlyHOA;

    var diff = targetTotalMonthly - currentTotalMonthly;
    var diffText = "";
    if (diff < 0) {
        diffText = `<div style="margin-top:10px; font-weight:700; color:#16a34a; text-align:center;">🏡 You would save $${Math.round(Math.abs(diff)).toLocaleString()}/month in ${select.options[select.selectedIndex].text}!</div>`;
    } else if (diff > 0) {
        diffText = `<div style="margin-top:10px; font-weight:700; color:#dc2626; text-align:center;">⚠️ You would pay $${Math.round(diff).toLocaleString()}/month more in ${select.options[select.selectedIndex].text}!</div>`;
    } else {
        diffText = `<div style="margin-top:10px; font-weight:700; color:#6b7280; text-align:center;">= Equal monthly payment in both states.</div>`;
    }

    container.innerHTML = `
        <table class="comp-table" style="font-size:11.5px; width:100%; border-collapse:collapse; text-align:left; margin-top:10px;">
            <thead>
                <tr style="border-bottom:1.5px solid var(--bdr);">
                    <th style="padding:6px 4px;">PITI Component</th>
                    <th style="padding:6px 4px; text-align:right;">${stateName} (Current)</th>
                    <th style="padding:6px 4px; text-align:right;">${select.options[select.selectedIndex].text}</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid var(--bdr);">
                    <td style="padding:6px 4px;">Mortgage Principal & Interest</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(monthlyPI).toLocaleString()}</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(monthlyPI).toLocaleString()}</td>
                </tr>
                <tr style="border-bottom:1px solid var(--bdr);">
                    <td style="padding:6px 4px;">Property Tax (Rate: ${currentTaxRate.toFixed(2)}% vs ${targetTaxRate.toFixed(2)}%)</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(currentMonthlyTax).toLocaleString()}</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(targetMonthlyTax).toLocaleString()}</td>
                </tr>
                <tr style="border-bottom:1px solid var(--bdr);">
                    <td style="padding:6px 4px;">Homeowners Insurance</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(currentMonthlyInsurance).toLocaleString()}</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(targetMonthlyInsurance).toLocaleString()}</td>
                </tr>
                <tr style="border-bottom:1px solid var(--bdr);">
                    <td style="padding:6px 4px;">Private Mortgage Insurance (PMI)</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(currentMonthlyPMI).toLocaleString()}</td>
                    <td style="padding:6px 4px; text-align:right;">$${Math.round(targetMonthlyPMI).toLocaleString()}</td>
                </tr>
                <tr style="border-bottom:1.5px solid var(--bdr); font-weight:700;">
                    <td style="padding:6px 4px;">Total Monthly PITI</td>
                    <td style="padding:6px 4px; text-align:right; color:#111;">$${Math.round(currentTotalMonthly).toLocaleString()}</td>
                    <td style="padding:6px 4px; text-align:right; color:var(--re);">$${Math.round(targetTotalMonthly).toLocaleString()}</td>
                </tr>
            </tbody>
        </table>
        ${diffText}
    `;
    container.style.display = "block";
}

function updateMortgageUrlParams() {
    var params = new URLSearchParams();
    
    var homePrice = document.getElementById("mortgage-home-price").value;
    var downCash = document.getElementById("mortgage-downpayment-cash").value;
    var downPct = document.getElementById("mortgage-downpayment-pct").value;
    var rate = document.getElementById("mortgage-interest-rate").value;
    var term = document.getElementById("mortgage-loan-term").value;
    var freq = document.getElementById("mortgage-payment-frequency").value;
    var taxRate = document.getElementById("mortgage-tax-rate").value;
    var insurance = document.getElementById("mortgage-insurance").value;
    var hoa = document.getElementById("mortgage-hoa").value;
    var credit = document.getElementById("mortgage-credit-tier").value;
    
    var extraMonthly = document.getElementById("extra-monthly").value;
    var extraAnnual = document.getElementById("extra-annual").value;
    var extraOnetime = document.getElementById("extra-onetime").value;
    var extraOnetimeMonth = document.getElementById("extra-onetime-month").value;
    
    var dtiIncome = document.getElementById("dti-income").value;
    var dtiDebts = document.getElementById("dti-debts").value;

    if (homePrice) params.set("price", homePrice);
    if (downCash) params.set("down_cash", downCash);
    if (downPct) params.set("down_pct", downPct);
    if (rate) params.set("rate", rate);
    if (term) params.set("term", term);
    if (freq) params.set("freq", freq);
    if (taxRate) params.set("tax", taxRate);
    if (insurance) params.set("ins", insurance);
    if (hoa) params.set("hoa", hoa);
    if (credit) params.set("credit", credit);
    
    if (parseFloat(extraMonthly) > 0) params.set("ex_mo", extraMonthly);
    if (parseFloat(extraAnnual) > 0) params.set("ex_an", extraAnnual);
    if (parseFloat(extraOnetime) > 0) params.set("ex_ot", extraOnetime);
    if (parseInt(extraOnetimeMonth) !== 12) params.set("ex_ot_m", extraOnetimeMonth);
    
    if (dtiIncome) params.set("dti_inc", dtiIncome);
    if (parseFloat(dtiDebts) > 0) params.set("dti_deb", dtiDebts);
    
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

function loadMortgageUrlParams() {
    var params = new URLSearchParams(window.location.search);
    
    if (params.has("price")) document.getElementById("mortgage-home-price").value = params.get("price");
    if (params.has("down_pct")) document.getElementById("mortgage-downpayment-pct").value = params.get("down_pct");
    
    if (params.has("down_cash")) {
        document.getElementById("mortgage-downpayment-cash").value = params.get("down_cash");
    } else if (params.has("price") || params.has("down_pct")) {
        var price = parseFloat(document.getElementById("mortgage-home-price").value) || 0;
        var pct = parseFloat(document.getElementById("mortgage-downpayment-pct").value) || 20;
        document.getElementById("mortgage-downpayment-cash").value = Math.round(price * (pct / 100));
    }
    
    if (params.has("rate")) document.getElementById("mortgage-interest-rate").value = params.get("rate");
    if (params.has("term")) document.getElementById("mortgage-loan-term").value = params.get("term");
    if (params.has("freq")) document.getElementById("mortgage-payment-frequency").value = params.get("freq");
    if (params.has("tax")) document.getElementById("mortgage-tax-rate").value = params.get("tax");
    if (params.has("ins")) document.getElementById("mortgage-insurance").value = params.get("ins");
    if (params.has("hoa")) document.getElementById("mortgage-hoa").value = params.get("hoa");
    if (params.has("credit")) document.getElementById("mortgage-credit-tier").value = params.get("credit");
    
    if (params.has("ex_mo")) {
        document.getElementById("extra-monthly").value = params.get("ex_mo");
        document.getElementById("mortgage-extra-payments").style.display = "block";
        document.getElementById("mortgage-extra-arrow").style.transform = "rotate(90deg)";
    }
    if (params.has("ex_an")) {
        document.getElementById("extra-annual").value = params.get("ex_an");
        document.getElementById("mortgage-extra-payments").style.display = "block";
        document.getElementById("mortgage-extra-arrow").style.transform = "rotate(90deg)";
    }
    if (params.has("ex_ot")) {
        document.getElementById("extra-onetime").value = params.get("ex_ot");
        document.getElementById("mortgage-extra-payments").style.display = "block";
        document.getElementById("mortgage-extra-arrow").style.transform = "rotate(90deg)";
    }
    if (params.has("ex_ot_m")) document.getElementById("extra-onetime-month").value = params.get("ex_ot_m");
    
    if (params.has("dti_inc")) {
        document.getElementById("dti-income").value = params.get("dti_inc");
        document.getElementById("mortgage-dti-panel").style.display = "block";
        document.getElementById("mortgage-dti-arrow").style.transform = "rotate(90deg)";
    }
    if (params.has("dti_deb")) {
        document.getElementById("dti-debts").value = params.get("dti_deb");
        document.getElementById("mortgage-dti-panel").style.display = "block";
        document.getElementById("mortgage-dti-arrow").style.transform = "rotate(90deg)";
    }
    
    if (params.get("calc") === "1") {
        calculateMortgage(true);
    }
}

function saveMortgageScenario() {
    var scenarios = JSON.parse(localStorage.getItem("usc_mortgage_scenarios") || "[]");
    
    var homePrice = parseFloat(document.getElementById("mortgage-home-price").value) || 0;
    var downpaymentCash = parseFloat(document.getElementById("mortgage-downpayment-cash").value) || 0;
    var interestRate = parseFloat(document.getElementById("mortgage-interest-rate").value) || 0;
    var termYears = parseInt(document.getElementById("mortgage-loan-term").value) || 30;
    var isBiweekly = document.getElementById("mortgage-payment-frequency").value === "biweekly";
    
    var taxRate = parseFloat(document.getElementById("mortgage-tax-rate").value) || 0;
    var annualInsurance = parseFloat(document.getElementById("mortgage-insurance").value) || 0;
    var monthlyHOA = parseFloat(document.getElementById("mortgage-hoa").value) || 0;
    
    var loanAmount = Math.max(0, homePrice - downpaymentCash);
    var monthlyRate = (interestRate / 100) / 12;
    var termMonths = termYears * 12;
    var monthlyPI = 0;
    if (loanAmount > 0) {
        if (monthlyRate > 0) {
            monthlyPI = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / (Math.pow(1 + monthlyRate, termMonths) - 1);
        } else {
            monthlyPI = loanAmount / termMonths;
        }
    }
    var monthlyTax = (homePrice * (taxRate / 100)) / 12;
    var monthlyInsurance = annualInsurance / 12;
    var creditTier = document.getElementById("mortgage-credit-tier").value;
    var monthlyPMI = 0;
    var downpaymentPct = homePrice > 0 ? (downpaymentCash / homePrice) * 100 : 0;
    if (downpaymentPct < 20.0 && loanAmount > 0) {
        var pmiRate = 0.70;
        if (creditTier === "excellent") pmiRate = 0.40;
        else if (creditTier === "fair") pmiRate = 1.10;
        else if (creditTier === "poor") pmiRate = 1.50;
        monthlyPMI = (loanAmount * (pmiRate / 100)) / 12;
    }
    var totalMonthly = monthlyPI + monthlyTax + monthlyInsurance + monthlyPMI + monthlyHOA;
    var displayPayment = isBiweekly ? (monthlyPI / 2) + (monthlyTax + monthlyInsurance + monthlyPMI + monthlyHOA) / 2 : totalMonthly;
    
    // Simulate amortization to get total interest paid
    var remainingBalance = loanAmount;
    var totalInterestPaid = 0;
    var ratePerPeriod = isBiweekly ? (interestRate / 100) / 26 : (interestRate / 100) / 12;
    var paymentPerPeriod = isBiweekly ? monthlyPI / 2 : monthlyPI;
    var totalPeriods = isBiweekly ? termYears * 26 : termYears * 12;
    
    var extraMonthly = parseFloat(document.getElementById("extra-monthly").value) || 0;
    var extraAnnual = parseFloat(document.getElementById("extra-annual").value) || 0;
    var extraOnetime = parseFloat(document.getElementById("extra-onetime").value) || 0;
    var extraOnetimeMonth = parseInt(document.getElementById("extra-onetime-month").value) || 0;
    
    for (var m = 1; m <= totalPeriods; m++) {
        if (remainingBalance <= 0) break;
        var intPay = remainingBalance * ratePerPeriod;
        var prinPay = paymentPerPeriod - intPay;
        if (prinPay < 0) prinPay = 0;
        
        var totalPrincipalToPay = prinPay;
        if (isBiweekly) {
            totalPrincipalToPay += extraMonthly / 2;
            if (m === Math.round(extraOnetimeMonth * 2.16)) totalPrincipalToPay += extraOnetime;
            if (m % 26 === 0) totalPrincipalToPay += extraAnnual;
        } else {
            totalPrincipalToPay += extraMonthly;
            if (m === extraOnetimeMonth) totalPrincipalToPay += extraOnetime;
            if (m % 12 === 0) totalPrincipalToPay += extraAnnual;
        }
        totalPrincipalToPay = Math.min(remainingBalance, totalPrincipalToPay);
        totalInterestPaid += intPay;
        remainingBalance -= totalPrincipalToPay;
    }

    var newScenario = {
        id: Date.now(),
        homePrice: homePrice,
        loanAmount: loanAmount,
        downpaymentPct: downpaymentPct,
        downpaymentCash: downpaymentCash,
        interestRate: interestRate,
        termYears: termYears,
        isBiweekly: isBiweekly,
        payment: displayPayment,
        interestPaid: totalInterestPaid,
        label: "$" + Math.round(homePrice/1000) + "k, " + termYears + "y @ " + interestRate + "%" + (isBiweekly ? " BW" : "")
    };
    
    scenarios.push(newScenario);
    if (scenarios.length > 3) {
        scenarios.shift(); // keep 3 max
    }
    
    localStorage.setItem("usc_mortgage_scenarios", JSON.stringify(scenarios));
    renderSavedScenarios();
}

function deleteMortgageScenario(id) {
    var scenarios = JSON.parse(localStorage.getItem("usc_mortgage_scenarios") || "[]");
    scenarios = scenarios.filter(function(s) { return s.id !== id; });
    localStorage.setItem("usc_mortgage_scenarios", JSON.stringify(scenarios));
    renderSavedMortgageScenarios();
}

function renderSavedMortgageScenarios() {
    var container = document.getElementById("mortgage-scenarios-container");
    if (!container) return;
    
    var scenarios = JSON.parse(localStorage.getItem("usc_mortgage_scenarios") || "[]");
    if (scenarios.length === 0) {
        container.innerHTML = "";
        container.style.display = "none";
        return;
    }
    
    var tableHeaders = scenarios.map(function(s) {
        return `<th style="padding:10px 6px; text-align:right; min-width: 100px;">
            <div style="font-weight:700; font-size:12px; color:#111;">${s.label}</div>
            <button onclick="deleteMortgageScenario(${s.id})" style="border:none; background:none; color:#ef4444; font-size:10px; cursor:pointer; padding:2px 0; font-weight:700;">[Remove]</button>
        </th>`;
    }).join("");
    
    var homePriceRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right;">$${Math.round(s.homePrice).toLocaleString()}</td>`;
    }).join("");
    
    var loanAmtRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right;">$${Math.round(s.loanAmount).toLocaleString()}</td>`;
    }).join("");
    
    var downPayRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right;">$${Math.round(s.downpaymentCash).toLocaleString()} (${Math.round(s.downpaymentPct)}%)</td>`;
    }).join("");
    
    var rateRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right;">${s.interestRate.toFixed(2)}%</td>`;
    }).join("");
    
    var termRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right;">${s.termYears} Years</td>`;
    }).join("");
    
    var freqRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right;">${s.isBiweekly ? "Bi-Weekly" : "Monthly"}</td>`;
    }).join("");
    
    var paymentRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right; font-weight:700; color:var(--re);">$${Math.round(s.payment).toLocaleString()}</td>`;
    }).join("");
    
    var interestRow = scenarios.map(function(s) {
        return `<td style="padding:8px 6px; text-align:right; font-weight:700; color:#111;">$${Math.round(s.interestPaid).toLocaleString()}</td>`;
    }).join("");

    container.innerHTML = `
        <div class="det-card" style="border: 2px solid var(--or); border-top: 5px solid var(--or); background: var(--bg2);">
            <div class="det-title" style="color:var(--or); font-size:14px; display:flex; align-items:center; gap:6px;">💾 SAVED SCENARIOS COMPARISON</div>
            <p style="font-size:11.5px; color:var(--mt); margin-top:4px; margin-bottom:12px;">Compare up to 3 saved configurations side-by-side. New saves replace the oldest.</p>
            <div style="overflow-x:auto;">
                <table class="comp-table" style="width:100%; font-size:11px; border-collapse:collapse; min-width: 400px;">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--bdr);">
                            <th style="padding:10px 6px; text-align:left;">Parameters</th>
                            ${tableHeaders}
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left;">Home Price</td>
                            ${homePriceRow}
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left;">Loan Amount</td>
                            ${loanAmtRow}
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left;">Down Payment</td>
                            ${downPayRow}
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left;">Interest Rate</td>
                            ${rateRow}
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left;">Loan Term</td>
                            ${termRow}
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left;">Frequency</td>
                            ${freqRow}
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr); background: var(--soft);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left; color:var(--re);">Est. Payment</td>
                            ${paymentRow}
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:8px 6px; font-weight:700; text-align:left;">Total Interest</td>
                            ${interestRow}
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    container.style.display = "block";
}';

    $data['css'] = '/* Mortgage visualizer styles */
.exemption-accordion-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: var(--bg2);
  border: 1.5px solid var(--bdr);
  border-radius: 8px;
  cursor: pointer;
  margin-top: 15px;
  user-select: none;
}
.exemption-accordion-header:hover {
  background: #fef2f2;
}
.savings-banner {
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
  animation: slideDown 0.3s ease-out;
}
.usc-compare-btn:hover {
  background: #fff7ed;
  border-color: var(--or) !important;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}';

    return $data;
}

/**
 * Returns default FAQs for Mortgage Calculator
 */
function usc_get_default_mortgage_faqs($state) {
    $name = $state['name'];
    $state_slug = strtolower(str_replace(' ', '-', $name));

    $faq_pool = [
        [
            'q' => 'How much down payment do I need for a mortgage in ' . $name . '?',
            'a' => 'While 20% down is the standard to avoid paying Private Mortgage Insurance (PMI), conventional mortgages are available with as little as 3% down. FHA loans require a minimum of 3.5% down, and VA/USDA loans offer 0% down options for qualified borrowers in ' . $name . '.'
        ],
        [
            'q' => 'What is a good Debt-to-Income (DTI) ratio for a mortgage?',
            'a' => 'Lenders typically recommend keeping your front-end DTI (monthly housing costs alone) at or below 28% of your gross monthly income, and your back-end DTI (housing costs plus student loans, credit cards, auto loans) at or under 36%. Some lenders permit DTIs up to 43% or 50% under specific circumstances.'
        ],
        [
            'q' => 'How does interest rate affect my monthly mortgage payment?',
            'a' => 'Your interest rate determines the cost of borrowing. A higher interest rate increases your monthly Principal & Interest (P&I) payment and leads to significantly more interest paid over the life of the loan. For example, a 1% rate difference on a $300,000 mortgage can shift your monthly payment by nearly $200.'
        ],
        [
            'q' => 'What are closing costs and how much are they in ' . $name . '?',
            'a' => 'Closing costs are the fees paid to finalize your home loan, including lender origination fees, home appraisal, title search/title insurance, and recording taxes. In ' . $name . ', closing costs typically average between 2% and 4% of the total loan amount.'
        ],
        [
            'q' => 'What is Private Mortgage Insurance (PMI) and how do I avoid it?',
            'a' => 'PMI is an insurance premium required by lenders to protect themselves if you default on conventional loans with less than a 20% down payment. It is typically calculated as an annual percentage of the loan amount and paid monthly. You can avoid PMI entirely by making a down payment of 20% or more.'
        ],
        [
            'q' => 'How do extra payments shorten my mortgage term?',
            'a' => 'Any extra payments you make are applied directly toward your loan\'s principal balance, rather than interest. By lowering the principal balance early, you reduce the interest generated in all subsequent months, which accelerates your payoff schedule and saves thousands of dollars in lifetime interest.'
        ],
        [
            'q' => 'What is PITI and what does it stand for?',
            'a' => 'PITI stands for Principal, Interest, Taxes, and Insurance. It represents the comprehensive components that make up a homeowner\'s total monthly mortgage payment: Principal (paying back the loan), Interest (cost of borrowing), Taxes (local real estate taxes), and Insurance (homeowners insurance).'
        ],
        [
            'q' => 'Does my credit score impact my mortgage rate in ' . $name . '?',
            'a' => 'Yes. Your credit score has a major impact on the interest rate lenders will offer you. Borrowers with excellent credit scores (740+) qualify for the lowest interest rates, whereas lower scores lead to higher rates and more expensive PMI premiums.'
        ],
        [
            'q' => 'What is the difference between a 15-year and a 30-year mortgage?',
            'a' => 'A 30-year mortgage has lower monthly payments because the principal repayment is stretched over three decades. A 15-year mortgage has higher monthly payments, but typically qualifies for a lower interest rate and allows you to pay off the home twice as fast, saving massive amounts of total interest.'
        ],
        [
            'q' => 'How do property taxes and homeowners insurance affect my payment?',
            'a' => 'Property taxes and homeowners insurance are usually escrowed by lenders and divided into 12 monthly installments added to your P&I payment. High property tax rates or insurance premiums (such as in hurricane-prone zones) can significantly raise your final monthly housing budget.'
        ]
    ];

    return usc_get_deterministic_faqs($state_slug, $faq_pool, 10);
}

/**
 * Returns default SEO title for Mortgage Calculator
 */
function usc_get_default_mortgage_seo_title($state_name) {
    return $state_name . ' Mortgage Calculator | Monthly PITI Payment Estimates';
}

/**
 * Returns default SEO description for Mortgage Calculator
 */
function usc_get_default_mortgage_seo_desc($state) {
    return 'Calculate your monthly mortgage payment (PITI) in ' . $state['name'] . ' with our free advanced calculator. Simulates extra payments payoff and closing costs.';
}

/**
 * Returns the 13 Mortgage text variations
 */
function usc_get_mortgage_variations() {
    return [
        'intro' => [
            "Buying a home is exciting, right up until you realize the monthly payment is more than just principal and interest. In {state_name}, property taxes, homeowners insurance, PMI, and HOA dues can quietly add hundreds of dollars to your bill. This calculator shows your true all-in monthly cost so there are no nasty surprises after you get the keys.",
            "House hunting in {state_name} is a lot more fun when you know exactly what you can afford. The sticker price is only half the story; your real monthly payment bundles taxes and insurance on top of the loan. Run your numbers here first and you will shop with confidence instead of crossing your fingers at the closing table.",
            "A mortgage is probably the biggest loan you will ever take on, so it pays to understand it before you sign. In {state_name}, your monthly PITI payment combines principal, interest, property tax, and insurance, plus PMI if your down payment is under 20%. This tool breaks all of it down in plain numbers.",
            "Before you fall in love with a listing in {state_name}, find out what it actually costs each month. Interest rates, your down payment, local tax rates, and insurance all push the number around. This calculator models the full payment and even shows how extra payments can shave years off your loan."
        ],
        'whatis' => [
            "A <strong>{state_name} Mortgage Calculator</strong> is a premium financial tool designed to calculate your complete monthly housing cost. It estimates your principal and interest (P&I) based on interest rates and loan term, while incorporating localized property taxes, home insurance premiums, and Private Mortgage Insurance (PMI).",
            "Think of the <strong>{state_name} Mortgage Calculator</strong> as your personal home-buying assistant. By entering your purchase price, down payment, and interest rate, the tool models your monthly payment obligations and details how much cash you will need to bring to the closing table.",
            "The <strong>{state_name} Mortgage Calculator</strong> is a comprehensive modeling tool for home buyers. It computes your amortization schedule, projects long-term interest costs, and highlights the financial benefit of making extra payments to shorten the lifetime of your home loan in {state_name}."
        ],
        'why' => [
            "Using this calculator gives you a major advantage when planning your home purchase. You can simulate different down payment scenarios to see how they impact your PMI, analyze closing costs specific to {state_name}, and test whether a 15-year or 30-year loan term saves you more in the long run.",
            "Running your numbers through a mortgage calculator helps prevent budget surprises. It tells you exactly how much of your monthly income will go toward housing (your DTI ratio), helping you stay within standard bank lending guidelines and negotiate purchase offers with absolute confidence.",
            "Whether you are a first-time home buyer or looking to refinance your current home in {state_name}, this tool provides critical insights. It lets you simulate prepayments to see how much faster you can become debt-free, helping you manage your home equity and overall wealth effectively."
        ],
        'how' => [
            "The calculator processes your loan step-by-step: first, it subtracts your down payment from the home price to find the loan amount; second, it computes the monthly principal and interest; third, it adds monthly tax and insurance allocations; and finally, it applies PMI and HOA fees if applicable.",
            "To estimate your monthly housing budget, the engine runs a series of financial equations. It calculates your baseline amortization based on your loan term and interest rate, factors in monthly escrows for property taxes and hazard insurance, and checks if your down payment is under 20% to apply monthly PMI fees.",
            "Behind the scenes, the tool models a complete loan amortization. It uses the standard mortgage payment formula to determine your P&I, estimates PMI premiums using your credit score tier, calculates tax and insurance installments, and aggregates everything into a final monthly PITI total."
        ],
        'inputs' => [
            "To get the most accurate estimate, you should input your target home price, down payment cash amount, interest rate, select a loan term, and verify the average property tax and homeowners insurance settings for your specific location.",
            "The calculator utilizes a few key inputs: the home purchase price, your down payment percentage or dollar amount, your loan interest rate, the term of the mortgage, and any additional monthly expenses like HOA fees or extra principal payments.",
            "Getting a precise mortgage breakdown is easy when you provide your purchase price, down payment, current market interest rate, credit score tier, and any extra monthly payments you plan to contribute toward the principal balance."
        ],
        'formula' => [
            "The basic monthly Principal & Interest (P&I) payment is calculated using the standard formula: M = P * ( r * (1 + r)^n ) / ( (1 + r)^n - 1 ), where P is the loan amount, r is the monthly interest rate, and n is the total number of monthly payments.",
            "Your mortgage payment formula is computed as: Monthly P&I = Principal * [ r(1+r)^n ] / [ (1+r)^n - 1 ], where r represents the monthly interest rate (annual rate / 12) and n represents the term in months (years * 12). Taxes, insurance, and PMI are added to this baseline amount.",
            "To calculate your monthly repayment, the tool uses the mathematical formula: P&I = L * [ c(1+c)^n ] / [ (1+c)^n - 1 ], where L is the loan balance, c is the monthly interest percentage, and n is the payment frequency. This forms the foundation of your monthly PITI budget."
        ],
        'howtouse' => [
            "Using this tool is straightforward: enter the property purchase price, select your down payment, choose your interest rate and loan term, customize tax and insurance rates if desired, and click calculate to view your interactive charts and amortization schedule.",
            "To estimate your payments, simply enter your target home price, adjust your down payment percentage, input the interest rate, and run the calculation. You can expand the advanced sections to test extra payoff payments and check your DTI affordability.",
            "Follow these simple steps: fill in the home value and down payment, input your loan terms, and hit the calculate button. The tool will instantly generate your monthly payment breakdown, amortization schedule line chart, and closing cost estimate."
        ],
        'example' => [
            "Let's look at an illustrative calculation. Suppose a buyer in {state_name} purchases a home for $350,000, puts 20% down ($70,000), and secures a 30-year fixed mortgage at an interest rate of 6.5%.",
            "To see how this works in practice, imagine buying a home in {state_name} valued at $350,000. With a 20% down payment ($70,000), your loan amount is $280,000. At a 6.5% interest rate, your monthly P&I payment would be approximately $1,770.",
            "Consider a practical example: a property purchase of $350,000 in {state_name} with a down payment of 10% ($35,000). The resulting loan balance is $315,000, which at a 6.5% interest rate requires a monthly P&I payment of $1,991, plus monthly taxes, insurance, and PMI."
        ],
        'factors' => [
            "Your final housing costs are influenced by multiple factors: your down payment size determines if you must pay PMI, your credit score affects your interest rate and PMI rate, and local property taxes and insurance premiums add to your monthly escrow.",
            "Several key elements control your mortgage payments. A larger down payment reduces your loan balance and eliminates PMI once it reaches 20%. Additionally, your credit score determines the interest rate tier, and HOA fees add to your non-escrowed costs.",
            "Your monthly mortgage budget varies based on: interest rate fluctuations, down payment percentages (under 20% requires PMI), local property tax assessment rates, and annual homeowners hazard insurance premiums."
        ],
        'benefits' => [
            "Our mortgage tool offers several key advantages: it provides high-precision PITI estimates, allows you to simulate extra payments to see your interest savings, calculates DTI affordability ratios, and protects your privacy by processing calculations locally.",
            "Why use our calculator? It details your full housing expenses including property taxes and closing costs, helps you model the impact of paying extra principal, and does not store or share your personal financial information.",
            "This calculator is designed for maximum depth and transparency. You can itemize your purchase closing costs, test how quickly you can pay off your home with extra payments, and evaluate your DTI ratio without sharing personal financial records."
        ],
        'mistakes' => [
            "A common mistake is failing to account for property taxes and homeowners insurance when budget planning. Relying purely on the P&I payment can cause you to underestimate your monthly housing costs by hundreds of dollars.",
            "Avoid common pitfalls like ignoring closing costs or failing to budget for monthly PMI. Many buyers forget that closing costs require an additional 2% to 4% in cash upfront, which cannot always be rolled into the loan amount.",
            "To get accurate results, ensure you include homeowners association (HOA) fees if the property is in a managed community, and remember that conventional PMI applies until your loan-to-value ratio drops to 80%."
        ],
        'usecases' => [
            "This tool is perfect if you are house hunting in {state_name} and need to compare affordability, checking how much interest you can save by refinancing, or planning to pay off your mortgage early using extra payments.",
            "Real-world uses include: comparing different purchase price ranges to see what monthly payments fit your budget, estimating the cash you will need for closing costs, and analyzing your debt-to-income ratio before applying for lender pre-approval.",
            "Use this calculator when analyzing job relocations to {state_name} to check regional home affordability, testing if a 15-year term is manageable, or estimating the payoff timeline with yearly tax refund prepayments."
        ],
        'conclusion' => [
            "A home is a long commitment, so the more clearly you understand the monthly math, the better you sleep at night. Use this {state_name} mortgage calculator to test different prices, down payments, and terms until the payment feels right, then go make your offer with confidence.",
            "The smartest buyers run the numbers before they fall for a house, not after. With your full {state_name} PITI payment in front of you, you can set a realistic budget, compare 15- versus 30-year terms, and see how a little extra principal each month saves you thousands in interest.",
            "Homeownership should build your wealth, not stretch you thin. This {state_name} mortgage calculator helps you find a payment you can actually live with, plan for closing costs, and map out an early-payoff strategy. Come back any time rates or your down payment change.",
            "Knowing your real monthly payment is the difference between a confident offer and a financial headache. Lean on this {state_name} calculator to compare scenarios, understand your PITI, and plan your path to owning your home outright. Re-run it whenever the market shifts."
        ]
    ];
}

/**
 * Returns the resolved article content for Mortgage Calculator based on state
 */
function usc_get_default_mortgage_article_content($state) {
    $name = $state['name'];
    $state_slug = strtolower(str_replace(' ', '-', $name));

    $replacements = [
        'state_name' => $name
    ];

    $pool = usc_get_mortgage_variations();

    $html = '<!-- usc-v5-article -->
<div class="usc-article-content">
  <p>' . usc_get_section_content($state_slug, 'intro', $pool, $replacements) . '</p>
  
  <h2>1. What is the ' . esc_html($name) . ' Mortgage Calculator?</h2>
  <p>' . usc_get_section_content($state_slug, 'whatis', $pool, $replacements) . '</p>

  <h2>2. Why Calculate Your Monthly Payments Ahead of Time?</h2>
  <p>' . usc_get_section_content($state_slug, 'why', $pool, $replacements) . '</p>

  <h2>3. How Does the Calculator Process Your Payments?</h2>
  <p>' . usc_get_section_content($state_slug, 'how', $pool, $replacements) . '</p>

  <h2>4. Key Inputs Needed for Mortgage Estimates</h2>
  <p>' . usc_get_section_content($state_slug, 'inputs', $pool, $replacements) . '</p>

  <h2>5. Understanding the Mortgage Payment Formula</h2>
  <p>' . usc_get_section_content($state_slug, 'formula', $pool, $replacements) . '</p>

  <h2>6. How to Use the Calculator Step-by-Step</h2>
  <p>' . usc_get_section_content($state_slug, 'howtouse', $pool, $replacements) . '</p>

  <h2>7. Practical Mortgage Calculation Example in ' . esc_html($name) . '</h2>
  <p>' . usc_get_section_content($state_slug, 'example', $pool, $replacements) . '</p>

  <h2>8. Primary Factors Influencing Your Payments</h2>
  <p>' . usc_get_section_content($state_slug, 'factors', $pool, $replacements) . '</p>

  <h2>9. Benefits of Using Our Premium Mortgage Calculator</h2>
  <p>' . usc_get_section_content($state_slug, 'benefits', $pool, $replacements) . '</p>

  <h2>10. Common Home Buyer Budgeting Mistakes to Avoid</h2>
  <p>' . usc_get_section_content($state_slug, 'mistakes', $pool, $replacements) . '</p>

  <h2>11. Real-World Use Cases for the Mortgage Calculator</h2>
  <p>' . usc_get_section_content($state_slug, 'usecases', $pool, $replacements) . '</p>

  <h2>12. Conclusion: Taking Control of Your Home Financing</h2>
  <p>' . usc_get_section_content($state_slug, 'conclusion', $pool, $replacements) . '</p>

  <h2>13. ' . esc_html($name) . ' Mortgage Costs to Watch in 2026</h2>
  <p>A few moving parts shape your ' . esc_html($name) . ' payment in 2026. Thirty-year fixed rates have hovered in the mid-6% range, so even a half-point change noticeably moves your monthly number, run a couple of scenarios above to feel the difference. Conventional loans still require PMI until you reach 20% equity, FHA loans allow as little as 3.5% down, and most lenders look for a total debt-to-income ratio under about 43%. Property tax and insurance vary widely by county, so adjust those fields to match your target neighborhood for the most realistic estimate. As a quick affordability gut-check, many lenders and financial planners suggest keeping your total monthly housing payment under roughly 28% of your gross monthly income, and your total debt under about 36%.</p>
  <h3>What actually makes up your monthly payment (PITI)</h3>
  <ul>
    <li><strong>Principal:</strong> the portion that actually pays down what you borrowed. It starts small and grows every year as the loan matures.</li>
    <li><strong>Interest:</strong> the lender\'s charge for the loan. It is front-loaded, which is why early payments barely dent the balance and why extra principal early on saves so much.</li>
    <li><strong>Taxes:</strong> local property taxes, usually collected monthly into an escrow account and paid to your county on your behalf.</li>
    <li><strong>Insurance:</strong> homeowners hazard insurance, plus private mortgage insurance (PMI) if your down payment is under 20%.</li>
    <li><strong>HOA dues:</strong> not technically part of the loan, but a very real monthly cost in many ' . esc_html($name) . ' communities and condos.</li>
  </ul>
  <h3>Smart ways to lower your ' . esc_html($name) . ' payment</h3>
  <ul>
    <li>Put down 20% to skip PMI entirely, or request its removal once you cross 20% equity.</li>
    <li>Compare a 15-year term: the rate is usually lower and the lifetime interest savings are dramatic, even though the monthly payment is higher.</li>
    <li>Shop at least three lenders. Even a 0.25% lower rate adds up to thousands of dollars over the life of a 30-year loan.</li>
    <li>Re-shop your homeowners insurance each year, and appeal an over-assessed property tax bill if comparable homes are valued lower.</li>
    <li>Send a little extra toward principal each month, or make one extra payment a year, to shave years off the loan and cut total interest.</li>
  </ul>

  <h2>14. Related ' . esc_html($name) . ' Calculators</h2>
  <p>Planning a move or a new budget? These free ' . esc_html($name) . ' tools pair well with your mortgage math:</p>
  ' . usc_get_related_links_html($state_slug, $name, 'mortgage') . '

  <h2>15. Helpful Official Resources</h2>
  <p>For trustworthy, up-to-date guidance, go straight to the source:</p>
  <ul>
    <li><a href="https://www.consumerfinance.gov/owning-a-home/" target="_blank" rel="nofollow noopener">CFPB Owning a Home</a> &mdash; unbiased federal guidance on loans and closing costs.</li>
    <li><a href="https://www.freddiemac.com/pmms" target="_blank" rel="nofollow noopener">Freddie Mac mortgage rate survey</a> &mdash; the current national average rates.</li>
    <li><a href="https://www.hud.gov/buying" target="_blank" rel="nofollow noopener">HUD home-buying resources</a> &mdash; first-time buyer programs and counseling.</li>
  </ul>
</div>';

    return $html;
}
