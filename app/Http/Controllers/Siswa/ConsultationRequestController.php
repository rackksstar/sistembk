<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsultationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * @deprecated Gunakan ConsultationController — route legacy tetap dipertahankan.
 */
class ConsultationRequestController extends Controller
{
    public function store(StoreConsultationRequest $request): RedirectResponse
    {
        return app(ConsultationController::class)->store($request);
    }
}
