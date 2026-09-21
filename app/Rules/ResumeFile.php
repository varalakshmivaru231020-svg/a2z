<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * A resume must be a PDF, DOC or DOCX whose *contents* match its extension.
 *
 * Laravel's built-in "mimes" rule relies on a single guessed extension, which wrongly
 * rejects genuine legacy .doc files (libmagic often reports them as the generic
 * "CDFV2" OLE container) and some .docx files (reported as plain zip). Here the
 * extension has to be one we allow AND the sniffed type has to be one of the types
 * that extension can legitimately produce - so a script renamed to "cv.pdf" still fails.
 */
class ResumeFile implements ValidationRule
{
    private const ALLOWED = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/CDFV2', 'application/vnd.ms-office', 'application/x-ole-storage'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('The resume could not be uploaded. Please try again.');

            return;
        }

        if (! self::extensionFor($value)) {
            $fail('Your resume must be a PDF, DOC or DOCX file.');
        }
    }

    /** The safe, validated extension for this upload, or null if it is not an acceptable resume. */
    public static function extensionFor(UploadedFile $file): ?string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $sniffed = $file->getMimeType();

        return in_array($sniffed, self::ALLOWED[$extension] ?? [], true) ? $extension : null;
    }
}
