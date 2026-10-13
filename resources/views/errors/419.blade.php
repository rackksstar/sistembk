@include('errors.layout', [
    'errorCode' => '419',
    'errorTitle' => 'Sesi Telah Berakhir',
    'errorMessage' => 'Sesi Anda sudah berakhir. Muat ulang halaman dan masuk kembali bila diminta.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
