<?php

class Collect_email extends CController {

    function __construct() {
        parent::__construct();
    }

    function cookies_close(){

        $cookie_collect_email_name = "brilio_collect_email_close";
        $cookie_collect_email_value = md5($_SERVER['REMOTE_ADDR']);

        setcookie($cookie_collect_email_name, $cookie_collect_email_value, time() + (86400 * 1), "/");  //86400 = detik to day = 1 hari

    }

    function save_collect_email(){
        if(isset($_POST['emailsubscribe'])){

            $email=$_POST['emailsubscribe'];

            $url = "http://app.kapanlagi.com/useraction/mail_subscribe.php?typeaction=subscribe_by_interest&interest_category=17&send=savesubscribe&email=$email&origin=brilio.net&specific_interest=4";

            $this->httpGet($url);
            $this->send_mail($email);

            if(getOneDataMongo(['email' =>$email], $this->config['mongo_prefix'] . 'subscribers', true) == false)
            {
              insertDataMongo($this->config['mongo_prefix'] . "subscribers", ['email' =>$email], true);
              echo 'success';
            }
            else {
              echo 'taken';
            }
            // writeDataMongo('json_'.$email, ['email' =>$email ], $this->config['mongo_prefix'] . "subscribers");

        }
    }

    function httpGet($url){
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    //  curl_setopt($ch,CURLOPT_HEADER, false);

        $output=curl_exec($ch);

        curl_close($ch);
        return $output;
    }

    function send_mail($email){
        $to = "$email";
        $subject = "Brilio! Emailmu Sudah Terdaftar";

        $message = "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
    <title>BRILIO</title>
    <style type='text/css'>
        /* reset */

        article,
        aside,
        details,
        figcaption,
        figure,
        footer,
        header,
        hgroup,
        nav,
        section,
        summary {
            display: block
        }

        audio,
        canvas,
        video {
            display: inline-block;
            *display: inline;
            *zoom: 1
        }

        audio:not([controls]) {
            display: none;
            height: 0
        }

        [hidden] {
            display: none
        }

        html {
            font-size: 100%;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%
        }

        html,
        button,
        input,
        select,
        textarea {
            font-family: sans-serif
        }

        body {
            margin: 0
        }

        a:focus {
            outline: thin dotted
        }

        a:active,
        a:hover {
            outline: 0
        }

        h1 {
            font-size: 2em;
            margin: 0 0.67em 0
        }

        h2 {
            font-size: 1.5em;
            margin: 0 0 .83em 0
        }

        h3 {
            font-size: 1.17em;
            margin: 1em 0
        }

        h4 {
            font-size: 1em;
            margin: 1.33em 0
        }

        h5 {
            font-size: .83em;
            margin: 1.67em 0
        }

        h6 {
            font-size: .75em;
            margin: 2.33em 0
        }

        abbr[title] {
            border-bottom: 1px dotted
        }

        b,
        strong {
            font-weight: bold
        }

        blockquote {
            margin: 1em 40px
        }

        dfn {
            font-style: italic
        }

        mark {
            background: #ff0;
            color: #000
        }

        p,
        pre {
            margin: 1em 0
        }

        code,
        kbd,
        pre,
        samp {
            font-family: monospace, serif;
            _font-family: 'courier new', monospace;
            font-size: 1em
        }

        pre {
            white-space: pre;
            white-space: pre-wrap;
            word-wrap: break-word
        }

        q {
            quotes: none
        }

        q:before,
        q:after {
            content: '';
            content: none
        }

        small {
            font-size: 75%
        }

        sub,
        sup {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: baseline
        }

        sup {
            top: -0.5em
        }

        sub {
            bottom: -0.25em
        }

        dl,
        menu,
        ol,
        ul {
            margin: 1em 0
        }

        dd {
            margin: 0 0 0 40px
        }

        menu,
        ol,
        ul {
            padding: 0 0 0 40px
        }

        nav ul,
        nav ol {
            list-style: none;
            list-style-image: none
        }

        img {
            border: 0;
            -ms-interpolation-mode: bicubic
        }

        svg:not(:root) {
            overflow: hidden
        }

        figure {
            margin: 0
        }

        form {
            margin: 0
        }

        fieldset {
            border: 1px solid #c0c0c0;
            margin: 0 2px;
            padding: .35em .625em .75em
        }

        legend {
            border: 0;
            padding: 0;
            white-space: normal;
            *margin-left: -7px
        }

        button,
        input,
        select,
        textarea {
            font-size: 100%;
            margin: 0;
            vertical-align: baseline;
            *vertical-align: middle
        }

        button,
        input {
            line-height: normal
        }

        button,
        html input[type='button'],
        input[type='reset'],
        input[type='submit'] {
            -webkit-appearance: button;
            cursor: pointer;
            *overflow: visible
        }

        button[disabled],
        input[disabled] {
            cursor: default
        }

        input[type='checkbox'],
        input[type='radio'] {
            box-sizing: border-box;
            padding: 0;
            *height: 13px;
            *width: 13px
        }

        input[type='search'] {
            -webkit-appearance: textfield;
            -moz-box-sizing: content-box;
            -webkit-box-sizing: content-box;
            box-sizing: content-box
        }

        input[type='search']::-webkit-search-cancel-button,
        input[type='search']::-webkit-search-decoration {
            -webkit-appearance: none
        }

        button::-moz-focus-inner,
        input::-moz-focus-inner {
            border: 0;
            padding: 0
        }

        textarea {
            overflow: auto;
            vertical-align: top
        }

        table {
            border-collapse: collapse;
            border-spacing: 0
        }
        /* custom client-specific styles including styles for different online clients */

        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }
        /* hotmail / outlook.com */

        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: 100%;
        }
        /* hotmail / outlook.com */

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        /* Outlook */

        #outlook a {
            padding: 0;
        }
        /* Outlook */

        img {
            -ms-interpolation-mode: bicubic;
            display: block;
            outline: none;
            text-decoration: none;
        }
        /* IExplorer */

        body,
        table,
        td,
        p,
        a,
        li,
        blockquote {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            font-weight: normal!important;
        }

        .ExternalClass td[class='ecxflexibleContainerBox'] h3 {
            padding-top: 10px !important;
        }
        /* hotmail */
        /* email template styles */

        h1 {
            display: block;
            font-size: 26px;
            font-style: normal;
            font-weight: normal;
            line-height: 100%;
        }

        h2 {
            display: block;
            font-size: 20px;
            font-style: normal;
            font-weight: normal;
            line-height: 120%;
        }

        h3 {
            display: block;
            font-size: 17px;
            font-style: normal;
            font-weight: normal;
            line-height: 110%;
        }

        h4 {
            display: block;
            font-size: 18px;
            font-style: italic;
            font-weight: normal;
            line-height: 100%;
        }

        .flexibleImage {
            height: auto;
        }

        table[class=flexibleContainerCellDivider] {
            padding-bottom: 0 !important;
            padding-top: 0 !important;
        }

        body,
        #bodyTbl {
            background-color: #FFF;
        }

        #emailHeader {
            background-color: #E1E1E1;
        }

        #emailBody {
            background-color: #FFFFFF;
        }

        #emailFooter {
            background-color: #E1E1E1;
        }

        .textContent {
            color: #8B8B8B;
            font-family: Helvetica;
            font-size: 16px;
            line-height: 125%;
            text-align: Left;
        }

        .textContent a {
            color: #205478;
            text-decoration: underline;
        }

        .emailButton {
            background-color: #205478;
            border-collapse: separate;
        }

        .buttonContent {
            color: #FFFFFF;
            font-family: Helvetica;
            font-size: 18px;
            font-weight: bold;
            line-height: 100%;
            padding: 15px;
            text-align: center;
        }

        .buttonContent a {
            color: #FFFFFF;
            display: block;
            text-decoration: none!important;
            border: 0!important;
        }

        #invisibleIntroduction {
            display: none;
            display: none !important;
        }
        /* hide the introduction text */
        /* other framework hacks and overrides */

        span[class=ios-color-hack] a {
            color: #275100!important;
            text-decoration: none!important;
        }
        /* Remove all link colors in IOS (below are duplicates based on the color preference) */

        span[class=ios-color-hack2] a {
            color: #205478!important;
            text-decoration: none!important;
        }

        span[class=ios-color-hack3] a {
            color: #8B8B8B!important;
            text-decoration: none!important;
        }
        /* phones and sms */

        .a[href^='tel'],
        a[href^='sms'] {
            text-decoration: none!important;
            color: #606060!important;
            pointer-events: none!important;
            cursor: default!important;
        }

        .mobile_link a[href^='tel'],
        .mobile_link a[href^='sms'] {
            text-decoration: none!important;
            color: #606060!important;
            pointer-events: auto!important;
            cursor: default!important;
        }
        /* responsive styles */

        @media only screen and (max-width: 480px) {
            body {
                width: 100% !important;
                min-width: 100% !important;
            }
            table[id='emailHeader'],
            table[id='emailBody'],
            table[id='emailFooter'],
            table[class='flexibleContainer'] {
                width: 100% !important;
            }
            td[class='flexibleContainerBox'],
            td[class='flexibleContainerBox'] table {
                display: block;
                width: 100%;
                text-align: left;
            }
            td[class='imageContent'] img {
                height: auto !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            img[class='flexibleImage'] {
                height: auto !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            img[class='flexibleImageSmall'] {
                height: auto !important;
                width: auto !important;
            }
            table[class='flexibleContainerBoxNext'] {
                padding-top: 10px !important;
            }
            table[class='emailButton'] {
                width: 100% !important;
            }
            td[class='buttonContent'] {
                padding: 0 !important;
            }
            td[class='buttonContent'] a {
                padding: 15px !important;
            }
        }
    </style>
    <!--
      MS Outlook custom styles
    -->
    <!--[if mso 12]>
      <style type='text/css'>
        .flexibleContainer{display:block !important; width:100% !important;}
      </style>
    <![endif]-->
    <!--[if mso 14]>
      <style type='text/css'>
        .flexibleContainer{display:block !important; width:100% !important;}
      </style>
    <![endif]-->

</head>

<body bgcolor='#FFF' leftmargin='0' marginwidth='0' topmargin='0' marginheight='0' offset='0'>
    <center style='background-color:#E1E1E1;'>
        <table border='0' cellpadding='0' cellspacing='0' height='100%' width='100%' id='bodyTbl' style='table-layout: fixed;max-width:100% !important;width: 100% !important;min-width: 100% !important;'>
            <tr>
                <td align='center' valign='top' id='bodyCell'>

                    <table bgcolor='#FFFFFF' border='0' cellpadding='0' cellspacing='0' width='100%' id='emailBody'>

                        <tr>
                            <td align='center' valign='top'>
                                <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                    <tr>
                                        <td align='center' valign='top'>
                                            <table border='0' cellpadding='0' cellspacing='0' width='100%' class='flexibleContainer'>
                                                <tr>
                                                    <td align='center' valign='top' width='100%' class='flexibleContainerCell'>
                                                        <table border='0' cellpadding='10' cellspacing='0' width='100%'>
                                                            <tr>
                                                                <td align='center' valign='top'>

                                                                    <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                                                        <tr>
                                                                            <td valign='top' class='textContent'>
                                                                                <h3 style='font-size:24px;line-height:1.6em;padding:0;font-weight:bold;display:block'>Brilio !</h3>
                                                                                <div style='style='font-size:17px;line-height:1.6em;padding:0;display:block''>
                                                                                    Pendaftaran Anda telah dikonfirmasi.
                                                                                    <br> Email Anda <a href='mailto:$email' style='color:#5dade2;text-decoration:none' target='_blank'>$email</a> telah ditambahkan ke daftar kami dan akan menerima cerita-cerita menarik dari <a href='http://brilio.net' style='color:#5dade2;text-decoration:none' target='_blank'>brilio.net</a>
                                                                                    <br><br>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </table>

                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- footer -->
                        <tr style='background:#efefef; border-top:3px solid #ed474b;'>
                            <td align='center' valign='top' style='border-top:3px solid #ed474b;'>
                                <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                    <tr>
                                        <td align='center' valign='top'>
                                            <table border='0' cellpadding='10' cellspacing='0' width='100%' class='flexibleContainer'>
                                                <tr>
                                                    <td valign='top' width='100%' class='flexibleContainerCell'>

                                                        <table align='left' border='0' cellpadding='0' cellspacing='0' width='100%'>
                                                            <tr>
                                                                <td align='left' valign='top' class='flexibleContainerBox'>
                                                                    <table border='0' cellpadding='0' cellspacing='0' width='80' style='max-width:100%;'>
                                                                        <tr>
                                                                            <td align='left' class='textContent'>
                                                                                <img src='http://i77.photobucket.com/albums/j77/santoenet/logo_email_zpsplk3gkdh.png' height='50' style='margin-left: 20px; margin-top: 13px; float: left;'>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td align='right' valign='middle' class='flexibleContainerBox'>
                                                                    <table class='flexibleContainerBoxNext' border='0' cellpadding='0' cellspacing='0' width='340' style='max-width:100%;'>
                                                                        <tr>
                                                                            <td align='left' class='textContent'>
                                                                                <ul style='margin: 0; padding: 20px 20px; float: right;'>
                                                                                    <li style='list-style: none; float: left; margin-left: 8px;'>
                                                                                        <a href='https://www.facebook.com/BrilioDotNetID' class='fb' target='_blank'>
                                                                                            <img src='http://i77.photobucket.com/albums/j77/santoenet/icn_facebook_zpssvtlaas2.png'>
                                                                                        </a>
                                                                                    </li>
                                                                                    <li style='list-style: none; float: left; margin-left: 8px;'>
                                                                                        <a href='https://twitter.com/brilionet' class='twitter' target='_blank'>
                                                                                            <img src='http://i77.photobucket.com/albums/j77/santoenet/icn_twitter_zpspjjfavly.png'>
                                                                                        </a>
                                                                                    </li>
                                                                                    <li style='list-style: none; float: left; margin-left: 8px;'>
                                                                                        <a href='#' class='gplus'>
                                                                                            <img src='http://i77.photobucket.com/albums/j77/santoenet/icn_google_zpse7wdenbf.png'>
                                                                                        </a>
                                                                                    </li>
                                                                                    <li style='list-style: none; float: left; margin-left: 8px;'>
                                                                                        <a href='https://www.youtube.com/channel/UCs7XuBhYZzOYlCORYMJZSoQ' class='youtube' target='_blank'>
                                                                                            <img src='http://i77.photobucket.com/albums/j77/santoenet/icn_youtube_zpsxqmnu4zb.png'>
                                                                                        </a>
                                                                                    </li>
                                                                                    <li style='list-style: none; float: left; margin-left: 8px;'>
                                                                                        <a href='https://instagram.com/brilionet/' class='instagram' target='_blank'>
                                                                                            <img src='http://i77.photobucket.com/albums/j77/santoenet/icn_instagram_zpsqsi2qkte.png'>
                                                                                        </a>
                                                                                    </li>
                                                                                    <li style='list-style: none; float: left; margin-left: 8px;'>
                                                                                        <a href='#' class='feed'>
                                                                                            <img src='http://i77.photobucket.com/albums/j77/santoenet/icn_rss_zpss5ysceyp.png'>
                                                                                        </a>
                                                                                    </li>
                                                                                </ul>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>

                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    </table>
                    <!-- // end of footer -->

                </td>
            </tr>
        </table>
    </center>

</body>

</html>
        ";

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: Brilio.net <nobody@brilio.net>' . "\r\n";
        mail($to,$subject,$message,$headers);
        // if(mail($to,$subject,$message,$headers))
        //     return true;
        // else
        //     return false;
    }


}