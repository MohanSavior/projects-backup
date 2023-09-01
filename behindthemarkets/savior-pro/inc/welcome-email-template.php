<?php
function welcome_email_users($first_name)
{
    ob_start();
    ?>
    <!doctype html>
        <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
            <head>
                <title></title>
                <!--[if !mso]><!-->
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <!--<![endif]-->
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <style type="text/css">
                    #outlook a{padding:0;}body{margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;}table,td{border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;}img{border:0;height:auto;line-height:100%;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;}p{display:block;margin:0;}
                </style>
                <!--[if mso]> 
                <noscript>
                    <xml>
                        <o:OfficeDocumentSettings>
                            <o:AllowPNG/>
                            <o:PixelsPerInch>96</o:PixelsPerInch>
                        </o:OfficeDocumentSettings>
                    </xml>
                </noscript>
                <![endif]-->
                <!--[if lte mso 11]>
                <style type="text/css">
                    .ogf{width:100% !important;}
                </style>
                <![endif]-->
                <!--[if !mso]><!-->
                <link href="https://fonts.googleapis.com/css?family=Open Sans:400,700i,700" rel="stylesheet" type="text/css">
                <link href="https://fonts.googleapis.com/css?family=Roboto:400,700i,700" rel="stylesheet" type="text/css">
                <style type="text/css">
                </style>
                <!--<![endif]-->
                <style type="text/css">
                    @media only screen and (min-width:599px){.xc568{width:568px!important;max-width:568px;}.xc536{width:536px!important;max-width:536px;}.pc100{width:100%!important;max-width:100%;}.pc71{width:71%!important;max-width:71%;}.pc3{width:3%!important;max-width:3%;}.pc25{width:25%!important;max-width:25%;}.pc0{width:0%!important;max-width:0%;}}
                </style>
                <style media="screen and (min-width:599px)">.moz-text-html .xc568{width:568px!important;max-width:568px;}.moz-text-html .xc536{width:536px!important;max-width:536px;}.moz-text-html .pc100{width:100%!important;max-width:100%;}.moz-text-html .pc71{width:71%!important;max-width:71%;}.moz-text-html .pc3{width:3%!important;max-width:3%;}.moz-text-html .pc25{width:25%!important;max-width:25%;}.moz-text-html .pc0{width:0%!important;max-width:0%;}</style>
                <style type="text/css">
                    @media only screen and (max-width:599px){table.fwm{width:100%!important;}td.fwm{width:auto!important;}}
                </style>
                <style type="text/css">
                    u+.emailify a,#MessageViewBody a,a[x-apple-data-detectors]{color:inherit!important;text-decoration:none!important;font-size:inherit!important;font-family:inherit!important;font-weight:inherit!important;line-height:inherit!important;}span.MsoHyperlink{mso-style-priority:99;color:inherit;}span.MsoHyperlinkFollowed{mso-style-priority:99;color:inherit;}u+.emailify .glist{margin-left:0!important;}
                    @media only screen and (max-width:599px){.emailify{height:100%!important;margin:0!important;padding:0!important;width:100%!important;}u+.emailify .glist{margin-left:25px!important;}td.x{padding-left:0!important;padding-right:0!important;}br.sb{display:none!important;}.hd-1{display:block!important;height:auto!important;overflow:visible!important;}div.r.pr-16>table>tbody>tr>td{padding-right:16px!important}div.r.pl-16>table>tbody>tr>td{padding-left:16px!important}td.b.fw-1>table{width:100%!important}td.fw-1>table>tbody>tr>td>a{display:block!important;width:100%!important;padding-left:0!important;padding-right:0!important;}td.b.fw-1>table{width:100%!important}td.fw-1>table>tbody>tr>td{width:100%!important;padding-left:0!important;padding-right:0!important;}}
                </style>
                <meta name="color-scheme" content="light dark">
                <meta name="supported-color-schemes" content="light dark">
                <!--[if gte mso 9]>
                <style>li{text-indent:-1em;}</style>
                <![endif]-->
            </head>
            <body class="emailify" style="word-spacing:normal;background-color:#e5e5e5;">
                <div style="background-color:#e5e5e5;">
                    <!--[if mso | IE]>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:16px 16px 16px 16px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                <![endif]-->
                                                                <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="background-color:transparent;border:none;vertical-align:middle;padding:10px 0px 0px 0px;">
                                                                                    <table border="0" cellpadding="0" cellspacing="0" style="" width="100%">
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td align="center" class="i " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0;">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td style="width:568px;"> <img alt="" height="auto" src="https://s3.amazonaws.com/btm.website.storage/2022/01/d50d32491a6a6e0471d3d6079204b2d5.png" style="border:0;border-radius:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="568"></td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:10px 32px 10px 32px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:536px;">
                                                                <![endif]-->
                                                                <div class="xc536 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="center" class="d " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                    <p style="border-top:solid 1px #cccccc;font-size:1px;margin:0px auto;width:100%;"></p>
                                                                                    <!--[if mso | IE]>
                                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 1px #cccccc;font-size:1px;margin:0px auto;width:536px;" width="536px">
                                                                                        <tr>
                                                                                            <td style="height:0;line-height:0;"> &nbsp;</td>
                                                                                        </tr>
                                                                                    </table>
                                                                                    <![endif]-->
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:10px 32px 16px 32px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:536px;">
                                                                <![endif]-->
                                                                <div class="xc536 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="center" class="x " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                    <div style="font-family:Open Sans,Arial,sans-serif;font-size:28px;line-height:32px;text-align:center;color:#000000;">
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:28px;font-family:Open Sans,Arial,sans-serif;font-weight:400;color:#000000;line-height:32px;">Welcome to </span><span style="mso-line-height-rule:exactly;font-size:28px;font-family:Open Sans,Arial,sans-serif;font-weight:700;font-style:italic;color:#000000;line-height:32px;">Behind the Markets!</span></p>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:20px 32px 20px 32px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:536px;">
                                                                <![endif]-->
                                                                <div class="xc536 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="left" class="x " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                    <div style="font-family:Roboto,Arial,sans-serif;font-size:16px;line-height:24px;text-align:left;color:#000000;">
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Hello <?php echo $first_name; ?>,</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">We are excited to announce that we have launched our new website.</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">What does this mean for you? A host of new great new features including:</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">
                                                                                            <ul>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Continued access to all the great financial research you are currently receiving</li>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Seamless navigation of Research, Trades, Reports, and more</li>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Searchable Reports right at your fingertips</li>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">The ability to update your profile, passwords, email address on the website</li>
                                                                                            </ul>
                                                                                        </span></p>
																						<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">What’s Next?</span></p>
																						<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Your account has been created and you can access the site by clicking <a href="https://behindthemarkets.com/login/" target="_blank" style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><b>here.</b></a></span></p>
																						<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">If you’re not able to access your account, you will need to <b>reset your password</b> by following the steps below:</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            <ol>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Access your new account <a href="https://behindthemarkets.com/login/" target="_blank" style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><b>here</b></a>.</li>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Select "Forgot Password?"</li>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Follow the steps and email to reset your password.</li>
                                                                                                <li style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">You will now have access to your new account.</li>
                                                                                            </ol>
                                                                                        </span></p>
                                                                                        <p style="Margin:0;text-align: center;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;text-align:center;">
                                                                                            <a href="https://behindthemarkets.com/login/" style="display:inline-block;width:200px;background:#ff9911;color:#ffffff;font-family:Roboto,Arial,sans-serif;font-size:13px;font-weight:normal;line-height:100%;margin:0;text-decoration:none;text-transform:none;padding:12px 0px 12px 0px;mso-padding-alt:0;border-radius:60px 60px 60px 60px;" target="_blank"> <span style="mso-line-height-rule:exactly;font-size:14px;font-family:Roboto,Arial,sans-serif;font-weight:700;color:#ffffff;line-height:16px;text-decoration:underline;">Access Account Now</span></a>
                                                                                        </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                        
                                                                                        <!-- <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"> <b>P.S.</b> You can also reset your password by going to: </span> <span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><a href="https://behindthemarkets.com/login/" target="_blank">https://behindthemarkets.com/login/ </a> and selecting "forgot password" then follow the steps or if you are already logged into your account you can go to "my account" in your customer dashboard and follow the steps to change your password.</span></p>
                                                                                        
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p> -->
                                                                                        
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">If you have any issues, do not reply to this email. Please contact us at: </span> <span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><a href="mailto:support@behindthemarkets.com" target="_blank">support@behindthemarkets.com </a> or <a href="tel:1-800-851-1965" target="_blank">1-800-851-1965</a>.</span></p>
                                                                                        
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table>
                    
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:0px 16px 20px 16px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                <![endif]-->
                                                                <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="background-color:transparent;border:none;vertical-align:middle;padding:0px 32px 0px 32px;">
                                                                                    <table border="0" cellpadding="0" cellspacing="0" style="" width="100%">
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td align="left" class="i fw-1 " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0;" class="fwm">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td style="width:300px;" class="fwm"> <img alt="" height="auto" src="https://s3.amazonaws.com/btm.website.storage/2022/01/26d742660939a76923f0c0edfb003bdf.png" style="border:0;border-radius:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="300"></td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:24px 16px 24px 16px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                <![endif]-->
                                                                <!-- <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="center" class="x m" style="font-size:0;padding:0;padding-bottom:8px;word-break:break-word;">
                                                                                    <div style="font-family:Roboto,Arial,sans-serif;font-size:28px;line-height:34px;text-align:center;color:#000000;">
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:28px;font-family:Roboto,Arial,sans-serif;font-weight:700;color:#000000;line-height:34px;text-decoration:underline;"><a href="https://www.behindthemarkets.com/login/" style="color:#000000;text-decoration:underline;" target="_blank">Click Here To Login To Your Account</a></span></p>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="s m" style="font-size:0;padding:0;padding-bottom:8px;word-break:break-word;">
                                                                                    <div style="height:4px;line-height:4px;">&#8202;</div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td align="center" vertical-align="middle" class="b fw-1 " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;width:117px;line-height:100%;">
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td align="center" bgcolor="#ff9911" style="border:none;border-radius:60px 60px 60px 60px;cursor:auto;mso-padding-alt:12px 0px 12px 0px;background:#ff9911;" valign="middle"> <a href="https://www.behindthemarkets.com/login/" style="display:inline-block;width:117px;background:#ff9911;color:#ffffff;font-family:Roboto,Arial,sans-serif;font-size:13px;font-weight:normal;line-height:100%;margin:0;text-decoration:none;text-transform:none;padding:12px 0px 12px 0px;mso-padding-alt:0;border-radius:60px 60px 60px 60px;" target="_blank"> <span style="mso-line-height-rule:exactly;font-size:14px;font-family:Roboto,Arial,sans-serif;font-weight:700;color:#ffffff;line-height:16px;text-decoration:underline;">Login Here!</span></a></td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div> -->
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#eeeeee">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#eeeeee;background-color:#eeeeee;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#eeeeee;background-color:#eeeeee;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:16px 16px 0px 16px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                <![endif]-->
                                                                <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="center" class="i " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0;">
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td style="width:156px;"> <img alt="" height="auto" src="https://s3.amazonaws.com/btm.website.storage/2022/01/c7e0f35c91630df02982ad7a9f922f45.png" style="border:0;border-radius:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="156"></td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#eeeeee">
                        <tr>
                            <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                <![endif]-->
                                <div class="r pr-16 pl-16 " style="background:#eeeeee;background-color:#eeeeee;margin:0px auto;border-radius:0;max-width:600px;">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#eeeeee;background-color:#eeeeee;width:100%;border-radius:0;">
                                        <tbody>
                                            <tr>
                                                <td style="border:none;direction:ltr;font-size:0;padding:5px 16px 16px 16px;text-align:left;">
                                                    <!--[if mso | IE]>
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                <![endif]-->
                                                                <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="center" class="x " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                    <div style="font-family:Roboto,Arial,sans-serif;font-size:11px;line-height:13px;text-align:center;color:#000000;">
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets is a newsletter offered to the public on a subscription basis. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">While subscribers receive the benefit of Behind the Markets opinions, none of the information contained therein constitutes a recommendation from Behind the Markets that any particular security, portfolio of securities, transaction, or investment strategy is suitable for any specific person. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">You further understand that we will not advise you personally concerning the nature, potential, value or suitability of any particular security, portfolio of securities, transaction, investment strategy or other matter. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">To the extent any of the information contained in Behind the Markets may be deemed to be investment advice, such information is impersonal and not tailored to the investment needs of any specific person. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets&rsquo; past results are not necessarily indicative of future performance. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Employees of Behind the Markets are subject to certain restrictions in transacting for their own benefit. SPECIFICALLY, EMPLOYEES ARE NOT PERMITTED TO BUY OR SELL ANY SECURITY RECOMMENDED FOR THREE (3) TRADING DAYS FOLLOWING THE ISSUE OF A REPORT OR UPDATE.</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets&rsquo; Newsletter contains Behind the Markets&rsquo; own opinions, and none of the information contained therein constitutes a recommendation by Behind the Markets that any particular security, portfolio of securities, transaction, or investment strategy is suitable for any specific person. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets&rsquo; past results are not necessarily indicative of future performance. DO NOT EMAIL Behind the Markets SEEKING PERSONALIZED INVESTMENT ADVICE, WHICH WE CANNOT PROVIDE. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The Editor's personal investing goals and risk tolerance may be substantially different from those discussed in the Newsletter and/or circumstances may have changed by the expiration of the three day restricted period, the investment actions taken by the Editor in the accounts the Editor directly or indirectly owns may vary from (and may even be contrary to) the advice and recommendations in the Newsletter.</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Investing involves substantial risk. Neither the Editor, the publisher, nor any of their respective affiliates make any guarantee or other promise as to any results that may be obtained from using the Newsletter. While past performance may be analyzed in the Newsletter, past performance should not be considered indicative of future performance. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">No subscriber should make any investment decision without first consulting his or her own personal financial advisor and conducting his or her own research and due diligence, including carefully reviewing the prospectus and other public filings of the issuer. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">To the maximum extent permitted by law, the Editor, the publisher and their respective affiliates disclaim any and all liability in the event any information, commentary, analysis, opinions, advice and/or recommendations in the Newsletter prove to be inaccurate, incomplete or unreliable, or result in any investment or other losses. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The Newsletter's commentary, analysis, opinions, advice and recommendations represent the personal and subjective views of the Editor and are subject to change at any time</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">without notice.</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The information provided in the Newsletter is obtained from sources which the Editor believes to be reliable. However, the Editor has not independently verified or otherwise investigated all such information. Neither the Editor, the publisher, nor any of their respective affiliates guarantees the accuracy or completeness of any such information. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The Newsletter is not a solicitation or offer to buy or sell any securities. Further, the Newsletter is in no way intended to be a solicitation for any services offered by Behind the Markets </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Neither the Editor, the publisher, nor any of their respective affiliates are responsible for any errors or omissions in the Newsletter. </span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Want to change how you receive these emails?</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">You can update your preferences or unsubscribe from this list.</span></p>
                                                                                        <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                            </td>
                        </tr>
                    </table> 
                    <![endif]-->
                </div>
            </body>
        </html>
    <?php
    $html = ob_get_contents();
    ob_get_clean();
    return $html;
}

// echo welcome_email_users('Jhon');