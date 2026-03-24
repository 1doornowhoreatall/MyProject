<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin: 0; padding: 0;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #6C7A89;">
    <tr>
        <td style="text-align: center;padding: 50px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto;">
                <tr>
                    <td style="background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;">
                        <h2 style="margin-bottom: 20px;">{{ __('NEW DEPOSIT') }}</h2>
                        <p>{{ __('Hello Administrator, we inform you that a new deposit of') }} <strong>{{ $valor }}</strong>, {{ __('made by the user') }} <strong>{{ $usuario }}</strong>, {{ __('has been registered in your account.') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
