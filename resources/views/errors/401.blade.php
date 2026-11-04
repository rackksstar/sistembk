@include('errors.layout', [
    'errorCode' => '401',
    'errorTitle' => 'Perlu Masuk Terlebih Dahulu',
    'errorMessage' => 'Silakan masuk ke akun Anda untuk melanjutkan ke halaman ini.',
    'actionUrl' => route('login'),
    'actionLabel' => 'Ke Halaman Masuk',
])
