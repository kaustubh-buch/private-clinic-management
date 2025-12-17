<?php

namespace App\Services;

use App\Models\Patient;
use Carbon\Carbon;

class TemplateService
{
    /**
     * Replace predefined placeholder fields in the given template with actual patient and clinic data.
     *
     *
     * @param string  $template The template content containing placeholder fields.
     * @param Patient $patient  The patient whose data will be used for replacements.
     *
     * @return string The processed template with placeholder values replaced.
     */
    public function setPredefinedCode(string $template, Patient $patient)
    {
        $replacements = [
            '(First Name)' => $patient->first_name,
            '(Last Name)' => $patient->last_name,
            '(Clinic Name)' => $patient->clinic->name,
            '(Online Booking Link)' => $patient->clinic->booking_link ?? '-',
            '(Clinic Phone Number)' => $patient->clinic->contact_no ?? '-',
            '(Date of Last Recall)' => ! empty($patient->last_recall) ? $this->formatRecallDate($patient->last_recall) : null,
            '(Insurance)' => ! empty($patient->insurance) ? $patient->insurance->display_name : null,
        ];

        $allowedFields = config('constants.TEMPLATE_ALLOW_DYNAMIC_FIELDS');

        $search = [];
        $replace = [];

        foreach ($allowedFields as $field) {
            $placeHolder = '('.$field.')';
            $search[] = $placeHolder;
            $replace[] = $replacements[$placeHolder] ?? $placeHolder;
        }

        $template = str_replace($search, $replace, $template);

        return $template;
    }

    /**
     * Format a recall date into a natural, readable string.
     *
     * @param Carbon $date The date to be formatted.
     *
     * @return string The formatted, human-readable recall date.
     */
    public function formatRecallDate(Carbon $date): string
    {
        // Convert to Carbon instance if not already
        $date = Carbon::parse($date);

        $month = $date->format('F'); // e.g., "February"
        $year = $date->year;
        $currentYear = now()->year;

        if ($year === $currentYear) {
            return $month;
        } elseif ($year === $currentYear - 1) {
            return $month.' last year';
        } else {
            return $month.' '.$year;
        }
    }
}
