<?php
/**
 * ADP/SmartAsset-Grade default HTML, CSS, and JS Calculator templates
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns default HTML, CSS, and JS based on calculator type and state
 */
function ust_get_default_templates($type, $state_slug) {
    $states = ust_get_states_data();
    $state_name = isset($states[$state_slug]) ? $states[$state_slug]['name'] : 'USA';
    
    $data = [];

    if ($type === 'income-tax') {
        $tax_db = ust_get_income_tax_data();
        $state_rules = isset($tax_db['states'][$state_slug]) ? $tax_db['states'][$state_slug] : ['type' => 'none', 'deduction' => 0, 'flat_rate' => 0];

        // 1. INCOME TAX HTML
        $data['html'] = '<!-- income-tax-v3 -->
<div class="info-box">
  📌 Estimate your Federal Income Tax, ' . esc_html($state_name) . ' State Income Tax, FICA deductions (Social Security and Medicare), and take-home pay schedules. Customize your filing status and pre-tax deductions below.
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">💵 INCOME & DEDUCTIONS</label></div>
  
  <div class="field">
    <label class="lbl">GROSS ANNUAL SALARY <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Total annual earnings before taxes, retirement contributions, or health benefits.</span></span></label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="income-gross" value="75000">
      <span class="inp-suffix">$</span>
    </div>
    <div class="hint">Annual earnings before taxes and benefits</div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">TAX YEAR <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Choose the tax year. 2026 uses the latest IRS inflation-adjusted federal brackets, standard deduction, and the higher Social Security wage base.</span></span></label>
      <div class="sel-wrap">
        <select id="income-tax-year" class="sel">
          <option value="2025" selected>Tax Year 2025 (filed in 2026)</option>
          <option value="2026">Tax Year 2026 (filed in 2027)</option>
        </select>
      </div>
      <div class="hint">Switches federal brackets, standard deduction & FICA cap</div>
    </div>
    <div class="field">
      <label class="lbl">FILING STATUS <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Determines your progressive federal and state tax brackets and standard deduction amount.</span></span></label>
      <div class="sel-wrap">
        <select id="income-filing-status" class="sel">
          <option value="single" selected>Single</option>
          <option value="married">Married Filing Jointly</option>
          <option value="head">Head of Household</option>
        </select>
      </div>
    </div>
    <div class="field">
      <label class="lbl">DISPLAY PAY FREQUENCY <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">How frequently you receive a paycheck. This divides the final take-home amounts for display.</span></span></label>
      <div class="sel-wrap">
        <select id="income-frequency" class="sel">
          <option value="1" selected>Annual Summary</option>
          <option value="12">Monthly paycheck</option>
          <option value="24">Semi-monthly paycheck</option>
          <option value="26">Bi-weekly paycheck</option>
          <option value="52">Weekly paycheck</option>
        </select>
      </div>
    </div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">PRE-TAX DEDUCTIONS ($/YR) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Contributions to Traditional 401(k), HSA, or health insurance premiums that reduce taxable income.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="income-pretax" value="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Traditional 401(k), HSA, health premiums, etc.</div>
    </div>
    <div class="field">
      <label class="lbl">POST-TAX DEDUCTIONS ($/YR) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Deductions taken after taxes, such as Roth 401(k), Roth IRA, or garnishments.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="income-posttax" value="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Roth 401(k), Roth IRA, garnishments, etc.</div>
    </div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">OTHER INCOME ($/YR) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Additional annual earnings from freelance, rental assets, interest, dividends, etc.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="income-other" value="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Freelance, interest, rental earnings, etc.</div>
    </div>
    <div class="field">
      <label class="lbl">FEDERAL TAX CREDITS ($/YR) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Direct dollar-for-dollar reductions of tax liability (e.g. Child Tax Credit).</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="income-credits" value="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Child Tax Credit, clean energy credits, etc.</div>
    </div>
  </div>
  ' . ($state_slug === 'new-york' ? '
  <div class="field" id="field-income-local-tax">
    <label class="lbl">LOCAL RESIDENCY TAX <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">NYC and Yonkers levy additional local income taxes on residents.</span></span></label>
    <div class="sel-wrap">
      <select id="income-local-tax" class="sel">
        <option value="none" selected>Outside NYC / Yonkers (0% Local Tax)</option>
        <option value="nyc">New York City Resident (3.078% - 3.876%)</option>
        <option value="yonkers">Yonkers Resident (16.75% State Tax Surcharge)</option>
      </select>
    </div>
    <div class="hint">NYC local progressive tax rates or Yonkers surcharge rate</div>
  </div>' : '') . '

  <button type="button" class="calc-btn" id="btn-calculate-income" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Take-Home Pay</button>
  </div>

<!-- RESULTS PANEL -->
<div class="sec" id="results-panel" style="display:none;">
  <div class="sec-head"><label class="lbl">📊 CALCULATION SUMMARY</label></div>

  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--bdr); padding-bottom: 20px;">
    <!-- SVG Donut Chart -->
    <div style="position: relative; width: 160px; height: 160px; flex-shrink: 0;">
      <svg width="160" height="160" viewBox="0 0 160 160" id="income-donut-svg" style="transform: rotate(-90deg); filter: drop-shadow(0px 4px 8px rgba(0,0,0,0.06));">
        <!-- SVG slices drawn dynamically -->
      </svg>
      <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; font-family: sans-serif; pointer-events: none;">
        <div style="font-size: 18px; font-weight: 800; color: var(--pri);" id="income-donut-center-pct">0%</div>
        <div style="font-size: 9px; color: var(--mt); font-weight: 600; text-transform: uppercase;">Take-Home</div>
      </div>
    </div>
    <div style="flex: 1; min-width: 200px;">
      <div style="font-size: 26px; font-weight: 800; color: var(--succ);" id="res-net-pay">$56,250</div>
      <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--mt); font-weight:700; margin-bottom: 8px;" id="lbl-net-pay">ESTIMATED ANNUAL TAKE-HOME</div>
      <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size:12px; margin-bottom: 10px;">
        <div>Marginal Tax: <strong id="res-marginal-rate">22.0%</strong></div>
        <div>Effective Tax: <strong id="res-effective-rate">12.5%</strong></div>
      </div>
      <!-- Legends -->
      <div id="income-donut-legend" style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 10px; line-height: 1.3;"></div>
    </div>
  </div>

  <!-- PROGRESS DEDUCTIONS BREAKDOWN -->
  <div style="margin-top: 20px;">
    <div class="prog-row">
      <div class="prog-label"><span>💵 TAKE-HOME PAY</span><strong id="bar-net-val">$56,250</strong></div>
      <div class="prog-bar-wrap"><div class="prog-bar bg-green" id="bar-net-width" style="width: 75%;"></div></div>
    </div>
    <div class="prog-row">
      <div class="prog-label"><span>🏛️ FEDERAL INCOME TAX</span><strong id="bar-fed-val">$7,500</strong></div>
      <div class="prog-bar-wrap"><div class="prog-bar bg-blue" id="bar-fed-width" style="width: 10%;"></div></div>
    </div>
    <div class="prog-row">
      <div class="prog-label"><span>🏢 STATE INCOME TAX</span><strong id="bar-state-val">$3,200</strong></div>
      <div class="prog-bar-wrap"><div class="prog-bar bg-purple" id="bar-state-width" style="width: 5%;"></div></div>
    </div>
    <div class="prog-row" id="row-bar-local" style="display:none;">
      <div class="prog-label"><span>🏙️ LOCAL/CITY INCOME TAX</span><strong id="bar-local-val">$0</strong></div>
      <div class="prog-bar-wrap"><div class="prog-bar" id="bar-local-width" style="width: 0%; background: #06b6d4;"></div></div>
    </div>
    <div class="prog-row">
      <div class="prog-label"><span>💼 FICA (SS & MEDICARE)</span><strong id="bar-fica-val">$5,738</strong></div>
      <div class="prog-bar-wrap"><div class="prog-bar bg-orange" id="bar-fica-width" style="width: 7.6%;"></div></div>
    </div>
    <div class="prog-row" id="row-pretax-detail" style="display:none;">
      <div class="prog-label"><span>💰 PRE-TAX CONTRIBUTIONS</span><strong id="bar-pretax-val">$0</strong></div>
      <div class="prog-bar-wrap"><div class="prog-bar bg-gray" id="bar-pretax-width" style="width: 0%;"></div></div>
    </div>
    <div class="prog-row" id="row-posttax-detail" style="display:none;">
      <div class="prog-label"><span>🛍️ POST-TAX CONTRIBUTIONS</span><strong id="bar-posttax-val">$0</strong></div>
      <div class="prog-bar-wrap"><div class="prog-bar bg-pink" id="bar-posttax-width" style="width: 0%; background: #ec4899;"></div></div>
    </div>
  </div>

  <div id="income-advisor-box" class="usc-alert-box blue" style="margin-top: 15px; display:none; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;"></div>
</div>

<!-- PAY STUB STATEMENT -->
<div class="sec" id="schedule-panel" style="display:none; margin-top: 25px;">
  <div class="sec-head"><label class="lbl">📆 PAYCHECK STATEMENT STUB</label></div>
  
  <div class="usc-stub-tabs" style="display: flex; gap: 8px; margin-bottom: 12px;">
      <button type="button" onclick="switchStubFreq(\'annual\', this)" style="flex: 1; padding: 10px 5px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--bdr); border-radius: 8px; background: var(--grad); color: #fff; cursor: pointer; border-color: transparent; font-family: inherit; transition: all 0.2s;">Annual</button>
      <button type="button" onclick="switchStubFreq(\'monthly\', this)" style="flex: 1; padding: 10px 5px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--bdr); border-radius: 8px; background: #fff; color: var(--mt); cursor: pointer; font-family: inherit; transition: all 0.2s;">Monthly</button>
      <button type="button" onclick="switchStubFreq(\'biweekly\', this)" style="flex: 1; padding: 10px 5px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--bdr); border-radius: 8px; background: #fff; color: var(--mt); cursor: pointer; font-family: inherit; transition: all 0.2s;">Bi-Weekly</button>
      <button type="button" onclick="switchStubFreq(\'weekly\', this)" style="flex: 1; padding: 10px 5px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--bdr); border-radius: 8px; background: #fff; color: var(--mt); cursor: pointer; font-family: inherit; transition: all 0.2s;">Weekly</button>
  </div>

  <!-- Pay Stub Statement Card -->
  <div class="usc-stub-card" style="border: 2px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; margin-top: 10px;">
      <div style="background: #002b60; color: #fff; padding: 14px 16px; font-weight: 800; font-size: 14px; display: flex; justify-content: space-between;">
          <span>📄 EARNINGS STATEMENT (PAY STUB)</span>
          <span id="stub-freq-label" style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 11px;">ANNUAL</span>
      </div>
      <div style="padding: 16px; font-size: 13px; color: #374151;">
          <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #f3f4f6; padding: 8px 0; font-weight: 700;">
              <span>TOTAL GROSS EARNINGS</span>
              <span id="stub-gross-val">$0</span>
          </div>
          <div style="font-weight: 800; font-size: 11px; color: var(--re); margin-top: 14px; margin-bottom: 6px; text-transform: uppercase;">Tax Withholdings</div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding: 6px 0;"><span>Federal Income Tax</span><span id="stub-fed-val">$0</span></div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding: 6px 0;"><span>Social Security (6.2%)</span><span id="stub-ss-val">$0</span></div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding: 6px 0;"><span>Medicare (1.45%)</span><span id="stub-med-val">$0</span></div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding: 6px 0;"><span>State Income Tax</span><span id="stub-state-val">$0</span></div>
          <div style="display: none; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding: 6px 0;" id="row-stub-local"><span>Local/City Income Tax</span><span id="stub-local-val">$0</span></div>
          <div style="font-weight: 800; font-size: 11px; color: var(--re); margin-top: 14px; margin-bottom: 6px; text-transform: uppercase;">Other Deductions</div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding: 6px 0;"><span>Pre-Tax Contributions</span><span id="stub-pretax-val">$0</span></div>
          <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #f3f4f6; padding: 6px 0;"><span>Post-Tax Deductions</span><span id="stub-posttax-val">$0</span></div>
          <div style="display: flex; justify-content: space-between; padding: 12px 0 4px 0; font-weight: 800; font-size: 16px; color: var(--succ);">
              <span>NET TAKE-HOME PAY</span>
              <span id="stub-net-val">$0</span>
          </div>
      </div>
  </div>
</div>';

        // 2. INCOME TAX CSS
        $data['css'] = '/* Income Tax Layout styling */';

        // 3. INCOME TAX JS
        $data['js'] = 'const stateRules = ' . json_encode($state_rules) . ';
const federalYears = ' . json_encode(ust_get_federal_tax_years()) . ';
let federalRules = federalYears["2025"];

let currentStubFreq = "annual";
let calcResultsCache = {};

function switchStubFreq(freq, btn) {
    const tabs = btn.parentNode.querySelectorAll("button");
    tabs.forEach(t => { t.style.background = "#fff"; t.style.color = "var(--mt)"; t.style.borderColor = "var(--bdr)"; });
    btn.style.background = "var(--grad)"; btn.style.color = "#fff"; btn.style.borderColor = "transparent";
    currentStubFreq = freq; updateStubUI();
}

function updateStubUI() {
    if (!calcResultsCache.gross) return;
    let divisor = 1;
    if (currentStubFreq === "monthly") divisor = 12;
    else if (currentStubFreq === "biweekly") divisor = 26;
    else if (currentStubFreq === "weekly") divisor = 52;
    document.getElementById("stub-freq-label").innerText = currentStubFreq.toUpperCase();
    document.getElementById("stub-gross-val").innerText = "$" + Math.round(calcResultsCache.gross / divisor).toLocaleString();
    document.getElementById("stub-fed-val").innerText = "$" + Math.round(calcResultsCache.fed / divisor).toLocaleString();
    document.getElementById("stub-ss-val").innerText = "$" + Math.round(calcResultsCache.ss / divisor).toLocaleString();
    document.getElementById("stub-med-val").innerText = "$" + Math.round(calcResultsCache.med / divisor).toLocaleString();
    document.getElementById("stub-state-val").innerText = "$" + Math.round(calcResultsCache.state / divisor).toLocaleString();
    const stubLocal = document.getElementById("stub-local-val");
    if (stubLocal) {
        stubLocal.innerText = "$" + Math.round((calcResultsCache.local || 0) / divisor).toLocaleString();
    }
    document.getElementById("stub-pretax-val").innerText = "$" + Math.round(calcResultsCache.pretax / divisor).toLocaleString();
    document.getElementById("stub-posttax-val").innerText = "$" + Math.round(calcResultsCache.posttax / divisor).toLocaleString();
    document.getElementById("stub-net-val").innerText = "$" + Math.round(calcResultsCache.net / divisor).toLocaleString();
}

function updateIncomeDonut(net, fed, state, fica, pretax, posttax, local = 0) {
    const total = net + fed + state + fica + pretax + posttax + local;
    const segments = [
        { label: "Take-Home Pay", value: net, color: "#10b981" },
        { label: "Federal Income Tax", value: fed, color: "#3b82f6" },
        { label: "State Income Tax", value: state, color: "#8b5cf6" },
        { label: "Local Income Tax", value: local, color: "#06b6d4" },
        { label: "FICA Tax", value: fica, color: "#f97316" },
        { label: "Pre-Tax Deductions", value: pretax, color: "#6b7280" },
        { label: "Post-Tax Deductions", value: posttax, color: "#ec4899" }
    ].filter(s => s.value > 0);

    const svg = document.getElementById("income-donut-svg");
    if (!svg) return;
    svg.innerHTML = "";

    const r = 55;
    const cx = 80;
    const cy = 80;
    const circ = 2 * Math.PI * r;
    
    if (total === 0) {
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", cx);
        circle.setAttribute("cy", cy);
        circle.setAttribute("r", r);
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", "#e5e7eb");
        circle.setAttribute("stroke-width", "20");
        svg.appendChild(circle);
        return;
    }

    let offset = 0;
    let legendHtml = "";
    
    segments.forEach(seg => {
        const pct = seg.value / total;
        const strokeLength = pct * circ;
        const strokeOffset = offset;
        
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", cx);
        circle.setAttribute("cy", cy);
        circle.setAttribute("r", r);
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", seg.color);
        circle.setAttribute("stroke-width", "20");
        circle.setAttribute("stroke-dasharray", `${strokeLength} ${circ}`);
        circle.setAttribute("stroke-dashoffset", -strokeOffset);
        circle.style.transition = "stroke-width 0.2s ease, filter 0.2s ease";
        circle.style.cursor = "pointer";

        circle.addEventListener("mouseenter", () => {
            circle.setAttribute("stroke-width", "24");
            circle.setAttribute("filter", "brightness(1.1)");
            document.getElementById("income-donut-center-pct").innerText = Math.round(pct * 100) + "%";
            document.getElementById("income-donut-center-pct").style.color = seg.color;
        });
        circle.addEventListener("mouseleave", () => {
            circle.setAttribute("stroke-width", "20");
            circle.removeAttribute("filter");
            document.getElementById("income-donut-center-pct").innerText = Math.round((net/total)*100) + "%";
            document.getElementById("income-donut-center-pct").style.color = "var(--pri)";
        });

        svg.appendChild(circle);
        offset += strokeLength;

        legendHtml += `
            <div style="display:flex; align-items:center; gap:4px; padding: 2px 6px; background: rgba(0,0,0,0.02); border-radius: 4px; border: 1px solid var(--bdr);">
                <span style="width:8px; height:8px; border-radius:50%; background:${seg.color}; display:inline-block;"></span>
                <span><strong>${seg.label}:</strong> $${Math.round(seg.value).toLocaleString()}</span>
            </div>
        `;
    });

    document.getElementById("income-donut-center-pct").innerText = Math.round((net/total)*100) + "%";
    document.getElementById("income-donut-center-pct").style.color = "var(--pri)";
    document.getElementById("income-donut-legend").innerHTML = legendHtml;
}

function calculateTax() {
    const gross = parseFloat(document.getElementById("income-gross").value) || 0;
    const filing = document.getElementById("income-filing-status").value;
    const taxYearSel = document.getElementById("income-tax-year");
    const taxYear = taxYearSel ? taxYearSel.value : "2025";
    federalRules = federalYears[taxYear] || federalYears["2025"];
    const pretax = parseFloat(document.getElementById("income-pretax").value) || 0;
    const posttax = parseFloat(document.getElementById("income-posttax").value) || 0;
    const other = parseFloat(document.getElementById("income-other").value) || 0;
    const credits = parseFloat(document.getElementById("income-credits").value) || 0;
    const frequency = parseFloat(document.getElementById("income-frequency").value) || 1;

    const totalGross = gross + other;
    const taxableFed = Math.max(0, totalGross - pretax);

    // 1. FICA - Calculated strictly on W-2 wage base salary (gross)
    const ssLimit = federalRules.ss_wage_base || 176100;
    const ssTax = Math.min(gross, ssLimit) * 0.062;
    const medThreshold = (federalRules.medicare_threshold && federalRules.medicare_threshold[filing]) ? federalRules.medicare_threshold[filing] : ((filing === "married") ? 250000 : 200000);
    let medTax = gross * 0.0145;
    if (gross > medThreshold) {
        medTax += (gross - medThreshold) * 0.009;
    }
    const totalFica = ssTax + medTax;

    // 2. Federal
    const fedDeduction = federalRules.standard_deduction[filing];
    const fedTaxable = Math.max(0, taxableFed - fedDeduction);
    let fedTax = 0;
    let prevLimit = 0;
    const fedBrackets = federalRules.brackets[filing];
    let marginalRate = 0;

    for (let i = 0; i < fedBrackets.length; i++) {
        const bracket = fedBrackets[i];
        const limit = bracket.limit;
        const rate = bracket.rate;
        if (fedTaxable > limit) {
            fedTax += (limit - prevLimit) * rate;
            prevLimit = limit;
        } else {
            fedTax += (fedTaxable - prevLimit) * rate;
            marginalRate = rate;
            break;
        }
    }
    fedTax = Math.max(0, fedTax - credits);

    // 3. State
    let stateTax = 0;
    let stateDeduction = stateRules.deduction || 0;
    if (filing === "married" && stateRules.type === "graduated") {
        stateDeduction *= 2;
    }
    const stateTaxable = Math.max(0, taxableFed - stateDeduction);
    let stateMarginalRate = 0;

    if (stateRules.type === "flat") {
        stateTax = stateTaxable * stateRules.flat_rate;
        stateMarginalRate = stateRules.flat_rate;
    } else if (stateRules.type === "graduated") {
        let prevLimit = 0;
        const brackets = stateRules.brackets;
        for (let i = 0; i < brackets.length; i++) {
            const bracket = brackets[i];
            let limit = bracket.limit;
            if (filing === "married" && limit !== null && limit !== Infinity) {
                limit *= 2;
            }
            const rate = bracket.rate;
            if (stateTaxable > limit) {
                stateTax += (limit - prevLimit) * rate;
                prevLimit = limit;
            } else {
                stateTax += (stateTaxable - prevLimit) * rate;
                stateMarginalRate = rate;
                break;
            }
        }
    }

    // 3. Local tax residency calculation (NYC / Yonkers)
    let localTax = 0;
    const localTaxSelect = document.getElementById("income-local-tax");
    if (localTaxSelect) {
        const localVal = localTaxSelect.value;
        if (localVal === "nyc") {
            const nycBrackets = (filing === "married") ? [
                { limit: 21600, rate: 0.03078 },
                { limit: 45000, rate: 0.03762 },
                { limit: 90000, rate: 0.03819 },
                { limit: Infinity, rate: 0.03876 }
            ] : [
                { limit: 12000, rate: 0.03078 },
                { limit: 25000, rate: 0.03762 },
                { limit: 50000, rate: 0.03819 },
                { limit: Infinity, rate: 0.03876 }
            ];
            const nyDeduction = (filing === "married") ? 16000 : 8000;
            const nycTaxable = Math.max(0, taxableFed - nyDeduction);
            let prevLimit = 0;
            for (let i = 0; i < nycBrackets.length; i++) {
                const b = nycBrackets[i];
                if (nycTaxable > b.limit) {
                    localTax += (b.limit - prevLimit) * b.rate;
                    prevLimit = b.limit;
                } else {
                    localTax += (nycTaxable - prevLimit) * b.rate;
                    break;
                }
            }
        } else if (localVal === "yonkers") {
            localTax = stateTax * 0.1675;
        }
    }

    // Totals
    const totalDeductions = totalFica + fedTax + stateTax + localTax + pretax + posttax;
    const net = Math.max(0, totalGross - totalDeductions);
    const effRate = totalGross > 0 ? ((fedTax + stateTax + localTax + totalFica) / totalGross) * 100 : 0;
    const netPercent = totalGross > 0 ? (net / totalGross) * 100 : 0;

    // Cache results for paycheck statement stub
    calcResultsCache = {
        gross: totalGross,
        fed: fedTax,
        ss: ssTax,
        med: medTax,
        state: stateTax,
        local: localTax,
        pretax: pretax,
        posttax: posttax,
        net: net
    };

    // Show Panels
    const panel = document.getElementById("results-panel");
    const schedPanel = document.getElementById("schedule-panel");
    panel.style.display = "block";
    schedPanel.style.display = "block";

    // Update UI Summary
    const displayedNet = net / frequency;
    document.getElementById("res-net-pay").innerText = "$" + Math.round(displayedNet).toLocaleString();
    
    const freqLabels = { "1": "ESTIMATED ANNUAL TAKE-HOME", "12": "ESTIMATED MONTHLY TAKE-HOME", "24": "ESTIMATED SEMI-MONTHLY TAKE-HOME", "26": "ESTIMATED BI-WEEKLY TAKE-HOME", "52": "ESTIMATED WEEKLY TAKE-HOME" };
    document.getElementById("lbl-net-pay").innerText = freqLabels[frequency] || "ESTIMATED ANNUAL TAKE-HOME";
    
    const netPctEl = document.getElementById("res-net-percent");
    if (netPctEl) netPctEl.innerText = Math.round(netPercent) + "%";
    document.getElementById("res-marginal-rate").innerText = ((marginalRate + stateMarginalRate) * 100).toFixed(1) + "%";
    document.getElementById("res-effective-rate").innerText = effRate.toFixed(1) + "%";

    // Progress bar values
    document.getElementById("bar-net-val").innerText = "$" + Math.round(net).toLocaleString();
    document.getElementById("bar-fed-val").innerText = "$" + Math.round(fedTax).toLocaleString();
    document.getElementById("bar-state-val").innerText = "$" + Math.round(stateTax).toLocaleString();
    document.getElementById("bar-fica-val").innerText = "$" + Math.round(totalFica).toLocaleString();
    document.getElementById("bar-pretax-val").innerText = "$" + Math.round(pretax).toLocaleString();
    document.getElementById("bar-posttax-val").innerText = "$" + Math.round(posttax).toLocaleString();

    // Local tax progress bar
    const rowBarLocal = document.getElementById("row-bar-local");
    const rowStubLocal = document.getElementById("row-stub-local");
    if (localTax > 0) {
        if (rowBarLocal) rowBarLocal.style.display = "";
        if (rowStubLocal) rowStubLocal.style.display = "flex";
        document.getElementById("bar-local-val").innerText = "$" + Math.round(localTax).toLocaleString();
        document.getElementById("bar-local-width").style.width = (totalGross > 0 ? (localTax / totalGross) * 100 : 0) + "%";
    } else {
        if (rowBarLocal) rowBarLocal.style.display = "none";
        if (rowStubLocal) rowStubLocal.style.display = "none";
    }

    // Progress bar widths
    document.getElementById("bar-net-width").style.width = (totalGross > 0 ? (net / totalGross) * 100 : 0) + "%";
    document.getElementById("bar-fed-width").style.width = (totalGross > 0 ? (fedTax / totalGross) * 100 : 0) + "%";
    document.getElementById("bar-state-width").style.width = (totalGross > 0 ? (stateTax / totalGross) * 100 : 0) + "%";
    document.getElementById("bar-fica-width").style.width = (totalGross > 0 ? (totalFica / totalGross) * 100 : 0) + "%";
    document.getElementById("bar-pretax-width").style.width = (totalGross > 0 ? (pretax / totalGross) * 100 : 0) + "%";
    document.getElementById("bar-posttax-width").style.width = (totalGross > 0 ? (posttax / totalGross) * 100 : 0) + "%";

    // Update conic gradient chart (multi-slice)
    const fedPercent = totalGross > 0 ? (fedTax / totalGross) * 100 : 0;
    const statePercent = totalGross > 0 ? (stateTax / totalGross) * 100 : 0;
    const localPercent = totalGross > 0 ? (localTax / totalGross) * 100 : 0;
    const FicaPercent = totalGross > 0 ? (totalFica / totalGross) * 100 : 0;
    const pretaxPercent = totalGross > 0 ? (pretax / totalGross) * 100 : 0;
    const posttaxPercent = totalGross > 0 ? (posttax / totalGross) * 100 : 0;

    let cum = 0;
    const netEnd = cum + netPercent; cum += netPercent;
    const fedEnd = cum + fedPercent; cum += fedPercent;
    const stateEnd = cum + statePercent; cum += statePercent;
    const localEnd = cum + localPercent; cum += localPercent;
    const FicaEnd = cum + FicaPercent; cum += FicaPercent;
    const pretaxEnd = cum + pretaxPercent; cum += pretaxPercent;

    const chart = document.getElementById("income-chart-circle");
    if (chart) {
        chart.style.background = `conic-gradient(
            var(--succ) 0% ${netEnd}%,
            #3b82f6 ${netEnd}% ${fedEnd}%,
            #a855f7 ${fedEnd}% ${stateEnd}%,
            #06b6d4 ${stateEnd}% ${localEnd}%,
            #f97316 ${localEnd}% ${FicaEnd}%,
            #94a3b8 ${FicaEnd}% ${pretaxEnd}%,
            #ec4899 ${pretaxEnd}% 100%
        )`;
    }

    const pretaxRow = document.getElementById("row-pretax-detail");
    if (pretax > 0) {
        pretaxRow.style.display = "";
    } else {
        pretaxRow.style.display = "none";
    }

    const posttaxRow = document.getElementById("row-posttax-detail");
    if (posttax > 0) {
        posttaxRow.style.display = "";
    } else {
        posttaxRow.style.display = "none";
    }

    // Update SVG Donut chart
    updateIncomeDonut(net, fedTax, stateTax, totalFica, pretax, posttax, localTax);

    // Update Pay Stub UI elements
    updateStubUI();

    // Dynamic Tax Optimization Advice
    const advisorBox = document.getElementById("income-advisor-box");
    if (advisorBox) {
        if (marginalRate > 0 && pretax < 23000) {
            const potentialIncrease = 1000;
            const savedTaxes = potentialIncrease * marginalRate;
            advisorBox.innerHTML = `💡 <strong>Tax Optimization Advisor:</strong> Contributing an additional <strong>$1,000</strong> to your pre-tax Traditional 401(k) or HSA would reduce your taxable income, saving you approximately <strong>$` + Math.round(savedTaxes) + `</strong> in federal taxes at your marginal rate of ` + ((marginalRate) * 100).toFixed(1) + `%.`;
            advisorBox.style.display = "block";
        } else {
            advisorBox.style.display = "none";
        }
    }

    // Offset-adjusted Smooth Scroll
    const yOffset = -20; 
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}

document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-income");
    if (btn) {
        btn.addEventListener("click", calculateTax);
    }
    const yearSel = document.getElementById("income-tax-year");
    if (yearSel) {
        yearSel.addEventListener("change", function() {
            const panel = document.getElementById("results-panel");
            if (panel && panel.style.display !== "none") {
                calculateTax();
            }
        });
    }
});';
    } elseif ($type === 'property-tax') {
        $pt_db = ust_get_property_tax_data();
        $state_pt = isset($pt_db[$state_slug]) ? $pt_db[$state_slug] : ['rate' => 0.01, 'assessment_ratio' => 1.00, 'counties' => []];
        $state_pt['slug'] = $state_slug;

        // Generate county options
        $counties_html = '<option value="">-- State Average Rate (' . ($state_pt['rate'] * 100) . '%) --</option>';
        if (!empty($state_pt['counties'])) {
            foreach ($state_pt['counties'] as $c) {
                $counties_html .= '<option value="' . esc_attr($c['name']) . '" data-rate="' . $c['rate'] . '">' . esc_html($c['name']) . ' (' . ($c['rate'] * 100) . '%)</option>';
            }
        }

        // 1. PROPERTY TAX HTML
        $data['html'] = '<!-- property-tax-v1 -->
<div class="info-box">
  📌 Calculate your estimated annual property tax alongside mortgage PITI expenses in ' . esc_html($state_name) . '. Select your local county, enter your down payment and mortgage details to see a full monthly breakdown.
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">🏠 PROPERTY DETAILS</label></div>

  <div class="field">
    <label class="lbl">HOME MARKET VALUE <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The estimated current price your property would sell for in the open market.</span></span></label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="property-home-value" value="350000">
      <span class="inp-suffix">$</span>
    </div>
    <div class="hint">The current estimated market value of your property</div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">DOWN PAYMENT ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The initial cash payment you make toward purchasing the home. Private Mortgage Insurance (PMI) applies if under 20%.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="prop-down-payment" value="70000">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">PMI applies if down payment is less than 20%</div>
    </div>
    <div class="field">
      <label class="lbl">MORTGAGE INTEREST RATE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The annual interest rate charged by your lender for the mortgage loan.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="prop-interest-rate" value="6.5" step="0.1">
        <span class="inp-suffix">%</span>
      </div>
      <div class="hint">Annual interest rate for your mortgage loan</div>
    </div>
  </div>

  <div class="field">
    <label class="lbl">LOAN TERM <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The duration over which you agree to repay the mortgage loan (typically 15 or 30 years).</span></span></label>
    <div class="sel-wrap">
      <select id="prop-loan-term" class="sel">
        <option value="30" selected>30-Year Fixed Mortgage</option>
        <option value="15">15-Year Fixed Mortgage</option>
      </select>
    </div>
    <div class="hint">Mortgage loan repayment duration</div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">ZIP CODE LOOKUP (SIMULATED) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Enter a 5-digit ZIP code to detect the local county and load its estimated tax rate.</span></span></label>
      <div class="inp-wrap">
        <input type="text" class="inp" id="property-zip" placeholder="e.g. 90210" maxlength="5">
        <span class="inp-suffix">📍</span>
      </div>
      <div class="hint" id="property-zip-feedback" style="font-weight:600; color:var(--mt);">Enter 5-digit ZIP code to detect county</div>
    </div>
    <div class="field">
      <label class="lbl">LOCAL COUNTY / JURISDICTION <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The local county authority that sets the property tax assessment rate for your home.</span></span></label>
      <div class="sel-wrap">
        <select id="property-county" class="sel">
          ' . $counties_html . '
        </select>
      </div>
      <div class="hint">Select your county to load local tax rates</div>
    </div>
  </div>

  <div class="field">
    <label class="lbl">EXEMPTION RELIEF OPTIONS</label>
    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
      <label class="chk-label" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size:13px;">
        <input type="checkbox" id="prop-homestead" value="1"> Homestead Exemption (Primary Residence) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">A tax relief that reduces the taxable assessed value of your primary home.</span></span>
      </label>
      <label class="chk-label" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size:13px;">
        <input type="checkbox" id="prop-senior" value="1"> Senior Citizen Relief (Age 65+) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Special property tax reductions or caps offered to homeowners aged 65 and older.</span></span>
      </label>
      <label class="chk-label" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size:13px;">
        <input type="checkbox" id="prop-veteran" value="1"> Disabled Veteran Exemption <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Exemptions granted to veterans with service-connected disabilities to reduce property tax liabilities.</span></span>
      </label>
    </div>
  </div>

  <div class="field">
    <label class="lbl">HOME VALUE APPRECIATION RATE (<span id="lbl-prop-appreciation">3</span>%)</label>
    <div class="inp-wrap" style="display: flex; align-items: center; gap: 10px; border:none; padding:0;">
      <input type="range" id="prop-appreciation" min="1" max="15" value="3" style="flex: 1; accent-color: var(--pri);">
    </div>
    <div class="hint">Expected annual appreciation rate for 5-year projections</div>
  </div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">ANNUAL HOME INSURANCE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Estimated yearly premium for homeowners insurance coverage.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="prop-insurance" value="1200">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Estimated annual hazard insurance premium</div>
    </div>
    <div class="field">
      <label class="lbl">MONTHLY HOA FEES ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Monthly fees paid to a homeowners association for community maintenance and services.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="prop-hoa" value="0">
        <span class="inp-suffix">$</span>
      </div>
      <div class="hint">Monthly homeowners association dues</div>
    </div>
  </div>

  <div class="field" style="margin-top: 12px; margin-bottom: 12px;">
    <label class="lbl">EXTRA MONTHLY PRINCIPAL PAYMENT ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">An additional monthly amount paid directly to the mortgage principal to pay off the loan faster and save on interest.</span></span></label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="prop-extra-payment" value="0">
      <span class="inp-suffix">$</span>
    </div>
    <div class="hint">Accelerate your payoff timeline and reduce total interest paid</div>
  </div>

  <button type="button" class="calc-btn" id="btn-calculate-property" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Property Tax & Expenses</button>
</div>

<!-- RESULTS PANEL -->
<div class="sec" id="results-panel" style="display:none;">
  <div class="sec-head"><label class="lbl">📊 ESTIMATED TAX LIABILITY & EXPENSES</label></div>

  <div class="grid2" style="margin-bottom: 20px;">
    <div class="det-card" style="text-align: center;">
      <div style="font-size: 26px; font-weight: 800; color: var(--pri);" id="res-annual-tax">$3,500</div>
      <div style="font-size: 11px; text-transform: uppercase; color: var(--mt); font-weight:700;">ANNUAL PROPERTY TAX</div>
    </div>
    <div class="det-card" style="text-align: center;">
      <div style="font-size: 26px; font-weight: 800; color: var(--succ);" id="res-total-monthly-cost">$392</div>
      <div style="font-size: 11px; text-transform: uppercase; color: var(--mt); font-weight:700;">MONTHLY COST (PITI)</div>
    </div>
  </div>

  <div class="usc-alert-box" id="prop-benchmark-box" style="margin-top: 15px; display: none; margin-bottom: 15px;"></div>

  <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 25px 0; gap: 20px; border-bottom: 1px solid var(--bdr); padding-bottom: 20px;">
    <!-- SVG Donut Chart -->
    <div style="position: relative; width: 180px; height: 180px; flex-shrink:0;">
      <svg width="180" height="180" viewBox="0 0 180 180" id="prop-donut-svg" style="transform: rotate(-90deg); filter: drop-shadow(0px 4px 10px rgba(0,0,0,0.06));">
        <!-- SVG slices drawn dynamically -->
      </svg>
      <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; font-family: sans-serif; pointer-events: none;">
        <div style="font-size: 10px; text-transform: uppercase; color: var(--mt); font-weight: 700; letter-spacing: 0.5px;">Monthly</div>
        <div style="font-size: 18px; font-weight: 800; color: var(--pri);" id="donut-center-cost">$0</div>
        <div style="font-size: 9px; color: var(--mt); font-weight: 600;">Total PITI</div>
      </div>
    </div>
    <!-- Legend -->
    <div id="prop-donut-legend" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; font-size: 10px; line-height: 1.3; max-width: 480px;"></div>
  </div>

  <div style="font-size:12px; line-height: 1.6;">
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>State Assessment Ratio:</span>
      <strong id="res-ratio-val">' . ($state_pt['assessment_ratio'] * 100) . '%</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Assessed Value:</span>
      <strong id="res-assessed-val">$350,000</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Exemption Relief:</span>
      <strong id="res-exemptions-val">$0</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Taxable Assessed Value:</span>
      <strong id="res-taxable-val">$350,000</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Applied Tax Rate:</span>
      <strong id="res-rate-val">' . ($state_pt['rate'] * 100) . '%</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Monthly Property Tax Portion:</span>
      <strong id="res-monthly-tax">$292</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Monthly Mortgage P&I:</span>
      <strong id="res-mortgage-val">$0</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Monthly Private Mortgage Insurance (PMI):</span>
      <strong id="res-pmi-val">$0</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Monthly Home Insurance:</span>
      <strong id="res-insurance-val">$100</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Monthly HOA Fees:</span>
      <strong id="res-hoa-val">$0</strong>
    </div>
    <div style="display:flex; justify-content:space-between; padding: 6px 0; font-size: 13px; color: var(--pri);">
      <span><strong>Total Monthly Housing Cost (PITI + HOA):</strong></span>
      <strong id="res-total-monthly-cost-bold">$392</strong>
    </div>
  </div>
  <div id="prop-advanced-benefits" class="usc-alert-box green" style="margin-top: 15px; display: none; font-size: 12.5px; line-height: 1.5; font-weight:600;"></div>
</div>

<!-- 5-YEAR PROJECTIONS -->
<div class="sec">
  <div class="sec-head"><label class="lbl">📈 5-YEAR PROPERTY TAX & PITI PROJECTIONS</label></div>
  <p style="font-size: 11px; color: var(--mt); margin-bottom: 12px;">Assumes a standard annual property value appreciation rate.</p>
  <div style="overflow-x: auto;">
    <table class="usc-custom-table" style="width: 100%; border-collapse: collapse; font-size:12px;">
      <thead>
        <tr>
          <th>Year</th>
          <th>Estimated Market Value</th>
          <th>Annual Property Tax</th>
          <th>Monthly PITI Payment</th>
        </tr>
      </thead>
      <tbody id="projection-tbody">
        <!-- Populated via JS -->
      </tbody>
    </table>
  </div>
</div>

<!-- MORTGAGE AMORTIZATION SCHEDULE -->
<div class="sec" id="amortization-panel" style="margin-top: 25px;">
  <div class="sec-head"><label class="lbl">📆 5-YEAR MORTGAGE AMORTIZATION SCHEDULE</label></div>
  <p style="font-size: 11px; color: var(--mt); margin-bottom: 12px;">Detailed breakdown of principal, interest, taxes, and remaining balances.</p>
  <div style="overflow-x: auto;">
    <table class="usc-custom-table" style="width: 100%; border-collapse: collapse; font-size:12px;">
      <thead>
        <tr>
          <th>Year</th>
          <th>Principal Paid</th>
          <th>Interest Paid</th>
          <th>Taxes Paid</th>
          <th>Remaining Balance</th>
        </tr>
      </thead>
      <tbody id="amortization-tbody">
        <!-- Populated via JS -->
      </tbody>
    </table>
  </div>
</div>';

        // 2. PROPERTY TAX CSS
        $data['css'] = '/* Property Tax Layout styling */';

        // 3. PROPERTY TAX JS
        $data['js'] = 'const statePtRules = ' . json_encode($state_pt) . ';

function updateDonut(pi, tax, pmi, ins, hoa) {
    const total = pi + tax + pmi + ins + hoa;
    const segments = [
        { label: "Principal & Interest", value: pi, color: "#4f46e5" },
        { label: "Property Tax", value: tax, color: "#f59e0b" },
        { label: "PMI", value: pmi, color: "#ef4444" },
        { label: "Insurance", value: ins, color: "#10b981" },
        { label: "HOA Fees", value: hoa, color: "#06b6d4" }
    ].filter(s => s.value > 0);

    const svg = document.getElementById("prop-donut-svg");
    if (!svg) return;
    svg.innerHTML = "";

    const r = 60;
    const cx = 90;
    const cy = 90;
    const circ = 2 * Math.PI * r;

    if (total === 0) {
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", cx);
        circle.setAttribute("cy", cy);
        circle.setAttribute("r", r);
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", "#e5e7eb");
        circle.setAttribute("stroke-width", "20");
        svg.appendChild(circle);
        return;
    }

    let offset = 0;
    let legendHtml = "";
    
    segments.forEach(seg => {
        const pct = seg.value / total;
        const strokeLength = pct * circ;
        const strokeOffset = offset;
        
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", cx);
        circle.setAttribute("cy", cy);
        circle.setAttribute("r", r);
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", seg.color);
        circle.setAttribute("stroke-width", "20");
        circle.setAttribute("stroke-dasharray", `${strokeLength} ${circ}`);
        circle.setAttribute("stroke-dashoffset", -strokeOffset);
        circle.style.transition = "stroke-width 0.2s ease, filter 0.2s ease";
        circle.style.cursor = "pointer";

        circle.addEventListener("mouseenter", () => {
            circle.setAttribute("stroke-width", "24");
            circle.setAttribute("filter", "brightness(1.1)");
            document.getElementById("donut-center-cost").innerText = "$" + Math.round(seg.value).toLocaleString();
            document.getElementById("donut-center-cost").style.color = seg.color;
        });
        circle.addEventListener("mouseleave", () => {
            circle.setAttribute("stroke-width", "20");
            circle.removeAttribute("filter");
            document.getElementById("donut-center-cost").innerText = "$" + Math.round(total).toLocaleString();
            document.getElementById("donut-center-cost").style.color = "var(--pri)";
        });

        svg.appendChild(circle);
        offset += strokeLength;

        legendHtml += `
            <div style="display:flex; align-items:center; gap:4px; padding: 2px 6px; background: rgba(0,0,0,0.02); border-radius: 4px; border: 1px solid var(--bdr);">
                <span style="width:8px; height:8px; border-radius:50%; background:${seg.color}; display:inline-block;"></span>
                <span><strong>${seg.label}:</strong> $${Math.round(seg.value).toLocaleString()}</span>
            </div>
        `;
    });

    document.getElementById("donut-center-cost").innerText = "$" + Math.round(total).toLocaleString();
    document.getElementById("donut-center-cost").style.color = "var(--pri)";
    document.getElementById("prop-donut-legend").innerHTML = legendHtml;
}

function handleZipLookup() {
    const zipInput = document.getElementById("property-zip");
    const feedback = document.getElementById("property-zip-feedback");
    const countySelect = document.getElementById("property-county");
    if (!zipInput) return;
    
    let zip = zipInput.value.replace(/\D/g, "");
    zipInput.value = zip.substring(0, 5);
    
    if (zip.length === 5) {
        if (statePtRules.counties && statePtRules.counties.length > 0) {
            const sum = zip.split("").reduce((acc, char) => acc + parseInt(char), 0);
            const idx = sum % statePtRules.counties.length;
            const matchedCounty = statePtRules.counties[idx];
            
            countySelect.value = matchedCounty.name;
            feedback.innerText = "✅ ZIP matched: " + matchedCounty.name + " (" + (matchedCounty.rate * 100).toFixed(2) + "%)";
            feedback.style.color = "var(--succ)";
            calculatePropertyTax();
        } else {
            feedback.innerText = "✅ ZIP matched. Applied average rate.";
            feedback.style.color = "var(--succ)";
            countySelect.value = "";
            calculatePropertyTax();
        }
    } else if (zip.length > 0) {
        feedback.innerText = "Entering 5-digit ZIP code...";
        feedback.style.color = "var(--mt)";
    } else {
        feedback.innerText = "Enter 5-digit ZIP code to detect county";
        feedback.style.color = "var(--mt)";
    }
}

function calculatePropertyTax() {
    const homeVal = parseFloat(document.getElementById("property-home-value").value) || 0;
    const countySelect = document.getElementById("property-county");
    const selectedOption = countySelect.options[countySelect.selectedIndex];

    // Determine applied rate
    let rate = statePtRules.rate;
    if (selectedOption && selectedOption.value !== "") {
        rate = parseFloat(selectedOption.getAttribute("data-rate")) || statePtRules.rate;
    }

    const ratio = statePtRules.assessment_ratio;
    const assessed = homeVal * ratio;

    // Calculate exemptions
    let exemptions = 0;
    if (document.getElementById("prop-homestead").checked) {
        exemptions += 25000 * ratio;
    }
    if (document.getElementById("prop-senior").checked) {
        exemptions += 10000 * ratio;
    }
    if (document.getElementById("prop-veteran").checked) {
        exemptions += 15000 * ratio;
    }

    const taxable = Math.max(0, assessed - exemptions);
    const annualTax = taxable * rate;
    const monthlyTax = annualTax / 12;

    // Mortgage calculations
    const downPayment = parseFloat(document.getElementById("prop-down-payment").value) || 0;
    const interestRate = parseFloat(document.getElementById("prop-interest-rate").value) || 0;
    const loanTerm = parseFloat(document.getElementById("prop-loan-term").value) || 30;

    const loanAmount = Math.max(0, homeVal - downPayment);
    let monthlyPI = 0;
    if (loanAmount > 0) {
        if (interestRate > 0) {
            const monthlyInterest = interestRate / 100 / 12;
            const totalPayments = loanTerm * 12;
            monthlyPI = (loanAmount * monthlyInterest * Math.pow(1 + monthlyInterest, totalPayments)) / (Math.pow(1 + monthlyInterest, totalPayments) - 1);
        } else {
            monthlyPI = loanAmount / (loanTerm * 12);
        }
    }

    // PMI calculation (0.5% annually if down payment < 20% of home value)
    let monthlyPMI = 0;
    if (downPayment < homeVal * 0.20 && loanAmount > 0) {
        monthlyPMI = (loanAmount * 0.005) / 12;
    }

    const insurance = parseFloat(document.getElementById("prop-insurance").value) || 0;
    const hoa = parseFloat(document.getElementById("prop-hoa").value) || 0;
    const monthlyInsurance = insurance / 12;
    const totalMonthlyCost = monthlyTax + monthlyPI + monthlyPMI + monthlyInsurance + hoa;

    // Update UI
    document.getElementById("res-annual-tax").innerText = "$" + Math.round(annualTax).toLocaleString();
    document.getElementById("res-monthly-tax").innerText = "$" + Math.round(monthlyTax).toLocaleString();
    document.getElementById("res-assessed-val").innerText = "$" + Math.round(assessed).toLocaleString();
    document.getElementById("res-exemptions-val").innerText = "$" + Math.round(exemptions).toLocaleString();
    document.getElementById("res-taxable-val").innerText = "$" + Math.round(taxable).toLocaleString();
    document.getElementById("res-rate-val").innerText = (rate * 100).toFixed(3) + "%";

    document.getElementById("res-mortgage-val").innerText = "$" + Math.round(monthlyPI).toLocaleString();
    document.getElementById("res-pmi-val").innerText = "$" + Math.round(monthlyPMI).toLocaleString();
    document.getElementById("res-insurance-val").innerText = "$" + Math.round(monthlyInsurance).toLocaleString();
    document.getElementById("res-hoa-val").innerText = "$" + Math.round(hoa).toLocaleString();
    document.getElementById("res-total-monthly-cost").innerText = "$" + Math.round(totalMonthlyCost).toLocaleString();
    document.getElementById("res-total-monthly-cost-bold").innerText = "$" + Math.round(totalMonthlyCost).toLocaleString();

    // Projections Table
    const appRate = parseFloat(document.getElementById("prop-appreciation").value) || 3;
    const appFactor = 1 + (appRate / 100);

    // Tax growth cap logic
    let taxAppreciationCap = 100; // default no cap
    let capName = "";
    if (statePtRules && statePtRules.slug) {
        if (statePtRules.slug === "california") {
            taxAppreciationCap = 2;
            capName = "California Prop 13 (2%)";
        } else if (statePtRules.slug === "florida" && document.getElementById("prop-homestead").checked) {
            taxAppreciationCap = 3;
            capName = "Florida Save Our Homes (3%)";
        } else if (statePtRules.slug === "oregon") {
            taxAppreciationCap = 3;
            capName = "Oregon Measure 50 (3%)";
        }
    }
    const taxAppFactor = 1 + (Math.min(appRate, taxAppreciationCap) / 100);

    let tableHtml = "";
    let currentValue = homeVal;
    let currentAssessedVal = assessed;
    for (let year = 1; year <= 5; year++) {
        currentValue *= appFactor;
        currentAssessedVal *= taxAppFactor;
        const currentTaxable = Math.max(0, currentAssessedVal - exemptions);
        const currentTax = currentTaxable * rate;
        const currentMonthlyTax = currentTax / 12;
        const currentTotalMonthly = currentMonthlyTax + monthlyPI + monthlyPMI + monthlyInsurance + hoa;

        let capNote = "";
        if (taxAppreciationCap < 100 && year === 1) {
            capNote = ` <span style="font-size:9px;color:var(--succ);display:block;font-weight:normal;">*Reflects ${capName} tax cap</span>`;
        }

        tableHtml += `<tr>
            <td><strong>Year ${year}</strong></td>
            <td>$${Math.round(currentValue).toLocaleString()}</td>
            <td>$${Math.round(currentTax).toLocaleString()}${capNote}</td>
            <td style="color:var(--pri);font-weight:700;">$${Math.round(currentTotalMonthly).toLocaleString()} <span style="font-size:10px;font-weight:normal;color:var(--mt);">/mo</span></td>
        </tr>`;
    }
    document.getElementById("projection-tbody").innerHTML = tableHtml;

    // Amortization Schedule Calculation
    let remainingBalance = loanAmount;
    const r_monthly = (interestRate / 100) / 12;
    let amortHtml = "";
    
    // Mortgage Acceleration Simulator
    const extraPrincipal = parseFloat(document.getElementById("prop-extra-payment").value) || 0;
    
    // Simulate standard amortization total interest
    let totalInterestStd = 0;
    let balStd = loanAmount;
    const stdTermMonths = loanTerm * 12;
    if (loanAmount > 0) {
        for (let m = 1; m <= stdTermMonths; m++) {
            if (balStd <= 0) break;
            let interestPayment = balStd * r_monthly;
            if (r_monthly <= 0) interestPayment = 0;
            let principalPayment = Math.min(balStd, monthlyPI - interestPayment);
            totalInterestStd += interestPayment;
            balStd -= principalPayment;
        }
    }

    // Simulate accelerated amortization
    let totalInterestAcc = 0;
    let balAcc = loanAmount;
    let monthsAcc = 0;
    if (loanAmount > 0) {
        for (let m = 1; m <= stdTermMonths; m++) {
            if (balAcc <= 0) {
                monthsAcc = m - 1;
                break;
            }
            let interestPayment = balAcc * r_monthly;
            if (r_monthly <= 0) interestPayment = 0;
            let principalPayment = Math.min(balAcc, monthlyPI - interestPayment);
            let extraApplied = Math.min(balAcc - principalPayment, extraPrincipal);
            totalInterestAcc += interestPayment;
            balAcc -= (principalPayment + extraApplied);
            monthsAcc = m;
        }
        if (balAcc > 0 && monthsAcc === stdTermMonths) {
            monthsAcc = stdTermMonths;
        }
    }

    // Populate amortization schedule table
    remainingBalance = loanAmount;
    for (let year = 1; year <= 5; year++) {
        let principalPaidYear = 0;
        let interestPaidYear = 0;
        
        // Loop over 12 months for this year
        for (let month = 1; month <= 12; month++) {
            if (remainingBalance > 0) {
                let interestPayment = remainingBalance * r_monthly;
                if (r_monthly <= 0) interestPayment = 0;
                let principalPayment = Math.min(remainingBalance, monthlyPI - interestPayment);
                let extraApplied = Math.min(remainingBalance - principalPayment, extraPrincipal);
                
                principalPaidYear += (principalPayment + extraApplied);
                interestPaidYear += interestPayment;
                remainingBalance -= (principalPayment + extraApplied);
            }
        }
        
        // Calculate projected property tax for that year (with cap-respected appreciation)
        let appreciatedAssessed = assessed * Math.pow(taxAppFactor, year - 1);
        let yearTaxable = Math.max(0, appreciatedAssessed - exemptions);
        let yearTax = yearTaxable * rate;
        
        amortHtml += `<tr>
            <td><strong>Year ${year}</strong></td>
            <td>$${Math.round(principalPaidYear).toLocaleString()}</td>
            <td>$${Math.round(interestPaidYear).toLocaleString()}</td>
            <td>$${Math.round(yearTax).toLocaleString()}</td>
            <td>$${Math.round(remainingBalance).toLocaleString()}</td>
        </tr>`;
    }
    document.getElementById("amortization-tbody").innerHTML = amortHtml;

    // Display Mortgage Acceleration summary box
    const benefitsBox = document.getElementById("prop-advanced-benefits");
    if (benefitsBox) {
        if (extraPrincipal > 0 && loanAmount > 0) {
            const interestSaved = totalInterestStd - totalInterestAcc;
            const monthsSaved = stdTermMonths - monthsAcc;
            const yearsSaved = (monthsSaved / 12).toFixed(1);
            
            if (interestSaved > 0 && monthsSaved > 0) {
                benefitsBox.innerHTML = `🔥 <strong>Mortgage Acceleration Benefit:</strong> By paying an extra <strong>$${extraPrincipal.toLocaleString()}/month</strong> towards your principal, you will pay off your mortgage <strong>${yearsSaved} years</strong> earlier and save <strong>$${Math.round(interestSaved).toLocaleString()}</strong> in total interest!`;
                benefitsBox.style.display = "block";
            } else {
                benefitsBox.style.display = "none";
            }
        } else {
            benefitsBox.style.display = "none";
        }
    }

    // Draw Donut chart
    updateDonut(monthlyPI, monthlyTax, monthlyPMI, monthlyInsurance, hoa);

    // Update benchmark box
    const benchmarkBox = document.getElementById("prop-benchmark-box");
    if (benchmarkBox) {
        const ratePct = rate * 100;
        let diffText = "";
        let colorClass = "blue";
        if (ratePct < 0.8) {
            diffText = `🟢 Your effective property tax rate of <strong>${ratePct.toFixed(3)}%</strong> is <strong>lower</strong> than the US national average (~1.04%).`;
            colorClass = "green";
        } else if (ratePct <= 1.3) {
            diffText = `🔵 Your effective property tax rate of <strong>${ratePct.toFixed(3)}%</strong> is <strong>moderate</strong>, sitting around the average US range.`;
            colorClass = "blue";
        } else {
            diffText = `🔴 Your effective property tax rate of <strong>${ratePct.toFixed(3)}%</strong> is <strong>higher</strong> than the US national average (~1.04%).`;
            colorClass = "red";
        }
        benchmarkBox.className = "usc-alert-box " + colorClass;
        benchmarkBox.innerHTML = diffText;
        benchmarkBox.style.display = "block";
    }

    // Show results panel and scroll
    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    
    // Offset-adjusted Smooth Scroll
    const yOffset = -20; 
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}

document.addEventListener("DOMContentLoaded", function() {
    const slider = document.getElementById("prop-appreciation");
    if (slider) {
        slider.addEventListener("input", function() {
            document.getElementById("lbl-prop-appreciation").innerText = this.value;
        });
    }

    const btn = document.getElementById("btn-calculate-property");
    if (btn) {
        btn.addEventListener("click", calculatePropertyTax);
    }

    const zipInput = document.getElementById("property-zip");
    if (zipInput) {
        zipInput.addEventListener("input", handleZipLookup);
    }
});';
    } elseif ($type === 'sales-tax') {
        require_once UST_PATH . 'data/sales-tax.php';
        $sales_db = ust_get_sales_tax_data();
        $state_sales = isset($sales_db[$state_slug]) ? $sales_db[$state_slug] : ['rate' => 0.05, 'avg_local' => 0.02, 'counties' => []];

        // Generate county options
        $counties_html = '<option value="">-- Average Combined Rate (' . (($state_sales['rate'] + $state_sales['avg_local']) * 100) . '%) --</option>';
        if (!empty($state_sales['counties'])) {
            foreach ($state_sales['counties'] as $c) {
                $counties_html .= '<option value="' . esc_attr($c['name']) . '" data-rate="' . $c['rate'] . '">' . esc_html($c['name']) . ' (' . ($c['rate'] * 100) . '%)</option>';
            }
        }

        // 1. SALES TAX HTML
        $data['html'] = '<!-- sales-tax-v1 -->
<div class="info-box">
  📌 Calculate your combined sales tax for purchases in ' . esc_html($state_name) . '. Add multiple items to your cart, select categories to apply specific exemption/reduced tax rules, and review the receipt.
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">🔄 REVERSE SALES TAX (TAX-INCLUSIVE TOTAL → PRE-TAX PRICE)</label></div>
  <div class="grid2">
    <div class="field">
      <label class="lbl">TAX-INCLUSIVE TOTAL ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The final amount you paid including sales tax. We back out the pre-tax price and the tax portion.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="sales-rev-total" value="106.00" step="0.01">
        <span class="inp-suffix">$</span>
      </div>
    </div>
    <div class="field">
      <label class="lbl">COMBINED SALES TAX RATE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Pre-filled with the ' . esc_html($state_name) . ' average combined rate. Adjust to match your receipt.</span></span></label>
      <div class="inp-wrap">
        <input type="number" class="inp" id="sales-rev-rate" value="' . number_format(($state_sales['rate'] + $state_sales['avg_local']) * 100, 2, '.', '') . '" step="0.01">
        <span class="inp-suffix">%</span>
      </div>
    </div>
  </div>
  <button type="button" class="calc-btn" id="btn-sales-rev" style="width: 100%; margin-top: 10px; font-weight:700;">Reverse Calculate Pre-Tax Price</button>
  <div class="grid2" id="sales-rev-results" style="display:none; margin-top: 15px;">
    <div class="det-card" style="text-align:center;">
      <div style="font-size: 22px; font-weight: 800; color: var(--succ);" id="res-sales-rev-pretax">$0.00</div>
      <div style="font-size: 11px; text-transform: uppercase; color: var(--mt); font-weight:700;">PRE-TAX PRICE</div>
    </div>
    <div class="det-card" style="text-align:center;">
      <div style="font-size: 22px; font-weight: 800; color: var(--pri);" id="res-sales-rev-tax">$0.00</div>
      <div style="font-size: 11px; text-transform: uppercase; color: var(--mt); font-weight:700;">SALES TAX PORTION</div>
    </div>
  </div>
</div>

<div class="sec">
  <div class="sec-head"><label class="lbl">🛒 SHOPPING CART / RECEIPT BUILDER</label></div>

  <div class="grid2">
    <div class="field">
      <label class="lbl">ZIP CODE LOOKUP (SIMULATED) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Allows you to lookup the local sales tax jurisdiction by entering your 5-digit ZIP code.</span></span></label>
      <div class="inp-wrap">
        <input type="text" class="inp" id="sales-zip" placeholder="e.g. 90210" maxlength="5">
        <span class="inp-suffix">📍</span>
      </div>
      <div class="hint" id="sales-zip-feedback" style="font-weight:600; color:var(--mt);">Enter 5-digit ZIP code to detect county</div>
    </div>
    <div class="field">
      <label class="lbl">LOCAL COUNTY / CITY JURISDICTION <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The county or municipal authority that charges a local sales tax on top of the state rate.</span></span></label>
      <div class="sel-wrap">
        <select id="sales-county" class="sel">
          ' . $counties_html . '
        </select>
      </div>
      <div class="hint">Select your local city/county to load standard sales tax rates</div>
    </div>
  </div>

  <div class="field">
    <label class="lbl">LOCAL ADD-ON RATE OVERRIDE (OPTIONAL) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Custom rate to overwrite the standard local city or county sales tax rate.</span></span></label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="sales-local-override" placeholder="e.g. 2.5" step="0.01">
      <span class="inp-suffix">%</span>
    </div>
    <div class="hint">Override to specify a custom local sales tax percentage</div>
  </div>

  <div style="margin-top: 15px; overflow-x: auto;">
    <table class="usc-custom-table" style="width:100%; border-collapse:collapse; font-size:12px; margin-bottom: 10px;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 6px; color:var(--lbl);">Item Description</th>
          <th style="text-align: left; padding: 6px; width: 140px; color:var(--lbl);">Category</th>
          <th style="text-align: left; padding: 6px; width: 90px; color:var(--lbl);">Price ($)</th>
          <th style="text-align: left; padding: 6px; width: 60px; color:var(--lbl);">Qty</th>
          <th style="text-align: center; padding: 6px; width: 50px; color:var(--lbl);">Action</th>
        </tr>
      </thead>
      <tbody id="sales-cart-tbody">
        <!-- JS will populate rows -->
      </tbody>
    </table>
    <button type="button" class="calc-btn" id="btn-sales-add-item" style="background:var(--succ); width:auto; padding:6px 12px; font-size:12px; margin-bottom:15px; font-weight:700;">➕ Add Item</button>
  </div>

  <div class="field">
    <label class="lbl">GLOBAL DISCOUNT PERCENTAGE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">A discount percentage applied to all items in your cart before taxes are calculated.</span></span></label>
    <div class="inp-wrap">
      <input type="number" class="inp" id="sales-discount" value="0" min="0" max="100">
      <span class="inp-suffix">%</span>
    </div>
    <div class="hint">Any discount applied to the total price before tax</div>
  </div>

  <button type="button" class="calc-btn" id="btn-calculate-sales" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Sales Tax</button>
</div>

<!-- RESULTS PANEL -->
<div class="sec" id="results-panel" style="display:none;">
  <div class="sec-head"><label class="lbl">📊 ITEMISED INVOICE RECEIPT</label></div>

  <div class="grid2" style="margin-bottom: 20px;">
    <div class="det-card" style="text-align: center;">
      <div style="font-size: 26px; font-weight: 800; color: var(--pri);" id="res-total-tax-amount">$0.00</div>
      <div style="font-size: 11px; text-transform: uppercase; color: var(--mt); font-weight:700;">TOTAL SALES TAX</div>
    </div>
    <div class="det-card" style="text-align: center;">
      <div style="font-size: 26px; font-weight: 800; color: var(--succ);" id="res-total-cost">$0.00</div>
      <div style="font-size: 11px; text-transform: uppercase; color: var(--mt); font-weight:700;">TOTAL PURCHASE COST</div>
    </div>
  </div>

  <div class="usc-alert-box" id="sales-benchmark-box" style="margin-top: 15px; display: none; margin-bottom: 15px;"></div>

  <!-- SVG Donut Chart -->
  <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 25px 0; gap: 20px; border-bottom: 1px solid var(--bdr); padding-bottom: 20px;">
    <div style="position: relative; width: 180px; height: 180px; flex-shrink: 0;">
      <svg width="180" height="180" viewBox="0 0 180 180" id="sales-donut-svg" style="transform: rotate(-90deg); filter: drop-shadow(0px 4px 10px rgba(0,0,0,0.06));">
        <!-- SVG slices drawn dynamically -->
      </svg>
      <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; font-family: sans-serif; pointer-events: none;">
        <div style="font-size: 10px; text-transform: uppercase; color: var(--mt); font-weight: 700; letter-spacing: 0.5px;">Combined Tax</div>
        <div style="font-size: 18px; font-weight: 800; color: var(--pri);" id="sales-donut-center-val">$0.00</div>
        <div style="font-size: 9px; color: var(--mt); font-weight: 600;">Total Tax</div>
      </div>
    </div>
    <!-- Legend -->
    <div id="sales-donut-legend" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; font-size: 10px; line-height: 1.3; max-width: 480px;"></div>
  </div>

  <div style="overflow-x: auto; margin-bottom: 15px;">
    <table class="usc-custom-table" style="width:100%; border-collapse:collapse; font-size:12px;">
      <thead>
        <tr>
          <th style="text-align:left; color:var(--lbl);">Item</th>
          <th style="text-align:left; color:var(--lbl);">Net Subtotal</th>
          <th style="text-align:left; color:var(--lbl);">State Tax</th>
          <th style="text-align:left; color:var(--lbl);">Local Tax</th>
          <th style="text-align:left; color:var(--lbl);">Total Cost</th>
        </tr>
      </thead>
      <tbody id="invoice-tbody">
        <!-- Dynamically populated -->
      </tbody>
    </table>
  </div>

  <div style="font-size:12px; line-height: 1.6;">
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Pre-Tax Cart Value:</span>
      <strong id="res-pretax-val">$0.00</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Discount Applied:</span>
      <strong id="res-discount-val">$0.00</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Discounted Net Value:</span>
      <strong id="res-net-val">$0.00</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>State Sales Tax Rate:</span>
      <strong id="res-state-rate-val">' . ($state_sales['rate'] * 100) . '%</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Local Sales Tax Rate:</span>
      <strong id="res-local-rate-val">0.0%</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>Combined Sales Tax Rate:</span>
      <strong id="res-combined-rate-val">' . ($state_sales['rate'] * 100) . '%</strong>
    </div>
    <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
      <span>State Tax Amount:</span>
      <strong id="res-state-tax-amount">$0.00</strong>
    </div>
    <div style="display:flex; justify-content:space-between; padding: 6px 0;">
      <span>Local Tax Amount:</span>
      <strong id="res-local-tax-amount">$0.00</strong>
    </div>
  </div>
</div>

<!-- COMMON BENCHMARKS COST TABLE -->
<div class="sec">
  <div class="sec-head"><label class="lbl">📆 SALES TAX ON PURCHASE BENCHMARKS</label></div>
  <div style="overflow-x: auto;">
    <table class="usc-custom-table" style="width: 100%; border-collapse: collapse; font-size:12px;">
      <thead>
        <tr>
          <th style="color:var(--lbl);">Purchase Price</th>
          <th style="color:var(--lbl);">State Tax</th>
          <th style="color:var(--lbl);">Local Tax</th>
          <th style="color:var(--lbl);">Combined Tax</th>
          <th style="color:var(--lbl);">Total Cost</th>
        </tr>
      </thead>
      <tbody id="sales-benchmarks-tbody">
        <!-- Populated via JS -->
      </tbody>
    </table>
  </div>
</div>';

        // 2. SALES TAX CSS
        $data['css'] = '/* Sales Tax Layout styling */';

        // 3. SALES TAX JS
        $data['js'] = 'const stateSalesRules = ' . json_encode($state_sales) . ';

function updateSalesDonut(subtotal, stateTax, localTax) {
    const total = subtotal + stateTax + localTax;
    const segments = [
        { label: "Purchase Value", value: subtotal, color: "#10b981" },
        { label: "State Sales Tax", value: stateTax, color: "#3b82f6" },
        { label: "Local Sales Tax", value: localTax, color: "#f59e0b" }
    ].filter(s => s.value > 0);

    const svg = document.getElementById("sales-donut-svg");
    if (!svg) return;
    svg.innerHTML = "";

    const r = 60;
    const cx = 90;
    const cy = 90;
    const circ = 2 * Math.PI * r;

    if (total === 0) {
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", cx);
        circle.setAttribute("cy", cy);
        circle.setAttribute("r", r);
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", "#e5e7eb");
        circle.setAttribute("stroke-width", "20");
        svg.appendChild(circle);
        return;
    }

    let offset = 0;
    let legendHtml = "";
    
    segments.forEach(seg => {
        const pct = seg.value / total;
        const strokeLength = pct * circ;
        const strokeOffset = offset;
        
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", cx);
        circle.setAttribute("cy", cy);
        circle.setAttribute("r", r);
        circle.setAttribute("fill", "none");
        circle.setAttribute("stroke", seg.color);
        circle.setAttribute("stroke-width", "20");
        circle.setAttribute("stroke-dasharray", `${strokeLength} ${circ}`);
        circle.setAttribute("stroke-dashoffset", -strokeOffset);
        circle.style.transition = "stroke-width 0.2s ease, filter 0.2s ease";
        circle.style.cursor = "pointer";

        circle.addEventListener("mouseenter", () => {
            circle.setAttribute("stroke-width", "24");
            circle.setAttribute("filter", "brightness(1.1)");
            document.getElementById("sales-donut-center-val").innerText = "$" + Math.round(seg.value).toLocaleString();
            document.getElementById("sales-donut-center-val").style.color = seg.color;
        });
        circle.addEventListener("mouseleave", () => {
            circle.setAttribute("stroke-width", "20");
            circle.removeAttribute("filter");
            document.getElementById("sales-donut-center-val").innerText = "$" + Math.round(stateTax + localTax).toLocaleString();
            document.getElementById("sales-donut-center-val").style.color = "var(--pri)";
        });

        svg.appendChild(circle);
        offset += strokeLength;

        legendHtml += `
            <div style="display:flex; align-items:center; gap:4px; padding: 2px 6px; background: rgba(0,0,0,0.02); border-radius: 4px; border: 1px solid var(--bdr);">
                <span style="width:8px; height:8px; border-radius:50%; background:${seg.color}; display:inline-block;"></span>
                <span><strong>${seg.label}:</strong> $${Math.round(seg.value).toLocaleString()}</span>
            </div>
        `;
    });

    document.getElementById("sales-donut-center-val").innerText = "$" + Math.round(stateTax + localTax).toLocaleString();
    document.getElementById("sales-donut-center-val").style.color = "var(--pri)";
    document.getElementById("sales-donut-legend").innerHTML = legendHtml;
}

function handleSalesZipLookup() {
    const zipInput = document.getElementById("sales-zip");
    const feedback = document.getElementById("sales-zip-feedback");
    const countySelect = document.getElementById("sales-county");
    if (!zipInput) return;
    
    let zip = zipInput.value.replace(/\D/g, "");
    zipInput.value = zip.substring(0, 5);
    
    if (zip.length === 5) {
        if (stateSalesRules.counties && stateSalesRules.counties.length > 0) {
            const sum = zip.split("").reduce((acc, char) => acc + parseInt(char), 0);
            const idx = sum % stateSalesRules.counties.length;
            const matchedCounty = stateSalesRules.counties[idx];
            
            countySelect.value = matchedCounty.name;
            feedback.innerText = "✅ ZIP matched: " + matchedCounty.name + " (" + (matchedCounty.rate * 100).toFixed(2) + "%)";
            feedback.style.color = "var(--succ)";
            
            // Clear override if ZIP matched
            document.getElementById("sales-local-override").value = "";
            calculateSalesTax();
        } else {
            feedback.innerText = "✅ ZIP matched. Applied average rate.";
            feedback.style.color = "var(--succ)";
            countySelect.value = "";
            calculateSalesTax();
        }
    } else if (zip.length > 0) {
        feedback.innerText = "Entering 5-digit ZIP code...";
        feedback.style.color = "var(--mt)";
    } else {
        feedback.innerText = "Enter 5-digit ZIP code to detect county";
        feedback.style.color = "var(--mt)";
    }
}

let cartItems = [
    { name: "Electronics", category: "general", price: 500, qty: 1 },
    { name: "Fresh Produce", category: "groceries", price: 30, qty: 2 },
    { name: "First Aid Kit", category: "medicine", price: 15, qty: 1 }
];

function renderCart() {
    const tbody = document.getElementById("sales-cart-tbody");
    if (!tbody) return;
    tbody.innerHTML = "";
    cartItems.forEach((item, index) => {
        tbody.innerHTML += `
        <tr>
            <td style="padding: 6px;"><input type="text" class="inp" style="padding:4px 8px; font-size:12px;" value="${item.name}" onchange="window.updateCartItem(${index}, &quot;name&quot;, this.value)"></td>
            <td style="padding: 6px;">
                <div class="sel-wrap" style="height:30px; border:1px solid var(--bdr); border-radius:4px;">
                    <select class="sel" style="padding:4px 8px; font-size:12px; height:30px; line-height:30px; border:none; background:transparent;" onchange="window.updateCartItem(${index}, &quot;category&quot;, this.value)">
                        <option value="general" ${item.category === "general" ? "selected" : ""}>General Goods</option>
                        <option value="groceries" ${item.category === "groceries" ? "selected" : ""}>Groceries</option>
                        <option value="medicine" ${item.category === "medicine" ? "selected" : ""}>Prescription Medicine</option>
                        <option value="clothing" ${item.category === "clothing" ? "selected" : ""}>Clothing</option>
                    </select>
                </div>
            </td>
            <td style="padding: 6px;"><input type="number" class="inp" style="padding:4px 8px; font-size:12px;" value="${item.price}" onchange="window.updateCartItem(${index}, &quot;price&quot;, parseFloat(this.value) || 0)"></td>
            <td style="padding: 6px;"><input type="number" class="inp" style="padding:4px 8px; font-size:12px;" value="${item.qty}" onchange="window.updateCartItem(${index}, &quot;qty&quot;, parseInt(this.value) || 0)"></td>
            <td style="padding: 6px; text-align: center;"><button type="button" style="background:#ef4444; color:#fff; border:none; border-radius:4px; padding:4px 8px; cursor:pointer;" onclick="window.deleteCartItem(${index})">❌</button></td>
        </tr>`;
    });
}

window.updateCartItem = function(index, field, value) {
    if (cartItems[index]) {
        cartItems[index][field] = value;
    }
};

window.deleteCartItem = function(index) {
    cartItems.splice(index, 1);
    renderCart();
};

function calculateSalesReverse() {
    const total = parseFloat(document.getElementById("sales-rev-total").value) || 0;
    const rate = (parseFloat(document.getElementById("sales-rev-rate").value) || 0) / 100;
    const preTax = (1 + rate) > 0 ? total / (1 + rate) : total;
    const tax = total - preTax;
    document.getElementById("res-sales-rev-pretax").innerText = "$" + preTax.toFixed(2);
    document.getElementById("res-sales-rev-tax").innerText = "$" + tax.toFixed(2);
    document.getElementById("sales-rev-results").style.display = "grid";
}

function calculateSalesTax() {
    const discountPercent = parseFloat(document.getElementById("sales-discount").value) || 0;
    const countySelect = document.getElementById("sales-county");
    const selectedOption = countySelect.options[countySelect.selectedIndex];

    // Determine state rate
    const stateRate = stateSalesRules.rate;

    // Determine local rate
    let localRate = stateSalesRules.avg_local || 0;
    if (selectedOption && selectedOption.value !== "") {
        const selectedRate = parseFloat(selectedOption.getAttribute("data-rate"));
        localRate = Math.max(0, selectedRate - stateRate);
    }

    // Check for local rate override
    const overrideInput = document.getElementById("sales-local-override").value;
    if (overrideInput !== "") {
        localRate = parseFloat(overrideInput) / 100;
    }

    const discountFactor = Math.max(0, 1 - discountPercent / 100);

    let totalPreTax = 0;
    let totalDiscountedSubtotal = 0;
    let totalStateTax = 0;
    let totalLocalTax = 0;

    let invoiceHtml = "";

    cartItems.forEach(item => {
        const preTaxVal = item.price * item.qty;
        const netVal = preTaxVal * discountFactor;

        // Apply category exemptions
        let appliedStateRate = stateRate;
        let appliedLocalRate = localRate;

        if (item.category === "groceries") {
            appliedStateRate = stateRate > 0.04 ? 0.01 : 0;
        } else if (item.category === "medicine") {
            appliedStateRate = 0;
            appliedLocalRate = 0;
        } else if (item.category === "clothing") {
            appliedStateRate = stateRate * 0.5;
        }

        const itemStateTax = netVal * appliedStateRate;
        const itemLocalTax = netVal * appliedLocalRate;
        const itemTotalTax = itemStateTax + itemLocalTax;
        const itemTotalCost = netVal + itemTotalTax;

        totalPreTax += preTaxVal;
        totalDiscountedSubtotal += netVal;
        totalStateTax += itemStateTax;
        totalLocalTax += itemLocalTax;

        invoiceHtml += `<tr>
            <td><strong>${item.name}</strong> <span style="font-size:10px; color:var(--mt);">(${item.category})</span></td>
            <td>$${netVal.toFixed(2)}</td>
            <td>$${itemStateTax.toFixed(2)} <span style="font-size:9px; color:var(--mt);">(${(appliedStateRate*100).toFixed(1)}%)</span></td>
            <td>$${itemLocalTax.toFixed(2)} <span style="font-size:9px; color:var(--mt);">(${(appliedLocalRate*100).toFixed(1)}%)</span></td>
            <td style="font-weight:700;">$${itemTotalCost.toFixed(2)}</td>
        </tr>`;
    });

    const totalTaxAmount = totalStateTax + totalLocalTax;
    const totalCost = totalDiscountedSubtotal + totalTaxAmount;

    // Update UI
    document.getElementById("res-total-tax-amount").innerText = "$" + totalTaxAmount.toFixed(2);
    document.getElementById("res-total-cost").innerText = "$" + totalCost.toFixed(2);
    document.getElementById("res-pretax-val").innerText = "$" + totalPreTax.toFixed(2);
    document.getElementById("res-discount-val").innerText = "-$" + (totalPreTax - totalDiscountedSubtotal).toFixed(2);
    document.getElementById("res-net-val").innerText = "$" + totalDiscountedSubtotal.toFixed(2);
    document.getElementById("res-state-rate-val").innerText = (stateRate * 100).toFixed(2) + "%";
    document.getElementById("res-local-rate-val").innerText = (localRate * 100).toFixed(2) + "%";
    document.getElementById("res-combined-rate-val").innerText = ((stateRate + localRate) * 100).toFixed(2) + "%";
    document.getElementById("res-state-tax-amount").innerText = "$" + totalStateTax.toFixed(2);
    document.getElementById("res-local-tax-amount").innerText = "$" + totalLocalTax.toFixed(2);
    document.getElementById("invoice-tbody").innerHTML = invoiceHtml;

    // Update SVG Donut chart
    updateSalesDonut(totalDiscountedSubtotal, totalStateTax, totalLocalTax);

    // Update benchmark box
    const benchmarkBox = document.getElementById("sales-benchmark-box");
    if (benchmarkBox) {
        const ratePct = (stateRate + localRate) * 100;
        let diffText = "";
        let colorClass = "blue";
        if (ratePct < 6.0) {
            diffText = `🟢 Your combined sales tax rate of <strong>${ratePct.toFixed(2)}%</strong> is <strong>lower</strong> than the US national average (~6.0%).`;
            colorClass = "green";
        } else if (ratePct <= 8.5) {
            diffText = `🔵 Your combined sales tax rate of <strong>${ratePct.toFixed(2)}%</strong> is <strong>moderate</strong>, sitting around the average US combined range.`;
            colorClass = "blue";
        } else {
            diffText = `🔴 Your combined sales tax rate of <strong>${ratePct.toFixed(2)}%</strong> is <strong>higher</strong> than the US national average (~6.0%).`;
            colorClass = "red";
        }
        benchmarkBox.className = "usc-alert-box " + colorClass;
        benchmarkBox.innerHTML = diffText;
        benchmarkBox.style.display = "block";
    }

    // Benchmarks
    const avgCombinedRate = totalDiscountedSubtotal > 0 ? (totalStateTax + totalLocalTax) / totalDiscountedSubtotal : (stateRate + localRate);
    const benchmarks = [10, 50, 100, 500, 1000, 5000];
    let tableHtml = "";
    benchmarks.forEach(bp => {
        const bpTax = bp * avgCombinedRate;
        const bpTotal = bp + bpTax;
        tableHtml += `<tr>
            <td><strong>$${bp.toLocaleString()}</strong></td>
            <td>$${(bp * (totalStateTax / (totalDiscountedSubtotal || 1))).toFixed(2)}</td>
            <td>$${(bp * (totalLocalTax / (totalDiscountedSubtotal || 1))).toFixed(2)}</td>
            <td><strong>$${bpTax.toFixed(2)}</strong></td>
            <td style="color:var(--succ);font-weight:700;">$${bpTotal.toFixed(2)}</td>
        </tr>`;
    });
    document.getElementById("sales-benchmarks-tbody").innerHTML = tableHtml;

    // Show panel and scroll
    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    
    // Offset-adjusted Smooth Scroll
    const yOffset = -20; 
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}

document.addEventListener("DOMContentLoaded", function() {
    renderCart();
    
    const btnAdd = document.getElementById("btn-sales-add-item");
    if (btnAdd) {
        btnAdd.addEventListener("click", function() {
            cartItems.push({ name: "New Item", category: "general", price: 10, qty: 1 });
            renderCart();
        });
    }

    const btn = document.getElementById("btn-calculate-sales");
    if (btn) {
        btn.addEventListener("click", calculateSalesTax);
    }

    const zipInput = document.getElementById("sales-zip");
    if (zipInput) {
        zipInput.addEventListener("input", handleSalesZipLookup);
    }

    const btnRev = document.getElementById("btn-sales-rev");
    if (btnRev) {
        btnRev.addEventListener("click", calculateSalesReverse);
    }
});';
    } elseif ($type === 'other') {
        $data = ust_get_other_tax_templates($state_slug);
    }

    return $data;
}

/**
 * Returns HTML, CSS, and JS for the 12 general calculators under 'Other' category
 */
function ust_get_other_tax_templates($slug) {
    $data = ['html' => '', 'css' => '', 'js' => ''];
    $states_registry = ust_get_states_data();

    // 1. GENERAL CSS FOR OTHER CALCULATORS (premium widgets style)
    $data['css'] = '
.usc-other-result-card {
    background: #f8fafc;
    border: 1px solid var(--bdr);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 15px;
    text-align: center;
}
.usc-other-result-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--pri);
    margin-bottom: 4px;
}
.usc-other-result-label {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--mt);
    font-weight: 700;
    letter-spacing: 0.5px;
}
.usc-alert-box {
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 13px;
    line-height: 1.5;
    margin-top: 15px;
}
.usc-alert-box.green {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}
.usc-alert-box.red {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.usc-alert-box.blue {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
}
';

    switch ($slug) {
        case 'federal-income-tax-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Estimate your federal income tax obligation for the current tax year. Enter your annual gross earnings, choose your filing status, and input pre-tax deductions.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 INCOME DETAILS</label></div>
    <div class="field">
        <label class="lbl">ANNUAL GROSS SALARY ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Total annual earnings before taxes and deductions.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="fed-salary" value="75000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">FILING STATUS <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Determines your federal tax brackets and standard deduction.</span></span></label>
        <div class="sel-wrap">
            <select id="fed-status" class="sel">
                <option value="single">Single</option>
                <option value="married">Married Filing Jointly</option>
                <option value="head">Head of Household</option>
            </select>
        </div>
    </div>
    <div class="field">
        <label class="lbl">PRE-TAX DEDUCTIONS (401k, HSA, etc.) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Contributions that lower your taxable income, e.g. traditional 401(k), HSA.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="fed-pretax" value="0">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-fed" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Federal Tax</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 TAX ESTIMATION SUMMARY</label></div>
    
    <div id="fed-advisor-box" class="usc-alert-box blue" style="display:none; margin-bottom: 20px;"></div>

    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-fed-tax">$0</div>
            <div class="usc-other-result-label">Federal Income Tax</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-fed-takehome" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Net Take-Home Pay</div>
        </div>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Standard Deduction:</span>
            <strong id="res-fed-standard">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Taxable Income:</span>
            <strong id="res-fed-taxable">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Social Security Withholding (6.2%):</span>
            <strong id="res-fed-ss">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Medicare Withholding (1.45%):</span>
            <strong id="res-fed-med">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>Effective Tax Rate:</span>
            <strong id="res-fed-effective">0.0%</strong>
        </div>
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <div style="font-weight: 700; font-size: 13px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">Income Breakdown (Tax vs Take-Home)</div>
        <div style="display: flex; height: 24px; border-radius: 6px; overflow: hidden; background: #e5e7eb; margin-bottom: 15px;">
            <div id="fed-bar-net" style="background: var(--succ); transition: width 0.3s;" title="Take-Home Pay"></div>
            <div id="fed-bar-tax" style="background: var(--pri); transition: width 0.3s;" title="Federal Income Tax"></div>
            <div id="fed-bar-fica" style="background: var(--or); transition: width 0.3s;" title="FICA Tax"></div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 11px; color: var(--mt);">
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 10px; height: 10px; background: var(--succ); border-radius: 2px;"></span>
                <span>Net Take-home: <strong id="lbl-fed-net">$0</strong> (<span id="pct-fed-net">0%</span>)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 10px; height: 10px; background: var(--pri); border-radius: 2px;"></span>
                <span>Federal Tax: <strong id="lbl-fed-tax">$0</strong> (<span id="pct-fed-tax">0%</span>)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 10px; height: 10px; background: var(--or); border-radius: 2px;"></span>
                <span>FICA (SS+Med): <strong id="lbl-fed-fica">$0</strong> (<span id="pct-fed-fica">0%</span>)</span>
            </div>
        </div>
    </div>
</div>';

            $data['js'] = '
function calculateFederalTax() {
    const salary = parseFloat(document.getElementById("fed-salary").value) || 0;
    const status = document.getElementById("fed-status").value;
    const pretax = parseFloat(document.getElementById("fed-pretax").value) || 0;

    const stdDeductions = { single: 15000, married: 30000, head: 22500 };
    const stdDeduct = stdDeductions[status];
    const taxable = Math.max(0, salary - pretax - stdDeduct);

    // FICA calculations
    const ssTaxable = Math.min(salary, 176100);
    const ssTax = ssTaxable * 0.062;
    const medTax = salary * 0.0145;
    
    // Add-on Medicare surtax for high earners (0.9% above 200k/250k)
    let medSurtax = 0;
    const surtaxLimit = (status === "married") ? 250000 : 200000;
    if (salary > surtaxLimit) {
        medSurtax = (salary - surtaxLimit) * 0.009;
    }
    const totalFica = ssTax + medTax + medSurtax;

    // Brackets (2025/2026 Estimate)
    const brackets = {
        single: [
            { limit: 11925, rate: 0.10 },
            { limit: 48475, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250525, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        married: [
            { limit: 23850, rate: 0.10 },
            { limit: 96950, rate: 0.12 },
            { limit: 206700, rate: 0.22 },
            { limit: 394600, rate: 0.24 },
            { limit: 501050, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        head: [
            { limit: 17000, rate: 0.10 },
            { limit: 64850, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250500, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ]
    };

    let fedTax = 0;
    let tempTaxable = taxable;
    let prevLimit = 0;
    const activeBrackets = brackets[status];
    let marginalRate = 0;

    for (let i = 0; i < activeBrackets.length; i++) {
        const b = activeBrackets[i];
        const currentRange = b.limit - prevLimit;
        if (tempTaxable > currentRange) {
            fedTax += currentRange * b.rate;
            tempTaxable -= currentRange;
            prevLimit = b.limit;
        } else {
            fedTax += tempTaxable * b.rate;
            marginalRate = b.rate;
            break;
        }
    }

    const netPay = Math.max(0, salary - fedTax - totalFica - pretax);
    const effective = salary > 0 ? ((fedTax + totalFica) / salary) * 100 : 0;

    document.getElementById("res-fed-tax").innerText = "$" + Math.round(fedTax).toLocaleString();
    document.getElementById("res-fed-takehome").innerText = "$" + Math.round(netPay).toLocaleString();
    document.getElementById("res-fed-standard").innerText = "$" + stdDeduct.toLocaleString();
    document.getElementById("res-fed-taxable").innerText = "$" + Math.round(taxable).toLocaleString();
    document.getElementById("res-fed-ss").innerText = "$" + Math.round(ssTax).toLocaleString();
    document.getElementById("res-fed-med").innerText = "$" + Math.round(medTax + medSurtax).toLocaleString();
    document.getElementById("res-fed-fica").innerText = "$" + Math.round(totalFica).toLocaleString();
    document.getElementById("res-fed-effective").innerText = effective.toFixed(2) + "%";

    const pctNet = (salary > 0) ? (netPay / salary) * 100 : 0;
    const pctFed = (salary > 0) ? (fedTax / salary) * 100 : 0;
    const pctFica = (salary > 0) ? (totalFica / salary) * 100 : 0;

    document.getElementById("fed-bar-net").style.width = pctNet + "%";
    document.getElementById("fed-bar-tax").style.width = pctFed + "%";
    document.getElementById("fed-bar-fica").style.width = pctFica + "%";

    document.getElementById("lbl-fed-net").innerText = "$" + Math.round(netPay).toLocaleString();
    document.getElementById("lbl-fed-tax").innerText = "$" + Math.round(fedTax).toLocaleString();
    document.getElementById("lbl-fed-fica").innerText = "$" + Math.round(totalFica).toLocaleString();

    document.getElementById("pct-fed-net").innerText = pctNet.toFixed(1) + "%";
    document.getElementById("pct-fed-tax").innerText = pctFed.toFixed(1) + "%";
    document.getElementById("pct-fed-fica").innerText = pctFica.toFixed(1) + "%";

    // Advisor box
    const advisorBox = document.getElementById("fed-advisor-box");
    if (advisorBox) {
        if (marginalRate > 0 && pretax < 23000) {
            const savedTaxes = 1000 * marginalRate;
            advisorBox.innerHTML = `💡 <strong>Tax Optimization Advisor:</strong> Contributing an additional <strong>$1,000</strong> to your pre-tax Traditional 401(k) or HSA would save you approximately <strong>$${Math.round(savedTaxes)}</strong> in federal taxes at your marginal rate of ${((marginalRate) * 100).toFixed(1)}%.`;
            advisorBox.style.display = "block";
        } else {
            advisorBox.style.display = "none";
        }
    }

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-fed");
    if (btn) {
        btn.addEventListener("click", calculateFederalTax);
    }
});';
            break;

        case 'state-income-tax-calculator':
            require_once UST_PATH . 'data/income-tax.php';
            $states_data = ust_get_income_tax_data()['states'];
            $states_options = '';
            foreach ($states_registry as $s_slug => $s_info) {
                $states_options .= '<option value="' . esc_attr($s_slug) . '">' . esc_html($s_info['name']) . '</option>';
            }

            $data['html'] = '
<div class="info-box">
    📌 Estimate and compare state-level income tax burdens. Select your state, gross annual salary, pre-tax deductions, and filing status to compute tax liabilities.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 INCOME & REGION DETAILS</label></div>
    <div class="field">
        <label class="lbl">ANNUAL GROSS SALARY ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Total annual earnings before taxes and deductions.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="state-salary" value="75000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">PRE-TAX DEDUCTIONS (401k, HSA, etc.) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Contributions that lower your taxable income, e.g. traditional 401(k), HSA.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="state-pretax" value="0">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">STATE <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Select the state for which you want to calculate income tax.</span></span></label>
        <div class="sel-wrap">
            <select id="state-select" class="sel">
                ' . $states_options . '
            </select>
        </div>
    </div>
    <div class="field">
        <label class="lbl">FILING STATUS <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Determines your state tax brackets and progressive deduction amounts.</span></span></label>
        <div class="sel-wrap">
            <select id="state-status" class="sel">
                <option value="single">Single</option>
                <option value="married">Married Filing Jointly</option>
            </select>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-state-inc" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate State Tax</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 STATE TAX ESTIMATION SUMMARY</label></div>

    <div id="state-advisor-box" class="usc-alert-box blue" style="display:none; margin-bottom: 20px;"></div>

    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-state-tax">$0</div>
            <div class="usc-other-result-label">State Income Tax</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-state-takehome" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Net State Take-Home</div>
        </div>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>State Tax System Type:</span>
            <strong id="res-state-systype">Graduated</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>State Deduction Allowance:</span>
            <strong id="res-state-deduct">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Taxable State Income:</span>
            <strong id="res-state-taxable">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Effective State Rate:</span>
            <strong id="res-state-effective">0.0%</strong>
        </div>
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <div style="font-weight: 700; font-size: 13px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">State Tax Rate Visualizer</div>
        <div style="display: flex; height: 18px; border-radius: 4px; overflow: hidden; background: #e5e7eb; margin-bottom: 12px;">
            <div id="state-rate-bar" style="background: var(--pri); width: 0%; transition: width 0.3s;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--mt); margin-bottom: 15px;">
            <span>0% (Zero Tax States)</span>
            <span>Your Effective Rate: <strong id="lbl-state-eff-rate">0.0%</strong></span>
            <span>13.3% (CA Max)</span>
        </div>
        
        <div style="font-weight: 700; font-size: 12px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">National Comparison Benchmarks</div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border: 1px solid var(--bdr); border-radius: 6px; font-size: 12px;">
                <span>Zero-Tax States (TX, FL, NV, WA, etc.)</span>
                <span style="background: var(--succ); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700;">0.0% Rate</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border: 1px solid var(--bdr); border-radius: 6px; font-size: 12px;">
                <span>California (Highest Marginal State Rate)</span>
                <span style="background: #ef4444; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700;">Up to 13.3%</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border: 1px solid var(--bdr); border-radius: 6px; font-size: 12px;">
                <span>New York (High Progressive Rate)</span>
                <span style="background: #ef4444; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700;">Up to 10.9%</span>
            </div>
        </div>
    </div>
</div>';

            $data['js'] = '
const stateTaxRules = ' . json_encode($states_data) . ';

function calculateStateTax() {
    const salary = parseFloat(document.getElementById("state-salary").value) || 0;
    const pretax = parseFloat(document.getElementById("state-pretax").value) || 0;
    const stateKey = document.getElementById("state-select").value;
    const status = document.getElementById("state-status").value;

    const stateRule = stateTaxRules[stateKey] || { type: "none", deduction: 0, flat_rate: 0 };
    
    let deduct = stateRule.deduction || 0;
    if (status === "married" && deduct > 0) {
        deduct = deduct * 2;
    }

    const taxable = Math.max(0, salary - pretax - deduct);
    let stateTax = 0;
    let marginalRate = 0;

    if (stateRule.type === "flat") {
        stateTax = taxable * stateRule.flat_rate;
        marginalRate = stateRule.flat_rate;
    } else if (stateRule.type === "graduated" && stateRule.brackets) {
        let tempTaxable = taxable;
        let prevLimit = 0;
        for (let i = 0; i < stateRule.brackets.length; i++) {
            const b = stateRule.brackets[i];
            const currentRange = b.limit - prevLimit;
            if (tempTaxable > currentRange) {
                stateTax += currentRange * b.rate;
                tempTaxable -= currentRange;
                prevLimit = b.limit;
            } else {
                stateTax += tempTaxable * b.rate;
                marginalRate = b.rate;
                break;
            }
        }
    }

    const netPay = Math.max(0, salary - stateTax - pretax);
    const effective = salary > 0 ? (stateTax / salary) * 100 : 0;

    document.getElementById("res-state-tax").innerText = "$" + Math.round(stateTax).toLocaleString();
    document.getElementById("res-state-takehome").innerText = "$" + Math.round(netPay).toLocaleString();
    document.getElementById("res-state-systype").innerText = stateRule.type.charAt(0).toUpperCase() + stateRule.type.slice(1);
    document.getElementById("res-state-deduct").innerText = "$" + deduct.toLocaleString();
    document.getElementById("res-state-taxable").innerText = "$" + Math.round(taxable).toLocaleString();
    document.getElementById("res-state-effective").innerText = effective.toFixed(2) + "%";

    const stateBarPct = Math.min(100, (effective / 13.3) * 100);
    document.getElementById("state-rate-bar").style.width = stateBarPct + "%";
    document.getElementById("lbl-state-eff-rate").innerText = effective.toFixed(2) + "%";

    // State advisor box
    const advisorBox = document.getElementById("state-advisor-box");
    if (advisorBox) {
        if (marginalRate > 0 && pretax < 23000) {
            const savedTaxes = 1000 * marginalRate;
            advisorBox.innerHTML = `💡 <strong>State Tax Savings Advisor:</strong> Contributing an additional <strong>$1,000</strong> to your pre-tax retirement or HSA would save you approximately <strong>$${Math.round(savedTaxes)}</strong> in state taxes at your marginal rate of ${((marginalRate) * 100).toFixed(2)}%.`;
            advisorBox.style.display = "block";
        } else {
            advisorBox.style.display = "none";
        }
    }

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-state-inc");
    if (btn) {
        btn.addEventListener("click", calculateStateTax);
    }
});';
            break;

        case 'income-tax-refund-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Estimate whether you will receive a refund check or owe taxes to the IRS in April.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 ANNUAL ESTIMATES</label></div>
    <div class="field">
        <label class="lbl">TOTAL ANNUAL TAX LIABILITY ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The total amount of tax you actually owe the government for the entire tax year.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="refund-liability" value="12000">
            <span class="inp-suffix">$</span>
        </div>
        <div class="hint">The actual total amount of tax you owe for the year</div>
    </div>
    <div class="field">
        <label class="lbl">TOTAL TAX WITHHELD FROM PAYCHECKS ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The total amount of taxes already paid throughout the year (check W-2 Box 2 or estimated payments).</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="refund-withheld" value="14000">
            <span class="inp-suffix">$</span>
        </div>
        <div class="hint">Check W-2 Box 2 (Federal Tax Withheld) plus estimated payments</div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-refund" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Tax Refund</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 REFUND SUMMARY</label></div>
    <div class="usc-other-result-card" id="refund-card" style="margin-bottom: 20px;">
        <div class="usc-other-result-value" id="res-refund-amount">$2,000</div>
        <div class="usc-other-result-label" id="res-refund-label">Estimated Tax Refund</div>
    </div>
    <div id="refund-status-box" class="usc-alert-box green">
        🎉 You paid more tax than required. The IRS will return the excess of $2,000 as a refund check.
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <div style="font-weight: 700; font-size: 13px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">Withholding Alignment Gauge</div>
        <p style="font-size: 11px; color: var(--mt); margin: 0 0 15px;">Your goal is "Perfect Alignment" (Zero owed / Zero refund) to maximize monthly cash flow without penalty.</p>
        
        <div style="position: relative; height: 35px; background: linear-gradient(to right, #ef4444 0%, #f59e0b 35%, #10b981 45%, #10b981 55%, #f59e0b 65%, #ef4444 100%); border-radius: 6px; margin-bottom: 25px;">
            <div id="refund-gauge-pin" style="position: absolute; top: -5px; left: 50%; transform: translateX(-50%); width: 4px; height: 45px; background: #000; border-radius: 2px; transition: left 0.3s;">
                <div style="position: absolute; top: -10px; left: -8px; width: 20px; height: 20px; background: #000; border-radius: 50%; color: #fff; font-size: 10px; font-weight: 700; text-align: center; line-height: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">📍</div>
            </div>
        </div>
        
        <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--mt); font-weight: 700; text-transform: uppercase;">
            <span style="color:#ef4444;">Owe Large Amt</span>
            <span style="color:#f59e0b;">Owe Small Amt</span>
            <span style="color:#10b981;">Perfect (+$0)</span>
            <span style="color:#f59e0b;">Refund Small</span>
            <span style="color:#ef4444;">Refund Large</span>
        </div>
    </div>
</div>';

            $data['js'] = '
function calculateRefund() {
    const liability = parseFloat(document.getElementById("refund-liability").value) || 0;
    const withheld = parseFloat(document.getElementById("refund-withheld").value) || 0;

    const difference = withheld - liability;
    const cardEl = document.getElementById("refund-card");
    const labelEl = document.getElementById("res-refund-label");
    const amountEl = document.getElementById("res-refund-amount");
    const boxEl = document.getElementById("refund-status-box");

    if (difference >= 0) {
        amountEl.innerText = "$" + Math.round(difference).toLocaleString();
        amountEl.style.color = "var(--succ)";
        labelEl.innerText = "Estimated Tax Refund";
        boxEl.className = "usc-alert-box green";
        boxEl.innerHTML = "🎉 <strong>Refund Due:</strong> You overpaid your taxes by <strong>$" + Math.round(difference).toLocaleString() + "</strong>. The IRS will return this balance to you as a refund check.";
    } else {
        const owed = Math.abs(difference);
        amountEl.innerText = "$" + Math.round(owed).toLocaleString();
        amountEl.style.color = "#ef4444";
        labelEl.innerText = "Estimated Amount Owed";
        boxEl.className = "usc-alert-box red";
        boxEl.innerHTML = "⚠️ <strong>Taxes Owed:</strong> You underpaid your taxes by <strong>$" + Math.round(owed).toLocaleString() + "</strong>. You will need to pay this amount to the IRS in April to avoid underpayment interest.";
    }

    let pct = 50;
    if (difference !== 0) {
        const clampedDiff = Math.max(-5000, Math.min(5000, difference));
        pct = 50 + (clampedDiff / 5000) * 50;
    }
    const pin = document.getElementById("refund-gauge-pin");
    if (pin) pin.style.left = pct + "%";

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-refund");
    if (btn) {
        btn.addEventListener("click", calculateRefund);
    }
});';
            break;

        case 'tax-withholding-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Verify if your current employer paycheck withholding is adequate. Annualizes wages and estimates withholding per paycheck.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 PAYCHECK DETAILS</label></div>
    <div class="field">
        <label class="lbl">PAYCHECK GROSS WAGES ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The amount of money earned on this single paycheck before any taxes or deductions are taken out.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="wh-gross" value="2500">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">PAY FREQUENCY <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">How often you get paid (e.g., Weekly = 52 times a year, Bi-weekly = 26 times).</span></span></label>
        <div class="sel-wrap">
            <select id="wh-freq" class="sel">
                <option value="52">Weekly (52 paychecks)</option>
                <option value="26" selected>Bi-weekly (26 paychecks)</option>
                <option value="24">Semi-monthly (24 paychecks)</option>
                <option value="12">Monthly (12 paychecks)</option>
            </select>
        </div>
    </div>
    <div class="field">
        <label class="lbl">FILING STATUS <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Filing status registered on your employer W-4 form.</span></span></label>
        <div class="sel-wrap">
            <select id="wh-status" class="sel">
                <option value="single">Single</option>
                <option value="married">Married Filing Jointly</option>
                <option value="head">Head of Household</option>
            </select>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-wh" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Withholding</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 PAYCHECK WITHHOLDING SUMMARY</label></div>
    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-wh-paycheck">$0</div>
            <div class="usc-other-result-label">Withholding per Paycheck</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-wh-annual" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Annual Estimated Withholding</div>
        </div>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Annualized Gross Income:</span>
            <strong id="res-wh-annual-gross">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Federal Standard Deduction:</span>
            <strong id="res-wh-standard">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Annual Taxable Income:</span>
            <strong id="res-wh-taxable">$0</strong>
        </div>
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <div style="font-weight: 700; font-size: 13px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">🛠️ IRS Form W-4 Adjustments Planner</div>
        <p style="font-size: 11px; color: var(--mt); margin: 0 0 15px;">Simulate W-4 settings to adjust your paycheck withholding. Enter values to see real-time impact:</p>
        
        <div class="grid2">
            <div class="field" style="margin-bottom: 10px;">
                <label class="lbl" style="font-size: 10px;">CHILD DEPENDENTS (UNDER 17 - $2,000 EACH) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Number of qualifying children under 17 for the Child Tax Credit.</span></span></label>
                <div class="inp-wrap">
                    <input type="number" class="inp" id="wh-sim-children" value="0" min="0" style="padding: 6px 10px; font-size: 13px;">
                </div>
            </div>
            <div class="field" style="margin-bottom: 10px;">
                <label class="lbl" style="font-size: 10px;">OTHER DEPENDENTS ($500 EACH) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Number of other dependents like older children or relatives who qualify for a $500 credit.</span></span></label>
                <div class="inp-wrap">
                    <input type="number" class="inp" id="wh-sim-other-deps" value="0" min="0" style="padding: 6px 10px; font-size: 13px;">
                </div>
            </div>
        </div>

        <div class="grid2">
            <div class="field" style="margin-bottom: 10px;">
                <label class="lbl" style="font-size: 10px;">OTHER INCOME (W-4 STEP 4A) ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Expected non-job income (interest, dividends, retirement) that you want tax withheld for.</span></span></label>
                <div class="inp-wrap">
                    <input type="number" class="inp" id="wh-sim-other-inc" value="0" style="padding: 6px 10px; font-size: 13px;">
                </div>
            </div>
            <div class="field" style="margin-bottom: 10px;">
                <label class="lbl" style="font-size: 10px;">EXTRA DEDUCTIONS (W-4 STEP 4B) ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Expected deductions other than standard deduction to reduce paycheck tax withholding.</span></span></label>
                <div class="inp-wrap">
                    <input type="number" class="inp" id="wh-sim-deductions" value="0" style="padding: 6px 10px; font-size: 13px;">
                </div>
            </div>
        </div>

        <div class="field" style="margin-bottom: 15px;">
            <label class="lbl" style="font-size: 10px;">EXTRA WITHHOLDING PER PAYCHECK (W-4 STEP 4C) ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Additional flat dollar amount you want withheld from each paycheck.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="wh-sim-extra" value="0" style="padding: 6px 10px; font-size: 13px;">
            </div>
        </div>

        <div class="usc-alert-box blue" style="padding: 12px; font-size: 12px; text-align: left; margin-top: 10px;">
            <div>🆕 <strong>Simulated Withholding:</strong> <strong id="res-wh-sim-percheck">$0</strong> per paycheck (formerly <strong id="res-wh-orig-percheck">$0</strong>)</div>
            <div style="margin-top: 4px;">🎯 <strong>Annual Tax Credit Reduction:</strong> Saved <strong id="res-wh-sim-credits">$0</strong> in total tax liability!</div>
        </div>
    </div>
</div>';

            $data['js'] = '
function calculateWithholding() {
    const gross = parseFloat(document.getElementById("wh-gross").value) || 0;
    const freq = parseInt(document.getElementById("wh-freq").value) || 26;
    const status = document.getElementById("wh-status").value;

    const annualized = gross * freq;
    const stdDeductions = { single: 15000, married: 30000, head: 22500 };
    const stdDeduct = stdDeductions[status];
    const taxable = Math.max(0, annualized - stdDeduct);

    const brackets = {
        single: [
            { limit: 11925, rate: 0.10 },
            { limit: 48475, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250525, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        married: [
            { limit: 23850, rate: 0.10 },
            { limit: 96950, rate: 0.12 },
            { limit: 206700, rate: 0.22 },
            { limit: 394600, rate: 0.24 },
            { limit: 501050, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        head: [
            { limit: 17000, rate: 0.10 },
            { limit: 64850, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250500, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ]
    };

    let annualTax = 0;
    let tempTaxable = taxable;
    let prevLimit = 0;
    const activeBrackets = brackets[status];

    for (let i = 0; i < activeBrackets.length; i++) {
        const b = activeBrackets[i];
        const currentRange = b.limit - prevLimit;
        if (tempTaxable > currentRange) {
            annualTax += currentRange * b.rate;
            tempTaxable -= currentRange;
            prevLimit = b.limit;
        } else {
            annualTax += tempTaxable * b.rate;
            break;
        }
    }

    const perPaycheck = annualTax / freq;

    document.getElementById("res-wh-paycheck").innerText = "$" + Math.round(perPaycheck).toLocaleString();
    document.getElementById("res-wh-annual").innerText = "$" + Math.round(annualTax).toLocaleString();
    document.getElementById("res-wh-annual-gross").innerText = "$" + Math.round(annualized).toLocaleString();
    document.getElementById("res-wh-standard").innerText = "$" + stdDeduct.toLocaleString();
    document.getElementById("res-wh-taxable").innerText = "$" + Math.round(taxable).toLocaleString();

    updateWithholdingPlanner();

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}

function updateWithholdingPlanner() {
    const gross = parseFloat(document.getElementById("wh-gross").value) || 0;
    const freq = parseInt(document.getElementById("wh-freq").value) || 26;
    const status = document.getElementById("wh-status").value;

    const children = parseInt(document.getElementById("wh-sim-children").value) || 0;
    const otherDeps = parseInt(document.getElementById("wh-sim-other-deps").value) || 0;
    const otherInc = parseFloat(document.getElementById("wh-sim-other-inc").value) || 0;
    const deductions = parseFloat(document.getElementById("wh-sim-deductions").value) || 0;
    const extraPerCheck = parseFloat(document.getElementById("wh-sim-extra").value) || 0;

    const stdDeductions = { single: 15000, married: 30000, head: 22500 };
    const stdDeduct = stdDeductions[status];

    // Original tax
    const originalAnnual = gross * freq;
    const originalTaxable = Math.max(0, originalAnnual - stdDeduct);
    const brackets = {
        single: [
            { limit: 11925, rate: 0.10 },
            { limit: 48475, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250525, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        married: [
            { limit: 23850, rate: 0.10 },
            { limit: 96950, rate: 0.12 },
            { limit: 206700, rate: 0.22 },
            { limit: 394600, rate: 0.24 },
            { limit: 501050, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        head: [
            { limit: 17000, rate: 0.10 },
            { limit: 64850, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250500, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ]
    };

    function calculateBaseTax(taxableAmt) {
        let tax = 0;
        let tempTaxable = taxableAmt;
        let prevLimit = 0;
        const activeBrackets = brackets[status];
        for (let i = 0; i < activeBrackets.length; i++) {
            const b = activeBrackets[i];
            const currentRange = b.limit - prevLimit;
            if (tempTaxable > currentRange) {
                tax += currentRange * b.rate;
                tempTaxable -= currentRange;
                prevLimit = b.limit;
            } else {
                tax += tempTaxable * b.rate;
                break;
            }
        }
        return tax;
    }

    const origTax = calculateBaseTax(originalTaxable);
    const origPerCheck = origTax / freq;

    // Simulated tax
    const simAnnual = originalAnnual + otherInc;
    const simTaxable = Math.max(0, simAnnual - stdDeduct - deductions);
    let simTax = calculateBaseTax(simTaxable);
    
    // Apply credits
    const totalCredits = (children * 2000) + (otherDeps * 500);
    simTax = Math.max(0, simTax - totalCredits);

    const simPerCheck = (simTax / freq) + extraPerCheck;

    document.getElementById("res-wh-orig-percheck").innerText = "$" + Math.round(origPerCheck).toLocaleString();
    document.getElementById("res-wh-sim-percheck").innerText = "$" + Math.round(simPerCheck).toLocaleString();
    document.getElementById("res-wh-sim-credits").innerText = "$" + totalCredits.toLocaleString();
}

document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-wh");
    if (btn) {
        btn.addEventListener("click", calculateWithholding);
    }
    const inputs = ["wh-sim-children", "wh-sim-other-deps", "wh-sim-other-inc", "wh-sim-deductions", "wh-sim-extra"];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", updateWithholdingPlanner);
    });
});';
            break;

        case 'tax-bracket-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Find your highest marginal tax bracket rate and see how progressive federal tax is calculated.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 INCOME DETAILS</label></div>
    <div class="field">
        <label class="lbl">ANNUAL TAXABLE INCOME ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Your annual gross earnings minus standard or itemized deductions. This is the amount actually taxed.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="bracket-income" value="60000">
            <span class="inp-suffix">$</span>
        </div>
        <div class="hint">Adjust gross income by subtracting standard deduction ($15,750 Single)</div>
    </div>
    <div class="field">
        <label class="lbl">FILING STATUS <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Determines the threshold limits of your federal progressive tax brackets.</span></span></label>
        <div class="sel-wrap">
            <select id="bracket-status" class="sel">
                <option value="single">Single</option>
                <option value="married">Married Filing Jointly</option>
                <option value="head">Head of Household</option>
            </select>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-bracket" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Tax Bracket</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 TAX BRACKET STATUS</label></div>
    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-marginal-rate">12%</div>
            <div class="usc-other-result-label">Marginal Tax Bracket</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-effective-rate" style="color:var(--succ);">8.2%</div>
            <div class="usc-other-result-label">Effective Tax Rate</div>
        </div>
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:10px; color:var(--pri);">Progressive Tax Bracket Allocation</h4>
        <div id="bracket-filler-list" style="display:flex; flex-direction:column; gap:12px;">
            <!-- Generated via JS -->
        </div>
    </div>

    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Total Graduated Tax:</span>
            <strong id="res-total-grad-tax">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>Income taxed at highest rate:</span>
            <strong id="res-top-taxed">$0</strong>
        </div>
    </div>
</div>';

            $data['js'] = '
function calculateBracket() {
    const income = parseFloat(document.getElementById("bracket-income").value) || 0;
    const status = document.getElementById("bracket-status").value;

    const brackets = {
        single: [
            { limit: 11925, rate: 0.10 },
            { limit: 48475, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250525, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        married: [
            { limit: 23850, rate: 0.10 },
            { limit: 96950, rate: 0.12 },
            { limit: 206700, rate: 0.22 },
            { limit: 394600, rate: 0.24 },
            { limit: 501050, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ],
        head: [
            { limit: 17000, rate: 0.10 },
            { limit: 64850, rate: 0.12 },
            { limit: 103350, rate: 0.22 },
            { limit: 197300, rate: 0.24 },
            { limit: 250500, rate: 0.32 },
            { limit: 626350, rate: 0.35 },
            { limit: Infinity, rate: 0.37 }
        ]
    };

    let totalTax = 0;
    let tempIncome = income;
    let prevLimit = 0;
    let marginalRate = 0;
    let topTaxed = 0;
    const activeBrackets = brackets[status];
    let fillerHtml = "";

    // First calculate marginal rate & top taxed amount
    let calcIncome = income;
    let calcPrevLimit = 0;
    for (let i = 0; i < activeBrackets.length; i++) {
        const b = activeBrackets[i];
        const currentRange = b.limit - calcPrevLimit;
        if (calcIncome > currentRange) {
            calcIncome -= currentRange;
        } else {
            marginalRate = b.rate;
            break;
        }
        calcPrevLimit = b.limit;
    }

    for (let i = 0; i < activeBrackets.length; i++) {
        const b = activeBrackets[i];
        const currentRange = b.limit - prevLimit;
        
        let taxedAmount = 0;
        if (tempIncome > currentRange) {
            taxedAmount = currentRange;
            totalTax += currentRange * b.rate;
            tempIncome -= currentRange;
        } else {
            taxedAmount = tempIncome;
            totalTax += tempIncome * b.rate;
            tempIncome = 0;
            if (marginalRate === b.rate) {
                topTaxed = taxedAmount;
            }
        }

        const pct = b.limit === Infinity ? (taxedAmount > 0 ? 100 : 0) : (taxedAmount / currentRange) * 100;
        
        if (taxedAmount > 0 || prevLimit < income) {
            const isActive = (marginalRate === b.rate && income > 0);
            const activeStyle = isActive ? "border: 1.5px solid var(--pri); background: #eff6ff; padding: 10px; border-radius: 8px;" : "padding: 6px;";
            const badgeText = isActive ? " <span style=\"background:var(--pri); color:#fff; padding:2px 6px; border-radius:4px; font-size:9px; font-weight:700; margin-left: 6px;\">👉 YOUR BRACKET</span>" : "";
            const barColor = isActive ? "background: #3b82f6;" : (pct === 100 ? "var(--succ)" : "var(--pri)");

            fillerHtml += `
            <div style="display:flex; flex-direction:column; gap:4px; ${activeStyle}">
                <div style="display:flex; justify-content:space-between; font-size:11px;">
                    <span><strong>Bracket ${Math.round(b.rate * 100)}%</strong> ($${prevLimit.toLocaleString()} to ${b.limit === Infinity ? "∞" : "$" + b.limit.toLocaleString()})${badgeText}</span>
                    <span style="font-weight:700; color:var(--pri);">$${Math.round(taxedAmount).toLocaleString()} (${pct.toFixed(0)}% full)</span>
                </div>
                <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; width:100%;">
                    <div style="${barColor}; height:100%; width:${pct}%;"></div>
                </div>
            </div>`;
        }

        prevLimit = b.limit;
    }

    if (income <= 0) {
        marginalRate = 0;
    }

    const effective = income > 0 ? (totalTax / income) * 100 : 0;

    document.getElementById("res-marginal-rate").innerText = (marginalRate * 100) + "%";
    document.getElementById("res-effective-rate").innerText = effective.toFixed(2) + "%";
    document.getElementById("res-total-grad-tax").innerText = "$" + Math.round(totalTax).toLocaleString();
    document.getElementById("res-top-taxed").innerText = "$" + Math.round(topTaxed).toLocaleString();
    document.getElementById("bracket-filler-list").innerHTML = fillerHtml;

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-bracket");
    if (btn) {
        btn.addEventListener("click", calculateBracket);
    }
});';
            break;

        case 'estimated-tax-calculator':
            $data['html'] = '
<div class="info-box">
    📌 For freelancers, contractors, and self-employed. Calculates FICA equivalent self-employment tax and income tax.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 EARNINGS & PAYMENTS</label></div>
    <div class="field">
        <label class="lbl">ESTIMATED NET SELF-EMPLOYMENT PROFIT ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Your business gross income minus deductible business expenses (Schedule C Net Profit).</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="est-profit" value="50000">
            <span class="inp-suffix">$</span>
        </div>
        <div class="hint">Schedule C net profit (total business receipts minus expenses)</div>
    </div>
    <div class="field">
        <label class="lbl">PREVIOUS ESTIMATED PAYMENTS MADE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Any quarterly estimated tax payments you have already made to the IRS for this tax year.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="est-paid" value="0">
            <span class="inp-suffix">$</span>
        </div>
        <div class="hint">Enter total estimated tax payments sent to the IRS so far this year</div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-est" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Estimated Tax</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 ESTIMATED TAX SUMMARY</label></div>
    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-est-se">$0</div>
            <div class="usc-other-result-label">Self-Employment Tax</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-est-quarterly" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Estimated Quarterly Payment</div>
        </div>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Estimated Income Tax:</span>
            <strong id="res-est-income-tax">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Total Combined Annual Tax:</span>
            <strong id="res-est-total-annual">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>Remaining Estimated Balance Due:</span>
            <strong id="res-est-remaining">$0</strong>
        </div>
    </div>
    <div style="margin-top: 25px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:10px; color:var(--pri);">📅 4-QUARTER ESTIMATED TAX DEADLINES</h4>
        <div style="overflow-x: auto;">
            <table class="usc-custom-table" style="width: 100%; border-collapse: collapse; font-size:12px;">
                <thead>
                    <tr>
                        <th style="color:var(--lbl);">Voucher</th>
                        <th style="color:var(--lbl);">Due Date</th>
                        <th style="color:var(--lbl);">Estimated Amount</th>
                        <th style="color:var(--lbl);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Voucher 1 (Q1)</strong></td>
                        <td>April 15, 2026</td>
                        <td id="res-est-q1">$0</td>
                        <td><label class="chk-label" style="display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Paid</label></td>
                    </tr>
                    <tr>
                        <td><strong>Voucher 2 (Q2)</strong></td>
                        <td>June 15, 2026</td>
                        <td id="res-est-q2">$0</td>
                        <td><label class="chk-label" style="display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Paid</label></td>
                    </tr>
                    <tr>
                        <td><strong>Voucher 3 (Q3)</strong></td>
                        <td>September 15, 2026</td>
                        <td id="res-est-q3">$0</td>
                        <td><label class="chk-label" style="display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Paid</label></td>
                    </tr>
                    <tr>
                        <td><strong>Voucher 4 (Q4)</strong></td>
                        <td>January 15, 2027</td>
                        <td id="res-est-q4">$0</td>
                        <td><label class="chk-label" style="display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Paid</label></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>';

            $data['js'] = '
function calculateEstimatedTax() {
    const profit = parseFloat(document.getElementById("est-profit").value) || 0;
    const paid = parseFloat(document.getElementById("est-paid").value) || 0;

    // Self-employment tax calculation (15.3% on 92.35% of earnings)
    const seTaxable = profit * 0.9235;
    const ssPortion = Math.min(seTaxable, 176100) * 0.124;
    const medPortion = seTaxable * 0.029;
    const totalSeTax = ssPortion + medPortion;

    // 50% of SE tax is deductible
    const seDeduction = totalSeTax / 2;

    // Standard deduction Single
    const stdDeduct = 15000;
    const taxableIncome = Math.max(0, profit - seDeduction - stdDeduct);

    // Progressive federal bracket single
    const brackets = [
        { limit: 11925, rate: 0.10 },
        { limit: 48475, rate: 0.12 },
        { limit: 103350, rate: 0.22 },
        { limit: 197300, rate: 0.24 },
        { limit: 250525, rate: 0.32 },
        { limit: 626350, rate: 0.35 },
        { limit: Infinity, rate: 0.37 }
    ];

    let incomeTax = 0;
    let tempTaxable = taxableIncome;
    let prevLimit = 0;
    for (let i = 0; i < brackets.length; i++) {
        const b = brackets[i];
        const currentRange = b.limit - prevLimit;
        if (tempTaxable > currentRange) {
            incomeTax += currentRange * b.rate;
            tempTaxable -= currentRange;
            prevLimit = b.limit;
        } else {
            incomeTax += tempTaxable * b.rate;
            break;
        }
    }

    const totalAnnual = totalSeTax + incomeTax;
    const remaining = Math.max(0, totalAnnual - paid);
    const quarterly = remaining / 4;

    document.getElementById("res-est-se").innerText = "$" + Math.round(totalSeTax).toLocaleString();
    document.getElementById("res-est-quarterly").innerText = "$" + Math.round(quarterly).toLocaleString();
    document.getElementById("res-est-income-tax").innerText = "$" + Math.round(incomeTax).toLocaleString();
    document.getElementById("res-est-total-annual").innerText = "$" + Math.round(totalAnnual).toLocaleString();
    document.getElementById("res-est-remaining").innerText = "$" + Math.round(remaining).toLocaleString();

    document.getElementById("res-est-q1").innerText = "$" + Math.round(quarterly).toLocaleString();
    document.getElementById("res-est-q2").innerText = "$" + Math.round(quarterly).toLocaleString();
    document.getElementById("res-est-q3").innerText = "$" + Math.round(quarterly).toLocaleString();
    document.getElementById("res-est-q4").innerText = "$" + Math.round(quarterly).toLocaleString();

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-est");
    if (btn) {
        btn.addEventListener("click", calculateEstimatedTax);
    }
});';
            break;

        case 'capital-gains-tax-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Estimate and compare capital gains tax liability on investment sales for both short-term and long-term holding periods.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 INVESTMENT DETAILS</label></div>
    <div class="field">
        <label class="lbl">TOTAL SELLING PRICE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The total gross amount received from selling your investment asset (stocks, crypto, real estate).</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="cg-sale" value="10000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">COST BASIS / PURCHASE PRICE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The original purchase price of the asset plus commissions, fees, or improvements.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="cg-basis" value="6000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">FILING STATUS <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Determines the thresholds of your progressive capital gains tax rates.</span></span></label>
        <div class="sel-wrap">
            <select id="cg-status" class="sel">
                <option value="single">Single</option>
                <option value="married">Married Filing Jointly</option>
            </select>
        </div>
    </div>
    <div class="field">
        <label class="lbl">OTHER ANNUAL TAXABLE INCOME ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Your ordinary taxable income, which determines the rate bracket your capital gains fall into.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="cg-income" value="50000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-cg" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Capital Gains</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 CAPITAL GAINS RESULTS</label></div>

    <div id="cg-savings-box" class="usc-alert-box green" style="display:none; margin-bottom: 20px; font-weight: 600;"></div>

    <div class="grid2" style="margin-bottom: 20px; gap: 15px;">
        <div class="usc-other-result-card" style="border: 2px solid var(--succ); background: #f0fdf4;">
            <div style="font-weight: 800; font-size: 13px; color: var(--succ); text-transform: uppercase; margin-bottom: 8px;">Long-Term (> 1 Year)</div>
            <div class="usc-other-result-value" id="res-cg-tax-long" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Est. Tax Owed</div>
            <div style="font-size: 12px; margin-top: 6px; color: var(--mt);">Net Profit: <strong id="res-cg-payout-long">$0</strong></div>
            <div style="font-size: 11px; color: var(--mt);">Rate: <span id="res-cg-rate-long">0%</span></div>
        </div>
        <div class="usc-other-result-card" style="border: 1px solid var(--bdr);">
            <div style="font-weight: 800; font-size: 13px; color: #ef4444; text-transform: uppercase; margin-bottom: 8px;">Short-Term (≤ 1 Year)</div>
            <div class="usc-other-result-value" id="res-cg-tax-short" style="color:#ef4444;">$0</div>
            <div class="usc-other-result-label">Est. Tax Owed</div>
            <div style="font-size: 12px; margin-top: 6px; color: var(--mt);">Net Profit: <strong id="res-cg-payout-short">$0</strong></div>
            <div style="font-size: 11px; color: var(--mt);">Rate: <span id="res-cg-rate-short">0%</span></div>
        </div>
    </div>
    
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>Gross Capital Gain:</span>
            <strong id="res-cg-gain">$0</strong>
        </div>
        <div style="display:none; justify-content:space-between; padding: 6px 0; color:#b45309;" id="row-cg-niit">
            <span>Net Investment Income Tax (3.8% NIIT):</span>
            <strong id="res-cg-niit">$0</strong>
        </div>
        <div style="font-size:11px; color: var(--mt); padding-top: 4px;" id="note-cg-stack">Long-term gains are stacked on top of your other taxable income across the 0% / 15% / 20% brackets. Short-term gains are taxed as ordinary income.</div>
    </div>
    
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 20px 0; gap: 10px; border-top: 1px solid var(--bdr); padding-top: 15px;">
        <svg width="280" height="200" id="cg-bar-svg" style="border: 1px solid var(--bdr); border-radius: 6px; padding: 15px; background: rgba(0,0,0,0.01);">
            <!-- Svg content populated dynamically -->
        </svg>
        <div style="font-size: 11px; color: var(--mt); font-weight: 600;">Comparative Tax Liability ($)</div>
    </div>
</div>';

            $data['js'] = '
function drawCgBars(shortTax, longTax) {
    const svg = document.getElementById("cg-bar-svg");
    if (!svg) return;
    svg.innerHTML = "";

    const maxTax = Math.max(shortTax, longTax, 100);
    const width = 280;
    const height = 200;
    
    const padding = 40;
    const barWidth = 60;
    
    const longHeight = (longTax / maxTax) * (height - 80);
    const shortHeight = (shortTax / maxTax) * (height - 80);

    const xLong = padding;
    const xShort = width - padding - barWidth;

    const yLong = height - 40 - longHeight;
    const yShort = height - 40 - shortHeight;

    const rectLong = document.createElementNS("http://www.w3.org/2000/svg", "circle"); // fallback to rect
    const rectLongEl = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rectLongEl.setAttribute("x", xLong);
    rectLongEl.setAttribute("y", yLong);
    rectLongEl.setAttribute("width", barWidth);
    rectLongEl.setAttribute("height", longHeight);
    rectLongEl.setAttribute("fill", "#10b981");
    rectLongEl.setAttribute("rx", "4");
    rectLongEl.style.cursor = "pointer";

    const rectShortEl = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rectShortEl.setAttribute("x", xShort);
    rectShortEl.setAttribute("y", yShort);
    rectShortEl.setAttribute("width", barWidth);
    rectShortEl.setAttribute("height", shortHeight);
    rectShortEl.setAttribute("fill", "#ef4444");
    rectShortEl.setAttribute("rx", "4");
    rectShortEl.style.cursor = "pointer";

    const textLongVal = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textLongVal.setAttribute("x", xLong + barWidth/2);
    textLongVal.setAttribute("y", yLong - 8);
    textLongVal.setAttribute("text-anchor", "middle");
    textLongVal.setAttribute("fill", "#10b981");
    textLongVal.setAttribute("font-size", "11px");
    textLongVal.setAttribute("font-weight", "bold");
    textLongVal.textContent = "$" + Math.round(longTax).toLocaleString();

    const textShortVal = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textShortVal.setAttribute("x", xShort + barWidth/2);
    textShortVal.setAttribute("y", yShort - 8);
    textShortVal.setAttribute("text-anchor", "middle");
    textShortVal.setAttribute("fill", "#ef4444");
    textShortVal.setAttribute("font-size", "11px");
    textShortVal.setAttribute("font-weight", "bold");
    textShortVal.textContent = "$" + Math.round(shortTax).toLocaleString();

    const textLongLabel = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textLongLabel.setAttribute("x", xLong + barWidth/2);
    textLongLabel.setAttribute("y", height - 20);
    textLongLabel.setAttribute("text-anchor", "middle");
    textLongLabel.setAttribute("fill", "var(--lbl)");
    textLongLabel.setAttribute("font-size", "10px");
    textLongLabel.setAttribute("font-weight", "600");
    textLongLabel.textContent = "Long-Term";

    const textShortLabel = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textShortLabel.setAttribute("x", xShort + barWidth/2);
    textShortLabel.setAttribute("y", height - 20);
    textShortLabel.setAttribute("text-anchor", "middle");
    textShortLabel.setAttribute("fill", "var(--lbl)");
    textShortLabel.setAttribute("font-size", "10px");
    textShortLabel.setAttribute("font-weight", "600");
    textShortLabel.textContent = "Short-Term";

    svg.appendChild(rectLongEl);
    svg.appendChild(rectShortEl);
    svg.appendChild(textLongVal);
    svg.appendChild(textShortVal);
    svg.appendChild(textLongLabel);
    svg.appendChild(textShortLabel);
}

function calculateCapitalGains() {
    const sale = parseFloat(document.getElementById("cg-sale").value) || 0;
    const basis = parseFloat(document.getElementById("cg-basis").value) || 0;
    const status = document.getElementById("cg-status").value;
    const otherIncome = Math.max(0, parseFloat(document.getElementById("cg-income").value) || 0);

    const gain = Math.max(0, sale - basis);

    // Ordinary 2025 federal brackets (short-term gains taxed as ordinary income, stacked on top of other income)
    const ordBrackets = (status === "married") ? [
        { limit: 23850, rate: 0.10 }, { limit: 96950, rate: 0.12 }, { limit: 206700, rate: 0.22 },
        { limit: 394600, rate: 0.24 }, { limit: 501050, rate: 0.32 }, { limit: 626350, rate: 0.35 }, { limit: Infinity, rate: 0.37 }
    ] : [
        { limit: 11925, rate: 0.10 }, { limit: 48475, rate: 0.12 }, { limit: 103350, rate: 0.22 },
        { limit: 197300, rate: 0.24 }, { limit: 250525, rate: 0.32 }, { limit: 626350, rate: 0.35 }, { limit: Infinity, rate: 0.37 }
    ];
    function ordTax(income) {
        let t = 0, prev = 0;
        for (let i = 0; i < ordBrackets.length; i++) {
            const lim = ordBrackets[i].limit, rate = ordBrackets[i].rate;
            if (income > lim) { t += (lim - prev) * rate; prev = lim; }
            else { t += (income - prev) * rate; break; }
        }
        return t;
    }

    // SHORT-TERM: marginal ordinary tax on the gain stacked on top of other taxable income
    const shortTaxOrd = Math.max(0, ordTax(otherIncome + gain) - ordTax(otherIncome));

    // LONG-TERM: stack the gain across the 0% / 15% / 20% bands (2025 thresholds)
    const t0 = (status === "married") ? 96700 : 48350;
    const t15 = (status === "married") ? 600050 : 533400;
    let stackStart = otherIncome;
    let remaining = gain;
    const band0 = Math.max(0, t0 - stackStart);
    const amt0 = Math.min(remaining, band0); remaining -= amt0; stackStart += amt0;
    const band15 = Math.max(0, t15 - stackStart);
    const amt15 = Math.min(remaining, band15); remaining -= amt15; stackStart += amt15;
    const amt20 = remaining;
    const longTaxBase = amt15 * 0.15 + amt20 * 0.20;

    // NIIT: 3.8% on the lesser of the gain or MAGI over the threshold (applies to both holding types)
    const niitThreshold = (status === "married") ? 250000 : 200000;
    const magi = otherIncome + gain;
    const niitBase = Math.max(0, Math.min(gain, magi - niitThreshold));
    const niit = niitBase * 0.038;

    const shortTax = shortTaxOrd + niit;
    const longTax = longTaxBase + niit;
    const shortPayout = gain - shortTax;
    const longPayout = gain - longTax;
    const shortEff = gain > 0 ? (shortTax / gain) * 100 : 0;
    const longEff = gain > 0 ? (longTax / gain) * 100 : 0;
    const savings = Math.max(0, shortTax - longTax);

    document.getElementById("res-cg-gain").innerText = "$" + Math.round(gain).toLocaleString();

    document.getElementById("res-cg-tax-long").innerText = "$" + Math.round(longTax).toLocaleString();
    document.getElementById("res-cg-payout-long").innerText = "$" + Math.round(longPayout).toLocaleString();
    document.getElementById("res-cg-rate-long").innerText = longEff.toFixed(1) + "% eff.";

    document.getElementById("res-cg-tax-short").innerText = "$" + Math.round(shortTax).toLocaleString();
    document.getElementById("res-cg-payout-short").innerText = "$" + Math.round(shortPayout).toLocaleString();
    document.getElementById("res-cg-rate-short").innerText = shortEff.toFixed(1) + "% eff.";

    const niitRow = document.getElementById("row-cg-niit");
    if (niitRow) {
        if (niit > 0) {
            niitRow.style.display = "flex";
            document.getElementById("res-cg-niit").innerText = "$" + Math.round(niit).toLocaleString();
        } else {
            niitRow.style.display = "none";
            document.getElementById("res-cg-niit").innerText = "$0";
        }
    }

    const boxEl = document.getElementById("cg-savings-box");
    if (savings > 0) {
        boxEl.style.display = "block";
        boxEl.innerHTML = `🎉 <strong>Holding Period Savings:</strong> You save approximately <strong>$${Math.round(savings).toLocaleString()}</strong> by holding this asset for more than 1 year (long-term) instead of selling short-term.`;
    } else {
        boxEl.style.display = "none";
    }

    drawCgBars(shortTax, longTax);

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-cg");
    if (btn) {
        btn.addEventListener("click", calculateCapitalGains);
    }
});';
            break;

        case 'self-employment-tax-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Computes standard 15.3% Social Security & Medicare taxes for 1099 contractors.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 BUSINESS EARNINGS</label></div>
    <div class="field">
        <label class="lbl">NET SELF-EMPLOYMENT EARNINGS ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Gross income generated from self-employment activities before expenses.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="se-earnings" value="60000">
            <span class="inp-suffix">$</span>
        </div>
        <div class="hint">Total net business income before deductions</div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-se" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Self-Employment Tax</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 SELF-EMPLOYMENT TAX SUMMARY</label></div>
    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-se-total">$0</div>
            <div class="usc-other-result-label">Total SE Tax Owed</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-se-deduct" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Above-the-Line Deduction</div>
        </div>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Taxable Self-Employment Income (92.35%):</span>
            <strong id="res-se-taxable">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Social Security Portion (12.4%):</span>
            <strong id="res-se-ss">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>Medicare Portion (2.9%):</span>
            <strong id="res-se-med">$0</strong>
        </div>
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <div style="font-weight: 700; font-size: 13px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">💼 Write-Off Deduction Planner</div>
        <p style="font-size: 11px; color: var(--mt); margin: 0 0 12px;">Simulate business expenses (like home office, tools, travel) to see your tax savings:</p>
        <div class="field" style="margin-bottom: 12px;">
            <label class="lbl" style="font-size: 10px;">ESTIMATED BUSINESS EXPENSES ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Write-offs like home office, travel, internet, or software that reduce your net profit.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="se-expenses-sim" value="5000" style="padding: 6px 10px; font-size: 13px;">
                <span class="inp-suffix">$</span>
            </div>
        </div>
        <div class="usc-alert-box green" style="padding: 10px 12px; font-size: 12px; text-align: left;">
            🎉 <strong>Estimated Tax Savings:</strong> You would save approximately <strong id="se-sim-savings">$706</strong> in self-employment taxes by claiming these write-offs!
        </div>
    </div>
</div>';

            $data['js'] = '
function calculateSE() {
    const earnings = parseFloat(document.getElementById("se-earnings").value) || 0;

    const taxable = earnings * 0.9235;
    const ssPortion = Math.min(taxable, 176100) * 0.124;
    const medPortion = taxable * 0.029;
    const totalTax = ssPortion + medPortion;
    const deduction = totalTax / 2;

    document.getElementById("res-se-total").innerText = "$" + Math.round(totalTax).toLocaleString();
    document.getElementById("res-se-deduct").innerText = "$" + Math.round(deduction).toLocaleString();
    document.getElementById("res-se-taxable").innerText = "$" + Math.round(taxable).toLocaleString();
    document.getElementById("res-se-ss").innerText = "$" + Math.round(ssPortion).toLocaleString();
    document.getElementById("res-se-med").innerText = "$" + Math.round(medPortion).toLocaleString();

    updateSeDeductionPlanner();

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}

function updateSeDeductionPlanner() {
    const earnings = parseFloat(document.getElementById("se-earnings").value) || 0;
    const simExpenses = parseFloat(document.getElementById("se-expenses-sim").value) || 0;
    
    // Original tax
    const taxable1 = earnings * 0.9235;
    const ss1 = Math.min(taxable1, 176100) * 0.124;
    const med1 = taxable1 * 0.029;
    const tax1 = ss1 + med1;
    
    // Tax after expenses
    const profit2 = Math.max(0, earnings - simExpenses);
    const taxable2 = profit2 * 0.9235;
    const ss2 = Math.min(taxable2, 176100) * 0.124;
    const med2 = taxable2 * 0.029;
    const tax2 = ss2 + med2;
    
    const savings = Math.max(0, tax1 - tax2);
    document.getElementById("se-sim-savings").innerText = "$" + Math.round(savings).toLocaleString();
}

document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-se");
    if (btn) {
        btn.addEventListener("click", calculateSE);
    }
    const simInp = document.getElementById("se-expenses-sim");
    if (simInp) {
        simInp.addEventListener("input", updateSeDeductionPlanner);
    }
});';
            break;

        case 'payroll-tax-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Breakdown employee tax withholdings and employer payroll matching liabilities.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 WAGES DETAILS</label></div>
    <div class="field">
        <label class="lbl">EMPLOYEE GROSS SALARY ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The worker’s total annual earnings before taxes and benefits. Used to compute employee FICA and employer payroll tax.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="pr-salary" value="50000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-pr" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Payroll Tax</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 PAYROLL LIABILITIES BREAKDOWN</label></div>
    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-pr-employee">$0</div>
            <div class="usc-other-result-label">Employee FICA Withheld</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-pr-employer" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Employer matching + FUTA/SUTA</div>
        </div>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Social Security (Employee 6.2%):</span>
            <strong id="res-pr-ess">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Medicare (Employee 1.45%):</span>
            <strong id="res-pr-emed">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Social Security (Employer 6.2%):</span>
            <strong id="res-pr-rss">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Medicare (Employer 1.45%):</span>
            <strong id="res-pr-rmed">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Federal Unemployment (Employer FUTA 0.6%):</span>
            <strong id="res-pr-futa">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>State Unemployment (Employer SUTA 2.7% est):</span>
            <strong id="res-pr-suta">$0</strong>
        </div>
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <div style="font-weight: 700; font-size: 13px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">Employer Cost vs Employee Take-Home</div>
        <div style="display: flex; height: 24px; border-radius: 6px; overflow: hidden; background: #e5e7eb; margin-bottom: 15px;" id="pr-chart-container">
            <div id="pr-bar-net" style="background: var(--succ); transition: width 0.3s;" title="Employee Take-Home"></div>
            <div id="pr-bar-fica-ee" style="background: var(--pri); transition: width 0.3s;" title="Employee FICA"></div>
            <div id="pr-bar-fica-er" style="background: var(--or); transition: width 0.3s;" title="Employer Payroll Burden"></div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 11px; color: var(--mt);">
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 10px; height: 10px; background: var(--succ); border-radius: 2px;"></span>
                <span>Net Take-home: <strong id="lbl-pr-net">$0</strong> (<span id="pct-pr-net">0%</span>)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 10px; height: 10px; background: var(--pri); border-radius: 2px;"></span>
                <span>Employee Tax: <strong id="lbl-pr-fica-ee">$0</strong> (<span id="pct-pr-fica-ee">0%</span>)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 10px; height: 10px; background: var(--or); border-radius: 2px;"></span>
                <span>Employer Burden: <strong id="lbl-pr-fica-er">$0</strong> (<span id="pct-pr-fica-er">0%</span>)</span>
            </div>
        </div>
    </div>
</div>';

            $data['js'] = '
function calculatePayroll() {
    const salary = parseFloat(document.getElementById("pr-salary").value) || 0;

    // Employee FICA
    const ssTaxable = Math.min(salary, 176100);
    const ssEmployee = ssTaxable * 0.062;
    const medEmployee = salary * 0.0145;
    const employeeFica = ssEmployee + medEmployee;

    // Employer matching
    const ssEmployer = ssEmployee;
    const medEmployer = medEmployee;
    const futa = Math.min(salary, 7000) * 0.006;
    const suta = Math.min(salary, 10000) * 0.027;
    const employerTotal = ssEmployer + medEmployer + futa + suta;

    document.getElementById("res-pr-employee").innerText = "$" + Math.round(employeeFica).toLocaleString();
    document.getElementById("res-pr-employer").innerText = "$" + Math.round(employerTotal).toLocaleString();
    document.getElementById("res-pr-ess").innerText = "$" + Math.round(ssEmployee).toLocaleString();
    document.getElementById("res-pr-emed").innerText = "$" + Math.round(medEmployee).toLocaleString();
    document.getElementById("res-pr-rss").innerText = "$" + Math.round(ssEmployer).toLocaleString();
    document.getElementById("res-pr-rmed").innerText = "$" + Math.round(medEmployer).toLocaleString();
    document.getElementById("res-pr-futa").innerText = "$" + Math.round(futa).toLocaleString();
    document.getElementById("res-pr-suta").innerText = "$" + Math.round(suta).toLocaleString();

    const netTakehome = salary - employeeFica;
    const totalCost = salary + employerTotal;
    const pctNet = (totalCost > 0) ? (netTakehome / totalCost) * 100 : 0;
    const pctEE = (totalCost > 0) ? (employeeFica / totalCost) * 100 : 0;
    const pctER = (totalCost > 0) ? (employerTotal / totalCost) * 100 : 0;

    document.getElementById("pr-bar-net").style.width = pctNet + "%";
    document.getElementById("pr-bar-fica-ee").style.width = pctEE + "%";
    document.getElementById("pr-bar-fica-er").style.width = pctER + "%";

    document.getElementById("lbl-pr-net").innerText = "$" + Math.round(netTakehome).toLocaleString();
    document.getElementById("lbl-pr-fica-ee").innerText = "$" + Math.round(employeeFica).toLocaleString();
    document.getElementById("lbl-pr-fica-er").innerText = "$" + Math.round(employerTotal).toLocaleString();

    document.getElementById("pct-pr-net").innerText = pctNet.toFixed(1) + "%";
    document.getElementById("pct-pr-fica-ee").innerText = pctEE.toFixed(1) + "%";
    document.getElementById("pct-pr-fica-er").innerText = pctER.toFixed(1) + "%";

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-pr");
    if (btn) {
        btn.addEventListener("click", calculatePayroll);
    }
});';
            break;

        case 'sales-tax-calculator':
            $data['html'] = '
<div class="info-box">
    📌 General retail sales tax calculator. Add multiple items to your cart, specify the combined tax rate, and categorize items to apply exemptions.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">🔄 REVERSE SALES TAX (TAX-INCLUSIVE TOTAL → PRE-TAX PRICE)</label></div>
    <div class="grid2">
        <div class="field">
            <label class="lbl">TAX-INCLUSIVE TOTAL ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The final amount you paid including sales tax. We back out the pre-tax price and the tax portion.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="gen-rev-total" value="106.00" step="0.01">
                <span class="inp-suffix">$</span>
            </div>
        </div>
        <div class="field">
            <label class="lbl">COMBINED SALES TAX RATE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The combined state plus local sales tax rate that was applied to the purchase.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="gen-rev-rate" value="6.0" step="0.01">
                <span class="inp-suffix">%</span>
            </div>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-gen-rev" style="width: 100%; margin-top: 10px; font-weight:700;">Reverse Calculate Pre-Tax Price</button>
    <div class="grid2" id="gen-rev-results" style="display:none; margin-top: 15px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-gen-rev-pretax">$0.00</div>
            <div class="usc-other-result-label">Pre-Tax Price</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-gen-rev-tax" style="color:var(--pri);">$0.00</div>
            <div class="usc-other-result-label">Sales Tax Portion</div>
        </div>
    </div>
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">🛒 SHOPPING CART / RECEIPT BUILDER</label></div>
    <div class="field">
        <label class="lbl">COMBINED SALES TAX RATE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The total combined percentage rate of state, county, and municipal sales taxes.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="gen-sales-rate" value="6.0" step="0.01">
            <span class="inp-suffix">%</span>
        </div>
    </div>
    <div style="margin-top: 15px; overflow-x: auto;">
        <table class="usc-custom-table" style="width:100%; border-collapse:collapse; font-size:12px; margin-bottom: 10px;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 6px; color:var(--lbl);">Item Description</th>
                    <th style="text-align: left; padding: 6px; width: 140px; color:var(--lbl);">Category</th>
                    <th style="text-align: left; padding: 6px; width: 90px; color:var(--lbl);">Price ($)</th>
                    <th style="text-align: left; padding: 6px; width: 60px; color:var(--lbl);">Qty</th>
                    <th style="text-align: center; padding: 6px; width: 50px; color:var(--lbl);">Action</th>
                </tr>
            </thead>
            <tbody id="gen-sales-cart-tbody">
                <!-- JS will populate rows -->
            </tbody>
        </table>
        <button type="button" class="calc-btn" id="btn-gen-sales-add-item" style="background:var(--succ); width:auto; padding:6px 12px; font-size:12px; margin-bottom:15px; font-weight:700;">➕ Add Item</button>
    </div>
    <div class="field">
        <label class="lbl">GLOBAL DISCOUNT PERCENTAGE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Discount rate applied to the shopping cart items subtotal before taxing.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="gen-sales-discount" value="0" min="0" max="100">
            <span class="inp-suffix">%</span>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-gensales" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Sales Tax</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 SALES TAX SUMMARY</label></div>
    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-gen-sales-tax">$0.00</div>
            <div class="usc-other-result-label">Sales Tax Amount</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-gen-sales-total" style="color:var(--succ);">$0.00</div>
            <div class="usc-other-result-label">Total Payout Cost</div>
        </div>
    </div>
    <div style="overflow-x: auto; margin-bottom: 15px;">
        <table class="usc-custom-table" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr>
                    <th style="text-align:left; color:var(--lbl);">Item</th>
                    <th style="text-align:left; color:var(--lbl);">Net Subtotal</th>
                    <th style="text-align:left; color:var(--lbl);">Tax Rate</th>
                    <th style="text-align:left; color:var(--lbl);">Tax Paid</th>
                    <th style="text-align:left; color:var(--lbl);">Total Cost</th>
                </tr>
            </thead>
            <tbody id="gen-sales-invoice-tbody">
                <!-- Dynamically populated -->
            </tbody>
        </table>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Pre-Tax Value:</span>
            <strong id="res-gen-pretax">$0.00</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Discount:</span>
            <strong id="res-gen-discount">$0.00</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>Discounted Net Value:</span>
            <strong id="res-gen-net">$0.00</strong>
        </div>
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:10px; color:var(--pri);">🛍️ LOCAL RATE VARIATIONS & TAX HOLIDAYS</h4>
        <p style="font-size: 11px; color: var(--mt); margin: 0 0 12px;">Your combined rate is compared below to high-tax cities and states with tax holidays:</p>
        
        <div style="display: flex; flex-direction: column; gap: 8px; font-size:12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: #f8fafc; border: 1px solid var(--bdr); border-radius: 6px;">
                <span>Zero Sales Tax States (OR, DE, NH, MT, AK)</span>
                <span style="background: var(--succ); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700;">0.0% Rate</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: #f8fafc; border: 1px solid var(--bdr); border-radius: 6px;">
                <span>US Average Sales Tax Rate</span>
                <span style="background: var(--pri); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700;">~6.0% Rate</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: #f8fafc; border: 1px solid var(--bdr); border-radius: 6px;">
                <span>High Sales Tax Cities (e.g., Chicago, IL; Seattle, WA)</span>
                <span style="background: #ef4444; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700;">10.25% Rate</span>
            </div>
        </div>

        <div class="usc-alert-box blue" style="padding: 10px 12px; font-size: 11px; margin-top: 15px; text-align: left;">
            💡 <strong>Tax Holiday Tip:</strong> Many states host "Back-to-School" tax holidays in August, exempting clothing (under $100) and school supplies from state sales tax. Check your local state guidelines to plan your purchases!
        </div>
    </div>
</div>';

            $data['js'] = '
let genCartItems = [
    { name: "Electronics", category: "general", price: 500, qty: 1 },
    { name: "Fresh Produce", category: "groceries", price: 30, qty: 2 },
    { name: "First Aid Kit", category: "medicine", price: 15, qty: 1 }
];

function renderGenCart() {
    const tbody = document.getElementById("gen-sales-cart-tbody");
    if (!tbody) return;
    tbody.innerHTML = "";
    genCartItems.forEach((item, index) => {
        tbody.innerHTML += `
        <tr>
            <td style="padding: 6px;"><input type="text" class="inp" style="padding:4px 8px; font-size:12px;" value="${item.name}" onchange="window.updateGenCartItem(${index}, &quot;name&quot;, this.value)"></td>
            <td style="padding: 6px;">
                <div class="sel-wrap" style="height:30px; border:1px solid var(--bdr); border-radius:4px;">
                    <select class="sel" style="padding:4px 8px; font-size:12px; height:30px; line-height:30px; border:none; background:transparent;" onchange="window.updateGenCartItem(${index}, &quot;category&quot;, this.value)">
                        <option value="general" ${item.category === "general" ? "selected" : ""}>General Goods</option>
                        <option value="groceries" ${item.category === "groceries" ? "selected" : ""}>Groceries</option>
                        <option value="medicine" ${item.category === "medicine" ? "selected" : ""}>Prescription Medicine</option>
                        <option value="clothing" ${item.category === "clothing" ? "selected" : ""}>Clothing</option>
                    </select>
                </div>
            </td>
            <td style="padding: 6px;"><input type="number" class="inp" style="padding:4px 8px; font-size:12px;" value="${item.price}" onchange="window.updateGenCartItem(${index}, &quot;price&quot;, parseFloat(this.value) || 0)"></td>
            <td style="padding: 6px;"><input type="number" class="inp" style="padding:4px 8px; font-size:12px;" value="${item.qty}" onchange="window.updateGenCartItem(${index}, &quot;qty&quot;, parseInt(this.value) || 0)"></td>
            <td style="padding: 6px; text-align: center;"><button type="button" style="background:#ef4444; color:#fff; border:none; border-radius:4px; padding:4px 8px; cursor:pointer;" onclick="window.deleteGenCartItem(${index})">❌</button></td>
        </tr>`;
    });
}

window.updateGenCartItem = function(index, field, value) {
    if (genCartItems[index]) {
        genCartItems[index][field] = value;
    }
};

window.deleteGenCartItem = function(index) {
    genCartItems.splice(index, 1);
    renderGenCart();
};

function calculateGenReverse() {
    const total = parseFloat(document.getElementById("gen-rev-total").value) || 0;
    const rate = (parseFloat(document.getElementById("gen-rev-rate").value) || 0) / 100;
    const preTax = (1 + rate) > 0 ? total / (1 + rate) : total;
    const tax = total - preTax;
    document.getElementById("res-gen-rev-pretax").innerText = "$" + preTax.toFixed(2);
    document.getElementById("res-gen-rev-tax").innerText = "$" + tax.toFixed(2);
    document.getElementById("gen-rev-results").style.display = "grid";
}

function calculateGenSales() {
    const rate = parseFloat(document.getElementById("gen-sales-rate").value) || 0;
    const discountPercent = parseFloat(document.getElementById("gen-sales-discount").value) || 0;

    const discountFactor = Math.max(0, 1 - discountPercent / 100);
    const combinedRate = rate / 100;

    let totalPreTax = 0;
    let totalDiscountedSubtotal = 0;
    let totalTaxAmount = 0;
    let invoiceHtml = "";

    genCartItems.forEach(item => {
        const preTaxVal = item.price * item.qty;
        const netVal = preTaxVal * discountFactor;

        // Apply category exemptions relative to combined rate
        let appliedRate = combinedRate;
        if (item.category === "groceries") {
            appliedRate = combinedRate > 0.04 ? 0.01 : 0;
        } else if (item.category === "medicine") {
            appliedRate = 0;
        } else if (item.category === "clothing") {
            appliedRate = combinedRate * 0.5;
        }

        const itemTax = netVal * appliedRate;
        const itemTotalCost = netVal + itemTax;

        totalPreTax += preTaxVal;
        totalDiscountedSubtotal += netVal;
        totalTaxAmount += itemTax;

        invoiceHtml += `<tr>
            <td><strong>${item.name}</strong> <span style="font-size:10px; color:var(--mt);">(${item.category})</span></td>
            <td>$${netVal.toFixed(2)}</td>
            <td>${(appliedRate*100).toFixed(2)}%</td>
            <td>$${itemTax.toFixed(2)}</td>
            <td style="font-weight:700;">$${itemTotalCost.toFixed(2)}</td>
        </tr>`;
    });

    const totalCost = totalDiscountedSubtotal + totalTaxAmount;

    document.getElementById("res-gen-sales-tax").innerText = "$" + totalTaxAmount.toFixed(2);
    document.getElementById("res-gen-sales-total").innerText = "$" + totalCost.toFixed(2);
    document.getElementById("res-gen-pretax").innerText = "$" + totalPreTax.toFixed(2);
    document.getElementById("res-gen-discount").innerText = "-$" + (totalPreTax - totalDiscountedSubtotal).toFixed(2);
    document.getElementById("res-gen-net").innerText = "$" + totalDiscountedSubtotal.toFixed(2);
    document.getElementById("gen-sales-invoice-tbody").innerHTML = invoiceHtml;

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}

document.addEventListener("DOMContentLoaded", function() {
    renderGenCart();
    
    const btnAdd = document.getElementById("btn-gen-sales-add-item");
    if (btnAdd) {
        btnAdd.addEventListener("click", function() {
            genCartItems.push({ name: "New Item", category: "general", price: 10, qty: 1 });
            renderGenCart();
        });
    }

    const btn = document.getElementById("btn-calculate-gensales");
    if (btn) {
        btn.addEventListener("click", calculateGenSales);
    }

    const btnRev = document.getElementById("btn-gen-rev");
    if (btnRev) {
        btnRev.addEventListener("click", calculateGenReverse);
    }
});';
            break;

        case 'property-tax-estimator':
            $data['html'] = '
<div class="info-box">
    📌 Estimate your total mortgage payment (PITI) and property tax. Enter property value, tax rate, down payment, and mortgage details.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">🏠 PROPERTY & MORTGAGE DETAILS</label></div>
    <div class="field">
        <label class="lbl">HOME MARKET VALUE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The estimated current fair market price of the property.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="gen-prop-value" value="300000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">EFFECTIVE PROPERTY TAX RATE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The annual tax amount divided by the home’s total value (national average is ~1.04%).</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="gen-prop-rate" value="1.1" step="0.01">
            <span class="inp-suffix">%</span>
        </div>
    </div>
    <div class="grid2">
        <div class="field">
            <label class="lbl">DOWN PAYMENT ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The initial cash payment made towards the purchase price of the home.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="gen-prop-down" value="60000">
                <span class="inp-suffix">$</span>
            </div>
        </div>
        <div class="field">
            <label class="lbl">INTEREST RATE (%) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The annual mortgage interest rate charged by your lender.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="gen-prop-interest" value="6.5" step="0.1">
                <span class="inp-suffix">%</span>
            </div>
        </div>
    </div>
    <div class="field">
        <label class="lbl">LOAN TERM <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The duration over which the mortgage loan is scheduled to be repaid.</span></span></label>
        <div class="sel-wrap">
            <select id="gen-prop-term" class="sel">
                <option value="30" selected>30-Year Fixed Mortgage</option>
                <option value="15">15-Year Fixed Mortgage</option>
            </select>
        </div>
    </div>
    <div class="grid2">
        <div class="field">
            <label class="lbl">ANNUAL HOME INSURANCE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Estimated yearly cost of homeowners hazard insurance.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="gen-prop-insurance" value="1200">
                <span class="inp-suffix">$</span>
            </div>
        </div>
        <div class="field">
            <label class="lbl">MONTHLY HOA FEES ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Monthly dues paid to a homeowners association.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="gen-prop-hoa" value="0">
                <span class="inp-suffix">$</span>
            </div>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-genprop" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Property Tax & Expenses</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 TAX & EXPENSE ESTIMATES</label></div>
    <div class="grid2" style="margin-bottom: 20px;">
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-gen-prop-annual">$3,300</div>
            <div class="usc-other-result-label">Annual Property Tax</div>
        </div>
        <div class="usc-other-result-card">
            <div class="usc-other-result-value" id="res-gen-prop-total-monthly" style="color:var(--succ);">$275</div>
            <div class="usc-other-result-label">Total Monthly PITI</div>
        </div>
    </div>
    <div style="font-size:12px; line-height: 1.6;">
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Monthly Property Tax Portion:</span>
            <strong id="res-gen-prop-monthly">$275</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Monthly Mortgage P&I:</span>
            <strong id="res-gen-prop-pi">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Monthly Private Mortgage Insurance (PMI):</span>
            <strong id="res-gen-prop-pmi">$0</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid var(--bdr); padding: 6px 0;">
            <span>Monthly Home Insurance:</span>
            <strong id="res-gen-prop-ins">$100</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding: 6px 0;">
            <span>Monthly HOA Fees:</span>
            <strong id="res-gen-prop-hoa-val">$0</strong>
        </div>
    </div>
    <!-- 5-YEAR PROJECTIONS -->
    <div style="margin-top: 25px; border-top: 1px solid var(--bdr); padding-top: 20px;">
      <h4 style="font-size:13px; font-weight:700; margin-bottom:10px; color:var(--pri);">📈 5-YEAR PROPERTY TAX & ESCROW PROJECTIONS</h4>
      <p style="font-size: 11px; color: var(--mt); margin-bottom: 12px;">Assumes a standard 3% annual property value appreciation rate.</p>
      <div style="overflow-x: auto;">
        <table class="usc-custom-table" style="width: 100%; border-collapse: collapse; font-size:12px;">
          <thead>
            <tr>
              <th style="color:var(--lbl);">Year</th>
              <th style="color:var(--lbl);">Estimated Value</th>
              <th style="color:var(--lbl);">Annual Property Tax</th>
              <th style="color:var(--lbl);">Monthly Tax Portion</th>
            </tr>
          </thead>
          <tbody id="gen-prop-projection-tbody">
            <!-- Populated via JS -->
          </tbody>
        </table>
      </div>
    </div>
</div>';

            $data['js'] = '
function calculateGenProp() {
    const value = parseFloat(document.getElementById("gen-prop-value").value) || 0;
    const rate = parseFloat(document.getElementById("gen-prop-rate").value) || 0;

    const annual = value * (rate / 100);
    const monthly = annual / 12;

    const down = parseFloat(document.getElementById("gen-prop-down").value) || 0;
    const interest = parseFloat(document.getElementById("gen-prop-interest").value) || 0;
    const term = parseFloat(document.getElementById("gen-prop-term").value) || 30;

    const loanAmount = Math.max(0, value - down);
    let monthlyPI = 0;
    if (loanAmount > 0) {
        if (interest > 0) {
            const monthlyInterest = interest / 100 / 12;
            const totalPayments = term * 12;
            monthlyPI = (loanAmount * monthlyInterest * Math.pow(1 + monthlyInterest, totalPayments)) / (Math.pow(1 + monthlyInterest, totalPayments) - 1);
        } else {
            monthlyPI = loanAmount / (term * 12);
        }
    }

    let monthlyPMI = 0;
    if (down < value * 0.20 && loanAmount > 0) {
        monthlyPMI = (loanAmount * 0.005) / 12;
    }

    const insurance = parseFloat(document.getElementById("gen-prop-insurance").value) || 0;
    const hoa = parseFloat(document.getElementById("gen-prop-hoa").value) || 0;
    const monthlyInsurance = insurance / 12;

    const totalMonthly = monthly + monthlyPI + monthlyPMI + monthlyInsurance + hoa;

    document.getElementById("res-gen-prop-annual").innerText = "$" + Math.round(annual).toLocaleString();
    document.getElementById("res-gen-prop-monthly").innerText = "$" + Math.round(monthly).toLocaleString();
    document.getElementById("res-gen-prop-pi").innerText = "$" + Math.round(monthlyPI).toLocaleString();
    document.getElementById("res-gen-prop-pmi").innerText = "$" + Math.round(monthlyPMI).toLocaleString();
    document.getElementById("res-gen-prop-ins").innerText = "$" + Math.round(monthlyInsurance).toLocaleString();
    document.getElementById("res-gen-prop-hoa-val").innerText = "$" + Math.round(hoa).toLocaleString();
    document.getElementById("res-gen-prop-total-monthly").innerText = "$" + Math.round(totalMonthly).toLocaleString();

    let projectionHtml = "";
    let currentVal = value;
    const appreciationRate = 0.03;
    for (let yr = 1; yr <= 5; yr++) {
        currentVal = currentVal * (1 + appreciationRate);
        const yrTax = currentVal * (rate / 100);
        projectionHtml += `<tr>
            <td><strong>Year ${yr}</strong></td>
            <td>$${Math.round(currentVal).toLocaleString()}</td>
            <td>$${Math.round(yrTax).toLocaleString()}</td>
            <td>$${Math.round(yrTax / 12).toLocaleString()}</td>
        </tr>`;
    }
    document.getElementById("gen-prop-projection-tbody").innerHTML = projectionHtml;

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-genprop");
    if (btn) {
        btn.addEventListener("click", calculateGenProp);
    }
});';
            break;

        case 'effective-property-tax-rate-calculator':
            $data['html'] = '
<div class="info-box">
    📌 Determine the real effective tax rate of your home based on actual taxes paid and market valuation.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">🏠 REAL ESTATE TAX BILL</label></div>
    <div class="field">
        <label class="lbl">ANNUAL PROPERTY TAX PAID ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The actual total dollar amount of property tax you pay to your local county or city annually.</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="eff-tax-paid" value="3600">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <div class="field">
        <label class="lbl">CURRENT HOME MARKET VALUE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The fair market value of your home (what it would sell for today).</span></span></label>
        <div class="inp-wrap">
            <input type="number" class="inp" id="eff-home-value" value="300000">
            <span class="inp-suffix">$</span>
        </div>
    </div>
    <button type="button" class="calc-btn" id="btn-calculate-effprop" style="width: 100%; margin-top: 15px; font-weight:700;">Calculate Effective Rate</button>
</div>
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 EFFECTIVE PERCENTAGE RATE</label></div>
    <div class="usc-other-result-card" style="margin-bottom: 20px;">
        <div class="usc-other-result-value" id="res-eff-pct">1.20%</div>
        <div class="usc-other-result-label">Real Effective Property Tax Rate</div>
    </div>
    <div id="eff-rate-status-box" class="usc-alert-box blue">
        Property tax rate is moderate.
    </div>
    <div style="margin-top: 20px; border-top: 1px solid var(--bdr); padding-top: 20px;">
        <div style="font-weight: 700; font-size: 13px; color: var(--lbl); margin-bottom: 8px; text-transform: uppercase;">Real Effective Rate Benchmark</div>
        <div style="display: flex; height: 18px; border-radius: 4px; overflow: hidden; background: #e5e7eb; margin-bottom: 12px;">
            <div id="eff-benchmark-bar" style="background: var(--pri); width: 0%; transition: width 0.3s;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--mt); margin-bottom: 15px;">
            <span>0% (Lowest)</span>
            <span>US Avg: <strong>1.04%</strong></span>
            <span>Your Rate: <strong id="lbl-eff-rate-val">0.0%</strong></span>
            <span>3% (Highest)</span>
        </div>
    </div>
</div>';

            $data['js'] = '
function calculateEffectivePropRate() {
    const taxPaid = parseFloat(document.getElementById("eff-tax-paid").value) || 0;
    const value = parseFloat(document.getElementById("eff-home-value").value) || 0;

    const rate = value > 0 ? (taxPaid / value) * 100 : 0;
    const boxEl = document.getElementById("eff-rate-status-box");

    document.getElementById("res-eff-pct").innerText = rate.toFixed(2) + "%";

    const barPct = Math.min(100, (rate / 3.0) * 100);
    document.getElementById("eff-benchmark-bar").style.width = barPct + "%";
    document.getElementById("lbl-eff-rate-val").innerText = rate.toFixed(2) + "%";

    if (rate < 0.5) {
        boxEl.className = "usc-alert-box green";
        boxEl.innerHTML = "🟢 <strong>Low Tax Level:</strong> Your effective rate of <strong>" + rate.toFixed(2) + "%</strong> is significantly below the national average. This is standard for low-tax regions like Alabama or Hawaii.";
    } else if (rate <= 1.5) {
        boxEl.className = "usc-alert-box blue";
        boxEl.innerHTML = "🔵 <strong>Moderate Tax Level:</strong> Your effective rate of <strong>" + rate.toFixed(2) + "%</strong> is moderate, fitting standard ranges for most areas in the United States.";
    } else {
        boxEl.className = "usc-alert-box red";
        boxEl.innerHTML = "🔴 <strong>High Tax Level:</strong> Your effective rate of <strong>" + rate.toFixed(2) + "%</strong> is high. This is typical for high-assessment regions like New Jersey, Illinois, or Texas.";
    }

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-effprop");
    if (btn) {
        btn.addEventListener("click", calculateEffectivePropRate);
    }
});';
            break;

        case 'state-tax-comparison-calculator':
            require_once UST_PATH . 'data/income-tax.php';
            require_once UST_PATH . 'data/property-tax.php';
            $income_db = ust_get_income_tax_data();
            $property_db = ust_get_property_tax_data();

            $states_options_a = '';
            $states_options_b = '';
            foreach ($states_registry as $s_slug => $s_info) {
                $sel_a = ($s_slug === 'california') ? 'selected' : '';
                $sel_b = ($s_slug === 'texas') ? 'selected' : '';
                $states_options_a .= '<option value="' . esc_attr($s_slug) . '" ' . $sel_a . '>' . esc_html($s_info['name']) . '</option>';
                $states_options_b .= '<option value="' . esc_attr($s_slug) . '" ' . $sel_b . '>' . esc_html($s_info['name']) . '</option>';
            }

            $data['html'] = '
<div class="info-box">
    📌 Compare estimated annual income tax and property tax side-by-side between any two US states to find your potential tax savings.
</div>
<div class="sec">
    <div class="sec-head"><label class="lbl">💵 RELOCATION & INCOME DETAILS</label></div>
    <div class="grid2">
        <div class="field">
            <label class="lbl">GROSS ANNUAL SALARY ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The annual household earnings used to calculate progressive income taxes.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="comp-salary" value="100000">
                <span class="inp-suffix">$</span>
            </div>
            <div class="hint">Your expected pre-tax annual income</div>
        </div>
        <div class="field">
            <label class="lbl">PROPERTY MARKET VALUE ($) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">Estimated fair market value of the property to compute property tax liabilities.</span></span></label>
            <div class="inp-wrap">
                <input type="number" class="inp" id="comp-home-value" value="500000">
                <span class="inp-suffix">$</span>
            </div>
            <div class="hint">Market value of home to estimate property tax</div>
        </div>
    </div>
    
    <div class="grid2">
        <div class="field">
            <label class="lbl">STATE A (ORIGIN) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The U.S. state you are currently living in.</span></span></label>
            <div class="sel-wrap">
                <select id="comp-state-a" class="sel">
                    ' . $states_options_a . '
                </select>
            </div>
        </div>
        <div class="field">
            <label class="lbl">STATE B (DESTINATION) <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">The U.S. state you are planning to relocate to.</span></span></label>
            <div class="sel-wrap">
                <select id="comp-state-b" class="sel">
                    ' . $states_options_b . '
                </select>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="lbl">FILING STATUS <span class="ust-tooltip">ℹ️<span class="ust-tooltiptext">IRS filing status used to determine standard deduction and income tax brackets in both states.</span></span></label>
        <div class="sel-wrap">
            <select id="comp-status" class="sel">
                <option value="single">Single</option>
                <option value="married" selected>Married Filing Jointly</option>
            </select>
        </div>
    </div>

    <button type="button" class="calc-btn" id="btn-calculate-comp" style="width: 100%; margin-top: 15px; font-weight:700;">Compare Taxes Side-by-Side</button>
</div>

<!-- RESULTS PANEL -->
<div class="sec" id="results-panel" style="display:none;">
    <div class="sec-head"><label class="lbl">📊 STATE COMPARISON SUMMARY</label></div>
    
    <div id="comp-savings-box" class="usc-alert-box green" style="display:none; margin-bottom: 20px; font-weight: 600; text-align: center;"></div>

    <div class="grid2" style="margin-bottom: 20px; gap: 15px;">
        <div class="usc-other-result-card" id="card-state-a" style="border: 1px solid var(--bdr);">
            <div style="font-weight: 800; font-size: 14px; color: var(--pri); text-transform: uppercase; margin-bottom: 8px;" id="lbl-state-a">State A</div>
            <div class="usc-other-result-value" id="res-total-tax-a" style="color:var(--pri);">$0</div>
            <div class="usc-other-result-label">Combined Annual Tax</div>
            <div style="font-size: 11px; margin-top: 8px; border-top: 1px solid var(--bdr); padding-top: 8px; text-align: left;">
                <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                    <span>Income Tax:</span>
                    <strong id="res-income-tax-a">$0</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Property Tax:</span>
                    <strong id="res-property-tax-a">$0</strong>
                </div>
            </div>
        </div>
        <div class="usc-other-result-card" id="card-state-b" style="border: 1px solid var(--bdr);">
            <div style="font-weight: 800; font-size: 14px; color: var(--succ); text-transform: uppercase; margin-bottom: 8px;" id="lbl-state-b">State B</div>
            <div class="usc-other-result-value" id="res-total-tax-b" style="color:var(--succ);">$0</div>
            <div class="usc-other-result-label">Combined Annual Tax</div>
            <div style="font-size: 11px; margin-top: 8px; border-top: 1px solid var(--bdr); padding-top: 8px; text-align: left;">
                <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                    <span>Income Tax:</span>
                    <strong id="res-income-tax-b">$0</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Property Tax:</span>
                    <strong id="res-property-tax-b">$0</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparative Bar Chart -->
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 25px 0; gap: 10px;">
        <svg width="320" height="220" id="comp-bar-svg" style="border: 1px solid var(--bdr); border-radius: 6px; padding: 15px; background: rgba(0,0,0,0.01);">
            <!-- Svg content populated dynamically -->
        </svg>
        <div style="font-size: 11px; color: var(--mt); font-weight: 600;">Side-by-Side Combined Tax Burden ($)</div>
    </div>

    <div style="overflow-x: auto; margin-top: 15px;">
        <table class="usc-custom-table" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr>
                    <th style="color:var(--lbl);">Tax Metric</th>
                    <th style="color:var(--lbl);" id="table-lbl-state-a">State A</th>
                    <th style="color:var(--lbl);" id="table-lbl-state-b">State B</th>
                    <th style="color:var(--lbl);">Difference</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Income Tax System</strong></td>
                    <td id="res-sys-type-a">-</td>
                    <td id="res-sys-type-b">-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td><strong>Est. State Income Tax</strong></td>
                    <td id="table-inc-a">$0</td>
                    <td id="table-inc-b">$0</td>
                    <td id="diff-inc" style="font-weight:700;">$0</td>
                </tr>
                <tr>
                    <td><strong>Property Tax Effective Rate</strong></td>
                    <td id="res-prop-rate-a">0.00%</td>
                    <td id="res-prop-rate-b">0.00%</td>
                    <td id="diff-prop-rate">-</td>
                </tr>
                <tr>
                    <td><strong>Est. Property Tax</strong></td>
                    <td id="table-prop-a">$0</td>
                    <td id="table-prop-b">$0</td>
                    <td id="diff-prop" style="font-weight:700;">$0</td>
                </tr>
                <tr style="background: rgba(0,0,0,0.02); font-weight: bold; border-top: 2px solid var(--bdr);">
                    <td><strong>Total Combined Tax</strong></td>
                    <td id="table-total-a" style="color: var(--pri);">$0</td>
                    <td id="table-total-b" style="color: var(--succ);">$0</td>
                    <td id="diff-total" style="color: var(--pri);">$0</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>';

            $data['js'] = '
const compIncomeRules = ' . json_encode($income_db['states']) . ';
const compPropertyRules = ' . json_encode($property_db) . ';

function drawCompBars(nameA, taxA, nameB, taxB) {
    const svg = document.getElementById("comp-bar-svg");
    if (!svg) return;
    svg.innerHTML = "";

    const maxTax = Math.max(taxA, taxB, 100);
    const width = 320;
    const height = 220;
    const padding = 45;
    const barWidth = 70;

    const heightA = (taxA / maxTax) * (height - 90);
    const heightB = (taxB / maxTax) * (height - 90);

    const xA = padding;
    const xB = width - padding - barWidth;

    const yA = height - 50 - heightA;
    const yB = height - 50 - heightB;

    const rectA = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rectA.setAttribute("x", xA);
    rectA.setAttribute("y", yA);
    rectA.setAttribute("width", barWidth);
    rectA.setAttribute("height", heightA);
    rectA.setAttribute("fill", "#6366f1");
    rectA.setAttribute("rx", "4");
    rectA.style.cursor = "pointer";

    const rectB = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rectB.setAttribute("x", xB);
    rectB.setAttribute("y", yB);
    rectB.setAttribute("width", barWidth);
    rectB.setAttribute("height", heightB);
    rectB.setAttribute("fill", "#10b981");
    rectB.setAttribute("rx", "4");
    rectB.style.cursor = "pointer";

    const textAVal = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textAVal.setAttribute("x", xA + barWidth/2);
    textAVal.setAttribute("y", yA - 8);
    textAVal.setAttribute("text-anchor", "middle");
    textAVal.setAttribute("fill", "#6366f1");
    textAVal.setAttribute("font-size", "11px");
    textAVal.setAttribute("font-weight", "bold");
    textAVal.textContent = "$" + Math.round(taxA).toLocaleString();

    const textBVal = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textBVal.setAttribute("x", xB + barWidth/2);
    textBVal.setAttribute("y", yB - 8);
    textBVal.setAttribute("text-anchor", "middle");
    textBVal.setAttribute("fill", "#10b981");
    textBVal.setAttribute("font-size", "11px");
    textBVal.setAttribute("font-weight", "bold");
    textBVal.textContent = "$" + Math.round(taxB).toLocaleString();

    const textALabel = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textALabel.setAttribute("x", xA + barWidth/2);
    textALabel.setAttribute("y", height - 30);
    textALabel.setAttribute("text-anchor", "middle");
    textALabel.setAttribute("fill", "var(--lbl)");
    textALabel.setAttribute("font-size", "10px");
    textALabel.setAttribute("font-weight", "600");
    textALabel.textContent = nameA;

    const textBLabel = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textBLabel.setAttribute("x", xB + barWidth/2);
    textBLabel.setAttribute("y", height - 30);
    textBLabel.setAttribute("text-anchor", "middle");
    textBLabel.setAttribute("fill", "var(--lbl)");
    textBLabel.setAttribute("font-size", "10px");
    textBLabel.setAttribute("font-weight", "600");
    textBLabel.textContent = nameB;

    svg.appendChild(rectA);
    svg.appendChild(rectB);
    svg.appendChild(textAVal);
    svg.appendChild(textBVal);
    svg.appendChild(textALabel);
    svg.appendChild(textBLabel);
}

function calculateComparison() {
    const salary = parseFloat(document.getElementById("comp-salary").value) || 0;
    const homeVal = parseFloat(document.getElementById("comp-home-value").value) || 0;
    const stateAKey = document.getElementById("comp-state-a").value;
    const stateBKey = document.getElementById("comp-state-b").value;
    const status = document.getElementById("comp-status").value;

    const stateAValName = document.getElementById("comp-state-a").options[document.getElementById("comp-state-a").selectedIndex].text;
    const stateBValName = document.getElementById("comp-state-b").options[document.getElementById("comp-state-b").selectedIndex].text;

    function calcIncomeTax(stateKey, gross) {
        const rule = compIncomeRules[stateKey] || { type: "none", deduction: 0, flat_rate: 0 };
        let deduct = rule.deduction || 0;
        if (status === "married" && deduct > 0) {
            deduct *= 2;
        }
        const taxable = Math.max(0, gross - deduct);
        let tax = 0;
        if (rule.type === "flat") {
            tax = taxable * rule.flat_rate;
        } else if (rule.type === "graduated" && rule.brackets) {
            let tempTaxable = taxable;
            let prevLimit = 0;
            for (let i = 0; i < rule.brackets.length; i++) {
                const b = rule.brackets[i];
                let limit = b.limit;
                if (status === "married" && limit !== Infinity && limit !== null) {
                    limit *= 2;
                }
                const currentRange = limit - prevLimit;
                if (tempTaxable > currentRange) {
                    tax += currentRange * b.rate;
                    tempTaxable -= currentRange;
                    prevLimit = limit;
                } else {
                    tax += tempTaxable * b.rate;
                    break;
                }
            }
        }
        return { tax: tax, type: rule.type };
    }

    function calcPropertyTax(stateKey, val) {
        const rule = compPropertyRules[stateKey] || { rate: 0.01 };
        return { tax: val * rule.rate, rate: rule.rate };
    }

    const taxIncA = calcIncomeTax(stateAKey, salary);
    const taxIncB = calcIncomeTax(stateBKey, salary);

    const taxPropA = calcPropertyTax(stateAKey, homeVal);
    const taxPropB = calcPropertyTax(stateBKey, homeVal);

    const totalTaxA = taxIncA.tax + taxPropA.tax;
    const totalTaxB = taxIncB.tax + taxPropB.tax;

    document.getElementById("lbl-state-a").innerText = stateAValName;
    document.getElementById("lbl-state-b").innerText = stateBValName;
    document.getElementById("table-lbl-state-a").innerText = stateAValName;
    document.getElementById("table-lbl-state-b").innerText = stateBValName;

    document.getElementById("res-total-tax-a").innerText = "$" + Math.round(totalTaxA).toLocaleString();
    document.getElementById("res-income-tax-a").innerText = "$" + Math.round(taxIncA.tax).toLocaleString();
    document.getElementById("res-property-tax-a").innerText = "$" + Math.round(taxPropA.tax).toLocaleString();
    document.getElementById("res-sys-type-a").innerText = taxIncA.type.charAt(0).toUpperCase() + taxIncA.type.slice(1);
    document.getElementById("res-prop-rate-a").innerText = (taxPropA.rate * 100).toFixed(2) + "%";

    document.getElementById("res-total-tax-b").innerText = "$" + Math.round(totalTaxB).toLocaleString();
    document.getElementById("res-income-tax-b").innerText = "$" + Math.round(taxIncB.tax).toLocaleString();
    document.getElementById("res-property-tax-b").innerText = "$" + Math.round(taxPropB.tax).toLocaleString();
    document.getElementById("res-sys-type-b").innerText = taxIncB.type.charAt(0).toUpperCase() + taxIncB.type.slice(1);
    document.getElementById("res-prop-rate-b").innerText = (taxPropB.rate * 100).toFixed(2) + "%";

    document.getElementById("table-inc-a").innerText = "$" + Math.round(taxIncA.tax).toLocaleString();
    document.getElementById("table-inc-b").innerText = "$" + Math.round(taxIncB.tax).toLocaleString();
    document.getElementById("table-prop-a").innerText = "$" + Math.round(taxPropA.tax).toLocaleString();
    document.getElementById("table-prop-b").innerText = "$" + Math.round(taxPropB.tax).toLocaleString();
    document.getElementById("table-total-a").innerText = "$" + Math.round(totalTaxA).toLocaleString();
    document.getElementById("table-total-b").innerText = "$" + Math.round(totalTaxB).toLocaleString();

    const diffInc = taxIncB.tax - taxIncA.tax;
    const diffProp = taxPropB.tax - taxPropA.tax;
    const diffTotal = totalTaxB - totalTaxA;

    function formatDiff(val) {
        if (val < 0) return "-$" + Math.round(Math.abs(val)).toLocaleString() + " (Savings)";
        if (val > 0) return "+$" + Math.round(val).toLocaleString() + " (Increase)";
        return "$0";
    }
    
    document.getElementById("diff-inc").innerText = formatDiff(diffInc);
    document.getElementById("diff-inc").style.color = (diffInc <= 0) ? "var(--succ)" : "#ef4444";
    document.getElementById("diff-prop").innerText = formatDiff(diffProp);
    document.getElementById("diff-prop").style.color = (diffProp <= 0) ? "var(--succ)" : "#ef4444";
    document.getElementById("diff-total").innerText = formatDiff(diffTotal);
    document.getElementById("diff-total").style.color = (diffTotal <= 0) ? "var(--succ)" : "#ef4444";
    
    const diffRate = (taxPropB.rate - taxPropA.rate) * 100;
    document.getElementById("diff-prop-rate").innerText = (diffRate < 0 ? "" : "+") + diffRate.toFixed(2) + "%";

    const savingsBox = document.getElementById("comp-savings-box");
    if (diffTotal < 0) {
        savingsBox.style.display = "block";
        savingsBox.className = "usc-alert-box green";
        savingsBox.innerHTML = `🎉 <strong>Relocation Savings:</strong> Moving from <strong>${stateAValName}</strong> to <strong>${stateBValName}</strong> saves you approximately <strong>$${Math.round(Math.abs(diffTotal)).toLocaleString()} per year</strong> in combined income & property taxes!`;
    } else if (diffTotal > 0) {
        savingsBox.style.display = "block";
        savingsBox.className = "usc-alert-box red";
        savingsBox.innerHTML = `⚠️ <strong>Relocation Tax Increase:</strong> Moving from <strong>${stateAValName}</strong> to <strong>${stateBValName}</strong> will increase your combined taxes by approximately <strong>$${Math.round(diffTotal).toLocaleString()} per year</strong>.`;
    } else {
        savingsBox.style.display = "none";
    }

    drawCompBars(stateAValName, totalTaxA, stateBValName, totalTaxB);

    const panel = document.getElementById("results-panel");
    panel.style.display = "block";
    const yOffset = -20;
    const y = panel.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: "smooth" });
}

document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("btn-calculate-comp");
    if (btn) {
        btn.addEventListener("click", calculateComparison);
    }
    const params = new URLSearchParams(window.location.search);
    const stateA = params.get("stateA");
    const stateB = params.get("stateB");
    if (stateA) {
        const selA = document.getElementById("comp-state-a");
        if (selA) selA.value = stateA;
    }
    if (stateB) {
        const selB = document.getElementById("comp-state-b");
        if (selB) selB.value = stateB;
    }
    if (stateA && stateB) {
        setTimeout(function() {
            if (btn) btn.click();
        }, 500);
    }
});';
            break;
    }

    return $data;
}
