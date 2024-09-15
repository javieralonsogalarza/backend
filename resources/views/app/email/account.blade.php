<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1799/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<table align="center" width="550" border="0" cellpadding="1" cellspacing="0" style="background: #fdfdfdf1; font-family: Arial">
    <tr>
        <td>
            <table width="100%" border="0" align="center" cellpadding="12" cellspacing="0" id="bmail">
                <p align="center" style="color: #0073be; font-size: 35px; margin-bottom: 0">¡Cuenta de acceso!</p>
                <tr style="color:#f1f1f1;">
                    <td>
                        <p style="font-size: 17px; color: #666666">
                            Hola {{ $nombres }}, <a href="https://laconfraternidaddeltenis.com/">{{ env('APP_NAME') }}</a> te da la bienvenida a tu cuenta. <br><br>
                            Ingresa a tu cuenta con tu email <b>{{ $cuenta }}</b> y con tu contraseña <b>{{ $password }}</b>.
                        </p>
                    </td>
                </tr>
                <tr><td></td></tr>
                <tr>
                    <td style="background: #0073be; color: #ffffff; font-size: 15px" align="center">Por favor, no responder este correo.</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</html>
