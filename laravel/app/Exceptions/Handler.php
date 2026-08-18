<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle validation exceptions — return structured Bengali messages
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $errors = $e->errors();
                // Flatten field names and prepend Bengali labels
                $formattedErrors = [];
                foreach ($errors as $field => $messages) {
                    $bnLabel = $this->getBengaliFieldName($field);
                    $formattedErrors[$field] = array_map(fn($msg) => "{$bnLabel}: {$msg}", $messages);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'বৈধতা ত্রুটি',
                    'errors' => $formattedErrors,
                ], 422);
            }
        });

        // Handle HTTP exceptions (404, 403, etc.) for API routes
        $this->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = $e->getStatusCode();
                $message = match ($status) {
                    403 => 'আপনার এই কাজটি করার অনুমতি নেই',
                    404 => 'এই সম্পদটি পাওয়া যায়নি',
                    405 => 'অনুমোদিত পদ্ধতি নয়',
                    500 => 'সার্ভারে ত্রুটি হয়েছে',
                    default => $e->getMessage() ?: 'একটি ত্রুটি হয়েছে',
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'status' => $status,
                ], $status);
            }
        });

        // Catch-all for API routes — return consistent error format
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') && $e->getStatusCode() ? $e->getStatusCode() : 500;

                return response()->json([
                    'success' => false,
                    'message' => $status === 500 ? 'সার্ভারে একটি অপ্রত্যাশিত ত্রুটি হয়েছে' : $e->getMessage(),
                    'status' => $status,
                    'debug' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ] : null,
                ], $status);
            }
        });
    }

    /**
     * Map field names to Bengali labels for validation error messages.
     */
    private function getBengaliFieldName(string $field): string
    {
        return match ($field) {
            'name_bn' => 'নাম (বাংলা)',
            'name_en' => 'নাম (ইংরেজি)',
            'email' => 'ইমেইল',
            'password' => 'পাসওয়ার্ড',
            'phone' => 'ফোন নম্বর',
            'title_bn' => 'শিরোনাম (বাংলা)',
            'title_en' => 'শিরোনাম (ইংরেজি)',
            'content_bn' => 'বিবরণ (বাংলা)',
            'content_en' => 'বিবরণ (ইংরেজি)',
            'class_id' => 'শ্রেণি',
            'section_id' => 'অংশ',
            'subject_id' => 'বিষয়',
            'session_id' => 'শিক্ষার্থী বর্ষ',
            'student_id' => 'ছাত্র',
            'teacher_id' => 'শিক্ষক',
            'tenant_id' => 'টেন্যান্ট',
            'total_marks' => 'মোট নম্বর',
            'passing_marks' => 'পাস নম্বর',
            'start_date' => 'শুরুর তারিক',
            'end_date' => 'শেষ তারিখ',
            'duration_minutes' => 'সময় (মিনিট)',
            'is_active' => 'সক্রিয় অবস্থা',
            'is_pinned' => 'পিন অবস্থা',
            'admission_date' => 'ভর্তির তারিখ',
            'date_of_birth' => 'জন্ম তারিখ',
            default => $field,
        };
    }
}
