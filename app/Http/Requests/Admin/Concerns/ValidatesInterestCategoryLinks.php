<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\InterestCategory;
use Illuminate\Validation\Validator;

trait ValidatesInterestCategoryLinks
{
    /**
     * Wajib minimal satu kategori minat tercentang agar rekomendasi Key bisa jalan.
     */
    protected function validateInterestCategoryLinks(Validator $validator, string $requiredMessage): void
    {
        $validator->after(function (Validator $validator) use ($requiredMessage) {
            $ids = collect($this->input('categories', []))
                ->filter(fn ($row) => ! empty($row['enabled']))
                ->keys()
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();

            if ($ids->isEmpty()) {
                $validator->errors()->add('categories', $requiredMessage);

                return;
            }

            $validCount = InterestCategory::query()
                ->whereIn('id', $ids)
                ->count();

            if ($validCount !== $ids->count()) {
                $validator->errors()->add('categories', 'Salah satu kategori minat tidak valid.');
            }

            foreach ($ids as $id) {
                $relevansi = data_get($this->input('categories'), "{$id}.relevansi");
                if ($relevansi === null || $relevansi === '') {
                    $validator->errors()->add(
                        "categories.{$id}.relevansi",
                        'Relevansi wajib diisi untuk kategori yang dipilih.'
                    );
                }
            }
        });
    }
}
