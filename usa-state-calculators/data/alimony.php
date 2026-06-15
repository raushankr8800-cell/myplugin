<?php
/**
 * Alimony/Spousal Support Calculator Template and Content Generator
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns default HTML, CSS, and JS for the Alimony Calculator based on state
 */
function usc_get_alimony_templates($state_slug) {
    $states = usc_get_states_data();
    $state_name = isset($states[$state_slug]) ? $states[$state_slug]['name'] : 'USA';

    $data = [];

    // 1. ALIMONY CALCULATOR HTML
    $data['html'] = '<!-- INFO BOX -->
<div class="info-box">
  📌 Estimate spousal support (alimony) payments, duration, and post-divorce income splits in ' . esc_html($state_name) . ' based on local state guidelines and standard formulas.
</div>

<!-- PAYING SPOUSE DETAILS -->
<div class="sec">
  <div class="sec-head"><label class="lbl">💵 PAYING SPOUSE (HIGHER EARNER) INCOME</label></div>
  
  <div class="unit-row">
    <span style="font-size:12px;font-weight:700;color:var(--re);letter-spacing:.5px">INCOME TYPE</span>
    <div class="unit-sw">
      <button class="unit-btn active" id="btnPayorSalary" onclick="setPayorPayType(\'salary\', this)">SALARY</button>
      <button class="unit-btn" id="btnPayorHourly" onclick="setPayorPayType(\'hourly\', this)">HOURLY</button>
    </div>
  </div>

  <div class="field" id="payor-salary-field">
    <label class="lbl">GROSS ANNUAL SALARY</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="payor-salary" placeholder="e.g. 100000" value="100000">
      <span class="inp-suffix">$</span>
    </div>
  </div>

  <div id="payor-hourly-fields" style="display:none;">
    <div class="grid2">
      <div class="field">
        <label class="lbl">HOURLY RATE</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="payor-hourly-rate" placeholder="e.g. 48" value="48">
          <span class="inp-suffix">$/hr</span>
        </div>
      </div>
      <div class="field">
        <label class="lbl">HOURS PER WEEK</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="payor-hourly-hours" placeholder="e.g. 40" value="40">
          <span class="inp-suffix">hrs</span>
        </div>
      </div>
    </div>
  </div>

  <div class="adv-trigger" onclick="togglePayorAdvanced()" style="font-size:11.5px; font-weight:700; color:var(--or); cursor:pointer; margin-top:12px; display:inline-flex; align-items:center; gap:4px;">
    <span id="payor-adv-arrow">▶</span> ADVANCED INCOME & TAX WORKSHEET
  </div>
  <div id="payor-adv-fields" style="display:none; margin-top:12px; border-left: 2px solid var(--or); padding-left: 12px;">
    <div class="grid2">
      <div class="field">
        <label class="lbl">ANNUAL BONUS / COMMISSIONS</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="payor-bonus" placeholder="e.g. 10000" value="0">
          <span class="inp-suffix">$</span>
        </div>
      </div>
      <div class="field">
        <label class="lbl">WEEKLY OVERTIME (HOURS)</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="payor-ot-hours" placeholder="e.g. 5" value="0">
          <span class="inp-suffix">hrs</span>
        </div>
      </div>
    </div>
    <div class="field">
      <label class="lbl">OVERTIME HOURLY RATE ($/hr)</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="payor-ot-rate" placeholder="e.g. 72" value="0">
        <span class="inp-suffix">$/hr</span>
      </div>
      <div class="hint">Leave 0 to automatically use 1.5x regular rate</div>
    </div>
    <div class="field">
      <label class="lbl">TAX FILING STATUS</label>
      <select class="inp" id="payor-filing-status" style="width:100%; height:38px; border:1.5px solid var(--bdr); border-radius:8px; padding:0 10px; font-size:13px; font-weight:700; color:#111;">
        <option value="single" selected>Single</option>
        <option value="married">Married (Filing Jointly)</option>
        <option value="hoh">Head of Household</option>
      </select>
    </div>
    <div class="grid2">
      <div class="field">
        <label class="lbl">HEALTH INSURANCE ($/mo)</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="payor-deduct-health" placeholder="e.g. 300" value="0">
          <span class="inp-suffix">$/mo</span>
        </div>
      </div>
      <div class="field">
        <label class="lbl">RETIREMENT/401K ($/mo)</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="payor-deduct-retirement" placeholder="e.g. 500" value="0">
          <span class="inp-suffix">$/mo</span>
        </div>
      </div>
    </div>
    <div class="field">
      <label class="lbl">OTHER MANDATORY DEDUCTIONS ($/mo)</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="payor-deduct-other" placeholder="e.g. 100" value="0">
        <span class="inp-suffix">$/mo</span>
      </div>
      <div class="hint">e.g. Union dues, involuntary retirement plans</div>
    </div>
  </div>
</div>

<!-- RECEIVING SPOUSE DETAILS -->
<div class="sec">
  <div class="sec-head"><label class="lbl">💵 RECEIVING SPOUSE (LOWER EARNER) INCOME</label></div>
  
  <div class="unit-row">
    <span style="font-size:12px;font-weight:700;color:var(--re);letter-spacing:.5px">INCOME TYPE</span>
    <div class="unit-sw">
      <button class="unit-btn active" id="btnRecipientSalary" onclick="setRecipientPayType(\'salary\', this)">SALARY</button>
      <button class="unit-btn" id="btnRecipientHourly" onclick="setRecipientPayType(\'hourly\', this)">HOURLY</button>
    </div>
  </div>

  <div class="field" id="recipient-salary-field">
    <label class="lbl">GROSS ANNUAL SALARY</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="recipient-salary" placeholder="e.g. 40000" value="40000">
      <span class="inp-suffix">$</span>
    </div>
  </div>

  <div id="recipient-hourly-fields" style="display:none;">
    <div class="grid2">
      <div class="field">
        <label class="lbl">HOURLY RATE</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="recipient-hourly-rate" placeholder="e.g. 19" value="19">
          <span class="inp-suffix">$/hr</span>
        </div>
      </div>
      <div class="field">
        <label class="lbl">HOURS PER WEEK</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="recipient-hourly-hours" placeholder="e.g. 40" value="40">
          <span class="inp-suffix">hrs</span>
        </div>
      </div>
    </div>
  </div>

  <div class="adv-trigger" onclick="toggleRecipientAdvanced()" style="font-size:11.5px; font-weight:700; color:var(--or); cursor:pointer; margin-top:12px; display:inline-flex; align-items:center; gap:4px;">
    <span id="recipient-adv-arrow">▶</span> ADVANCED INCOME & TAX WORKSHEET
  </div>
  <div id="recipient-adv-fields" style="display:none; margin-top:12px; border-left: 2px solid var(--or); padding-left: 12px;">
    <div class="grid2">
      <div class="field">
        <label class="lbl">ANNUAL BONUS / COMMISSIONS</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="recipient-bonus" placeholder="e.g. 5000" value="0">
          <span class="inp-suffix">$</span>
        </div>
      </div>
      <div class="field">
        <label class="lbl">WEEKLY OVERTIME (HOURS)</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="recipient-ot-hours" placeholder="e.g. 2" value="0">
          <span class="inp-suffix">hrs</span>
        </div>
      </div>
    </div>
    <div class="field">
      <label class="lbl">OVERTIME HOURLY RATE ($/hr)</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="recipient-ot-rate" placeholder="e.g. 30" value="0">
        <span class="inp-suffix">$/hr</span>
      </div>
      <div class="hint">Leave 0 to automatically use 1.5x regular rate</div>
    </div>
    <div class="field">
      <label class="lbl">TAX FILING STATUS</label>
      <select class="inp" id="recipient-filing-status" style="width:100%; height:38px; border:1.5px solid var(--bdr); border-radius:8px; padding:0 10px; font-size:13px; font-weight:700; color:#111;">
        <option value="single" selected>Single</option>
        <option value="married">Married (Filing Jointly)</option>
        <option value="hoh">Head of Household</option>
      </select>
    </div>
    <div class="grid2">
      <div class="field">
        <label class="lbl">HEALTH INSURANCE ($/mo)</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="recipient-deduct-health" placeholder="e.g. 150" value="0">
          <span class="inp-suffix">$/mo</span>
        </div>
      </div>
      <div class="field">
        <label class="lbl">RETIREMENT/401K ($/mo)</label>
        <div class="inp-wrap">
          <input type="number" class="inp" id="recipient-deduct-retirement" placeholder="e.g. 200" value="0">
          <span class="inp-suffix">$/mo</span>
        </div>
      </div>
    </div>
    <div class="field">
      <label class="lbl">OTHER MANDATORY DEDUCTIONS ($/mo)</label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="recipient-deduct-other" placeholder="e.g. 50" value="0">
        <span class="inp-suffix">$/mo</span>
      </div>
      <div class="hint">e.g. Union dues, involuntary retirement plans</div>
    </div>
  </div>
</div>

<!-- MARRIAGE & FAMILY DETAILS -->
<div class="sec">
  <div class="sec-head"><label class="lbl">📅 MARRIAGE & FAMILY DETAILS</label></div>

  <!-- MARRIAGE DURATION -->
  <div class="field">
    <label class="lbl">MARRIAGE DURATION (YEARS)</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="marriage-duration" value="12" min="1" max="60">
      <span class="inp-suffix">years</span>
    </div>
    <div class="hint">Total length of marriage before filing for divorce</div>
  </div>

  <!-- HAS CHILDREN TOGGLE -->
  <div class="field">
    <label class="lbl">DO YOU HAVE MINOR CHILDREN TOGETHER?</label>
    <div class="tog-group" style="margin-bottom:12px;">
      <button class="tog-btn" id="btnChildrenYes" onclick="setHasChildren(true, this)">Yes</button>
      <button class="tog-btn active" id="btnChildrenNo" onclick="setHasChildren(false, this)">No</button>
    </div>
  </div>

  <!-- CHILD SUPPORT PAID -->
  <div class="field" id="child-support-field" style="display:none;">
    <label class="lbl">MONTHLY CHILD SUPPORT PAID (IF ANY)</label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="child-support-paid" value="0" min="0">
      <span class="inp-suffix">$/mo</span>
    </div>
    <div class="hint">Monthly child support paid by the higher-earning spouse</div>
  </div>
</div>

<!-- STATUTORY FACTORS CHECKLIST -->
<div class="sec">
  <div class="adv-trigger" onclick="toggleAlimonyFactors()" style="font-size:12px; font-weight:700; color:var(--re); cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
    <span id="factors-arrow">▶</span> ⚖️ STATE STATUTORY FACTORS (JUDICIAL DISCRETION)
  </div>
  <div id="alimony-factors-section" style="display:none; margin-top:12px;">
    <p style="font-size:11px; color:var(--mt); margin-bottom:10px; line-height:1.4;">
      Family law courts evaluate several qualitative factors to decide whether to adjust spousal support. Check all that apply to see dynamic warnings in your results:
    </p>
    <div style="display:flex; flex-direction:column; gap:8px;">
      <label style="display:flex; align-items:flex-start; gap:8px; font-size:11.5px; cursor:pointer;">
        <input type="checkbox" id="factor-career-sacrifice" style="margin-top:2px;" onclick="calculateAlimony(false)">
        <span><strong>Career/Education Sacrifice:</strong> Supported spouse delayed or sacrificed career/education to support the marriage or care for children.</span>
      </label>
      <label style="display:flex; align-items:flex-start; gap:8px; font-size:11.5px; cursor:pointer;">
        <input type="checkbox" id="factor-medical-needs" style="margin-top:2px;" onclick="calculateAlimony(false)">
        <span><strong>Special Medical Needs:</strong> Either spouse has significant, chronic medical conditions or disabilities affecting work.</span>
      </label>
      <label style="display:flex; align-items:flex-start; gap:8px; font-size:11.5px; cursor:pointer;">
        <input type="checkbox" id="factor-earning-gap" style="margin-top:2px;" onclick="calculateAlimony(false)">
        <span><strong>Significant Standard of Living Gap:</strong> High difference in post-divorce standards, or recipient lacks vocational skills to reach self-sufficiency.</span>
      </label>
    </div>
  </div>
</div>

<!-- CALCULATE BTN -->
<button class="calc-btn" onclick="calculateAlimony(true)">
  ⚖️ CALCULATE SPOUSAL SUPPORT
</button>

<!-- RESULTS container -->
<div class="results" id="results"></div>
<!-- SAVED SCENARIOS WIDGET -->
<div id="alimony-scenarios-container" style="margin-top: 24px; display: none;"></div>';

    // 2. ALIMONY CALCULATOR JS
    $data['js'] = 'var payorPayType = "salary";
var recipientPayType = "salary";
var hasChildren = false;
var stateSlug = "' . esc_js($state_slug) . '";
var resultsCalculated = false;

document.addEventListener("DOMContentLoaded", function() {
    loadAlimonyUrlParams();
    renderSavedAlimonyScenarios();
    // Attach live calculations on input changes
    var inputs = document.querySelectorAll(".usc-calculator-container input, .usc-calculator-container select");
    inputs.forEach(function(input) {
        input.addEventListener("input", function() { calculateAlimony(false); });
        input.addEventListener("change", function() { calculateAlimony(false); });
    });
});

function setPayorPayType(type, btn) {
    payorPayType = type;
    document.getElementById("btnPayorSalary").classList.toggle("active", type === "salary");
    document.getElementById("btnPayorHourly").classList.toggle("active", type === "hourly");
    
    if (type === "salary") {
        document.getElementById("payor-salary-field").style.display = "block";
        document.getElementById("payor-hourly-fields").style.display = "none";
    } else {
        document.getElementById("payor-salary-field").style.display = "none";
        document.getElementById("payor-hourly-fields").style.display = "block";
    }
    calculateAlimony(false);
}

function setRecipientPayType(type, btn) {
    recipientPayType = type;
    document.getElementById("btnRecipientSalary").classList.toggle("active", type === "salary");
    document.getElementById("btnRecipientHourly").classList.toggle("active", type === "hourly");
    
    if (type === "salary") {
        document.getElementById("recipient-salary-field").style.display = "block";
        document.getElementById("recipient-hourly-fields").style.display = "none";
    } else {
        document.getElementById("recipient-salary-field").style.display = "none";
        document.getElementById("recipient-hourly-fields").style.display = "block";
    }
    calculateAlimony(false);
}

function setHasChildren(val, btn) {
    hasChildren = val;
    document.getElementById("btnChildrenYes").classList.toggle("active", val === true);
    document.getElementById("btnChildrenNo").classList.toggle("active", val === false);
    
    var csField = document.getElementById("child-support-field");
    if (csField) {
        csField.style.display = val ? "block" : "none";
    }
    calculateAlimony(false);
}

function togglePayorAdvanced() {
    var fields = document.getElementById("payor-adv-fields");
    var arrow = document.getElementById("payor-adv-arrow");
    if (fields.style.display === "none") {
        fields.style.display = "block";
        arrow.textContent = "▼";
    } else {
        fields.style.display = "none";
        arrow.textContent = "▶";
    }
}

function toggleRecipientAdvanced() {
    var fields = document.getElementById("recipient-adv-fields");
    var arrow = document.getElementById("recipient-adv-arrow");
    if (fields.style.display === "none") {
        fields.style.display = "block";
        arrow.textContent = "▼";
    } else {
        fields.style.display = "none";
        arrow.textContent = "▶";
    }
}

function toggleAlimonyFactors() {
    var sec = document.getElementById("alimony-factors-section");
    var arrow = document.getElementById("factors-arrow");
    if (sec.style.display === "none") {
        sec.style.display = "block";
        arrow.textContent = "▼";
    } else {
        sec.style.display = "none";
    }
}

function estimateMonthlyTaxes(grossAnnual, filingStatus, stateSlug) {
    if (grossAnnual <= 0) return 0;
    
    // 1. FICA TAX
    var socialSecurity = Math.min(grossAnnual, 168600) * 0.062;
    var medicareLimit = (filingStatus === "married") ? 250000 : 200000;
    var medicare = grossAnnual * 0.0145;
    if (grossAnnual > medicareLimit) {
        medicare += (grossAnnual - medicareLimit) * 0.009;
    }
    var totalFica = socialSecurity + medicare;
    
    // 2. FEDERAL INCOME TAX (2024 progressive brackets)
    var standardDeduction = 14600;
    if (filingStatus === "married") {
        standardDeduction = 29200;
    } else if (filingStatus === "hoh") {
        standardDeduction = 21900;
    }
    
    var taxableFederal = Math.max(0, grossAnnual - standardDeduction);
    var fedTax = 0;
    
    var brackets = [];
    if (filingStatus === "married") {
        brackets = [
            { limit: 23200, rate: 0.10 },
            { limit: 94300, rate: 0.12 },
            { limit: 201050, rate: 0.22 },
            { limit: 383900, rate: 0.24 },
            { limit: 487450, rate: 0.32 },
            { limit: 731200, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ];
    } else if (filingStatus === "hoh") {
        brackets = [
            { limit: 16550, rate: 0.10 },
            { limit: 63100, rate: 0.12 },
            { limit: 100500, rate: 0.22 },
            { limit: 191950, rate: 0.24 },
            { limit: 243700, rate: 0.32 },
            { limit: 609350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ];
    } else {
        brackets = [
            { limit: 11600, rate: 0.10 },
            { limit: 47150, rate: 0.12 },
            { limit: 100525, rate: 0.22 },
            { limit: 191950, rate: 0.24 },
            { limit: 243725, rate: 0.32 },
            { limit: 609350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ];
    }
    
    var prevLimit = 0;
    for (var i = 0; i < brackets.length; i++) {
        var limit = brackets[i].limit;
        var rate = brackets[i].rate;
        if (taxableFederal > limit) {
            fedTax += (limit - prevLimit) * rate;
            prevLimit = limit;
        } else {
            fedTax += (taxableFederal - prevLimit) * rate;
            break;
        }
    }
    
    // 3. STATE INCOME TAX ESTIMATION
    var stateTax = 0;
    if (stateSlug === "california") {
        var caTaxable = Math.max(0, grossAnnual - 5363);
        var caBrackets = [
            { limit: 10412, rate: 0.01 },
            { limit: 24684, rate: 0.02 },
            { limit: 38959, rate: 0.04 },
            { limit: 54081, rate: 0.06 },
            { limit: 68350, rate: 0.08 },
            { limit: 349137, rate: 0.093 },
            { limit: Infinity, rate: 0.103 }
        ];
        var caPrev = 0;
        for (var i = 0; i < caBrackets.length; i++) {
            var limit = caBrackets[i].limit;
            var rate = caBrackets[i].rate;
            if (caTaxable > limit) {
                stateTax += (limit - caPrev) * rate;
                caPrev = limit;
            } else {
                stateTax += (caTaxable - caPrev) * rate;
                break;
            }
        }
    } else if (stateSlug === "new-york") {
        var nyTaxable = Math.max(0, grossAnnual - 8000);
        var nyBrackets = [
            { limit: 8500, rate: 0.04 },
            { limit: 11700, rate: 0.045 },
            { limit: 13900, rate: 0.0525 },
            { limit: 21400, rate: 0.059 },
            { limit: 80650, rate: 0.0633 },
            { limit: 215400, rate: 0.0685 },
            { limit: Infinity, rate: 0.0965 }
        ];
        var nyPrev = 0;
        for (var i = 0; i < nyBrackets.length; i++) {
            var limit = nyBrackets[i].limit;
            var rate = nyBrackets[i].rate;
            if (nyTaxable > limit) {
                stateTax += (limit - nyPrev) * rate;
                nyPrev = limit;
            } else {
                stateTax += (nyTaxable - nyPrev) * rate;
                break;
            }
        }
    } else if (stateSlug === "pennsylvania") {
        stateTax = grossAnnual * 0.0307;
    } else if (stateSlug === "texas" || stateSlug === "florida" || stateSlug === "alaska" || stateSlug === "nevada" || stateSlug === "south-dakota" || stateSlug === "washington" || stateSlug === "wyoming" || stateSlug === "tennessee" || stateSlug === "new-hampshire") {
        stateTax = 0;
    } else {
        stateTax = Math.max(0, grossAnnual - 10000) * 0.045;
    }
    
    return (totalFica + fedTax + stateTax) / 12;
}

function calculateAlimony(forceShow) {
    if (forceShow === true) {
        resultsCalculated = true;
    }
    if (!resultsCalculated) {
        return;
    }
    
    // Resolve Payor Gross Annual (including advanced overtime & bonus)
    var payorBase = 0;
    if (payorPayType === "salary") {
        payorBase = parseFloat(document.getElementById("payor-salary").value) || 0;
    } else {
        var rate = parseFloat(document.getElementById("payor-hourly-rate").value) || 0;
        var hours = parseFloat(document.getElementById("payor-hourly-hours").value) || 40;
        payorBase = rate * hours * 52;
    }
    var payorBonus = parseFloat(document.getElementById("payor-bonus").value) || 0;
    var payorOtHours = parseFloat(document.getElementById("payor-ot-hours").value) || 0;
    var payorOtRateInput = parseFloat(document.getElementById("payor-ot-rate").value) || 0;
    var payorOtRate = payorOtRateInput || (1.5 * (payorPayType === "hourly" ? (parseFloat(document.getElementById("payor-hourly-rate").value) || 0) : (payorBase / 52 / 40)));
    var payorOtAnnual = payorOtHours * payorOtRate * 52;
    var payorGross = payorBase + payorBonus + payorOtAnnual;

    // Resolve Recipient Gross Annual (including advanced overtime & bonus)
    var recipientBase = 0;
    if (recipientPayType === "salary") {
        recipientBase = parseFloat(document.getElementById("recipient-salary").value) || 0;
    } else {
        var rate = parseFloat(document.getElementById("recipient-hourly-rate").value) || 0;
        var hours = parseFloat(document.getElementById("recipient-hourly-hours").value) || 40;
        recipientBase = rate * hours * 52;
    }
    var recipientBonus = parseFloat(document.getElementById("recipient-bonus").value) || 0;
    var recipientOtHours = parseFloat(document.getElementById("recipient-ot-hours").value) || 0;
    var recipientOtRateInput = parseFloat(document.getElementById("recipient-ot-rate").value) || 0;
    var recipientOtRate = recipientOtRateInput || (1.5 * (recipientPayType === "hourly" ? (parseFloat(document.getElementById("recipient-hourly-rate").value) || 0) : (recipientBase / 52 / 40)));
    var recipientOtAnnual = recipientOtHours * recipientOtRate * 52;
    var recipientGross = recipientBase + recipientBonus + recipientOtAnnual;

    var duration = parseFloat(document.getElementById("marriage-duration").value) || 0;
    var childSupport = parseFloat(document.getElementById("child-support-paid").value) || 0;

    var monthlyAlimony = 0;
    var alimonyDuration = 0;
    var modelUsed = "";
    var ruleText = "";
    var durationText = "";
    
    // Convert Gross Annual to Monthly Gross
    var pGrossMo = payorGross / 12;
    var rGrossMo = recipientGross / 12;
    
    // Real Tax & Deductions Worksheet calculation
    var payorFiling = document.getElementById("payor-filing-status").value;
    var payorDeductHealth = parseFloat(document.getElementById("payor-deduct-health").value) || 0;
    var payorDeductRetire = parseFloat(document.getElementById("payor-deduct-retirement").value) || 0;
    var payorDeductOther = parseFloat(document.getElementById("payor-deduct-other").value) || 0;
    var payorTaxesMo = estimateMonthlyTaxes(payorGross, payorFiling, stateSlug);
    
    var recipientFiling = document.getElementById("recipient-filing-status").value;
    var recipientDeductHealth = parseFloat(document.getElementById("recipient-deduct-health").value) || 0;
    var recipientDeductRetire = parseFloat(document.getElementById("recipient-deduct-retirement").value) || 0;
    var recipientDeductOther = parseFloat(document.getElementById("recipient-deduct-other").value) || 0;
    var recipientTaxesMo = estimateMonthlyTaxes(recipientGross, recipientFiling, stateSlug);
    
    // Compute True Net Disposable Income (subtracting taxes, pre-tax deductions, and child support for payer)
    var pNetMo = Math.max(0, pGrossMo - payorTaxesMo - payorDeductHealth - payorDeductRetire - payorDeductOther - childSupport);
    var rNetMo = Math.max(0, rGrossMo - recipientTaxesMo - recipientDeductHealth - recipientDeductRetire - recipientDeductOther);

    // 1. CALIFORNIA TEMPORARY SUPPORT FORMULA
    if (stateSlug === "california") {
        modelUsed = "California Guidelines (Santa Clara Formula)";
        monthlyAlimony = (pNetMo * 0.40) - (rNetMo * 0.50);
        ruleText = "California temporary support is calculated as 40% of the paying spouse\'s net monthly income minus 50% of the receiving spouse\'s net monthly income.";
        
        if (duration < 10) {
            alimonyDuration = duration * 0.5;
            durationText = "For marriages under 10 years, spousal support is typically ordered for half the duration of the marriage.";
        } else {
            durationText = "For marriages of 10 years or longer, California is a \'long-term marriage\' state. Spousal support has no automatic end date, and the court retains jurisdiction indefinitely.";
            alimonyDuration = "Indefinite / Long-Term";
        }
    }
    // 2. TEXAS SPOUSAL MAINTENANCE FORMULA
    else if (stateSlug === "texas") {
        modelUsed = "Texas Spousal Maintenance Statutory Caps";
        var estimatedMaintenance = Math.min(payorGross * 0.20, 60000) / 12; // 20% of gross or $5000/mo cap
        monthlyAlimony = Math.max(0, Math.min(estimatedMaintenance, (pGrossMo - rGrossMo) * 0.35));
        ruleText = "In Texas, spousal maintenance is capped at 20% of the payer\'s average monthly gross income or $5,000 per month, whichever is less.";
        
        if (duration < 10) {
            monthlyAlimony = 0;
            durationText = "Texas generally does not award spousal support for marriages under 10 years unless there are circumstances of family violence or physical disability.";
            alimonyDuration = 0;
        } else if (duration >= 10 && duration < 20) {
            alimonyDuration = 5;
            durationText = "For marriages between 10 and 20 years, support is legally capped at a maximum duration of 5 years.";
        } else if (duration >= 20 && duration < 30) {
            alimonyDuration = 7;
            durationText = "For marriages between 20 and 30 years, support is legally capped at a maximum duration of 7 years.";
        } else {
            alimonyDuration = 10;
            durationText = "For marriages of 30 years or longer, support is legally capped at a maximum duration of 10 years.";
        }
    }
    // 3. NEW YORK SPOUSAL MAINTENANCE GUIDELINES
    else if (stateSlug === "new-york") {
        modelUsed = "New York Statutory Maintenance Guidelines";
        var incomeCap = 203000;
        var cappedPayorGross = Math.min(payorGross, incomeCap);
        var pCappedMo = cappedPayorGross / 12;
        
        var formulaA = 0;
        var formulaB = (pGrossMo + rGrossMo) * 0.40 - rGrossMo;
        
        if (hasChildren) {
            formulaA = (pCappedMo * 0.20) - (rGrossMo * 0.25);
        } else {
            formulaA = (pCappedMo * 0.30) - (rGrossMo * 0.20);
        }
        
        monthlyAlimony = Math.min(formulaA, formulaB);
        ruleText = "New York guidelines use a dual-formula system (with/without child support factor) based on a payor income cap of $203,000, taking the lower output of the two calculations.";
        
        if (duration <= 15) {
            alimonyDuration = duration * 0.20;
            durationText = "Under New York guidelines, support for marriages up to 15 years typically lasts between 15% and 30% of the marriage length.";
        } else if (duration > 15 && duration <= 20) {
            alimonyDuration = duration * 0.35;
            durationText = "Under New York guidelines, support for moderate marriages (15-20 years) typically lasts between 30% and 40% of the marriage length.";
        } else {
            alimonyDuration = duration * 0.425;
            durationText = "Under New York guidelines, support for long marriages (20+ years) typically lasts between 35% and 50% of the marriage length.";
        }
    }
    // 4. ILLINOIS MAINTENANCE GUIDELINES
    else if (stateSlug === "illinois") {
        modelUsed = "Illinois Maintenance Formulas";
        monthlyAlimony = (pNetMo * 0.333) - (rNetMo * 0.25);
        
        var combinedNetMo = pNetMo + rNetMo;
        var maxRecipientTotal = combinedNetMo * 0.40;
        if ((rNetMo + monthlyAlimony) > maxRecipientTotal) {
            monthlyAlimony = Math.max(0, maxRecipientTotal - rNetMo);
        }
        
        ruleText = "Illinois maintenance is 33.3% of the payer\'s net income minus 25% of the recipient\'s net income, capped so the recipient\'s share does not exceed 40% of combined net resources.";
        
        var factor = 0.20;
        if (duration < 5) factor = 0.20;
        else if (duration < 6) factor = 0.24;
        else if (duration < 7) factor = 0.28;
        else if (duration < 8) factor = 0.32;
        else if (duration < 9) factor = 0.36;
        else if (duration < 10) factor = 0.40;
        else if (duration < 11) factor = 0.44;
        else if (duration < 12) factor = 0.48;
        else if (duration < 13) factor = 0.52;
        else if (duration < 14) factor = 0.56;
        else if (duration < 15) factor = 0.60;
        else if (duration < 16) factor = 0.64;
        else if (duration < 17) factor = 0.68;
        else if (duration < 18) factor = 0.72;
        else if (duration < 19) factor = 0.76;
        else if (duration < 20) factor = 0.80;
        else factor = 1.0;
        
        if (duration >= 20) {
            alimonyDuration = "Indefinite / Permanent";
            durationText = "For Illinois marriages of 20 years or longer, the court awards maintenance for a duration equal to the marriage length or permanently.";
        } else {
            alimonyDuration = duration * factor;
            durationText = "Illinois applies a progressive percentage factor (from 20% up to 80%) to the marriage length based on the exact number of years married.";
        }
    }
    // 5. MASSACHUSETTS FORMULA
    else if (stateSlug === "massachusetts") {
        modelUsed = "Massachusetts Alimony Reform Act Guidelines";
        monthlyAlimony = (pGrossMo - rGrossMo) * 0.325;
        ruleText = "Massachusetts guidelines recommend alimony equal to 30% to 35% (median 32.5% modeled) of the difference between the parties\' gross incomes at the time of divorce.";
        
        if (duration <= 5) {
            alimonyDuration = duration * 0.5;
            durationText = "For marriages of 5 years or less, support duration is capped at 50% of the marriage length.";
        } else if (duration <= 10) {
            alimonyDuration = duration * 0.6;
            durationText = "For marriages between 5 and 10 years, support duration is capped at 60% of the marriage length.";
        } else if (duration <= 15) {
            alimonyDuration = duration * 0.7;
            durationText = "For marriages between 10 and 15 years, support duration is capped at 70% of the marriage length.";
        } else if (duration <= 20) {
            alimonyDuration = duration * 0.8;
            durationText = "For marriages between 15 and 20 years, support duration is capped at 80% of the marriage length.";
        } else {
            alimonyDuration = "Indefinite / Permanent";
            durationText = "For Massachusetts marriages exceeding 20 years, spousal support can be awarded permanently.";
        }
    }
    // 6. PENNSYLVANIA GUIDELINES
    else if (stateSlug === "pennsylvania") {
        modelUsed = "Pennsylvania Statutory Spousal Support Guidelines";
        if (hasChildren) {
            monthlyAlimony = (pNetMo - rNetMo) * 0.30;
        } else {
            monthlyAlimony = (pNetMo - rNetMo) * 0.40;
        }
        ruleText = "Pennsylvania uses a guideline of 40% of net monthly income difference if there are no children, or 30% of the net income difference if children are present.";
        alimonyDuration = duration * 0.33; // 1 year support per 3 years marriage approx
        durationText = "In Pennsylvania, spousal support duration is at the discretion of the court, commonly estimated at roughly 1 year of support for every 3 years of marriage.";
    }
    // 7. COLORADO statutory guidelines
    else if (stateSlug === "colorado") {
        modelUsed = "Colorado Statutory Spousal Maintenance Formula";
        monthlyAlimony = (pGrossMo * 0.40) - (rGrossMo * 0.50);
        ruleText = "Colorado uses a statutory advisory guideline for combined incomes under $240,000/yr: 40% of the higher earner\'s gross monthly income minus 50% of the lower earner\'s gross monthly income.";
        
        var pct = 0.30;
        if (duration <= 3) pct = 0.30;
        else if (duration <= 5) pct = 0.35;
        else if (duration <= 10) pct = 0.40;
        else if (duration <= 15) pct = 0.45;
        else pct = 0.50;
        alimonyDuration = duration * pct;
        durationText = "Colorado guidelines use a progressive percentage table based on marriage length, ranging from 30% (for 3-year marriages) up to 50% (for marriages over 12.5 years).";
    }
    // 8. VIRGINIA statutory guidelines
    else if (stateSlug === "virginia") {
        modelUsed = "Virginia Statutory Guidelines (Temporary Support)";
        if (hasChildren) {
            monthlyAlimony = (pGrossMo * 0.26) - (rGrossMo * 0.58);
        } else {
            monthlyAlimony = (pGrossMo * 0.30) - (rGrossMo * 0.50);
        }
        ruleText = "Virginia guideline temporary support is calculated as 30% of payor gross minus 50% of recipient gross (no children), or 26% of payor gross minus 58% of recipient gross (with children).";
        alimonyDuration = duration * 0.50;
        durationText = "For marriages under 20 years, Virginia courts commonly award support for a duration equal to 50% of the marriage length.";
    }
    // 9. FLORIDA Guidelines
    else if (stateSlug === "florida") {
        modelUsed = "Florida Advisory Guidelines Model";
        monthlyAlimony = (pGrossMo * 0.30) - (rGrossMo * 0.20);
        var maxRecipientTotalGross = (pGrossMo + rGrossMo) * 0.40;
        if ((rGrossMo + monthlyAlimony) > maxRecipientTotalGross) {
            monthlyAlimony = Math.max(0, maxRecipientTotalGross - rGrossMo);
        }
        ruleText = "Under Florida’s recent alimony reform, spousal support calculations follow a need-vs-ability guideline, capped at 35% of the gross monthly income difference or a 40% total combined gross limit.";
        
        if (duration < 10) {
            alimonyDuration = duration * 0.50;
            durationText = "Short-term marriages (under 10 years) are eligible for support up to 50% of the marriage length.";
        } else if (duration >= 10 && duration < 20) {
            alimonyDuration = duration * 0.60;
            durationText = "Moderate-term marriages (10 to 20 years) are eligible for support up to 60% of the marriage length.";
        } else {
            alimonyDuration = duration * 0.75;
            durationText = "Long-term marriages (20+ years) are eligible for support up to 75% of the marriage length. Permanent alimony has been abolished in Florida.";
        }
    }
    // 10. DEFAULT AAML GUIDELINES FALLBACK (Other states)
    else {
        modelUsed = "AAML (American Academy of Matrimonial Lawyers) Guideline";
        monthlyAlimony = (pGrossMo * 0.30) - (rGrossMo * 0.20);
        var maxRecipientTotalGross = (pGrossMo + rGrossMo) * 0.40;
        if ((rGrossMo + monthlyAlimony) > maxRecipientTotalGross) {
            monthlyAlimony = Math.max(0, maxRecipientTotalGross - rGrossMo);
        }
        
        ruleText = "This calculation uses the national AAML Guideline: 30% of the payor\'s gross income minus 20% of the recipient\'s gross income, subject to the 40% combined income cap.";
        
        if (duration < 10) {
            alimonyDuration = duration * 0.4;
            durationText = "For short marriages (under 10 years), support duration is typically estimated at 30% to 50% of the marriage length.";
        } else if (duration >= 10 && duration < 20) {
            alimonyDuration = duration * 0.6;
            durationText = "For moderate marriages (10 to 20 years), support duration is estimated at 50% to 75% of the marriage length.";
        } else {
            alimonyDuration = "Indefinite / Long-Term";
            durationText = "For long-term marriages (over 20 years), spousal support is frequently awarded on an indefinite or permanent basis.";
        }
    }
    
    // Clean calculations
    monthlyAlimony = Math.max(0, monthlyAlimony);
    var annualAlimony = monthlyAlimony * 12;
    
    // Post-Support Net Income Split Calculation
    var payorFinalNet = pNetMo - monthlyAlimony;
    var recipientFinalNet = rNetMo + monthlyAlimony;
    
    var payorFinalPct = (pNetMo + rNetMo > 0) ? (payorFinalNet / (pNetMo + rNetMo)) * 100 : 50;
    var recipientFinalPct = (pNetMo + rNetMo > 0) ? (recipientFinalNet / (pNetMo + rNetMo)) * 100 : 50;
    
    var durationDisplay = typeof alimonyDuration === "number" ? Math.round(alimonyDuration * 10) / 10 + " Years" : alimonyDuration;
    
    // Calculate Support Options by Type
    var rehabAlimony = monthlyAlimony * 0.90;
    var rehabDuration = Math.round(Math.min(4, Math.max(2, duration * 0.3)) * 10) / 10;
    var transAlimony = monthlyAlimony * 0.85;
    var transDuration = Math.round(Math.min(2, Math.max(1, duration * 0.15)) * 10) / 10;
    
    // Calculate State Tax Savings
    var taxSavingsHtml = "";
    if (stateSlug === "california" || stateSlug === "new-york" || stateSlug === "pennsylvania") {
        var stateRate = 0;
        var stateTaxName = "";
        var stateNotes = "";
        if (stateSlug === "california") {
            stateRate = 0.06;
            stateTaxName = "California State Tax";
            stateNotes = "Note: Under CA SB 711, state deductions are aligned with federal rules for agreements signed after Jan 1, 2026, but remain deductible for prior orders or modifications.";
        } else if (stateSlug === "new-york") {
            stateRate = 0.065;
            stateTaxName = "New York State Tax";
            stateNotes = "Note: NY state tax treatment generally conforms to federal rules for post-2019 agreements, but allows deductions for pre-2019 orders.";
        } else if (stateSlug === "pennsylvania") {
            stateRate = 0.0307;
            stateTaxName = "Pennsylvania State Tax";
            stateNotes = "Note: PA conforms to federal rules for post-2018 agreements, but allows deductions for historical orders.";
        }
        
        var monthlySavings = monthlyAlimony * stateRate;
        var annualSavings = annualAlimony * stateRate;
        
        if (monthlyAlimony > 0) {
            taxSavingsHtml = `
                <div class="det-card" style="border-left: 4px solid #16a34a; background: #f0fdf4;">
                    <div class="det-title" style="color: #15803d; display:flex; align-items:center; gap:6px;">
                        💵 ESTIMATED STATE TAX SAVINGS (HISTORIC / QUALIFYING ORDERS)
                    </div>
                    <p style="font-size:12px; line-height:1.5; color:#166534; margin:0 0 10px 0;">
                        For agreements that qualify for state-level tax deductions (such as older or grand-fathered decrees in ${stateSlug === "california" ? "California" : stateSlug === "new-york" ? "New York" : "Pennsylvania"}), the payer may deduct alimony on their state return:
                    </p>
                    <div class="grid2" style="grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 8px;">
                        <div style="background:#fff; padding:8px; border-radius:6px; border:1px solid #bbf7d0; text-align:center;">
                            <div style="font-size:10px; color:#15803d; font-weight:700;">MONTHLY SAVINGS</div>
                            <div style="font-size:16px; font-weight:800; color:#16a34a;">$${Math.round(monthlySavings).toLocaleString()}</div>
                        </div>
                        <div style="background:#fff; padding:8px; border-radius:6px; border:1px solid #bbf7d0; text-align:center;">
                            <div style="font-size:10px; color:#15803d; font-weight:700;">ANNUAL SAVINGS</div>
                            <div style="font-size:16px; font-weight:800; color:#16a34a;">$${Math.round(annualSavings).toLocaleString()}</div>
                        </div>
                    </div>
                    <p style="font-size:10.5px; line-height:1.4; color:#15803d; margin:0; font-style:italic;">
                        * Calculated using a marginal rate of ${(stateRate * 100).toFixed(2)}% under ${stateTaxName}. ${stateNotes}
                    </p>
                </div>
            `;
        }
    }
    
    // Calculate Statutory Factors Advice
    var factorAdviceHtml = "";
    var adviceList = [];
    if (document.getElementById("factor-career-sacrifice") && document.getElementById("factor-career-sacrifice").checked) {
        adviceList.push("⚖️ <strong>Career Sacrifice Warning:</strong> A history of career/educational sacrifice often leads judges to award <strong>Rehabilitative Alimony</strong> (which pays for school or training) or increase support duration.");
    }
    if (document.getElementById("factor-medical-needs") && document.getElementById("factor-medical-needs").checked) {
        adviceList.push("⚖️ <strong>Medical/Disability Warning:</strong> Special medical needs or chronic health conditions are a major statutory factor. Courts frequently bypass standard duration formulas to order long-term or permanent alimony in these situations.");
    }
    if (document.getElementById("factor-earning-gap") && document.getElementById("factor-earning-gap").checked) {
        adviceList.push("⚖️ <strong>Standard of Living Discrepancy:</strong> A large post-divorce income discrepancy can motivate courts to adjust spousal support upward to prevent a sudden drop in the supported spouse\'s standard of living.");
    }
    if (adviceList.length > 0) {
        factorAdviceHtml = `
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--bdr);">
                <div style="font-size: 11.5px; font-weight: 700; color: var(--re); margin-bottom: 6px;">⚠️ STATUTORY FACTOR WARNINGS:</div>
                <ul style="margin: 0; padding-left: 16px; font-size: 11.5px; color: var(--mt); line-height: 1.5; display: flex; flex-direction: column; gap: 6px;">
                    ${adviceList.map(function(item) { return "<li>" + item + "</li>"; }).join("")}
                </ul>
            </div>
        `;
    }
    
    // Calculate column heights for before vs after chart
    var maxHeight = Math.max(pNetMo, payorFinalNet, rNetMo, recipientFinalNet, 100);
    var pBeforeHeight = (pNetMo / maxHeight) * 100;
    var pAfterHeight = (payorFinalNet / maxHeight) * 100;
    var rBeforeHeight = (rNetMo / maxHeight) * 100;
    var rAfterHeight = (recipientFinalNet / maxHeight) * 100;
    
    // Render Results HTML
    var res = document.getElementById("results");
    res.style.display = "block";
    
    res.innerHTML = `
        <div class="hero-grid">
            <div class="hero-card">
                <div class="hero-lbl">ESTIMATED MONTHLY ALIMONY</div>
                <div class="hero-val">$${Math.round(monthlyAlimony).toLocaleString()}</div>
                <div class="hero-sub">Estimated Monthly Support</div>
            </div>
            <div class="hero-card">
                <div class="hero-lbl">ANNUAL TOTAL</div>
                <div class="hero-val">$${Math.round(annualAlimony).toLocaleString()}</div>
                <div class="hero-sub">Estimated Annual Support</div>
            </div>
        </div>
        
        <button onclick="saveAlimonyScenario()" style="width: 100%; margin-bottom: 15px; padding: 12px; background: var(--re); color: #fff; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px;">💾 SAVE THIS SCENARIO FOR COMPARISON</button>

        <div class="det-card">
            <div class="det-title">📅 ESTIMATED SUPPORT DURATION</div>
            <div style="font-size: 20px; font-weight: 800; color: #111; margin-bottom: 8px;">${durationDisplay}</div>
            <p style="font-size:12px; line-height:1.5; color:var(--mt); margin:0;">${durationText}</p>
        </div>

        <div class="det-card">
            <div class="det-title">⚙️ CALCULATION METHODOLOGY</div>
            <p style="font-size:12px; line-height:1.6; color:var(--mt); margin:0;">
                <strong>Formula:</strong> ${modelUsed}<br>
                <strong>Rule:</strong> ${ruleText}
            </p>
            ${factorAdviceHtml}
        </div>

        <div class="det-card">
            <div class="det-title">📊 DISPOSABLE INCOME: BEFORE VS AFTER SUPPORT</div>
            <p style="font-size:12px; color:var(--mt); margin:0 0 16px 0;">
                See how the spousal support award rebalances the disposable net monthly income of both parties:
            </p>
            <div style="display:flex; justify-content:space-around; align-items:flex-end; height:180px; padding:10px 0; border-bottom:1.5px solid var(--bdr); margin-bottom:10px;">
                <div style="display:flex; flex-direction:column; align-items:center; width:22%; height:100%; justify-content:flex-end;">
                    <div style="font-size:9.5px; font-weight:700; color:#dc2626; margin-bottom:4px;">$${Math.round(pNetMo).toLocaleString()}</div>
                    <div style="width:100%; height:${pBeforeHeight}%; background:#fca5a5; border-radius:4px 4px 0 0; min-height:8px;" title="Paying Spouse Net Before Support"></div>
                    <div style="font-size:9.5px; font-weight:700; color:#6b7280; margin-top:6px; text-align:center; line-height:1.2;">Payer<br>Before</div>
                </div>
                <div style="display:flex; flex-direction:column; align-items:center; width:22%; height:100%; justify-content:flex-end;">
                    <div style="font-size:9.5px; font-weight:700; color:#b91c1c; margin-bottom:4px;">$${Math.round(payorFinalNet).toLocaleString()}</div>
                    <div style="width:100%; height:${pAfterHeight}%; background:#dc2626; border-radius:4px 4px 0 0; min-height:8px;" title="Paying Spouse Net After Support"></div>
                    <div style="font-size:9.5px; font-weight:700; color:#111; margin-top:6px; text-align:center; line-height:1.2;">Payer<br>After</div>
                </div>
                
                <div style="width:1px; height:100%; background:var(--bdr);"></div>
                
                <div style="display:flex; flex-direction:column; align-items:center; width:22%; height:100%; justify-content:flex-end;">
                    <div style="font-size:9.5px; font-weight:700; color:#3b82f6; margin-bottom:4px;">$${Math.round(rNetMo).toLocaleString()}</div>
                    <div style="width:100%; height:${rBeforeHeight}%; background:#93c5fd; border-radius:4px 4px 0 0; min-height:8px;" title="Receiving Spouse Net Before Support"></div>
                    <div style="font-size:9.5px; font-weight:700; color:#6b7280; margin-top:6px; text-align:center; line-height:1.2;">Recipient<br>Before</div>
                </div>
                <div style="display:flex; flex-direction:column; align-items:center; width:22%; height:100%; justify-content:flex-end;">
                    <div style="font-size:9.5px; font-weight:700; color:#1d4ed8; margin-bottom:4px;">$${Math.round(recipientFinalNet).toLocaleString()}</div>
                    <div style="width:100%; height:${rAfterHeight}%; background:#1d4ed8; border-radius:4px 4px 0 0; min-height:8px;" title="Receiving Spouse Net After Support"></div>
                    <div style="font-size:9.5px; font-weight:700; color:#111; margin-top:6px; text-align:center; line-height:1.2;">Recipient<br>After</div>
                </div>
            </div>
            <div style="font-size:10.5px; text-align:center; color:var(--mt); font-style:italic;">
                * Bars represent relative monthly disposable incomes before vs. after support adjustments.
            </div>
        </div>

        <div class="det-card">
            <div class="det-title">📊 POST-SUPPORT MONTHLY INCOME SPLIT & TAX WORKSHEET</div>
            <div style="height: 24px; border-radius: 8px; overflow: hidden; display: flex; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); margin-bottom:12px;">
                <div style="width: ${payorFinalPct}%; background: #dc2626; height: 100%;" title="Payor Disposable Net: ${payorFinalPct.toFixed(0)}%"></div>
                <div style="width: ${recipientFinalPct}%; background: #3b82f6; height: 100%;" title="Recipient Disposable Net: ${recipientFinalPct.toFixed(0)}%"></div>
            </div>
            <div class="grid2" style="grid-template-columns: repeat(2, 1fr); gap: 10px; font-size:11.5px; font-weight:700; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; background:#dc2626; display:inline-block; border-radius:50%;"></span> Payor Share: ${payorFinalPct.toFixed(0)}%</div>
                <div style="display:flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; background:#3b82f6; display:inline-block; border-radius:50%;"></span> Recipient Share: ${recipientFinalPct.toFixed(0)}%</div>
            </div>
            
            <table class="comp-table" style="font-size:11.5px; width:100%; border-collapse:collapse; text-align:left; margin-bottom:12px;">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--bdr);">
                        <th style="padding:6px 4px;">Disposable Net Calculation</th>
                        <th style="padding:6px 4px; text-align:right;">Paying Spouse</th>
                        <th style="padding:6px 4px; text-align:right;">Receiving Spouse</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Gross Monthly Income</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(pGrossMo).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right;">$${Math.round(rGrossMo).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Est. Income Taxes & FICA</td>
                        <td style="padding:6px 4px; text-align:right; color:#dc2626;">-$${Math.round(payorTaxesMo).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#dc2626;">-$${Math.round(recipientTaxesMo).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Health & Retirement Deductions</td>
                        <td style="padding:6px 4px; text-align:right; color:#dc2626;">-$${Math.round(payorDeductHealth + payorDeductRetire + payorDeductOther).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#dc2626;">-$${Math.round(recipientDeductHealth + recipientDeductRetire + recipientDeductOther).toLocaleString()}</td>
                    </tr>
                    ${childSupport > 0 ? `
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Child Support Paid</td>
                        <td style="padding:6px 4px; text-align:right; color:#dc2626;">-$${Math.round(childSupport).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right;">$0</td>
                    </tr>` : ""}
                    <tr style="border-bottom:1.5px solid var(--bdr); background:var(--bg2);">
                        <td style="padding:6px 4px;"><strong>Net Disposable (Pre-Support)</strong></td>
                        <td style="padding:6px 4px; text-align:right; font-weight:700;">$${Math.round(pNetMo).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; font-weight:700;">$${Math.round(rNetMo).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--bdr);">
                        <td style="padding:6px 4px;">Spousal Support Adjustment</td>
                        <td style="padding:6px 4px; text-align:right; color:#dc2626;">-$${Math.round(monthlyAlimony).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; color:#16a34a;">+$${Math.round(monthlyAlimony).toLocaleString()}</td>
                    </tr>
                    <tr style="border-bottom:2px solid var(--re); background:var(--soft); font-size:12px;">
                        <td style="padding:6px 4px;"><strong>Final Post-Support Net</strong></td>
                        <td style="padding:6px 4px; text-align:right; font-weight:900; color:#111;">$${Math.round(payorFinalNet).toLocaleString()}</td>
                        <td style="padding:6px 4px; text-align:right; font-weight:900; color:#111;">$${Math.round(recipientFinalNet).toLocaleString()}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="det-card">
            <div class="det-title">⚖️ SPOUSAL SUPPORT OPTIONS BY TYPE</div>
            <p style="font-size:12px; line-height:1.5; color:var(--mt); margin:0 0 12px 0;">
                Family courts and divorce agreements typically use different support structures based on the purpose of the award:
            </p>
            <div style="overflow-x: auto;">
                <table class="comp-table" style="font-size:11.5px; width:100%; border-collapse:collapse; text-align:left; min-width: 450px;">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--bdr);">
                            <th style="padding:6px 4px;">Support Type</th>
                            <th style="padding:6px 4px; text-align:right;">Monthly Est.</th>
                            <th style="padding:6px 4px; text-align:right;">Typical Duration</th>
                            <th style="padding:6px 4px;">Primary Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:6px 4px;"><strong>Temporary / Pendente Lite</strong></td>
                            <td style="padding:6px 4px; text-align:right; font-weight:700; color:var(--re);">$${Math.round(monthlyAlimony).toLocaleString()}</td>
                            <td style="padding:6px 4px; text-align:right;">During divorce case</td>
                            <td style="padding:6px 4px; color:var(--mt);">Maintain the status quo during proceedings.</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:6px 4px;"><strong>Rehabilitative Support</strong></td>
                            <td style="padding:6px 4px; text-align:right; font-weight:700; color:var(--or);">$${Math.round(rehabAlimony).toLocaleString()}</td>
                            <td style="padding:6px 4px; text-align:right;">${rehabDuration} Years</td>
                            <td style="padding:6px 4px; color:var(--mt);">Fund vocational training, college, or job search.</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--bdr);">
                            <td style="padding:6px 4px;"><strong>Transitional / Short-Term</strong></td>
                            <td style="padding:6px 4px; text-align:right; font-weight:700; color:#4f46e5;">$${Math.round(transAlimony).toLocaleString()}</td>
                            <td style="padding:6px 4px; text-align:right;">${transDuration} Years</td>
                            <td style="padding:6px 4px; color:var(--mt);">Assist with moving, deposits, or immediate post-divorce costs.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        ${taxSavingsHtml}

        <!-- COMPARE STATES DYNAMIC CARDS -->
        <div class="det-card" style="margin-top: 15px; text-align: center;">
            <div class="det-title" style="margin-bottom: 10px;">🌎 COMPARE ALIMONY ACROSS STATES</div>
            <p style="font-size: 12px; color: var(--mt); margin-bottom: 12px;">Compare spousal support rates in neighboring or popular states:</p>
            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                <a href="../california-alimony-calculator/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">CA vs ' . esc_html(strtoupper($state_name)) . '</a>
                <a href="../texas-alimony-calculator/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">TX vs ' . esc_html(strtoupper($state_name)) . '</a>
                <a href="../new-york-alimony-calculator/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">NY vs ' . esc_html(strtoupper($state_name)) . '</a>
                <a href="../florida-alimony-calculator/" class="usc-compare-btn" style="text-decoration:none; padding: 6px 12px; font-size:11.5px; border: 1.5px solid var(--bdr); border-radius: 8px; color:#111; font-weight:700;">FL vs ' . esc_html(strtoupper($state_name)) . '</a>
            </div>
        </div>

        <button class="reset-btn" onclick="resetAlimonyCalc()">🔄 CALCULATE AGAIN</button>
        <button class="print-btn" onclick="window.print()">🖨️ PRINT DETAILS REPORT</button>
    `;
    
    res.scrollIntoView({behavior: "smooth", block: "start"});
    updateAlimonyUrlParams();
}

function resetAlimonyCalc() {
    resultsCalculated = false;
    document.getElementById("results").style.display = "none";
    if (payorPayType === "salary") {
        document.getElementById("payor-salary").value = "100000";
    } else {
        document.getElementById("payor-hourly-rate").value = "48";
    }
    if (recipientPayType === "salary") {
        document.getElementById("recipient-salary").value = "40000";
    } else {
        document.getElementById("recipient-hourly-rate").value = "19";
    }
    
    // Reset advanced fields
    document.getElementById("payor-bonus").value = "0";
    document.getElementById("payor-ot-hours").value = "0";
    document.getElementById("payor-ot-rate").value = "0";
    document.getElementById("payor-filing-status").value = "single";
    document.getElementById("payor-deduct-health").value = "0";
    document.getElementById("payor-deduct-retirement").value = "0";
    document.getElementById("payor-deduct-other").value = "0";

    document.getElementById("recipient-bonus").value = "0";
    document.getElementById("recipient-ot-hours").value = "0";
    document.getElementById("recipient-ot-rate").value = "0";
    document.getElementById("recipient-filing-status").value = "single";
    document.getElementById("recipient-deduct-health").value = "0";
    document.getElementById("recipient-deduct-retirement").value = "0";
    document.getElementById("recipient-deduct-other").value = "0";
    
    // Reset checkboxes
    if (document.getElementById("factor-career-sacrifice")) document.getElementById("factor-career-sacrifice").checked = false;
    if (document.getElementById("factor-medical-needs")) document.getElementById("factor-medical-needs").checked = false;
    if (document.getElementById("factor-earning-gap")) document.getElementById("factor-earning-gap").checked = false;
    
    // Close advanced segments
    document.getElementById("payor-adv-fields").style.display = "none";
    document.getElementById("payor-adv-arrow").textContent = "▶";
    document.getElementById("recipient-adv-fields").style.display = "none";
    document.getElementById("recipient-adv-arrow").textContent = "▶";
    document.getElementById("alimony-factors-section").style.display = "none";
    document.getElementById("factors-arrow").textContent = "▶";
    
    document.getElementById("marriage-duration").value = "12";
    document.getElementById("child-support-paid").value = "0";
    setHasChildren(false, document.getElementById("btnChildrenNo"));
    updateAlimonyUrlParams();
    window.scrollTo({top:0, behavior:"smooth"});
}

function updateAlimonyUrlParams() {
    var params = new URLSearchParams();
    params.set("ptyp", payorPayType);
    params.set("rtyp", recipientPayType);
    
    if (payorPayType === "salary") {
        params.set("psal", document.getElementById("payor-salary").value);
    } else {
        params.set("phr", document.getElementById("payor-hourly-rate").value);
        params.set("phrs", document.getElementById("payor-hourly-hours").value);
    }
    
    var pBonus = document.getElementById("payor-bonus").value;
    var pOtH = document.getElementById("payor-ot-hours").value;
    var pOtR = document.getElementById("payor-ot-rate").value;
    var pFil = document.getElementById("payor-filing-status").value;
    var pDh = document.getElementById("payor-deduct-health").value;
    var pDr = document.getElementById("payor-deduct-retirement").value;
    var pDo = document.getElementById("payor-deduct-other").value;
    
    if (parseFloat(pBonus) > 0) params.set("pbon", pBonus);
    if (parseFloat(pOtH) > 0) params.set("poth", pOtH);
    if (parseFloat(pOtR) > 0) params.set("potr", pOtR);
    if (pFil !== "single") params.set("pfil", pFil);
    if (parseFloat(pDh) > 0) params.set("pdh", pDh);
    if (parseFloat(pDr) > 0) params.set("pdr", pDr);
    if (parseFloat(pDo) > 0) params.set("pdo", pDo);
    
    if (recipientPayType === "salary") {
        params.set("rsal", document.getElementById("recipient-salary").value);
    } else {
        params.set("rhr", document.getElementById("recipient-hourly-rate").value);
        params.set("rhrs", document.getElementById("recipient-hourly-hours").value);
    }
    
    var rBonus = document.getElementById("recipient-bonus").value;
    var rOtH = document.getElementById("recipient-ot-hours").value;
    var rOtR = document.getElementById("recipient-ot-rate").value;
    var rFil = document.getElementById("recipient-filing-status").value;
    var rDh = document.getElementById("recipient-deduct-health").value;
    var rDr = document.getElementById("recipient-deduct-retirement").value;
    var rDo = document.getElementById("recipient-deduct-other").value;
    
    if (parseFloat(rBonus) > 0) params.set("rbon", rBonus);
    if (parseFloat(rOtH) > 0) params.set("roth", rOtH);
    if (parseFloat(rOtR) > 0) params.set("rotr", rOtR);
    if (rFil !== "single") params.set("rfil", rFil);
    if (parseFloat(rDh) > 0) params.set("rdh", rDh);
    if (parseFloat(rDr) > 0) params.set("rdr", rDr);
    if (parseFloat(rDo) > 0) params.set("rdo", rDo);
    
    params.set("dur", document.getElementById("marriage-duration").value);
    params.set("kid", hasChildren ? "1" : "0");
    if (hasChildren) {
        params.set("cs", document.getElementById("child-support-paid").value);
    }
    
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

function loadAlimonyUrlParams() {
    var params = new URLSearchParams(window.location.search);
    
    if (params.has("ptyp")) {
        var ptyp = params.get("ptyp");
        setPayorPayType(ptyp, document.getElementById(ptyp === "salary" ? "btnPayorSalary" : "btnPayorHourly"));
    }
    if (params.has("rtyp")) {
        var rtyp = params.get("rtyp");
        setRecipientPayType(rtyp, document.getElementById(rtyp === "salary" ? "btnRecipientSalary" : "btnRecipientHourly"));
    }
    
    if (params.has("psal")) document.getElementById("payor-salary").value = params.get("psal");
    if (params.has("phr")) document.getElementById("payor-hourly-rate").value = params.get("phr");
    if (params.has("phrs")) document.getElementById("payor-hourly-hours").value = params.get("phrs");
    
    if (params.has("pbon")) document.getElementById("payor-bonus").value = params.get("pbon");
    if (params.has("poth")) document.getElementById("payor-ot-hours").value = params.get("poth");
    if (params.has("potr")) document.getElementById("payor-ot-rate").value = params.get("potr");
    if (params.has("pfil")) document.getElementById("payor-filing-status").value = params.get("pfil");
    if (params.has("pdh")) document.getElementById("payor-deduct-health").value = params.get("pdh");
    if (params.has("pdr")) document.getElementById("payor-deduct-retirement").value = params.get("pdr");
    if (params.has("pdo")) document.getElementById("payor-deduct-other").value = params.get("pdo");
    
    if (params.has("rsal")) document.getElementById("recipient-salary").value = params.get("rsal");
    if (params.has("rhr")) document.getElementById("recipient-hourly-rate").value = params.get("rhr");
    if (params.has("rhrs")) document.getElementById("recipient-hourly-hours").value = params.get("rhrs");
    
    if (params.has("rbon")) document.getElementById("recipient-bonus").value = params.get("rbon");
    if (params.has("roth")) document.getElementById("recipient-ot-hours").value = params.get("roth");
    if (params.has("rotr")) document.getElementById("recipient-ot-rate").value = params.get("rotr");
    if (params.has("rfil")) document.getElementById("recipient-filing-status").value = params.get("rfil");
    if (params.has("rdh")) document.getElementById("recipient-deduct-health").value = params.get("rdh");
    if (params.has("rdr")) document.getElementById("recipient-deduct-retirement").value = params.get("rdr");
    if (params.has("rdo")) document.getElementById("recipient-deduct-other").value = params.get("rdo");
    
    if (params.has("dur")) document.getElementById("marriage-duration").value = params.get("dur");
    if (params.has("kid")) {
        var kid = params.get("kid") === "1";
        setHasChildren(kid, document.getElementById(kid ? "btnChildrenYes" : "btnChildrenNo"));
    }
    if (params.has("cs")) document.getElementById("child-support-paid").value = params.get("cs");
    
    if (params.has("pbon") || params.has("poth") || params.has("potr") || params.has("pfil") || params.has("pdh") || params.has("pdr") || params.has("pdo")) {
        openPayorAdvancedPanel();
    }
    if (params.has("rbon") || params.has("roth") || params.has("rotr") || params.has("rfil") || params.has("rdh") || params.has("rdr") || params.has("rdo")) {
        openRecipientAdvancedPanel();
    }
    
    if (params.get("calc") === "1") {
        calculateAlimony(true);
    }
}

function openPayorAdvancedPanel() {
    var fields = document.getElementById("payor-adv-fields");
    var arrow = document.getElementById("payor-adv-arrow");
    if (fields && arrow) {
        fields.style.display = "block";
        arrow.textContent = "▼";
    }
}

function openRecipientAdvancedPanel() {
    var fields = document.getElementById("recipient-adv-fields");
    var arrow = document.getElementById("recipient-adv-arrow");
    if (fields && arrow) {
        fields.style.display = "block";
        arrow.textContent = "▼";
    }
}

function saveAlimonyScenario() {
    var scenarios = JSON.parse(localStorage.getItem("usc_alimony_scenarios") || "[]");
    
    var payorBase = 0;
    if (payorPayType === "salary") {
        payorBase = parseFloat(document.getElementById("payor-salary").value) || 0;
    } else {
        var rate = parseFloat(document.getElementById("payor-hourly-rate").value) || 0;
        var hours = parseFloat(document.getElementById("payor-hourly-hours").value) || 40;
        payorBase = rate * hours * 52;
    }
    var payorBonus = parseFloat(document.getElementById("payor-bonus").value) || 0;
    var payorOtHours = parseFloat(document.getElementById("payor-ot-hours").value) || 0;
    var payorOtRateInput = parseFloat(document.getElementById("payor-ot-rate").value) || 0;
    var payorOtRate = payorOtRateInput || (1.5 * (payorPayType === "hourly" ? (parseFloat(document.getElementById("payor-hourly-rate").value) || 0) : (payorBase / 52 / 40)));
    var payorOtAnnual = payorOtHours * payorOtRate * 52;
    var payorGross = payorBase + payorBonus + payorOtAnnual;

    var recipientBase = 0;
    if (recipientPayType === "salary") {
        recipientBase = parseFloat(document.getElementById("recipient-salary").value) || 0;
    } else {
        var rate = parseFloat(document.getElementById("recipient-hourly-rate").value) || 0;
        var hours = parseFloat(document.getElementById("recipient-hourly-hours").value) || 40;
        recipientBase = rate * hours * 52;
    }
    var recipientBonus = parseFloat(document.getElementById("recipient-bonus").value) || 0;
    var recipientOtHours = parseFloat(document.getElementById("recipient-ot-hours").value) || 0;
    var recipientOtRateInput = parseFloat(document.getElementById("recipient-ot-rate").value) || 0;
    var recipientOtRate = recipientOtRateInput || (1.5 * (recipientPayType === "hourly" ? (parseFloat(document.getElementById("recipient-hourly-rate").value) || 0) : (recipientBase / 52 / 40)));
    var recipientOtAnnual = recipientOtHours * recipientOtRate * 52;
    var recipientGross = recipientBase + recipientBonus + recipientOtAnnual;

    var duration = parseFloat(document.getElementById("marriage-duration").value) || 0;
    var childSupport = parseFloat(document.getElementById("child-support-paid").value) || 0;
    
    var monthlyAlimonyText = document.querySelector(".hero-val") ? document.querySelector(".hero-val").textContent : "$0";
    var durationValText = document.querySelector(".det-card div") ? document.querySelector(".det-card div").textContent : "0 Years";
    
    var monthlyAlimonyVal = parseFloat(monthlyAlimonyText.replace(/[^0-9.-]+/g,"")) || 0;
    
    var label = "$" + Math.round(payorGross/1000) + "k vs $" + Math.round(recipientGross/1000) + "k";
    
    var newScenario = {
        id: Date.now(),
        payorGross: payorGross,
        recipientGross: recipientGross,
        duration: duration,
        hasChildren: hasChildren,
        childSupport: childSupport,
        monthlyAlimony: monthlyAlimonyVal,
        durationDisplay: durationValText,
        label: label
    };
    
    scenarios.push(newScenario);
    if (scenarios.length > 3) {
        scenarios.shift();
    }
    
    localStorage.setItem("usc_alimony_scenarios", JSON.stringify(scenarios));
    renderSavedAlimonyScenarios();
}

function deleteAlimonyScenario(id) {
    var scenarios = JSON.parse(localStorage.getItem("usc_alimony_scenarios") || "[]");
    scenarios = scenarios.filter(function(s) { return s.id !== id; });
    localStorage.setItem("usc_alimony_scenarios", JSON.stringify(scenarios));
    renderSavedAlimonyScenarios();
}

function renderSavedAlimonyScenarios() {
    var container = document.getElementById("alimony-scenarios-container");
    if (!container) return;
    
    var scenarios = JSON.parse(localStorage.getItem("usc_alimony_scenarios") || "[]");
    if (scenarios.length === 0) {
        container.innerHTML = "";
        container.style.display = "none";
        return;
    }
    
    var tableHeaders = scenarios.map(function(s) {
        return \'<th style="padding:10px 6px; text-align:right; min-width: 100px;"><div style="font-weight:700; font-size:12px; color:#111;">\' + s.label + \'</div><button onclick="deleteAlimonyScenario(\' + s.id + \')" style="border:none; background:none; color:#ef4444; font-size:10px; cursor:pointer; padding:2px 0; font-weight:700;">[Remove]</button></th>\';
    }).join("");
    
    var payorRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">$\' + Math.round(s.payorGross).toLocaleString() + \'</td>\';
    }).join("");
    
    var recipientRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">$\' + Math.round(s.recipientGross).toLocaleString() + \'</td>\';
    }).join("");
    
    var durationRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">\' + s.duration + \' years</td>\';
    }).join("");
    
    var childrenRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">\' + (s.hasChildren ? "Yes" : "No") + \'</td>\';
    }).join("");
    
    var csRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right;">$\' + Math.round(s.childSupport).toLocaleString() + \'</td>\';
    }).join("");
    
    var monthlyRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right; font-weight:700; color:var(--re);">$\' + Math.round(s.monthlyAlimony).toLocaleString() + \'</td>\';
    }).join("");
    
    var durationDispRow = scenarios.map(function(s) {
        return \'<td style="padding:8px 6px; text-align:right; font-weight:700; color:#111;">\' + s.durationDisplay + \'</td>\';
    }).join("");

    container.innerHTML = \'<div class="det-card" style="border: 2px solid var(--re); border-top: 5px solid var(--re); background: var(--bg2); margin-top:20px;"><div class="det-title" style="color:var(--re); font-size:14px; display:flex; align-items:center; gap:6px;">💾 SAVED SCENARIOS COMPARISON</div><p style="font-size:11.5px; color:var(--mt); margin-top:4px; margin-bottom:12px;">Compare up to 3 saved configurations side-by-side. New saves replace the oldest.</p><div style="overflow-x:auto;"><table class="comp-table" style="width:100%; font-size:11px; border-collapse:collapse; min-width: 400px;"><thead><tr style="border-bottom:1.5px solid var(--bdr);"><th style="padding:10px 6px; text-align:left;">Parameters</th>\' + tableHeaders + \'</tr></thead><tbody><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Payer Annual Income</td>\' + payorRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Recipient Annual Income</td>\' + recipientRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Marriage Duration</td>\' + durationRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Minor Children</td>\' + childrenRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Child Support Paid</td>\' + csRow + \'</tr><tr style="border-bottom:1px solid var(--bdr); background: var(--soft);"><td style="padding:8px 6px; font-weight:700; text-align:left; color:var(--re);">Monthly Alimony</td>\' + monthlyRow + \'</tr><tr style="border-bottom:1px solid var(--bdr);"><td style="padding:8px 6px; font-weight:700; text-align:left;">Alimony Duration</td>\' + durationDispRow + \'</tr></tbody></table></div></div>\';
    container.style.display = "block";
}';

    // 3. DEFAULT ALIMONY STYLESHEET
    $data['css'] = '/* Alimony theme styles */
:root {
  --or: #f97316;
  --re: #dc2626;
  --grad: linear-gradient(135deg,#dc2626 0%,#f97316 100%);
  --soft: linear-gradient(135deg,#fef2f2 0%,#fff7ed 100%);
  --bdr: rgba(15,52,96,0.10);
  --mt: #6b7280;
  --bg: #ffffff;
  --bg2: #f9fafb;
}';

    return $data;
}

/**
 * Returns 10 detailed FAQs for Alimony Calculator based on state
 */
function usc_get_default_alimony_faqs($state) {
    $name = $state['name'];
    $state_slug = strtolower(str_replace(' ', '-', $name));

    $faq_pool = [
        [
            'q' => 'What is alimony and spousal support in ' . $name . '?',
            'a' => 'Alimony (also called spousal support or spousal maintenance) is court-ordered financial assistance paid from a higher-earning spouse to a lower-earning spouse following a legal separation or divorce in ' . $name . '. Its purpose is to mitigate unfair economic effects of divorce and help the recipient spouse remain self-supporting.'
        ],
        [
            'q' => 'How is spousal support calculated in ' . $name . '?',
            'a' => 'Calculation guidelines vary. In temporary support cases, states like California or New York apply standard guidelines. For final post-divorce maintenance, judges evaluate factor-based rules including spouse income levels, standard of living during marriage, child support obligations, and spousal earning potential.'
        ],
        [
            'q' => 'How long does spousal support last in ' . $name . '?',
            'a' => 'Support duration is primarily tied to the length of the marriage. Typically, support lasts 30% to 50% of the marriage duration for short/moderate marriages. Long-term marriages (usually 10 or 20+ years depending on local state guidelines) may qualify for permanent or open-ended support.'
        ],
        [
            'q' => 'Can alimony payments be modified in ' . $name . '?',
            'a' => 'Yes. Either spouse can request a spousal support modification if they demonstrate a significant change in circumstances, such as a major salary reduction, job loss, recipient cohabitation, remarriage, or retirement.'
        ],
        [
            'q' => 'Is alimony tax-deductible for the payer spouse?',
            'a' => 'No. Under federal tax law changes implemented in 2019, spousal support payments are no longer tax-deductible for the paying spouse, and they are not considered taxable income for the receiving spouse on federal tax returns.'
        ],
        [
            'q' => 'What is the difference between temporary and permanent alimony?',
            'a' => 'Temporary support (alimony pendente lite) is paid while the divorce proceedings are active to maintain the household standard of living. Permanent or post-judgment support is finalized in the divorce decree and lasts for a specified period or indefinitely.'
        ],
        [
            'q' => 'Does cheating or fault affect alimony in ' . $name . '?',
            'a' => 'In no-fault states like California, marital misconduct is generally not considered when calculating spousal support. However, in states that recognize fault-based divorces, misconduct can be factored into a judge\'s decision regarding alimony awards.'
        ],
        [
            'q' => 'What happens to alimony if the receiving spouse remarries?',
            'a' => 'In almost all jurisdictions, spousal support automatically terminates if the receiving spouse gets remarried, unless the divorce settlement agreement explicitly states otherwise.'
        ],
        [
            'q' => 'How does cohabitation affect spousal support?',
            'a' => 'If the recipient spouse cohabits (lives in a marriage-like relationship) with a new partner, the paying spouse can file a petition to reduce or terminate spousal support obligations, as cohabitation reduces the recipient\'s financial needs.'
        ],
        [
            'q' => 'How is spousal support enforced in ' . $name . '?',
            'a' => 'Alimony is legally binding. If a payer refuses to pay, the recipient can seek a court order for wage garnishment, driver\'s license suspension, passport holds, or file a motion for contempt of court, which can carry jail time.'
        ]
    ];

    return usc_get_deterministic_faqs($state_slug, $faq_pool, 10);
}

/**
 * Returns dynamic SEO Title for alimony calculators
 */
function usc_get_default_alimony_seo_title($state_name) {
    return $state_name . ' Alimony Calculator: Estimate Spousal Support';
}

/**
 * Returns dynamic SEO Description for alimony calculators
 */
function usc_get_default_alimony_seo_desc($state) {
    return 'Calculate spousal support and duration in ' . $state['name'] . ' using our free spousal maintenance calculator. Factors in spousal incomes, marriage duration, children, and state guidelines.';
}

/**
 * Returns spousal support variations for alimony article
 */
function usc_get_alimony_variations() {
    return [
        'intro' => [
            "Navigating spousal support (commonly known as alimony) is a pivotal part of divorce financial planning in {state_name}. Spousal maintenance is designed to bridge the income gap between partners and help the lower earner become self-sufficient. Understanding local state rules and typical formulas can help you prepare for negotiations or mediation.",
            "Spousal support calculations ensure that both spouses transition fairly into separate lives after divorce. In {state_name}, alimony guidelines consider various financial variables to establish spousal support amounts and durations. Our alimony tool provides a clear estimation based on standard guidelines.",
            "Calculating spousal maintenance helps take the financial uncertainty out of divorce. Between income differentials, marriage length, and tax implications, estimating support in {state_name} can feel overwhelming. A dedicated alimony calculator delivers immediate clarity for your budgeting needs."
        ],
        'whatis' => [
            "A <strong>{state_name} Alimony Calculator</strong> is a specialized financial tool designed to estimate monthly spousal support payments and payment durations. It takes both spouses\' gross incomes and the length of their marriage, applying local statutory guidelines or common legal formulas.",
            "The <strong>{state_name} Alimony Calculator</strong> acts as an educational guide to help spouses calculate spousal maintenance. By entering annual gross incomes, marriage duration, and child support adjustments, users receive a detailed breakdown of the post-divorce income split.",
            "This <strong>{state_name} Spousal Support Calculator</strong> applies state-specific formulas to estimate spousal maintenance obligations. It calculates monthly support payments and the estimated number of years support will be paid, mirroring the formulas used by family law professionals."
        ],
        'why' => [
            "Estimating spousal support in advance provides crucial financial predictability. It helps you draft realistic household budgets, prepare for divorce mediation sessions with objective figures, evaluate settlement proposals, and plan for your post-divorce future.",
            "Running your numbers through a spousal maintenance tool helps you understand your financial position. You can see how support payments will shift disposable incomes, test how marriage length limits duration, and make informed choices during negotiations.",
            "Whether you are the potential payor or recipient in {state_name}, estimating spousal support provides objective guidelines. It removes guesswork, helping both spouses align on fair expectations before starting expensive courtroom proceedings."
        ]
    ];
}

/**
 * Generates dynamic SEO article content for the Alimony Calculator
 */
function usc_get_default_alimony_article_content($state) {
    $name = $state['name'];
    $state_slug = strtolower(str_replace(' ', '-', $name));

    $replacements = [
        'state_name' => $name
    ];

    $pool = usc_get_alimony_variations();

    $html = '<h2>1. Introduction</h2>
<p>' . usc_get_section_content($state_slug, 'intro', $pool, $replacements) . '</p>

<h2>2. What is a ' . $name . ' Alimony Calculator?</h2>
<p>' . usc_get_section_content($state_slug, 'whatis', $pool, $replacements) . '</p>

<h2>3. Why Estimate Spousal Support?</h2>
<p>' . usc_get_section_content($state_slug, 'why', $pool, $replacements) . '</p>
<ul>
    <li><strong>Budgeting Predictability:</strong> Knowing the estimated spousal support payment allows both parties to plan monthly rent, insurance, and utilities for two separate households.</li>
    <li><strong>Mediation Baseline:</strong> Equips spouses with objective guidelines rather than arbitrary demands, facilitating a faster, more amicable settlement.</li>
    <li><strong>Saves Legal Costs:</strong> Helps couples understand statutory guidelines before spending thousands of dollars on attorney billable hours.</li>
</ul>

<h2>4. How Alimony is Calculated in ' . $name . '</h2>
<p>Unlike child support, which uses highly rigid formulas across the United States, spousal support determination relies heavily on local state laws and judicial discretion. In ' . $name . ', the court reviews several standard factors:</p>
<ol>
    <li><strong>Income Discrepancy:</strong> The primary trigger for alimony is a significant gap between the earning capacities of both spouses.</li>
    <li><strong>Marriage Duration:</strong> The length of the marriage determines how long support will be paid. Short marriages receive support for brief transitional periods, while long-term marriages may qualify for indefinite support.</li>
    <li><strong>Standard of Living:</strong> Courts aim to keep both spouses near the standard of living established during the marriage, where financially feasible.</li>
    <li><strong>Earning Capacity & Caregiving:</strong> Factors in whether one spouse sacrificed career opportunities to care for children or support the other spouse\'s education.</li>
</ol>

<h2>5. Tax Rules for Alimony Payments</h2>
<p>Under the Tax Cuts and Jobs Act of 2017 (effective for all divorces finalized after January 1, 2019), alimony tax rules changed significantly at the federal level:</p>
<ul>
    <li><strong>Payer Spouse:</strong> Alimony payments are <strong>no longer tax-deductible</strong>.</li>
    <li><strong>Recipient Spouse:</strong> Alimony received is <strong>no longer considered taxable income</strong>.</li>
</ul>
<p>This means spousal support is treated as tax-neutral on federal returns (paid using post-tax dollars), though some state income tax codes still allow state-level deductions.</p>

<h2>6. Frequently Asked Questions</h2>
<!-- usc-v5-article -->';

    return $html;
}
