<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const YNAB_ACCOUNT_ID = '';
    const ACCOUNT_MAP = [
        88 => ['id' => '0bf963bd-539a-43ff-a1ba-bbbc9cd49a0f', 'name' => 'Ally Checking'],
        29 => ['id' => '50ef91ca-6cef-430f-a5b8-f9d37c5dc29c', 'name' => 'Alta Grove Apartments'],
        3 => ['id' => '518b9487-9c7f-4bc1-a202-408b7bd10630', 'name' => 'Ameritrade'],
        2 => ['id' => '13209970-871b-4fb3-bf31-0fe160edde56', 'name' => 'BOA Checking'],
        8 => ['id' => 'c5d4ed0a-95e0-4c78-a734-4ffa31ec370e', 'name' => 'BOA Credit'],,
        1 => ['id' => '601a6bf8-8e52-4b2e-be68-6340ea826951', 'name' => 'BOA Savings'],
        81 => ['id' => '5db34709-86e4-44fe-937e-7c5816344dd2', 'name' => 'Barclay Card'],
        84 => ['id' => '9a87591f-53fa-4a20-ac3d-00555bf09b37', 'name' => 'Carolinas Healthcare'],
        4 => ['id' => '86578a0f-ee0b-488d-9131-047e0104518b', 'name' => 'Cash'],
        99 => ['id' => '383a78a5-06d3-4ec9-93cc-346132c8a211', 'name' => 'Chase Freedom'],
        5 => ['id' => '', 'name' => ''], 'Check',
        37 => ['id' => 'e5c35480-d72f-4185-875f-79c5cdf15be4', 'name' => 'Citi Double Cash'],
        21 => ['id' => '001c4201-131a-44c9-bb96-541e5eb3b32', 'name' => 'Discover'],
        83 => ['id' => '8c183adc-8f05-4df6-9e78-7137cc578d1b', 'name' => 'Gift Cards'],
        7 => ['id' => '8c183adc-8f05-4df6-9e78-7137cc578d1b', 'name' => 'Gift Cards'],
        36 => ['id' => '8c183adc-8f05-4df6-9e78-7137cc578d1b', 'name' => 'Gift Cards'],
        31 => ['id' => '8c183adc-8f05-4df6-9e78-7137cc578d1b', 'name' => 'Gift Cards'],
        35 => ['id' => '8c183adc-8f05-4df6-9e78-7137cc578d1b', 'name' => 'Gift Cards'],
        85 => ['id' => '', 'name' => ''], 'Home Equity Line of Credit',
        89 => ['id' => '734e751c-0038-4f25-95a3-94b36492c317', 'name' => 'Interactive Brokers'],
        90 => ['id' => '8f3f90b1-009e-4b1c-900a-5009c6345b61', 'name' => 'Kraken'],
        20 => ['id' => 'f7614085-1479-4c1b-94d0-b9ef0c077ad3', 'name' => 'Merrill Edge'],
        34 => ['id' => 'b7e3601a-b416-460f-a956-516abb84e987', 'name' => 'Money Orders'],
        22 => ['id' => '5fccf2a5-d9d2-418a-8e18-91569a5c8029', 'name' => 'Oanda'],
        27 => ['id' => 'a6565b59-23e9-45a2-8ae8-4975f4818047', 'name' => 'Paypal'],
        33 => ['id' => '1c9601dd-a685-496b-8bf0-a5eb8b50a01c', 'name' => 'Property Matters Realty'],
        94 => ['id' => '6c12f146-4d62-42f4-91ba-036bddb40616', 'name' => 'Robinhood'],
        93 => ['id' => 'b5fd1b11-2548-4e20-905d-53a140e92c9f', 'name' => 'Stockpile'],
        91 => ['id' => 'b9d0b519-988e-4669-b1da-62c005dc9299', 'name' => 'Uphold'],
        23 => ['id' => 'e4053125-ad42-43bd-bb26-9173bf7a8a47', 'name' => 'Wachovia Checking'],
        24 => ['id' => 'ffcd76e7-429c-4821-92b5-4a3ea7c436bf', 'name' => 'Wachovia Savings'],
        25 => ['id' => 'ac3285e8-545d-4a1b-90df-6f1a864282dc', 'name' => 'Wachovia Credit'],
        97 => ['id' => '52332f22-7a26-48eb-bf2b-0b57b244b301', 'name' => 'Wells Fargo Checking'],
        19 => ['id' => '540554da-e443-4924-a8e2-650d5b1dff02', 'name' => 'Wells Fargo Mortgage'],
        32 => ['id' => '224e5b7e-9668-4b02-94f2-3efd6681b563', 'name' => 'Wells Fargo Credit'],
    ];
    const CATEGORY_MAP = [
        50 => [], // 1011 Holland: Income / General Expenses
        90 => [], // 1011 Holland: Insurance
        8 => [], // 1011 Holland: Mortgage
        114 => [], // 1011 Holland: Property Taxes
        390 => [], // 103 Killian Ave: Purchase Costs
        412 => [], // 108 W. Lee: Improvements
        400 => [], // 108 W. Lee: Insurance
        399 => [], // 108 W. Lee: Property Taxes
        397 => [], // 108 W. Lee: Purchase Costs
        404 => [], // 108 W. Lee: Repairs / Maintenance
        376 => [], // 1649 Hoffman Rd: HELOC [Bank of America ]
        394 => [], // 1649 Hoffman Rd: Loan Expenses
        379 => [], // 1649 Hoffman Rd: Sale
        108 => [], // 1649 Hoffman: Insurance
        106 => [], // 1649 Hoffman: Mortgage
        12 => [], // 1649 Hoffman: Repairs / Maintenance
        377 => [], // 1829 Hart Rd: Purchase / Sale
        60 => [], // Adjust
        29 => [], // Auto Loan
        13 => [], // Auto: Insurance
        30 => [], // Auto: Maintenance
        31 => [], // Auto: Misc
        32 => [], // Auto: Tax
        33 => [], // Business Expense
        34 => [], // Cashback
        35 => [], // Change
        36 => [], // Clothes
        37 => [], // Computer
        93 => [], // Credit / Loan Interest Payment
        96 => [], // Credit Card Payment
        78 => [], // Deposit
        4 => [], // Education
        62 => [], // Fee
        403 => [], // Fitness
        1 => [], // Food
        74 => [], // Forex
        7 => [], // Gas
        94 => [], // Gift Card - Spending
        38 => [], // Gifts
        39 => [], // Giving
        40 => [], // Haircuts
        55 => [], // Health & Beauty Aids
        41 => [], // Home Office
        115 => [], // Hosting
        42 => [], // Household Supplies
        112 => [], // Household: Appliances
        43 => [], // Household: Furniture
        410 => [], // Household: General
        66 => [], // Income: Employment Paycheck
        69 => [], // Income: General
        65 => [], // Insurance Claim
        44 => [], // Insurance: Renters
        414 => [], // Internet
        45 => [], // Internet / TV
        46 => [], // Investments
        95 => [], // Invoice Payment
        72 => [], // Loan
        48 => [], // Medical / Health
        49 => [], // Misc
        14 => [], // Miscellanous
        98 => [], // Money Order Purchase
        105 => [], // Moving Expenses
        9 => [], // N/A
        107 => [], // New Home Purchase
        110 => [], // Paycheck - Bonus
        92 => [], // Paycheck - Salary
        2 => [], // Phone
        380 => [], // Purchase for Others
        76 => [], // Rebate
        61 => [], // Refund
        88 => [], // Reimbursable
        63 => [], // Reimbursement
        11 => [], // Rent
        367 => [], // Residence - Property Taxes
        89 => [], // Returned Check
        51 => [], // Rewards Program
        382 => [], // SECU - 1011 Holland HELOC
        369 => [], // Software Services
        3 => [], // Spending
        393 => [], // Spending - Others
        378 => [], // Storage
        370 => [], // Subscriptions
        111 => [], // Tax Returns
        52 => [], // Taxes
        27 => [], // Taxes: Accounting Services
        53 => [], // Temporary
        54 => [], // Tithe
        413 => [], // Tools & Equipment
        396 => [], // Transfer
        57 => [], // Utilities
        401 => [], // Utilities: Electric
        109 => [], // Utilities: Gas
        59 => [], // Withdrawal
    ];
    const VENDOR_MAP = [];

    protected $table = 'transactions';
    protected $fillable = [
        'account_id',
        'amount',
        'bank_reference_id',
        'category_id',
        'check_number',
        'commission',
        'cusip',
        'date_bank_processed',
        'date',
        'gross',
        'has_been_reimbursed',
        'is_refund',
        'is_reimbursable',
        'is_self_employment_income',
        'is_self_employment',
        'is_tax_deductible',
        'memo',
        'notes',
        'source_id',
        'symbol',
        'tips_taxed',
        'transfer_from_account_id',
        'transfer_id',
        'transfer_to_account_id',
        'type',
        'unit_price',
        'units',
        'use_tax',
        'import_id',
        'vendor_id',
        'ynab_id',
    ];

    public function account()
    {
        return $this->hasOne('App/Database/Models/Account', 'account_id', 'id');
    }

    public function category()
    {
        return $this->hasOne('App/Database/Models/Category', 'category_id', 'id');
    }

    public function from()
    {
        return $this->hasOne('App/Database/Models/AccountModel', 'transfer_from_account_id', 'id');
    }

    public function source()
    {
        return $this->hasOne('App/Database/Models/IncomeSource', 'source_id', 'id');
    }

    public function splits()
    {
        return $this->hasMany('App/Database/Models/TransactionSplit', 'id', 'transaction_id');
    }

    public function to()
    {
        return $this->hasOne('App/Database/Models/AccountModel', 'transfer_to_account_id', 'id');
    }

    public function toYnab()
    {
        $date = new DateTime($this->date_bank_processed);
        $vendor = $this->vendor;
        $account = self::ACCOUNT_MAP[$this->account_id] ?? null;
        $category = $this->ynabCategory($this->category_id);

        $data = [
            'account_id' => $account['id'],
            'date' => $date->format('Y-m-d'),
            'amount' => $this->amount * 100,
            // 'payee_id' => $payee->id,
            'payee_name' => $vendor->name ?? null,
            'category_id' => $category->id,
            'memo' => $this->memo,
            'cleared' => 'cleared',
            'approved' => true,
            'flag_color' => 'red',
            'import_id' => '365budget-' . $this->id,
            'subtransactions' => [],
        ];

        if ($this->type == 'transfer') {
            // $data['transfer_account_id'] = $payee->transfer_account_id;
            // $data['transfer_transaction_id'] = $payee->transfer_transaction_id;
        }

        foreach ($this->splits as $split) {
            $subCategory = $this->ynabCategory($split->category_id);

            $data['subtransactions'][] = [
                'amount' => $split->amount,
                // 'payee_id' => $vendor->id,
                'payee_name' => $vendor->name,
                'category_id' => $subCategory->id,
                'memo' => '',
            ];
        }
    }

    public function vendor()
    {
        return $this->hasOne('App/Database/Models/VendorModel', 'vendor_id', 'id');
    }

    protected function ynabAccount()
    {
        $id = null;
        $name = null;

        return (object) [
            'id' => $id,
            'name' => $name,
        ];
    }
}
