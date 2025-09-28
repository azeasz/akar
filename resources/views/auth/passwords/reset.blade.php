<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Reset Password</div>
                    <div class="card-body">
                        <div id="message" class="alert d-none"></div>
                        <form id="reset-form">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="mb-3">
                                <label for="email" class="form-label">Alamat Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ $email ?? old('email') }}" required readonly>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('reset-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const messageDiv = document.getElementById('message');

            fetch('/api/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json().then(body => ({ status: response.status, body })))
            .then(({ status, body }) => {
                messageDiv.classList.remove('d-none', 'alert-danger', 'alert-success');
                if (status >= 400) {
                    messageDiv.classList.add('alert-danger');
                    messageDiv.textContent = body.message || 'An error occurred.';
                    if (body.errors) {
                        messageDiv.innerHTML += '<br>' + Object.values(body.errors).flat().join('<br>');
                    }
                } else {
                    messageDiv.classList.add('alert-success');
                    messageDiv.textContent = 'Password berhasil direset. Anda sekarang bisa login dengan password baru Anda.';
                    form.reset();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.classList.remove('d-none', 'alert-success');
                messageDiv.classList.add('alert-danger');
                messageDiv.textContent = 'An unexpected error occurred. Please try again.';
            });
        });
    </script>
</body>
</html>
