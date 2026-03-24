<!-- resources/views/emails/reset-password.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h2>{{ __('Affiliate') }}</h2>
    <p>
        {{ __('User wants to be an affiliate. Contact them at email:') }} {{ $email }}
    </p>
</body>
</html>
