<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Submission;
use App\Support\SchemaFields;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SubmissionService
{
    protected function rateLimitKey(Form $form): string
    {
        return 'submissions:'.$form->id.':'.(request()->ip() ?? 'unknown');
    }

    public function checkRateLimit(Form $form): void
    {
        $limit = (int) ($form->settings['rate_limit'] ?? 5);

        if (RateLimiter::tooManyAttempts($this->rateLimitKey($form), $limit)) {
            throw ValidationException::withMessages([
                '_form' => 'Too many submissions. Please try again later.',
            ]);
        }
    }

    public function store(Form $form, array $answers): Submission
    {
        $this->checkRateLimit($form);

        $submission = Submission::create([
            'form_id' => $form->id,
            'data' => array_filter($answers, fn ($value) => $value !== null && $value !== '' && $value !== []),
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);

        RateLimiter::hit($this->rateLimitKey($form), 60);

        return $submission;
    }

    public function query(Form $form, ?string $search = null)
    {
        return Submission::query()
            ->where('form_id', $form->id)
            ->when($search, function ($query) use ($search) {
                $column = DB::connection()->getDriverName() === 'pgsql' ? 'data::text' : 'data';

                $query->whereRaw($column . ' like ?', ['%' . $search . '%']);
            });
    }

    public function columns(Form $form): array
    {
        return SchemaFields::answerable($form->schema);
    }

    public function exportCsv(Form $form, ?string $search = null)
    {
        $fields = $this->columns($form);

        $headers = array_values(array_map(
            fn ($field) => $field['label'] ?? $field['key'],
            $fields
        ));
        $headers[] = 'Submitted At';
        $headers[] = 'IP Address';

        $temp = tempnam(sys_get_temp_dir(), 'csv_');
        $out = fopen($temp, 'w');
        fputcsv($out, $headers);

        $this->query($form, $search)->orderBy('id')->chunkById(500, function ($rows) use ($out, $fields) {
            foreach ($rows as $row) {
                $values = [];

                foreach ($fields as $key => $field) {
                    $value = $row->data[$key] ?? '';
                    $values[] = is_array($value) ? implode('; ', $value) : (string) $value;
                }

                $values[] = $row->created_at?->toDateTimeString() ?? '';
                $values[] = (string) $row->ip_address;

                fputcsv($out, $values);
            }
        });

        fclose($out);

        return response()->download($temp, 'submissions.csv')->deleteFileAfterSend(true);
    }
}
