<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class DailyProductionReport extends Mailable
{
    use Queueable, SerializesModels;

    public $reportData;
    public $date;

    public function __construct($reportData, $date)
    {
        $this->reportData = $reportData;
        $this->date = $date;
    }

    public function build()
    {
        return $this->subject('Daily Production & Sales Report - ' . $this->date->format('d M Y'))
                    ->view('emails.daily-production-report')
                    ->attachData($this->generatePDF(), 'daily-report-' . $this->date->format('Y-m-d') . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }

    private function generatePDF()
    {
         // Use the reportData directly instead of individual variables
    $pdf = \PDF::loadView('admin.reports.daily-production-pdf', array_merge($this->reportData, [
        'selectedDate' => $this->date
    ]));

        return $pdf->output();
    }
}