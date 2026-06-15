<?php
/**
 * General Tax Calculators Registry, Default Content, and FAQs
 * Fully customized, human-styled unique articles and 10 FAQs per calculator.
 */

if (!defined('ABSPATH')) exit;

/**
 * Returns registry of the 12 general calculators under 'Other' category
 */
function ust_get_other_calculators_registry() {
    return [
        'federal-income-tax-calculator' => [
            'name' => 'Federal Income Tax Calculator',
            'desc' => 'Estimate your annual federal income tax liability, marginal brackets, FICA withholdings, and total take-home pay.',
            'title_seo' => 'Federal Income Tax Calculator - 2025/2026 Estimate',
            'desc_seo' => 'Estimate your annual federal income tax liability, FICA withholdings, and take-home pay using the latest IRS progressive tax brackets and standard deductions.'
        ],
        'state-income-tax-calculator' => [
            'name' => 'State Income Tax Calculator',
            'desc' => 'Compare state-level income tax rates across all 50 states to estimate your state income tax burden.',
            'title_seo' => 'State Income Tax Calculator - All 50 States Combined',
            'desc_seo' => 'Calculate and compare your state income tax liability across any U.S. state. Supports progressive, flat, and tax-free states.'
        ],
        'income-tax-refund-calculator' => [
            'name' => 'Income Tax Refund Calculator',
            'desc' => 'Determine if you will owe money to the IRS or receive a refund check based on your annual withholdings.',
            'title_seo' => 'Income Tax Refund Calculator - Estimate Owed or Refund',
            'desc_seo' => 'Calculate your estimated tax refund or total taxes owed to the IRS by comparing annual withholding amounts with actual tax liability.'
        ],
        'tax-withholding-calculator' => [
            'name' => 'Tax Withholding Calculator',
            'desc' => 'Analyze your current paycheck withholdings to check if they match your estimated annual tax obligation.',
            'title_seo' => 'Tax Withholding Calculator - Paycheck Tax Deductions',
            'desc_seo' => 'Verify if your current employer paycheck withholdings are accurate. Adjust W-4 parameters to avoid underpayment penalties or huge refund balances.'
        ],
        'tax-bracket-calculator' => [
            'name' => 'Tax Bracket Calculator',
            'desc' => 'Find your highest marginal tax rate bracket and calculate your overall effective tax rate.',
            'title_seo' => 'Tax Bracket Calculator - Find Your Marginal & Effective Rates',
            'desc_seo' => 'Find which federal tax bracket your taxable income falls into. Displays graduated tax rates, marginal brackets, and overall effective rate.'
        ],
        'estimated-tax-calculator' => [
            'name' => 'Estimated Tax Calculator',
            'desc' => 'Calculate quarterly estimated tax payments for freelancers, contractors, and self-employed individuals.',
            'title_seo' => 'Estimated Tax Calculator - Quarterly Payments (1040-ES)',
            'desc_seo' => 'Determine quarterly estimated tax payments for 1099 contractors and self-employed individuals. Avoid IRS underpayment penalties.'
        ],
        'capital-gains-tax-calculator' => [
            'name' => 'Capital Gains Tax Calculator',
            'desc' => 'Calculate short-term and long-term capital gains tax on stocks, crypto, real estate, and other investments.',
            'title_seo' => 'Capital Gains Tax Calculator - Stocks, Crypto, Real Estate',
            'desc_seo' => 'Calculate capital gains taxes on your investment sales. Supports short-term vs long-term rates, holding periods, and cost basis rules.'
        ],
        'self-employment-tax-calculator' => [
            'name' => 'Self-Employment Tax Calculator',
            'desc' => 'Calculate the 15.3% self-employment tax (Schedule SE) on net business earnings for independent contractors.',
            'title_seo' => 'Self-Employment Tax Calculator - 1099 Schedule SE Tax',
            'desc_seo' => 'Calculate your self-employment tax liability (15.3% Social Security and Medicare split) and write-off deductions on net business income.'
        ],
        'payroll-tax-calculator' => [
            'name' => 'Payroll Tax Calculator',
            'desc' => 'Calculate employee paycheck deductions and employer payroll liabilities (FICA, FUTA, SUTA).',
            'title_seo' => 'Payroll Tax Calculator - Employee & Employer Deductions',
            'desc_seo' => 'Calculate the total employee tax deductions and employer payroll taxes, including FICA (Social Security & Medicare), FUTA, and SUTA.'
        ],
        'sales-tax-calculator' => [
            'name' => 'Sales Tax Calculator',
            'desc' => 'Calculate total sales taxes for any retail purchase by entering custom state and local combined rates.',
            'title_seo' => 'Sales Tax Calculator - General Purchase Cost Estimator',
            'desc_seo' => 'Estimate total retail sales tax amounts and final purchase costs using state baseline rates and local county/city surtax overrides.'
        ],
        'property-tax-estimator' => [
            'name' => 'Property Tax Estimator',
            'desc' => 'Estimate annual and monthly property tax payments based on market value and average effective tax rates.',
            'title_seo' => 'Property Tax Estimator - Real Estate Escrow Calculations',
            'desc_seo' => 'Estimate monthly mortgage tax escrows and annual real estate taxes based on home market value and custom effective tax percentages.'
        ],
        'effective-property-tax-rate-calculator' => [
            'name' => 'Effective Property Tax Rate Calculator',
            'desc' => 'Calculate your property\'s effective tax rate percentage based on annual tax paid and home market value.',
            'title_seo' => 'Effective Property Tax Rate Calculator - Verify Assessment',
            'desc_seo' => 'Determine your property\'s real effective tax rate by dividing actual annual property tax paid by current fair market home value.'
        ],
        'state-tax-comparison-calculator' => [
            'name' => 'State-by-State Tax Comparison Calculator',
            'desc' => 'Compare state-level income and property taxes side-by-side between any two US states to find your potential tax savings.',
            'title_seo' => 'State-by-State Tax Comparison Calculator - Compare Income & Property Taxes',
            'desc_seo' => 'Compare the total tax burden between any two US states. Estimates side-by-side state income tax and property tax differences for relocation planning.'
        ]
    ];
}

/**
 * Returns dynamic FAQs for Other Tax Calculators (10 FAQs each)
 */
function ust_get_other_tax_faqs($slug) {
    $faqs = [
        'federal-income-tax-calculator' => [
            ['q' => "What are the federal standard deductions for this tax year?", 'a' => "The standard deduction is $15,000 for Single, $30,000 for Married Filing Jointly, and $22,500 for Head of Household filers."],
            ['q' => "What is the difference between marginal and effective federal tax rates?", 'a' => "Marginal rate is the bracket percentage applied to your last dollar of income. Effective rate is the average rate paid: total tax divided by gross income."],
            ['q' => "What consists of FICA taxes?", 'a' => "FICA consists of 6.2% Social Security tax (capped on earnings up to $168,600) and 1.45% Medicare tax (no cap)."],
            ['q' => "How do pre-tax deductions affect my federal tax?", 'a' => "Pre-tax deductions like 401(k) or HSA payments lower your Adjusted Gross Income (AGI), reducing the base on which your tax brackets are computed."],
            ['q' => "Who qualifies for the Head of Household filing status?", 'a' => "You must be unmarried, pay more than half the cost of keeping up a home, and have a qualifying dependent living with you for more than half the year."],
            ['q' => "Is there an additional Medicare tax for high earners?", 'a' => "Yes, an additional 0.9% Medicare tax is applied to wages exceeding $200,000 for Single and $250,000 for Married Filing Jointly."],
            ['q' => "How do tax credits differ from tax deductions?", 'a' => "Deductions lower your taxable income. Credits subtract directly from the total tax you owe, dollar-for-dollar, making credits much more valuable."],
            ['q' => "What is the Alternative Minimum Tax (AMT)?", 'a' => "The AMT is a secondary tax system designed to ensure high-income earners with significant deductions still pay a baseline minimum tax amount."],
            ['q' => "How often do federal tax brackets adjust?", 'a' => "The IRS adjusts bracket income thresholds annually to account for inflation, helping taxpayers avoid 'bracket creep' where inflation alone pushes them into higher tiers."],
            ['q' => "Can I claim itemized deductions instead of the standard deduction?", 'a' => "Yes, you should itemize if your total deductible expenses (mortgage interest, state/local taxes up to $10,000, charity) exceed your standard deduction allowance."]
        ],
        'state-income-tax-calculator' => [
            ['q' => "Which US states do not levy any state income tax?", 'a' => "Currently, Alaska, Florida, Nevada, South Dakota, Tennessee, Texas, Washington, and Wyoming have no state income tax on wages. New Hampshire taxes interest/dividends but is phasing it out by 2027."],
            ['q' => "What is a flat state tax system?", 'a' => "A flat system charges all income brackets a single fixed rate (e.g. Pennsylvania at 3.07% or Indiana at 3.05%), regardless of total earnings."],
            ['q' => "Do some states have local county or city income taxes?", 'a' => "Yes, states like Maryland, Ohio, Pennsylvania, Indiana, and New York allow municipalities to levy additional local income taxes on top of the state rate."],
            ['q' => "What is a state tax reciprocity agreement?", 'a' => "An agreement between neighboring states allowing commuters to pay income tax only to their resident state, preventing double-withholding."],
            ['q' => "How do non-resident tax filings work?", 'a' => "If you earn income in a state where you do not reside, you typically must file a non-resident tax return for that state and report resident earnings at home."],
            ['q' => "Do states offer standard deductions like the federal government?", 'a' => "Many states do, though thresholds are usually lower than federal limits. Some progressive states like California have separate state deduction schedules."],
            ['q' => "Is S-Corp or business income taxed differently at the state level?", 'a' => "Yes. Some states impose business franchise taxes or entity-level taxes on pass-through income, while others pass it directly to personal returns."],
            ['q' => "Does my home state credit me for taxes paid to other states?", 'a' => "Yes, resident states generally offer a credit for taxes paid to a non-resident state to prevent double taxation on the same income."],
            ['q' => "Which state has the highest personal income tax bracket?", 'a' => "California holds the highest marginal state tax rate, with a top bracket of 13.3% for millionaires, plus an extra 1.1% SDI payroll tax."],
            ['q' => "Are pre-tax health insurance premiums exempt from state taxes?", 'a' => "Yes, nearly all states conform to federal rules and exclude employer-sponsored health premiums from taxable state wages."]
        ],
        'income-tax-refund-calculator' => [
            ['q' => "Why does the IRS send tax refunds?", 'a' => "A tax refund represents the return of your own money because your total paycheck withholdings and tax payments exceeded your actual year-end tax liability."],
            ['q' => "Is getting a large tax refund a good financial strategy?", 'a' => "No. A large refund means you gave the government an interest-free loan. It is usually wiser to adjust your W-4 and get more take-home pay monthly."],
            ['q' => "How long does a tax refund take after filing?", 'a' => "Direct deposit refunds for electronic filings are usually issued within 21 days. Mailed paper returns can take 6 to 8 weeks."],
            ['q' => "Can the IRS withhold my refund to pay other debts?", 'a' => "Yes, the Treasury Offset Program can seize refunds to pay back taxes, child support, student loans, or state liabilities."],
            ['q' => "Does a tax refund count as taxable income next year?", 'a' => "Federal refunds are not taxable. State refunds are only taxable if you itemized deductions on last year's federal return and claimed a state tax deduction."],
            ['q' => "How do I check the status of my refund?", 'a' => "You can track your refund status online using the IRS 'Where's My Refund?' tool or the IRS2Go mobile app with your SSN and refund amount."],
            ['q' => "What is the difference between refundable and non-refundable tax credits?", 'a' => "Non-refundable credits reduce tax to zero. Refundable credits pay you the remaining balance even if your tax liability is already zero."],
            ['q' => "Why is my tax refund smaller than expected?", 'a' => "It could be due to math adjustments, unpaid penalties, offsets for other debts, or changes in filing status that the IRS corrected during processing."],
            ['q' => "Should I adjust my W-4 if I owe money instead of getting a refund?", 'a' => "Yes, if you owe more than $1,000, you should submit a new W-4 requesting extra withholdings to avoid future underpayment penalties."],
            ['q' => "What happens if I don't cash a tax refund check?", 'a' => "IRS checks are valid for one year. If expired, you must request a replacement check, but the funds remain yours and do not forfeit."]
        ],
        'tax-withholding-calculator' => [
            ['q' => "What is Form W-4 used for?", 'a' => "Employees submit Form W-4 to instruct their employer how much federal income tax to withhold from wage payments based on personal status and deductions."],
            ['q' => "What are the consequences of withholding too little tax?", 'a' => "Under-withholding can result in a tax bill in April, plus IRS underpayment penalties and interest charges if you pay less than 90% of your current liability."],
            ['q' => "How often should I recalculate my withholding?", 'a' => "Check it annually or immediately after major changes: marriage, divorce, birth of a child, home purchase, or a significant change in income."],
            ['q' => "What are withholding allowances?", 'a' => "The current W-4 form has removed standard allowances. It now uses direct dollar inputs for dependents, deductions, and other income to calculate withholdings."],
            ['q' => "How does pay frequency affect tax withholdings?", 'a' => "Employers annualize paycheck wages, calculate the annual tax liability, and divide it by the number of pay periods (e.g. 26 for bi-weekly) to determine withholding."],
            ['q' => "What is additional withholding?", 'a' => "An option on W-4 Line 4(c) to request a specific extra dollar amount be deducted from each paycheck, which is useful for dual-income households."],
            ['q' => "Can I claim exemption from withholding?", 'a' => "Only if you had no tax liability last year and expect to have no tax liability this year. You must write 'Exempt' on the W-4 form."],
            ['q' => "What happens if I work multiple jobs?", 'a' => "You must check the multi-job box on W-4 Step 2 or use the worksheet to calculate higher withholdings, as combining incomes pushes you into higher tax brackets."],
            ['q' => "How is bonus pay withheld?", 'a' => "Employers can withhold tax on bonuses using a flat supplemental rate (typically 22%) or by combining it with standard wages, which can artificially inflate your bracket."],
            ['q' => "What is the IRS withholding lock-in letter?", 'a' => "A directive sent to an employer by the IRS if an employee consistently under-withholds, forcing the employer to withhold at a higher rate."]
        ],
        'tax-bracket-calculator' => [
            ['q' => "What are the federal income tax brackets?", 'a' => "Rates are set at 10%, 12%, 22%, 24%, 32%, 35%, and 37% depending on taxable income levels and filing status."],
            ['q' => "How do progressive tax brackets work?", 'a' => "Your highest rate is only applied to the portion of income within that bracket, not your total income. Lower segments of your income are taxed at lower rates."],
            ['q' => "What is the difference between marginal and effective tax brackets?", 'a' => "Marginal rate is the tax rate applied to your highest dollar of income. Effective rate is the average rate (total tax divided by taxable income)."],
            ['q' => "Do tax brackets apply to gross income?", 'a' => "No. Brackets apply to taxable income, which is gross income minus standard/itemized deductions and pre-tax contributions."],
            ['q' => "Does entering a higher bracket mean my take-home pay decreases?", 'a' => "No. Since tax brackets are progressive, only the income above the threshold is taxed at the higher rate, meaning your take-home pay still increases overall."],
            ['q' => "Are tax brackets index-adjusted for inflation?", 'a' => "Yes, the IRS adjusts tax bracket thresholds annually to protect taxpayers from inflation-driven bracket creep."],
            ['q' => "Are tax brackets different for Married Filing Separately?", 'a' => "Yes. Brackets for married couples filing separately have narrower ranges to prevent tax avoidance, often mirroring single filer thresholds."],
            ['q' => "How does capital gains income affect my regular tax brackets?", 'a' => "Long-term capital gains are taxed at separate rates (0%, 15%, or 20%), but they do increase your Adjusted Gross Income, which can affect deduction phaseouts."],
            ['q' => "What is the tax bracket for Head of Household?", 'a' => "Head of Household tax brackets are wider than single brackets, providing lower rates on mid-range incomes to support single parents."],
            ['q' => "How can I lower my tax bracket?", 'a' => "Increase pre-tax retirement contributions (401k, IRA), fund HSAs, or itemize deductions to reduce your overall taxable income."]
        ],
        'estimated-tax-calculator' => [
            ['q' => "Who is required to pay quarterly estimated taxes?", 'a' => "Sole proprietors, partners, and S-corporation shareholders who expect to owe $1,000 or more when filing their return must pay estimated taxes."],
            ['q' => "What are the deadlines for quarterly estimated taxes?", 'a' => "Payments are due on April 15, June 15, September 15, and January 15 of the following year."],
            ['q' => "What is the estimated tax Safe Harbor rule?", 'a' => "You can avoid underpayment penalties if you pay 90% of this year's tax liability or 100% of last year's liability (110% if your AGI was over $150,000)."],
            ['q' => "What form is used to calculate estimated taxes?", 'a' => "Form 1040-ES (Estimated Tax for Individuals) is used to estimate income and calculate quarterly payments."],
            ['q' => "How do I pay estimated taxes?", 'a' => "You can pay online via IRS Direct Pay, the Electronic Federal Tax Payment System (EFTPS), or by mailing payment vouchers with a check."],
            ['q' => "What happens if I miss a quarterly tax deadline?", 'a' => "The IRS charges an underpayment penalty calculated based on the underpaid amount and the number of days it remained late."],
            ['q' => "Do I have to pay estimated taxes if I also have a W-2 job?", 'a' => "You can avoid estimated payments by adjusting your W-2 withholding (Form W-4) to deduct extra tax from your regular paycheck to cover your self-employment income."],
            ['q' => "How do I estimate income if my earnings fluctuate?", 'a' => "You can use the Annualized Income Installment Method on Form 2210 to adjust payments each quarter based on actual seasonal profits."],
            ['q' => "Does estimated tax include state taxes?", 'a' => "Form 1040-ES is only for federal taxes. Most states have their own estimated tax forms and quarterly schedules."],
            ['q' => "Are estimated tax payments required in your first year of self-employment?", 'a' => "If you had no tax liability in the prior year, you are exempt from penalties, but you must plan for a potentially large tax bill in April."]
        ],
        'capital-gains-tax-calculator' => [
            ['q' => "What is the capital gains tax rate?", 'a' => "Short-term gains are taxed at standard income rates. Long-term gains are taxed at preferential rates of 0%, 15%, or 20% depending on taxable income."],
            ['q' => "How long do I need to hold an asset for long-term capital gains?", 'a' => "You must hold the asset for more than one year (at least 366 days) before selling to qualify for lower long-term rates."],
            ['q' => "What is cost basis in capital gains?", 'a' => "Cost basis is the total purchase price of an asset, including transaction fees, commissions, and adjustments for splits or improvements."],
            ['q' => "How does the home sale capital gains exclusion work?", 'a' => "You can exclude up to $250,000 of gain (Single) or $500,000 (Married) if you owned and lived in the home as a primary residence for 2 of the past 5 years."],
            ['q' => "What is the Net Investment Income Tax (NIIT)?", 'a' => "An additional 3.8% tax applied to net investment income for high earners with modified AGI above $200,000 (Single) or $250,000 (Married)."],
            ['q' => "How can I offset my capital gains?", 'a' => "You can offset capital gains with capital losses. If losses exceed gains, you can deduct up to $3,000 of ordinary income annually and carry forward the rest."],
            ['q' => "What is a wash sale?", 'a' => "An IRS rule that disallows claiming a loss on an asset sale if you buy a substantially identical asset within 30 days before or after the sale."],
            ['q' => "Are cryptocurrency sales subject to capital gains tax?", 'a' => "Yes, the IRS treats cryptocurrency as property, meaning every trade or cash-out is a taxable capital gains transaction."],
            ['q' => "What is the capital gains rate for collectibles?", 'a' => "Collectibles like art, coins, and antiques are subject to a maximum long-term capital gains tax rate of 28% rather than standard rates."],
            ['q' => "How does inherited property capital gains work?", 'a' => "Inherited assets receive a 'step-up in basis' to the fair market value at the date of the decedent's death, significantly reducing taxable gains for heirs."]
        ],
        'self-employment-tax-calculator' => [
            ['q' => "What is the self-employment tax rate?", 'a' => "The rate is 15.3%, consisting of 12.4% for Social Security and 2.9% for Medicare, applied to 92.35% of net self-employment profit."],
            ['q' => "Is self-employment tax different from income tax?", 'a' => "Yes. Self-employment tax covers Social Security and Medicare. Standard federal and state income taxes are calculated separately on your taxable income."],
            ['q' => "What is the self-employment tax deduction?", 'a' => "You can deduct 50% of your self-employment tax on Form 1040, which reduces your Adjusted Gross Income (AGI) subject to regular income tax."],
            ['q' => "Is there an income cap on self-employment tax?", 'a' => "The 12.4% Social Security portion only applies to the first $168,600 of combined wages and self-employment earnings. The 2.9% Medicare portion has no cap."],
            ['q' => "Do I have to pay self-employment tax if net earnings are low?", 'a' => "You must pay self-employment tax if your net self-employment earnings are $400 or more for the year."],
            ['q' => "What business expenses can I deduct to reduce my tax?", 'a' => "You can deduct any ordinary and necessary business expenses: home office, vehicle mileage, equipment, advertising, and supplies on Schedule C."],
            ['q' => "How does the Qualified Business Income (QBI) deduction help?", 'a' => "QBI allows eligible self-employed individuals to deduct up to 20% of their net business income from their income taxes (does not reduce SE tax)."],
            ['q' => "Are self-employed health insurance premiums subject to SE tax?", 'a' => "Health premiums can be deducted to lower your income tax, but they do not reduce your net profit subject to self-employment tax."],
            ['q' => "What form is used to calculate self-employment tax?", 'a' => "IRS Schedule SE (Form 1040) is used to calculate self-employment tax based on net profit from Schedule C."],
            ['q' => "Does paying self-employment tax build Social Security benefits?", 'a' => "Yes, paying self-employment tax earns you Social Security credits and determines your future retirement and disability benefits, just like W-2 withholdings."]
        ],
        'payroll-tax-calculator' => [
            ['q' => "What is payroll tax?", 'a' => "Payroll taxes are dedicated taxes (FICA) withheld from employee paychecks and matched by employers to fund Social Security and Medicare programs."],
            ['q' => "What is the employer's portion of FICA tax?", 'a' => "Employers must pay 6.2% for Social Security and 1.45% for Medicare, matching the employee's contribution for a total of 7.65%."],
            ['q' => "What is the FUTA tax rate?", 'a' => "FUTA (Federal Unemployment Tax Act) is an employer-paid tax of 6.0% on the first $7,000 of employee wages, though a credit of up to 5.4% is common for state tax compliance."],
            ['q' => "What is SUTA tax?", 'a' => "SUTA (State Unemployment Tax Act) is a state-level payroll tax paid by employers. Rates vary by state and the employer's unemployment claims history."],
            ['q' => "Are payroll taxes and income taxes the same?", 'a' => "No. Payroll taxes fund Social Security/Medicare and have flat rates. Income taxes go to general government funds and have progressive rates."],
            ['q' => "Do independent contractors pay payroll taxes?", 'a' => "No. Employers do not pay or withhold payroll taxes for 1099 independent contractors; contractors pay self-employment taxes instead."],
            ['q' => "What is the wage base limit for Social Security tax?", 'a' => "The Social Security tax is only withheld on wages up to a set annual limit ($168,600). Earnings above this threshold are exempt from Social Security withholding."],
            ['q' => "How often must employers deposit payroll taxes?", 'a' => "Deposits must be made monthly or semi-weekly depending on the total payroll tax liability of the business."],
            ['q' => "What form is used to report quarterly payroll taxes?", 'a' => "Employers file Form 941 quarterly to report employee wage withholdings and employer matching FICA payments to the IRS."],
            ['q' => "Are employer payroll tax contributions tax-deductible?", 'a' => "Yes. Employers can deduct their portion of FICA, FUTA, and SUTA taxes as ordinary business expenses on their corporate tax return."]
        ],
        'sales-tax-calculator' => [
            ['q' => "What is a combined sales tax rate?", 'a' => "A combined rate is the sum of state baseline sales taxes plus local county, city, and special transit district taxes applied to a purchase."],
            ['q' => "Are groceries subject to sales tax?", 'a' => "Many states exempt unprepared groceries from state sales tax or tax them at a reduced rate, though local municipal taxes may still apply."],
            ['q' => "Are prescription drugs exempt from sales tax?", 'a' => "Yes, prescription medications are fully exempt from retail sales tax in almost all U.S. states."],
            ['q' => "What is use tax?", 'a' => "Use tax is a self-reported tax owed on goods purchased out-of-state (or online) where no sales tax was charged, typically paid with your state income tax return."],
            ['q' => "Why did I pay sales tax on an online purchase?", 'a' => "Online retailers must collect sales tax based on the delivery address if they have a physical presence or meet economic thresholds in your state."],
            ['q' => "What is a sales tax holiday?", 'a' => "A temporary period where states waive sales taxes on specific items like school supplies, clothing, and energy-efficient appliances up to set spending limits."],
            ['q' => "Do services face sales tax?", 'a' => "Sales tax primarily applies to tangible personal property, but states are increasingly taxing services like professional repair, landscaping, or digital streaming."],
            ['q' => "How do businesses buy goods tax-free for resale?", 'a' => "Businesses can present a resale certificate to suppliers, which allows them to purchase inventory tax-free because sales tax will be collected when sold to the final consumer."],
            ['q' => "Are non-profit organizations exempt from sales tax?", 'a' => "Yes, most states grant sales tax exemptions to registered 501(c)(3) charities, schools, and religious institutions when purchasing goods for organizational use."],
            ['q' => "Which state has the highest combined sales tax rate?", 'a' => "Tennessee and Louisiana consistently rank among the highest, with average combined state and local sales tax rates exceeding 9.5%."]
        ],
        'property-tax-estimator' => [
            ['q' => "How is property tax calculated?", 'a' => "Property tax is calculated by multiplying your property's taxable assessed value by the local county or municipal millage rate."],
            ['q' => "What is a millage rate?", 'a' => "A millage rate is the tax rate per $1,000 of assessed value. For example, 15 mills translates to a 1.5% tax rate on assessed value."],
            ['q' => "What is the difference between market value and assessed value?", 'a' => "Market value is estimated selling price. Assessed value is the taxable value determined by local assessors, often calculated using an assessment ratio."],
            ['q' => "What is a Homestead Exemption?", 'a' => "A homestead exemption reduces the taxable assessed value of your primary residence, protecting a portion of its value from property taxes."],
            ['q' => "Are property taxes paid monthly or annually?", 'a' => "Most homeowners pay property taxes monthly into a mortgage escrow account, and the lender pays the annual tax bill when it is due."],
            ['q' => "Can I appeal my property tax assessment?", 'a' => "Yes. If you believe your home's assessed value is higher than its fair market value, you can file an appeal with your local county board of equalization."],
            ['q' => "How often are properties reassessed?", 'a' => "Reassessment schedules vary by county. Some reassess property values annually, while others reassess every 2 to 3 years."],
            ['q' => "Do senior citizens get property tax discounts?", 'a' => "Yes, many jurisdictions offer property tax exemptions, assessment freezes, or deferrals for senior citizens, low-income homeowners, and disabled veterans."],
            ['q' => "What happens if I don't pay my property taxes?", 'a' => "Unpaid taxes result in a tax lien placed on your property, which can eventually lead to a tax sale foreclosure by the local government."],
            ['q' => "Do property taxes fund public schools?", 'a' => "Yes, property taxes are the primary source of local funding for public school districts, police and fire departments, and local infrastructure projects."]
        ],
        'effective-property-tax-rate-calculator' => [
            ['q' => "What is an effective property tax rate?", 'a' => "The effective rate is the actual annual property tax paid divided by the current fair market value of the property, expressed as a percentage."],
            ['q' => "Why does the effective tax rate differ from the nominal tax rate?", 'a' => "Nominal rates apply to assessed values. If assessments are set at 50% of market value, a nominal rate of 2% equals an effective rate of 1% on market value."],
            ['q' => "How do I find my property's effective tax rate?", 'a' => "Divide your total annual property tax bill by the current fair market appraisal value of your home, then multiply by 100."],
            ['q' => "Are effective property tax rates the same across a state?", 'a' => "No. Rates vary between counties and school districts due to differences in local spending budgets and assessment practices."],
            ['q' => "Which state has the lowest average effective property tax rate?", 'a' => "Hawaii has the lowest average effective rate at approximately 0.29% of home market value, followed closely by Alabama at 0.41%."],
            ['q' => "Which state has the highest average effective property tax rate?", 'a' => "New Jersey has the highest average effective rate at approximately 2.47% of home market value, followed by Illinois and Connecticut."],
            ['q' => "How does calculating effective rate help when buying a home?", 'a' => "It allows you to compare the real tax burden of homes in different neighborhoods, regardless of how local tax assessors calculate assessments."],
            ['q' => "Does my effective tax rate change when my home value goes up?", 'a' => "If your home value increases faster than your tax bill, your effective rate decreases. If taxes rise faster than market value, your effective rate increases."],
            ['q' => "Does a high effective tax rate decrease home values?", 'a' => "High tax rates can reduce buying power by increasing monthly payments, but they also fund amenities like good schools that support home values."],
            ['q' => "Can I appeal my taxes if my effective rate is higher than my neighbors?", 'a' => "Yes. If your effective rate is higher because your home's assessed value is set higher than similar nearby properties, it provides strong evidence for an appeal."]
        ],
        'state-tax-comparison-calculator' => [
            ['q' => "What is the State-by-State Tax Comparison Tool?", 'a' => "It is a relocation planning widget that lets you select any two US states, enter a salary and property value, and compare estimated income and property taxes side-by-side."],
            ['q' => "How do you calculate state income taxes in the comparison tool?", 'a' => "The tool uses the progressive or flat tax brackets of both selected states from our tax database, applying state standard deductions to estimate the tax liability for each state."],
            ['q' => "How are property taxes estimated for the comparison?", 'a' => "Property tax comparison uses the average effective property tax rate of each state, which is the actual percentage of market value paid by homeowners in that state."],
            ['q' => "Which states are the most tax-friendly for relocation?", 'a' => "States like Alaska, Florida, Texas, and Nevada have no state income tax. However, states with no income tax often have higher property or sales tax rates to fund public services."],
            ['q' => "Can this tool help me decide where to move for remote work?", 'a' => "Yes. By comparing your current state's tax burden with a prospective state, you can estimate your net take-home pay differences and housing tax costs side-by-side."],
            ['q' => "Does the comparison tool include local city or county taxes?", 'a' => "The tool uses state-wide average rates for property and income taxes to represent general burdens. Local municipal, county, or city taxes can vary significantly."],
            ['q' => "Are FICA taxes included in the comparison?", 'a' => "Federal FICA taxes (Social Security and Medicare) are identical across all states, so the comparison focuses primarily on state income and property taxes where differences exist."],
            ['q' => "How does filing status affect the state tax comparison?", 'a' => "Many states have separate tax brackets for Single, Married Filing Jointly, and Head of Household filers. The comparison tool adjusts standard deductions and brackets accordingly."],
            ['q' => "What is considered a flat tax state?", 'a' => "A flat tax state levies a single tax rate on all taxable income (e.g. Indiana at 3.05%), meaning high and low earners pay the same percentage rate on their income."],
            ['q' => "Why does the comparison tool show such a large tax difference?", 'a' => "Moving from a high-tax state (like California or New Jersey) to a low-tax state (like Texas or Florida) can result in thousands of dollars in annual savings depending on your income and property value."]
        ]
    ];
    return isset($faqs[$slug]) ? $faqs[$slug] : [];
}

/**
 * Generates dynamic SEO article content for Other Tax Calculators
 */
function ust_get_other_tax_default_content($slug) {
    switch ($slug) {
        case 'federal-income-tax-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Demystifying the Federal Tax System</h2>
<p>Calculating your actual tax liability goes beyond just looking at your base tax rate. Between progressive marginal tax rates, mandatory FICA payroll withholdings, and standard deduction options, determining your net pay can get complicated. Our federal income tax calculator helps you estimate your tax liability and net take-home salary.</p>

<h2>2. How the IRS Graduated Brackets Work</h2>
<p>The United States employs a progressive income tax system. This means your income is divided into segments, and higher rates are applied only to earnings that fall within those specific ranges. For example, being in the 22% bracket does not mean you pay 22% on your entire income. Lower segments of your income are still taxed at 10% and 12%, making your average tax rate lower than your marginal bracket.</p>

<h2>3. Deductions & Contributions: Reducing Taxable Income</h2>
<p>Before tax brackets are applied, the IRS allows you to reduce your taxable income using deductions. Most taxpayers claim the standard deduction ($15,000 for Single filers). Additionally, any pre-tax contributions you make to traditional 401(k) retirement plans or Health Savings Accounts (HSAs) are subtracted from your gross wages, helping you save money on taxes.</p>

<h2>4. Understanding Your Withholdings</h2>
<p>Your paycheck also includes mandatory FICA taxes. This is a flat tax that funds Social Security (6.2% on wages up to $168,600) and Medicare (1.45% on all earnings). Highly compensated individuals also face an additional 0.9% Medicare surtax on earnings above $200,000 (or $250,000 for married couples).</p>';

        case 'state-income-tax-calculator':
            return '<!-- ust-v1-article -->
<h2>1. State Income Taxes: Flat vs. Graduated Systems</h2>
<p>While federal income tax applies uniformly nationwide, state income taxes vary depending on where you live. Some states utilize a flat income tax rate, charging all income levels the same percentage. Others employ graduated progressive tax brackets similar to the federal system, while a few states charge no personal state income tax on wage earnings.</p>

<h2>2. Flat Tax States vs. Progressive Systems</h2>
<p>States like Pennsylvania (3.07%) and Indiana (3.05%) use a flat tax system, making calculations simple and predictable. Progressive states like California and New York apply higher tax rates to higher earnings, with top brackets reaching 13.3% and 10.9% respectively. These state taxes are calculated on top of your federal liabilities, affecting your overall net pay.</p>

<h2>3. States Without Personal Wage Income Tax</h2>
<p>Living in one of the eight states with no wage income tax—such as Florida, Texas, Nevada, or Washington—can significantly increase your take-home pay. However, these states often offset the lack of income tax with higher property taxes or sales taxes to fund local government operations.</p>

<h2>4. Working Across State Borders</h2>
<p>If you live in one state but work in another, you may have to file a non-resident tax return in the state where you work. Fortunately, many neighboring states have tax reciprocity agreements that allow commuters to pay income tax only to their home state, preventing double taxation.</p>';

        case 'income-tax-refund-calculator':
            return '<!-- ust-v1-article -->
<h2>1. The Truth About Your Annual Tax Refund</h2>
<p>Receiving a large tax refund check in the spring is often celebrated, but it represents the return of an interest-free loan you gave to the IRS. If you received a refund, it means your employer withheld too much tax from your paychecks throughout the year. Our refund estimator helps you calculate your tax balance and adjust your withholdings.</p>

<h2>2. Deciphering Owed vs. Refunded Balances</h2>
<p>When you file your tax return, your total tax liability is compared with the payments already made through paycheck withholdings. If you paid more than you owe, you receive a refund. If your withholdings were insufficient, you will owe the remaining balance to the IRS and may face underpayment interest charges.</p>

<h2>3. The Financial Strategy of a Zero-Balance Return</h2>
<p>The most efficient financial goal is to owe nothing and receive no refund, which is known as a zero-balance return. By adjusting your W-4 form to match withholdings closely with actual liabilities, you keep more money in your monthly paychecks to save, invest, or cover regular expenses throughout the year.</p>

<h2>4. How to Update Your Paycheck Withholdings</h2>
<p>If you received a large refund or owed a significant tax bill last year, you should submit a new W-4 form to your employer. Use this calculator to estimate the adjustments needed to increase or decrease your paycheck withholding amounts safely.</p>';

        case 'tax-withholding-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Withholding Optimization: Balancing Your Paycheck</h2>
<p>Managing the amount of tax withheld from your paycheck is key to avoiding an unexpected tax bill in April. Our withholding calculator analyzes your gross pay, pay period frequency, and W-4 status to estimate your federal withholding and help you determine if your tax payments are on track.</p>

<h2>2. How Employers Calculate Paycheck Withholding</h2>
<p>Employers use IRS tables to calculate tax withholding by annualizing your paycheck earnings, applying your W-4 filing status, subtracting standard deductions, and computing the estimated tax. This annual tax is then divided back down by your pay frequency (e.g. 26 pay periods for bi-weekly) to determine the tax deducted from each check.</p>

<h2>3. Adjusting Your Withholding via Form W-4</h2>
<p>If your withholding is too low, you can submit a new Form W-4 to your employer and enter an extra withholding amount on Line 4(c). If you want to lower withholdings to increase take-home pay, you can claim tax credits for dependents or enter additional deductions on the form.</p>

<h2>4. Who Should Monitor Paycheck Withholdings?</h2>
<p>Withholding checks are especially important for dual-income households, individuals with seasonal side jobs, and taxpayers who experienced major life events like marriage, buying a home, or the birth of a child.</p>';

        case 'tax-bracket-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Understanding Federal Income Tax Brackets</h2>
<p>Federal income tax is calculated using a graduated system with rates of 10%, 12%, 22%, 24%, 32%, 35%, and 37%. Our tax bracket calculator helps you find which bracket your taxable income falls into and estimates your marginal and effective tax rates based on the latest IRS thresholds.</p>

<h2>2. Marginal Tax Rates vs. Effective Tax Rates</h2>
<p>Your marginal rate is the tax percentage applied to the last dollar you earn (your highest bracket). Your effective tax rate is your total tax paid divided by your taxable income (your average rate). Because of the progressive nature of the tax code, your effective rate is always lower than your marginal bracket.</p>

<h2>3. How Graduated Brackets are Calculated</h2>
<p>Entering a higher tax bracket does not increase the tax rate on your entire income. Only the income above the bracket threshold is taxed at the higher rate. For example, if you enter the 22% bracket, your lower earnings are still taxed at 10% and 12%, protecting your take-home pay.</p>

<h2>4. Shifting Brackets with Deductions</h2>
<p>You can lower your tax bracket by reducing your taxable income. Contributing to pre-tax retirement plans (traditional 401k or IRA) and funding Health Savings Accounts (HSAs) lowers your Adjusted Gross Income, which can push you into a lower marginal tax bracket.</p>';

        case 'estimated-tax-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Estimated Taxes: A Guide for the Self-Employed</h2>
<p>Unlike W-2 employees who have taxes withheld from each paycheck, self-employed individuals, freelancers, and S-corporation shareholders must calculate and submit estimated tax payments to the IRS four times a year. Our estimated tax calculator helps you estimate your quarterly liabilities.</p>

<h2>2. The Self-Employment Tax Liability</h2>
<p>Self-employed workers must pay a 15.3% self-employment tax (Schedule SE) on 92.35% of their net business profits. This tax covers Social Security (12.4%) and Medicare (2.9%) obligations that are typically split between employees and employers in a standard job.</p>

<h2>3. Calculating Your Quarterly Payments</h2>
<p>To calculate quarterly payments, you must estimate your annual net business profit, subtract self-employment tax deductions, apply standard federal deductions, and compute your progressive income tax. The total estimated tax is then divided into four equal payments due in April, June, September, and January.</p>

<h2>4. Avoiding IRS Underpayment Penalties</h2>
<p>To avoid underpayment penalties, you must pay at least 90% of your current year\'s tax liability or 100% of your prior year\'s tax (110% if your AGI was over $150,000) through quarterly estimated payments or withholding from a W-2 job.</p>';

        case 'capital-gains-tax-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Capital Gains Tax: Maximizing Investment Payouts</h2>
<p>When you sell an investment asset like stocks, cryptocurrency, or real estate for a profit, the gain is subject to capital gains tax. Our capital gains calculator helps you estimate your tax liability based on your purchase cost, selling price, and holding period.</p>

<h2>2. Short-Term vs. Long-Term Capital Gains</h2>
<p>Assets held for one year or less are subject to short-term capital gains tax, which is calculated at your standard personal income tax rate. Assets held for more than one year qualify for long-term capital gains tax, which is calculated at lower rates of 0%, 15%, or 20% depending on your taxable income.</p>

<h2>3. Adjusting Your Cost Basis</h2>
<p>Your taxable capital gain is calculated by subtracting your cost basis from the final selling price. You can reduce your taxable gain by adding purchase fees, transaction commissions, and capital improvement costs to your original purchase price to increase your cost basis.</p>

<h2>4. Primary Residence Exclusions & Loss Offsets</h2>
<p>If you sell your primary home, you can exclude up to $250,000 of gain (Single) or $500,000 (Married) if you lived in the home for 2 of the past 5 years. You can also offset capital gains by selling losing investments, which is a tax strategy known as tax-loss harvesting.</p>';

        case 'self-employment-tax-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Self-Employment Taxes: Schedule SE and the 15.3% Rate</h2>
<p>Freelancers, independent 1099 contractors, and sole proprietors must pay a dedicated self-employment tax to cover their Social Security and Medicare contributions. Our self-employment tax calculator estimates this liability based on your net business earnings.</p>

<h2>2. The FICA Split for Self-Employed Workers</h2>
<p>W-2 employees pay 7.65% for FICA, and their employers match that payment. Self-employed individuals must pay both the employee and employer portions, resulting in a total self-employment tax rate of 15.3% on 92.35% of their net business profits.</p>

<h2>3. Social Security Cap and Medicare Surtax</h2>
<p>The 12.4% Social Security portion of the tax only applies to the first $168,600 of combined wage and business earnings. The 2.9% Medicare portion has no income limit, and high-income self-employed workers face an additional 0.9% Medicare surtax on earnings over $200,000 (Single) or $250,000 (Married).</p>

<h2>4. Above-the-Line Tax Deductions</h2>
<p>To offset the employer portion of the tax, the IRS allows you to deduct 50% of your total self-employment tax liability as an adjustment to income on Form 1040, which reduces your taxable income subject to regular income taxes.</p>';

        case 'payroll-tax-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Payroll Taxes: Employee & Employer Liabilities</h2>
<p>Payroll taxes are shared liabilities calculated from employee wages. Employers are responsible for withholding taxes from employee paychecks and paying matching contributions. Our payroll tax calculator provides a breakdown of these withholdings and employer burdens.</p>

<h2>2. Employee Withholdings: FICA and Income Taxes</h2>
<p>Employers must withhold flat FICA taxes from employee paychecks, which consist of 6.2% for Social Security (up to the $168,600 wage limit) and 1.45% for Medicare. Progressive federal and state income taxes are also withheld based on the employee\'s W-4 parameters.</p>

<h2>3. Employer Matching Contributions</h2>
<p>Employers must match employee FICA contributions by paying an additional 6.2% for Social Security and 1.45% for Medicare. These employer payroll contributions are tax-deductible as business expenses.</p>

<h2>4. Federal and State Unemployment Taxes (FUTA & SUTA)</h2>
<p>Employers must also pay Federal Unemployment Tax (FUTA) and State Unemployment Tax (SUTA). FUTA is a flat 0.6% tax on the first $7,000 of employee wages, while SUTA rates vary depending on the employer\'s history of unemployment claims in that state.</p>';

        case 'sales-tax-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Sales Taxes: Combined Rates and Local Surtaxes</h2>
<p>Unlike other nations with a unified value-added tax (VAT), sales tax in the United States is calculated using hierarchical rates that sum state baseline taxes and local municipal surtaxes. Our sales tax calculator estimates the total tax and final cost of purchases.</p>

<h2>2. How Combined Sales Taxes are Structured</h2>
<p>When purchasing an item, your sales tax bill is a combination of your state\'s baseline rate (e.g. California at 7.25%), county taxes, and city or transit district taxes. Combined rates can vary significantly between adjacent municipalities, affecting the cost of large purchases.</p>

<h2>3. Tax-Exempt Goods & Exclusions</h2>
<p>Most states exempt essential goods like unprepared foods (groceries) and prescription medicines from sales tax, though over-the-counter drugs and restaurant meals remain taxable. Use this calculator to apply custom exclusions for tax-exempt items.</p>

<h2>4. Online Purchases and Use Taxes</h2>
<p>Following recent tax laws, online retailers must collect sales tax based on the buyer\'s delivery address. If an out-of-state retailer does not collect sales tax, you are technically required to report and pay use tax on those goods to your resident state.</p>';

        case 'property-tax-estimator':
            return '<!-- ust-v1-article -->
<h2>1. Estimating Real Estate Property Taxes</h2>
<p>Property taxes are local taxes levied by counties, cities, and school districts to fund services like schools, libraries, road maintenance, and emergency response. Our property tax estimator calculates your annual tax and monthly escrow payments based on market value and effective rates.</p>

<h2>2. Assessed Value vs. Fair Market Value</h2>
<p>Property tax is calculated on your home\'s assessed value, which is determined by local government tax assessors. The assessed value is often a percentage of market value (the assessment ratio), meaning your taxable assessed value can be lower than your home\'s appraisal value.</p>

<h2>3. Understanding Millage Rates</h2>
<p>Local tax rates are often expressed as millage rates (mills), which represents the tax owed per $1,000 of assessed value. For example, a millage rate of 20 mills means you pay $20 for every $1,000 of assessed value (which is a 2% tax rate).</p>

<h2>4. Homestead Exemptions and Escrow Accounts</h2>
<p>Many jurisdictions offer homestead exemptions that reduce the taxable assessed value of primary residences, protecting homeowners from rising taxes. Most lenders require property taxes to be paid monthly as part of your mortgage payment and held in an escrow account.</p>';

        case 'effective-property-tax-rate-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Effective vs. Nominal Property Tax Rates</h2>
<p>When comparing property tax burdens across different neighborhoods or states, nominal millage rates can be misleading because of varying assessment practices. Calculating your effective property tax rate—which is actual taxes paid divided by market value—is the best way to compare tax burdens.</p>

<h2>2. Why Assessment Ratios Mislead Homeowners</h2>
<p>Some counties assess properties at 100% of fair market value, while others assess at a fraction of market value (e.g. 10%). A home with a high nominal tax rate but a low assessment ratio can have a lower actual tax bill than a home in a low-rate county with a high assessment ratio.</p>

<h2>3. How to Calculate Your Effective Tax Rate</h2>
<p>To find your effective property tax rate, divide your total annual property tax bill by the current market value of your home, then multiply by 100. This provides a percentage rate that represents your true tax burden relative to your housing wealth.</p>

<h2>4. Appealing Unfair Property Tax Assessments</h2>
<p>If your effective property tax rate is higher than similar properties in your neighborhood, it suggests your home is over-assessed. You can use this effective rate calculation as evidence to file an appeal with your local tax board and request a assessment reduction.</p>';

        case 'state-tax-comparison-calculator':
            return '<!-- ust-v1-article -->
<h2>1. Comparing State Tax Burdens Before Relocating</h2>
<p>When planning a relocation, comparing state tax rates is crucial. Our state-by-state comparison tool calculates both state income tax and property tax side-by-side. This helps you identify how your take-home pay and homeownership costs will change, giving you a complete view of your tax liabilities.</p>

<h2>2. Income Tax Differences: Progressive vs. Flat vs. None</h2>
<p>State income tax structures vary widely across the country. Progressive states like California charge up to 13.3% on high earnings, while flat-rate states charge a single percentage on all income. Nine states levy no personal income tax on wages, which can significantly increase your take-home pay depending on your earnings.</p>

<h2>3. Property Taxes: Why Effective Rates Matter</h2>
<p>Property taxes can be a major expense. To compare property taxes accurately, our tool uses effective tax rates rather than nominal millage rates. Since assessment ratios vary, using the effective rate—actual taxes paid divided by market value—provides the most accurate cost comparison.</p>

<h2>4. The Combined Tax Burden: Finding the Real Savings</h2>
<p>A low income tax state might have high property taxes, while a low property tax state might have high sales taxes. To find your true savings, you must look at the combined state tax burden. This tool aggregates estimated income and property taxes to show your overall financial picture in both states.</p>';
    }

    return '<h2>Introduction</h2><p>This general tax calculator helps you estimate your tax burdens quickly.</p>';
}
