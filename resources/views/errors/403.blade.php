@php
    $exceptionMessage = $exception->getMessage();
    $errorMessage = $exceptionMessage !== ''
        ? $exceptionMessage
        : 'Anda tidak memiliki izin untuk mengakses halaman ini.';
@endphp

@include('errors.layout', [
    'errorCode' => '403',
    'errorTitle' => 'Akses Ditolak',
    'errorMessage' => $errorMessage,
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
