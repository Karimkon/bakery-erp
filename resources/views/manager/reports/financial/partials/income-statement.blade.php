<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Income Statement (Profit & Loss)</h5>
        <small class="opacity-75">Period: {{ $data['period'] }}</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th width="40%">Item</th>
                        <th width="40%">Description</th>
                        <th width="20%" class="text-end">Amount (UGX)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Revenue Section -->
                    <tr class="table-info">
                        <td colspan="3"><strong>SALES REVENUE</strong></td>
                    </tr>
                    <tr>
                        <td>Bakery Sales</td>
                        <td>Daily bakery sales</td>
                        <td class="text-end">{{ number_format($data['revenue']['bakery_sales']) }}</td>
                    </tr>
                    <tr>
                        <td>Dispatch Sales</td>
                        <td>Driver route sales</td>
                        <td class="text-end">{{ number_format($data['revenue']['dispatch_sales']) }}</td>
                    </tr>
                    <tr>
                        <td>Kampala Shop Sales</td>
                        <td>Retail shop sales</td>
                        <td class="text-end">{{ number_format($data['revenue']['kampala_sales']) }}</td>
                    </tr>
                    <tr>
                        <td>Damage Sales</td>
                        <td>Sold damaged goods</td>
                        <td class="text-end">{{ number_format($data['revenue']['damage_sales']) }}</td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>TOTAL REVENUE</strong></td>
                        <td></td>
                        <td class="text-end"><strong>{{ number_format($data['revenue']['total_revenue']) }}</strong></td>
                    </tr>

                    <!-- COGS Section -->
                    <tr class="table-info">
                        <td colspan="3"><strong>COST OF GOODS SOLD (COGS)</strong></td>
                    </tr>
                    <tr>
                        <td>Ingredient Costs</td>
                        <td>Flour, sugar, etc.</td>
                        <td class="text-end">{{ number_format($data['cogs']['ingredient_costs']) }}</td>
                    </tr>
                    <tr>
                        <td>Packaging Costs</td>
                        <td>Bags, boxes, etc.</td>
                        <td class="text-end">{{ number_format($data['cogs']['packaging_costs']) }}</td>
                    </tr>
                    <tr class="table-danger">
                        <td><strong>TOTAL COGS</strong></td>
                        <td></td>
                        <td class="text-end"><strong>{{ number_format($data['cogs']['total_cogs']) }}</strong></td>
                    </tr>

                    <!-- Gross Profit -->
                    <tr class="table-warning">
                        <td><strong>GROSS PROFIT</strong></td>
                        <td>Sales - COGS</td>
                        <td class="text-end"><strong>{{ number_format($data['gross_profit']) }}</strong></td>
                    </tr>

                    <!-- Operating Expenses -->
                    <tr class="table-info">
                        <td colspan="3"><strong>OPERATING EXPENSES</strong></td>
                    </tr>
                    <tr>
                        <td>Rent</td>
                        <td>Shop/bakery rent</td>
                        <td class="text-end">{{ number_format($data['operating_expenses']['rent']) }}</td>
                    </tr>
                    <tr>
                        <td>Salaries</td>
                        <td>Staff wages</td>
                        <td class="text-end">{{ number_format($data['operating_expenses']['salaries']) }}</td>
                    </tr>
                    <tr>
                        <td>Utilities</td>
                        <td>Electricity, water</td>
                        <td class="text-end">{{ number_format($data['operating_expenses']['utilities']) }}</td>
                    </tr>
                    <tr>
                        <td>Transport</td>
                        <td>Delivery costs</td>
                        <td class="text-end">{{ number_format($data['operating_expenses']['transport']) }}</td>
                    </tr>
                    <tr>
                        <td>Kampala Expenses</td>
                        <td>Shop operating costs</td>
                        <td class="text-end">{{ number_format($data['operating_expenses']['kampala_expenses']) }}</td>
                    </tr>
                    <tr>
                        <td>Other Expenses</td>
                        <td>Miscellaneous</td>
                        <td class="text-end">{{ number_format($data['operating_expenses']['other']) }}</td>
                    </tr>
                    <tr class="table-danger">
                        <td><strong>TOTAL OPERATING EXPENSES</strong></td>
                        <td></td>
                        <td class="text-end"><strong>{{ number_format($data['operating_expenses']['total_expenses']) }}</strong></td>
                    </tr>

                    <!-- Net Profit -->
                    <tr class="{{ $data['net_profit'] >= 0 ? 'table-success' : 'table-danger' }}">
                        <td><strong>NET PROFIT</strong></td>
                        <td>Gross Profit - Expenses</td>
                        <td class="text-end"><strong>{{ number_format($data['net_profit']) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>