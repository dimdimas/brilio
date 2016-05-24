<?php

class StaticPageCOntroller extends CController {

    function __construct() 
    {
        parent::__construct();
        $this->model(array('today_tags_model', 'jsview_model', 'what_happen_model'));
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function robots() 
    {
        $data = array();
    }
    
    function feedback()
    {
        exit();
    }

    function popup() 
    {
        $data['TITLE'] = 'text wording dibawah image';
        $this->render('static/popup/view', $data);
    }

    function about() 
    {
        $TE = 'About';

        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/about',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "About",
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/about',
            'meta'               => $meta,
            'TE'                 => $TE,            
            //'tags_bottom' => $this->_tags_bottom(0, 10, 0, $TE_2),
        );

        $this->_render('desktop/static/about/view', $data);
    }

    function redaksi() 
    {
        $TE_1 = 'Menu';
        $TE_2 = 'Redaksi';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/redaksi',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/redaksi',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/redaksi/view', $data);
    }

    function sitemap() 
    {
        $TE_1 = 'Menu';
        $TE_2 = 'Sitemap';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/sitemap',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/sitemap',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/sitemap/view', $data);
    }

    function contact_us() 
    {
        $TE_2 = 'Contact Us';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/contact_us',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/contact_us',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/contact_us/view', $data);
    }

    function send_contact() 
    {
        if (isset($_POST['email'])) 
        {
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

    function pengaduan() 
    {
        $TE_1 = 'Menu';
        $TE_2 = 'Pengaduan';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/sitemap',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/sitemap',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/pengaduan/view', $data);
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

    function disclaimer() 
    {
        $TE_1 = 'Menu';
        $TE_2 = 'Desclaimer';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/desclaimer',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/desclaimer',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/disclaimer/view', $data);
    }

    function kode_etik() 
    {
        $TE_1 = 'Menu';
        $TE_2 = 'Kode Edit';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/kode_etik',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/kode_etik',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/kode_etik/view', $data);
    }

    function privacy_policy() 
    {
        $TE_1 = 'Menu';
        $TE_2 = 'Privacy Policy';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/privacy_policy',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/privacy_policy',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/privacy/view', $data);
    }

    function karir() 
    {
        $TE_1 = 'Menu';
        $TE_2 = 'Karir';
        
        $meta = array
        (
            'meta_title'         => 'Life and science - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'             => $this->config['base_url'].'company/karir',
            'og_image'           => '',
            'og_image_secure'    => '',
            'expires'            => '',
            'last_modifed'       =>'',
            'chartbeat_sections' => "Redaksi",
            'chartbeat_authors'  => 'Brilio.net',
        );

        $data = array
        (
            'full_url'           => $this->config['base_url'].'company/karir',
            'meta'               => $meta,
            'TE'                 => $TE_2,
        );

        $this->_render('static/karir/view', $data);
    }

}
