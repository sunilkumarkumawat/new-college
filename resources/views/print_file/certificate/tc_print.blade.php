@include('print_file.certificate.transfer_certificate_a4', [
    'student' => $student ?? null,
    'certificate_data' => $certificate_data ?? null
])