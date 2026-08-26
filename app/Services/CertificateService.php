<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Generate a certificate for a completed enrollment.
     * Called from EnrollmentCompleted event listener (runs via Queue Job).
     */
    public function generate(Enrollment $enrollment): Certificate
    {
        // Prevent duplicate certificates
        if ($enrollment->certificate) {
            return $enrollment->certificate;
        }

        $certificateNumber = $this->generateCertificateNumber();
        $filePath          = $this->generatePdf($enrollment, $certificateNumber);

        return Certificate::create([
            'enrollment_id'      => $enrollment->id,
            'certificate_number' => $certificateNumber,
            'file_path'          => $filePath,
            'issued_at'          => now(),
        ]);
    }

    /* -----------------------------------------------------------------------
     * Helpers
     * --------------------------------------------------------------------- */

    private function generateCertificateNumber(): string
    {
        $year    = now()->format('Y');
        $count   = Certificate::whereYear('issued_at', $year)->count() + 1;
        $padded  = str_pad($count, 6, '0', STR_PAD_LEFT);

        return "CERT-{$year}-{$padded}";
    }

    /**
     * Generate PDF and store it.
     * Replace this stub with barryvdh/laravel-dompdf when the package is installed.
     *
     * @return string  Storage path of the generated PDF
     */
    private function generatePdf(Enrollment $enrollment, string $certificateNumber): string
    {
        // TODO: Implement PDF generation with barryvdh/laravel-dompdf
        // $pdf = PDF::loadView('certificates.template', compact('enrollment', 'certificateNumber'));
        // $path = "certificates/{$certificateNumber}.pdf";
        // Storage::put($path, $pdf->output());
        // return $path;

        // Placeholder path until PDF package is installed
        return "certificates/{$certificateNumber}.pdf";
    }
}
