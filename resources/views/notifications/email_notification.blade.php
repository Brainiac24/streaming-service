<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
    <title>Email from Example</title>
    <style>
        .hover:hover {
            opacity: 0.9
        }
    </style>
</head>

<body style="font-family: Arial,Helvetica,sans-serif;background-color: #eeecec; margin: 0;">
    <table cellpadding="0" cellspacing="0" style="max-width: 600px; width:100%; margin: 0 auto;">
        <tbody>
            <tr>
                <td>
                    <table align="center" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td height="28" style="height:28px">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="font-family:Raleway,Arial,sans-serif;font-size:28px;color:red;font-weight:bold;height:42px">
                                    Example
                                </td>
                            </tr>
                            <tr>
                                <td height="28" style="height:28px">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" style="height: 791px; width: 100%; background-color: #FFF; filter: drop-shadow(0px 4px 32px rgba(0, 0, 0, 0.25)); border-radius: 28px;">
                        <tbody>
                            <tr style="height: 0;">
                                <td>
                                    <img style="border-top-left-radius: 28px; border-top-right-radius: 28px; width: 100%; max-height: 369px" src="{{ $event_cover_url }}" />
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; padding: 2% 6%">
                                    <table cellpadding="0" cellspacing="0" width="100%" height="100%" style="font-size: 16px;">
                                        <tbody>
                                            <tr>
                                                <td style="font-family:Raleway,Arial,sans-serif;font-size: 24px;font-weight: 700;">{{ $event_name }}</td>
                                            </tr>
                                            <tr>
                                                <td height="10px"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tbody>
                                                            <tr>
                                                                <td align="center" style="font-size: 16px;width: 209px;height:32px;border-radius:24px;background: #D9D9D9;">
                                                                    {{ $event_session_date }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td height="30px"></td>
                                            </tr>
                                            <tr>
                                                @@if($name)
                                                <td>Здравствуйте, @{{ $name }}!</td>
                                                @@else
                                                <td>Здравствуйте!</td>
                                                @@endif
                                            </tr>
                                            <tr>
                                                <td>Вы зарегистрированы на мероприятие</td>
                                            </tr>
                                            <tr style="height: 0;">
                                                <td style="padding: 10% 0;" align="center">
                                                    <a class="hover" href="{{ $event_session_url }}" target="_blank" style="display: inline-block;padding: 13px 47px;color: #FFF;text-decoration:none;border-radius: 28px;background: #3F51B5;">Перейти к мероприятию</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="color: #737373;font-size: 12px;">По всем вопросам обращайтесь в <a target="_blank" href="https://t.me/example_support">Службу поддержки</a> - мы на связи 24/7</td>
                                            </tr>
                                            <tr>
                                                <td height="10px"></td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="color: #737373;font-size: 12px;">Example {{ date('Y') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td style="height: 64px"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
