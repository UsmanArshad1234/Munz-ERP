<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'job_title' => [
                'Rider', 'Driver', 'Supervisor', 'Admin',
                'Accountant', 'Manager', 'Mechanic', 'Other',
            ],
            'department' => [
                'Operations', 'Fleet', 'Finance',
                'HR', 'Admin', 'Maintenance', 'Other',
            ],
            'employee_status' => [
                'Active', 'Inactive', 'On Leave', 'Resigned',
                'Cancelled Visa', 'Pending Joining', 'Terminated',
            ],
            'work_emirate' => [
                'Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman',
                'Fujairah', 'Ras Al Khaimah', 'Umm Al Quwain', 'Al Ain', 'Other',
            ],
            'zone' => [
                'Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Other',
            ],
            'platform_name' => [
                'Noon', 'Talabat', 'Careem', 'Deliveroo', 'Other',
            ],
            'salary_type' => [
                'Monthly', 'Fixed', 'WPS', 'No WPS', 'Other',
            ],
            'wps_status' => [
                'WPS', 'No WPS',
            ],
            'bike_status' => [
                'Available', 'Assigned', 'Under Maintenance',
                'Damaged', 'Sold', 'Cancelled', 'Inactive',
            ],
            'assignment_status' => [
                'Active', 'Returned', 'Pending Return', 'Cancelled',
            ],
            'loan_status' => [
                'Active', 'Paid', 'Cancelled', 'On Hold',
            ],
            'fine_type' => [
                'Traffic Fine', 'Salik', 'Parking Fine',
                'Company Penalty', 'Accident Fine', 'Other Deduction',
            ],
            'expense_category' => [
                'Fuel', 'Maintenance', 'Salik Paid by Company',
                'Traffic Fine Paid by Company', 'Parking',
                'Insurance', 'Registration / Mulkiya', 'Bike Purchase',
                'Bike Rental', 'Helmet', 'Delivery Box', 'Bike Stand',
                'Uniform', 'Mobile / SIM Card', 'Accommodation',
                'Office Rent', 'Visa', 'Medical', 'Emirates ID',
                'Labour Card', 'Driving License', 'PRO / Government Fees',
                'Software / IT', 'Bank Charges', 'Salary / Payroll Cost',
                'Other Expense',
            ],
            'income_type' => [
                'Income from Platform', 'Income from Rider',
                'Bonus', 'Incentive', 'Adjustment', 'Other',
            ],
            'payment_method' => [
                'Cash', 'Bank Transfer', 'Card', 'Cheque', 'Other',
            ],
        ];

        foreach ($defaults as $type => $labels) {
            foreach ($labels as $index => $label) {
                Setting::updateOrCreate(
                    ['type' => $type, 'value' => \Str::slug($label, '_')],
                    [
                        'label'      => $label,
                        'sort_order' => $index,
                        'is_active'  => true,
                        'is_default' => true,
                    ]
                );
            }
        }
    }
}
