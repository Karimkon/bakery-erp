<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportsExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $data;
    protected $reportType;

    public function __construct(array $data, string $reportType)
    {
        $this->data = $data;
        $this->reportType = $reportType;
    }

    public function array(): array
    {
        switch ($this->reportType) {
            case 'income_statement':
                return $this->formatIncomeStatement();
            case 'balance_sheet':
                return $this->formatBalanceSheet();
            case 'cash_flow':
                return $this->formatCashFlowStatement();
            default:
                return [];
        }
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'income_statement':
                return ['Item', 'Description', 'Amount (UGX)'];
            case 'balance_sheet':
                return ['Assets', 'Amount (UGX)', 'Liabilities & Equity', 'Amount (UGX)'];
            case 'cash_flow':
                return ['Category', 'Inflows (UGX)', 'Outflows (UGX)', 'Net Cash Flow (UGX)'];
            default:
                return [];
        }
    }

    public function title(): string
    {
        $titles = [
            'income_statement' => 'Income Statement',
            'balance_sheet' => 'Balance Sheet',
            'cash_flow' => 'Cash Flow Statement'
        ];

        return $titles[$this->reportType] ?? 'Financial Report';
    }

    private function formatIncomeStatement(): array
    {
        $data = $this->data;
        $rows = [];

        // Revenue Section
        $rows[] = ['SALES REVENUE', '', ''];
        $rows[] = ['Bakery Sales', 'Daily bakery sales', number_format($data['revenue']['bakery_sales'] ?? 0)];
        $rows[] = ['Dispatch Sales', 'Driver route sales', number_format($data['revenue']['dispatch_sales'] ?? 0)];
        $rows[] = ['Kampala Shop Sales', 'Retail shop sales', number_format($data['revenue']['kampala_sales'] ?? 0)];
        $rows[] = ['Damage Sales', 'Sold damaged goods', number_format($data['revenue']['damage_sales'] ?? 0)];
        $rows[] = ['TOTAL REVENUE', '', number_format($data['revenue']['total_revenue'] ?? 0)];
        $rows[] = ['', '', ''];

        // COGS Section
        $rows[] = ['COST OF GOODS SOLD (COGS)', '', ''];
        $rows[] = ['Ingredient Costs', 'Flour, sugar, etc.', number_format($data['cogs']['ingredient_costs'] ?? 0)];
        $rows[] = ['Packaging Costs', 'Bags, boxes, etc.', number_format($data['cogs']['packaging_costs'] ?? 0)];
        $rows[] = ['TOTAL COGS', '', number_format($data['cogs']['total_cogs'] ?? 0)];
        $rows[] = ['', '', ''];

        // Gross Profit
        $rows[] = ['GROSS PROFIT', 'Sales - COGS', number_format($data['gross_profit'] ?? 0)];
        $rows[] = ['', '', ''];

        // Operating Expenses
        $rows[] = ['OPERATING EXPENSES', '', ''];
        $rows[] = ['Rent', 'Shop/bakery rent', number_format($data['operating_expenses']['rent'] ?? 0)];
        $rows[] = ['Salaries', 'Staff wages', number_format($data['operating_expenses']['salaries'] ?? 0)];
        $rows[] = ['Utilities', 'Electricity, water', number_format($data['operating_expenses']['utilities'] ?? 0)];
        $rows[] = ['Transport', 'Delivery costs', number_format($data['operating_expenses']['transport'] ?? 0)];
        $rows[] = ['Kampala Expenses', 'Shop operating costs', number_format($data['operating_expenses']['kampala_expenses'] ?? 0)];
        $rows[] = ['Other Expenses', 'Miscellaneous', number_format($data['operating_expenses']['other'] ?? 0)];
        $rows[] = ['TOTAL OPERATING EXPENSES', '', number_format($data['operating_expenses']['total_expenses'] ?? 0)];
        $rows[] = ['', '', ''];

        // Net Profit
        $rows[] = ['NET PROFIT', 'Gross Profit - Expenses', number_format($data['net_profit'] ?? 0)];

        return $rows;
    }

    private function formatBalanceSheet(): array
    {
        $data = $this->data;
        $rows = [];

        // Assets
        $rows[] = ['ASSETS', number_format($data['assets']['cash'] ?? 0), 'LIABILITIES', number_format($data['liabilities']['accounts_payable'] ?? 0)];
        $rows[] = ['Cash', '', 'Accounts Payable', ''];
        $rows[] = ['Equipment (Oven, Mixers)', number_format($data['assets']['equipment'] ?? 0), 'Bank Loan', number_format($data['liabilities']['bank_loan'] ?? 0)];
        $rows[] = ['Inventory (Flour, Sugar, etc.)', number_format($data['assets']['inventory'] ?? 0), 'TOTAL LIABILITIES', number_format($data['liabilities']['total_liabilities'] ?? 0)];
        $rows[] = ['TOTAL ASSETS', number_format($data['assets']['total_assets'] ?? 0), '', ''];
        $rows[] = ['', '', 'OWNER\'S EQUITY', number_format($data['equity']['owners_equity'] ?? 0)];
        $rows[] = ['', '', 'TOTAL LIABILITIES + EQUITY', number_format($data['assets']['total_assets'] ?? 0)];

        return $rows;
    }

    private function formatCashFlowStatement(): array
    {
        $data = $this->data;
        $rows = [];

        // Operating Activities
        $rows[] = ['OPERATING ACTIVITIES', number_format($data['operating_activities']['cash_sales'] ?? 0), number_format($data['operating_activities']['cash_expenses'] ?? 0), ''];
        $rows[] = ['Cash Sales', '', '', ''];
        $rows[] = ['Cash Collections', number_format($data['operating_activities']['cash_collections'] ?? 0), '', ''];
        $rows[] = ['Cash Expenses', '', number_format($data['operating_activities']['cash_expenses'] ?? 0), ''];
        $rows[] = ['NET OPERATING CASH', '', '', number_format($data['operating_activities']['net_operating_cash'] ?? 0)];
        $rows[] = ['', '', '', ''];

        // Investing Activities
        $rows[] = ['INVESTING ACTIVITIES', '', number_format($data['investing_activities']['equipment_purchases'] ?? 0), ''];
        $rows[] = ['Equipment Purchases', '', number_format($data['investing_activities']['equipment_purchases'] ?? 0), ''];
        $rows[] = ['NET INVESTING CASH', '', '', number_format($data['investing_activities']['net_investing_cash'] ?? 0)];
        $rows[] = ['', '', '', ''];

        // Financing Activities
        $rows[] = ['FINANCING ACTIVITIES', number_format($data['financing_activities']['loan_received'] ?? 0), number_format($data['financing_activities']['loan_repayments'] ?? 0), ''];
        $rows[] = ['Loan Received', number_format($data['financing_activities']['loan_received'] ?? 0), '', ''];
        $rows[] = ['Loan Repayments', '', number_format($data['financing_activities']['loan_repayments'] ?? 0), ''];
        $rows[] = ['NET FINANCING CASH', '', '', number_format($data['financing_activities']['net_financing_cash'] ?? 0)];
        $rows[] = ['', '', '', ''];

        // Net Cash Flow
        $rows[] = ['NET CASH FLOW', '', '', number_format($data['net_cash_flow'] ?? 0)];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => ['font' => ['bold' => true]],
            
            // Section headers
            'A1:C1' => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFE6E6FA']]],
        ];
    }
}