<?php

class StaticPageController extends CController {

    function __construct() {
        parent::__construct();
    }

    function robots() {
        $data = array();
    }

    function feedback(){
        exit();
    }

    function popup() {
        $data['TITLE'] = 'text wording dibawah image';
        $this->render('mobile/static/popup/view', $data);
    }

    function about() {

        $TE_2 = 'About';

        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "About",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'about',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            // 'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/about/view', $data);
    }

    function redaksi() {

        $TE_1 = 'Menu';
        $TE_2 = 'About';

        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'redaksi',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/redaksi/view', $data);

    }

    function sitemap() {
        $TE_2 = 'Sitemap';

        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Sitemap",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'sitemap',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/sitemap/view', $data);
    }

    function contact_us() {

        $TE_2 = 'Contact_us';
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Contact_us",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'contact_us',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/contact_us/view', $data);
    }

    function send_contact() {
        if (isSet($_POST['email'])) {
            $nama = $_POST['nama'];
            $alamat = $_POST['alamat'];
            $telp = $_POST['telp'];
            $email = $_POST['email'];
            $pesan = $_POST['pesan'];
            $to = "redaksi@brilio.net";
            $subject = "Kritik dan Saran dari $email";

            $message = "<html>
  <body> Nama : " . $nama . "<br>
Alamat : " . $alamat . "<br>
Telp : " . $telp . "<br>
Pesan : " . $pesan . "</body>
</html>";

            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            $headers .= 'From: Brilio.net <nobody@brilio.net>' . "\r\n" . "X-Mailer: php";

            mail($to, $subject, $message, $headers);
        }
    }

    function pengaduan() {

        $TE_2 = 'pengaduan';
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Contact_us",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),

        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'pengaduan',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/pengaduan/view', $data);
    }

    function send_pengaduan() {
        if (isSet($_POST['email'])) {
            $title = $_POST['title'];
            $url = $_POST['url'];
            $pesan = $_POST['pesan'];
            $nama = $_POST['nama'];
            $alamat = $_POST['alamat'];
            $telp = $_POST['telp'];
            $email = $_POST['email'];
            $to = "redaksi@brilio.net";
            $subject = "Keberatan Pemberitaan dari $email";

            $message = "<html>
  <body>
Alamat : " . $alamat . "<br>
Telp : " . $telp . "
<br><br> Kepada Yth.<br>
Redaksi Brilio.net di tempat<br><br>

Dengan Hormat,<br>
Sehubungan dengan berita yang berjudu <b>" . $title . "</b> dengan url " . $url . " maka saya menyampaikan keberatan karena " . $pesan . ". Saya berharap redaksi brilio.net menanggapi dan menyelesaikan keberatan ini. <br>
Atas perhatian dan dan bantuannya saya sampaikan terima kasih</body>
</html>";
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            $headers .= 'From: Brilio.net <nobody@brilio.net>' . "\r\n" . "X-Mailer: php";

            mail($to, $subject, $message, $headers);
        }
    }

    function disclaimer() {

        $TE_2 = 'disclaimer';
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Disclaimer",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'disclaimer',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/disclaimer/view', $data);
    }

    function kode_etik() {
        $TE_2 = 'kode_etik';
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Kode Etik",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'kode_etik',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/kode_etik/view', $data);
    }


    function privacy_policy() {
       $TE_2 = 'privacy_policy';
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Privacy Policy",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['rel_url'].'privacy_policy',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/privacy/view', $data);
    }

    function karir() {
        $TE_2 = 'karir';
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'],
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Disclaimer",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'karir',
            'meta'               => $meta,
            'TE_2'               => $TE_2,
            'tags_bottom' 		=> $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('mobile/static/karir/view', $data);
    }

}
