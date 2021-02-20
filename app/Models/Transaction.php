<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    const LM_UNCATEGORIZED_ID = ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'];
    const ACCOUNT_MAP = [
        88 => ['id' => '0bf963bd-539a-43ff-a1ba-bbbc9cd49a0f', 'name' => 'Ally Checking'],
        29 => ['id' => '50ef91ca-6cef-430f-a5b8-f9d37c5dc29c', 'name' => 'Alta Grove Apartments'],
        3 => ['id' => '518b9487-9c7f-4bc1-a202-408b7bd10630', 'name' => 'Ameritrade'],
        2 => ['id' => '13209970-871b-4fb3-bf31-0fe160edde56', 'name' => 'BOA Checking'],
        8 => ['id' => 'c5d4ed0a-95e0-4c78-a734-4ffa31ec370e', 'name' => 'BOA Credit'],
        1 => ['id' => '601a6bf8-8e52-4b2e-be68-6340ea826951', 'name' => 'BOA Savings'],
        81 => ['id' => '5db34709-86e4-44fe-937e-7c5816344dd2', 'name' => 'Barclay Card'],
        84 => ['id' => '9a87591f-53fa-4a20-ac3d-00555bf09b37', 'name' => 'Carolinas Healthcare'],
        4 => ['id' => '86578a0f-ee0b-488d-9131-047e0104518b', 'name' => 'Cash'],
        99 => ['id' => '383a78a5-06d3-4ec9-93cc-346132c8a211', 'name' => 'Chase Freedom'],
        5 => ['id' => '57e415cf-c8c8-43a9-8297-db97f3c60312', 'name' => 'Check'], 'Check',
        37 => ['id' => 'e5c35480-d72f-4185-875f-79c5cdf15be4', 'name' => 'Citi Double Cash'],
        21 => ['id' => '001c4201-131a-44c9-bb96-541e5eb3b32e', 'name' => 'Discover'],
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
        // 1011 Holland
        50 => ['id' => '3a4d2633-780b-4050-9771-92c1c2e17e1f', 'name' => 'Income'],
        90 => ['id' => 'c946ace0-a763-476c-8931-bd3b5c627f8d', 'name' => 'Insurance'],
        8 => ['id' => 'af23ac65-269c-4ca6-8876-fcc5f5c664ad', 'name' => 'Purchase'],
        114 => ['id' => '1a8b559c-9c0b-4e40-86fc-63d9bbe1d2ea', 'name' => 'Property Taxes'],
        382 => ['id' => 'eabdd00a-4ca5-41c0-9446-b1e164121f05', 'name' => 'HELOC'],

        // 103 Killian Ave
        390 => ['id' => '81fca01c-4153-4acf-80f6-51973fe9df2e', 'name' => 'Purchase'],

        // 108 W. Lee
        412 => ['id' => '63dda31e-fbc5-4282-adee-df320210624c', 'name' => 'Improvements'],
        400 => ['id' => '5ee5ba9e-a544-4d2c-aca1-3c4c999b2bd3', 'name' => 'Insurance'],
        399 => ['id' => '84a52a39-fc36-41ff-8bb3-af4583591bee', 'name' => 'Property Taxes'],
        397 => ['id' => 'c4422772-1ea8-487e-b68f-2c33102a0a1f', 'name' => 'Purchase'],
        404 => ['id' => 'bf716e71-2995-40e5-8732-cad0f7c1b795', 'name' => 'Repairs / Maintenance'],

        // 1649 Hoffman Rd
        376 => ['id' => '5f8224c6-a5d4-4a98-8eed-047567868e01', 'name' => 'HELOC'],
        394 => ['id' => '5f8224c6-a5d4-4a98-8eed-047567868e01', 'name' => 'HELOC'],
        379 => ['id' => '1d3e1a8a-a707-4f62-81d6-a9b0892d8e45', 'name' => 'Sale'],
        108 => ['id' => 'd752e3b2-c5d0-4696-847b-0fcf2e6c2aac', 'name' => 'Insurance'],
        106 => ['id' => 'e0c32de6-979b-4e81-99ae-a9bb75231c63', 'name' => 'Purchase'], // 1649 Hoffman: Mortgage
        12 => ['id' => '764fa57b-b0c7-45ed-9ba4-b9880284c718', 'name' => 'Repairs / Maintenance'], // 1649 Hoffman: Repairs / Maintenance

        // 1829 Hart Rd
        377 => ['id' => '98903e9c-2b4b-4c6c-9303-96cfe8c55690', 'name' => 'Purchase'], // 1829 Hart Rd: Purchase / Sale

        // Auto
        29 => ['id' => '48262dd5-1850-42fd-ae0a-f879d5c136ad', 'name' => 'Purchase'], // Auto Loan
        13 => ['id' => 'ace66392-a51c-4a94-b764-baf816d05bae', 'name' => 'Insurance'], // Auto: Insurance
        30 => ['id' => 'dab9159b-676e-4916-93ef-cdf276db0800', 'name' => 'Maintenance'], // Auto: Maintenance
        31 => ['id' => '13852748-0ad5-4639-95fc-c45f5453924d', 'name' => 'Miscellaneous'], // Auto: Misc
        32 => ['id' => 'd60214b3-3e49-47fe-93b2-f578852ba171', 'name' => 'Tax'], // Auto: Tax

        // General
        60 => ['id' => 'de440151-0f32-4900-adc0-9ad5632a4007', 'name' => 'Miscellaneous'], // Adjust
        14 => ['id' => 'de440151-0f32-4900-adc0-9ad5632a4007', 'name' => 'Miscellaneous'], // Miscellanous
        33 => ['id' => 'c528e1a8-35eb-40a3-b4ba-ee7dd2b44fe3', 'name' => 'Business'], // Business Expense
        36 => ['id' => '1a814cc3-0d45-437c-adc9-4bcdd68ec794', 'name' => 'Clothing'], // Clothes
        93 => ['id' => '15e25773-a694-48fe-bfb7-ecaa12dd0905', 'name' => 'Debt Service'], // Credit / Loan Interest Payment
        96 => ['id' => '15e25773-a694-48fe-bfb7-ecaa12dd0905', 'name' => 'Debt Service'], // Credit Card Payment
        62 => ['id' => 'bafe5483-37f9-4db0-ae68-c7d5ccf1bb5b', 'name' => 'Banking Fees'], // Fee
        89 => ['id' => 'bafe5483-37f9-4db0-ae68-c7d5ccf1bb5b', 'name' => 'Banking Fees'], // Returned Check
        403 => ['id' => '12599e66-36f6-4851-aa19-2f6337354c34', 'name' => 'Fitness'], // Fitness
        1 => ['id' => '581b2022-79a8-4c5b-a7f0-a609919a4202', 'name' => 'Food'], // Food
        7 => ['id' => '05931538-70c4-4b76-98ff-1049efae63e3', 'name' => 'Transportation'], // Gas
        40 => ['id' => 'a75e9fa5-64c9-4f75-9598-a6fe18979f3d', 'name' => 'Haircuts'], // Haircuts
        55 => ['id' => '7f8bc755-423f-40e5-908a-e248200bd3d3', 'name' => 'Health & Beauty'], // Health & Beauty Aids
        41 => ['id' => '3577668a-272d-44f7-853f-cd6cbd52e75e', 'name' => 'Home Office'], // Home Office
        37 => ['id' => '3577668a-272d-44f7-853f-cd6cbd52e75e', 'name' => 'Home Office'], // Computer
        44 => ['id' => '20196104-e588-4c19-8890-1c75eb5dc1fa', 'name' => "Renter's Insurance"], // Insurance: Renters
        414 => ['id' => '5e0289c9-496b-4124-8719-79566a01c231', 'name' => 'Internet'], // Internet
        45 => ['id' => '5e0289c9-496b-4124-8719-79566a01c231', 'name' => 'Internet'], // Internet / TV
        48 => ['id' => '2784d709-73bf-4b4d-add2-f2da4ca9d369', 'name' => 'Medical'], // Medical / Health
        49 => ['id' => 'de440151-0f32-4900-adc0-9ad5632a4007', 'name' => 'Miscellaneous'], // Misc
        105 => ['id' => '37dc0777-6dc0-472e-bc0d-1669a0f66884', 'name' => 'Moving'], // Moving Expenses
        2 => ['id' => '2724bf19-dbad-4536-8861-f0c90f55d3b5', 'name' => 'Phone'], // Phone
        11 => ['id' => '75057694-0461-4b04-b75c-39e76d0231bd', 'name' => 'Rent'], // Rent
        369 => ['id' => '39c81e06-68bb-42b0-948b-2098e75d5d60', 'name' => 'Software Subscriptions'], // Software Services
        413 => ['id' => 'b9473b7f-3c2d-40f5-b6cb-31f6b7b1e374', 'name' => 'Tools & Equipment'], // Tools & Equipment

        // Giving
        39 => ['id' => '35da1f91-303d-41b5-81c2-75e3654236d5', 'name' => 'Charitable'], // Giving
        38 => ['id' => 'fa35036b-ed03-4aa8-a5f5-a81b528ae684', 'name' => 'Gifts'], // Gifts
        54 => ['id' => '596c4d33-ff08-418e-b2cc-192619166caf', 'name' => 'Tithe'], // Tithe
        393 => ['id' => '25a89616-7e22-49ba-b776-0bf446465318', 'name' => 'General'], // Spending - Others
        380 => ['id' => '25a89616-7e22-49ba-b776-0bf446465318', 'name' => 'General'], // Purchase for Others

        // Household
        42 => ['id' => '4c416cf0-27ab-4b4a-8688-533bb445527a', 'name' => 'Supplies'], // Household Supplies
        112 => ['id' => 'c110a34e-3b8e-4559-85eb-d328677d39e6', 'name' => 'Appliances'], // Household: Appliances
        43 => ['id' => '2f5d2b24-49f6-4cd9-b64a-b09aa380c81e', 'name' => 'Furniture'], // Household: Furniture
        410 => ['id' => '9fd8db01-d61b-477c-a9c9-390a25620697', 'name' => 'General'], // Household: General

        // Income
        72 => ['id' => 'e81a68e9-3358-41ac-bd3f-05f16d09c727', 'name' => 'Loan'], // Loan
        66 => ['id' => '82d6e66c-d008-4e9d-b212-ba39135f0234', 'name' => 'Salary'], // Income: Employment Paycheck
        69 => ['id' => '29f79347-c6e1-4df2-9744-d43c82fb0d28', 'name' => 'Other'], // Income: General
        65 => ['id' => '248e0c75-ba64-44aa-a0b5-e2e89b7e3cff', 'name' => 'Insurance Claim'], // Insurance Claim
        110 => ['id' => '0acf4804-e574-47e5-9403-fe205992310c', 'name' => 'Bonus'], // Paycheck - Bonus
        92 => ['id' => '82d6e66c-d008-4e9d-b212-ba39135f0234', 'name' => 'Salary'], // Paycheck - Salary
        51 => ['id' => 'fff86572-70fb-4cbe-baff-406fdbbb643a', 'name' => 'Rewards Programs'], // Rewards Program
        34 => ['id' => 'fff86572-70fb-4cbe-baff-406fdbbb643a', 'name' => 'Rewards Programs'], // Cashback

        // Investments
        46 => ['id' => '49ba2dd5-b64f-4938-85e8-02f48f934ce9', 'name' => 'Brokerages'], // Investments
        74 => ['id' => '49ba2dd5-b64f-4938-85e8-02f48f934ce9', 'name' => 'Brokerages'], // Forex
        115 => ['id' => 'f4c6a961-7918-4509-94b6-dfc48539f76e', 'name' => 'Businesses'], // Hosting

        // Spending
        94 => ['id' => '9230edb1-b5e1-4db6-b8dc-a29185a9e1fc', 'name' => 'General'], // Gift Card - Spending
        3 => ['id' => '9230edb1-b5e1-4db6-b8dc-a29185a9e1fc', 'name' => 'General'], // Spending

        // Taxes
        111 => ['id' => 'fcce6f40-c685-477f-967f-59207e2d22db', 'name' => 'Returns'], // Tax Returns
        52 => ['id' => '47f3726d-0aa4-43db-bf89-dcffe6693574', 'name' => 'General'], // Taxes
        27 => ['id' => '2f77848d-0bc5-4626-ac83-e2e93c9f9310', 'name' => 'Accounting Fees'], // Taxes: Accounting Services

        // Utilities
        57 => ['id' => '60cb84c9-354e-456e-907b-4279f1b425e1', 'name' => 'General'], // Utilities
        401 => ['id' => '6cb612af-4cd9-4cfa-ad71-747cbd8eea8b', 'name' => 'Electric'], // Utilities: Electric
        109 => ['id' => '', 'name' => ''], // Utilities: Gas

        // Other
        4 => ['id' => 'd961eab3-ede1-4dce-9520-8c07454dc8b4', 'name' => 'Education'], // Education
        78 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Deposit
        35 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Change
        95 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Invoice Payment
        98 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Money Order Purchase
        9 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // N/A
        107 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // New Home Purchase
        76 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Rebate
        61 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Refund
        88 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Reimbursable
        63 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Reimbursement
        367 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Residence - Property Taxes
        378 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Storage
        370 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Subscriptions
        53 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Temporary
        396 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Transfer
        59 => ['id' => '966edc65-722a-4979-8c5e-7a5fd29071ab', 'name' => 'Uncategorized'], // Withdrawal
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
        'LM_id',
    ];

    public function account()
    {
        return $this->hasOne('App\Models\Account', 'id', 'account_id');
    }

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function from()
    {
        return $this->hasOne('App\Models\Account', 'id', 'transfer_from_account_id');
    }

    public function source()
    {
        return $this->hasOne('App\Models\IncomeSource', 'id', 'source_id');
    }

    public function splits()
    {
        return $this->hasMany('App\Models\TransactionSplit', 'transaction_id', 'id');
    }

    public function to()
    {
        return $this->hasOne('App\Models\Account', 'id', 'transfer_to_account_id');
    }

    public function toLM()
    {
        $dateString = $this->date_bank_processed < '2016-02-28' ? '2016-02-28' : $this->date_bank_processed;
        $date = new DateTime($dateString);
        $vendor = $this->vendor;
        $account = self::ACCOUNT_MAP[$this->account_id];
        $category = self::CATEGORY_MAP[$this->category_id] ?? self::LM_UNCATEGORIZED_ID;
        $amount = ((float) $this->amount) * 1000;

        $data = [
            'account_id' => $account['id'],
            'date' => $date->format('Y-m-d'),
            'amount' => round($amount, 2),
            // 'payee_id' => $payee->id,
            'payee_name' => $vendor->name ?? null,
            'category_id' => $category['id'],
            'memo' => Str::limit($this->memo, 190),
            'cleared' => 'cleared',
            'approved' => true,
            'flag_color' => 'red',
            'import_id' => '365budget-' . $this->id,
            'subtransactions' => [],
        ];

        foreach ($this->splits as $split) {
            $amount = ((float) $split->amount) * 1000;
            if ($amount != 0) {
                $subCategory = self::CATEGORY_MAP[$this->category_id] ?? null;

                $data['subtransactions'][] = [
                    'amount' => round($amount, 2),
                    // 'payee_id' => $vendor->id,
                    'payee_name' => $vendor->name ?? null,
                    'category_id' => $subCategory['id'] ?? null,
                    'memo' => '',
                ];
            }
        }

        return $data;
    }

    public function vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
    }

    protected function LMAccount()
    {
        $id = null;
        $name = null;

        return (object) [
            'id' => $id,
            'name' => $name,
        ];
    }
}
