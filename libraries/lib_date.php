<?php

class Lib_date {

    var $tgl;
    var $bln;
    var $tahun;
    var $tanggal;
    var $wkt;
    var $session_time;

    function tgl_indo($tgl) {
        $ubah = gmdate($tgl, time() + 60 * 60 * 8);
        $pecah = explode("/", $ubah);
        $tanggal = $pecah[2];
        $bulan = $this->bulan($pecah[1]);
        $tahun = $pecah[0];
        return $tanggal . ' ' . $bulan . ' ' . $tahun;
    }

    function bulan($bln) {
        switch ($bln) {
            case 1:
                return "Januari";
                break;
            case 2:
                return "Februari";
                break;
            case 3:
                return "Maret";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "Mei";
                break;
            case 6:
                return "Juni";
                break;
            case 7:
                return "Juli";
                break;
            case 8:
                return "Agustus";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "Oktober";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "Desember";
                break;
        }
    }

    function month($bln) {
        switch ($bln) {
            case 1:
                return "January";
                break;
            case 2:
                return "February";
                break;
            case 3:
                return "March";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "May";
                break;
            case 6:
                return "June";
                break;
            case 7:
                return "July";
                break;
            case 8:
                return "August";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "October";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "December";
                break;
        }
    }


    function nama_hari($tanggal) {

        $ubah = gmdate($tanggal, time() + 60 * 60 * 8);
        $pecah = explode("-", $ubah);
        $tgl = $pecah[2];
        $bln = $pecah[1];
        $thn = $pecah[0];

        $nama = date("l", mktime(0, 0, 0, $bln, $tgl, $thn));
        $nama_hari = "";
        if ($nama == "Sunday") {
            $nama_hari = "Minggu";
        } else if ($nama == "Monday") {
            $nama_hari = "Senin";
        } else if ($nama == "Tuesday") {
            $nama_hari = "Selasa";
        } else if ($nama == "Wednesday") {
            $nama_hari = "Rabu";
        } else if ($nama == "Thursday") {
            $nama_hari = "Kamis";
        } else if ($nama == "Friday") {
            $nama_hari = "Jumat";
        } else if ($nama == "Saturday") {
            $nama_hari = "Sabtu";
        }
        return $nama_hari;

    }

    function hitung_mundur($wkt) {
        $waktu = array(365 * 24 * 60 * 60 => "tahun",
            30 * 24 * 60 * 60 => "bulan",
            7 * 24 * 60 * 60 => "minggu",
            24 * 60 * 60 => "hari",
            60 * 60 => "jam",
            60 => "menit",
            1 => "detik");

        $hitung = strtotime(gmdate("Y-m-d H:i:s", time() + 60 * 60 * 8)) - $wkt;
        $hasil = array();
        if ($hitung < 5) {
            $hasil = 'kurang dari 5 detik yang lalu';
        } else {
            $stop = 0;
            foreach ($waktu as $periode => $satuan) {
                if ($stop >= 6 || ($stop > 0 && $periode < 60))
                    break;
                $bagi = floor($hitung / $periode);
                if ($bagi > 0) {
                    $hasil[] = $bagi . ' ' . $satuan;
                    $hitung -= $bagi * $periode;
                    $stop++;
                } else if ($stop > 0)
                    $stop++;
            }
            $hasil = implode(' ', $hasil) . ' yang lalu';
        }
        return $hasil;
    }

    function tgl_sebelumnya($tanggal) {

        $pecah = explode("/", $tanggal);
        $tgl = $pecah[2];
        $bln = $pecah[1];
        $thn = $pecah[0];

        $tgl_sebelumnya = mktime(0, 0, 0, $bln, $tgl - 1, $thn);

        return date("Y/m/d",$tgl_sebelumnya);
    }

    function tgl_berikutnya($tanggal) {
        $pecah = explode("/", $tanggal);
        $tgl = $pecah[2];
        $bln = $pecah[1];
        $thn = $pecah[0];

        $tgl_sesudahnya = mktime(0, 0, 0, $bln, $tgl + 1, $thn);

        return date("Y/m/d",$tgl_sesudahnya);
    }

    function waktu($tanggal_waktu) {
        $timestamp = strtotime($tanggal_waktu);
        $time_difference = time() - $timestamp;

        $seconds = $time_difference;

        $minutes = round($time_difference / 60);
        $hours = round($time_difference / 3600);
        $days = round($time_difference / 86400);
        $weeks = round($time_difference / 604800);
        $months = round($time_difference / 2419200);
        $years = round($time_difference / 29030400);

        $tanggal_asli = date("d-m-Y", strtotime($tanggal_waktu));
        $waktu = date("H:i", strtotime($tanggal_waktu));
        $pecah = explode("-", $tanggal_asli);
        $tanggal = $pecah[0];
        $bulan = $this->bulan($pecah[1]);
        $tahun = $pecah[2];

        if ($minutes <= 5) {
            return "terbaru";
        } else if ($minutes <= 60) {
            if ($hours == 1) {
                return "1 menit yang lalu";
            } else {
                return "$minutes menit yang lalu";
            }
        } else if ($hours <= 24) {
            if ($hours == 1) {
                return "1 jam yang lalu";
            } else {
                return "$hours jam yang lalu";
            }
        } else if ($days <= 30) {
            if ($days == 1) {
                return "1 hari yang lalu";
            } else {
                return "$days hari yang lalu";
            }
        } else {
            return $tanggal . ' ' . $bulan . ', ' . $waktu . ' WIB';
        }
    }

    function time_read_news($tanggal_waktu) {
        $timestamp = strtotime($tanggal_waktu);
        $time_difference = time() - $timestamp;

        $seconds = $time_difference;

        $minutes = round($time_difference / 60);
        $hours = round($time_difference / 3600);
        $days = round($time_difference / 86400);
        $weeks = round($time_difference / 604800);
        $months = round($time_difference / 2419200);
        $years = round($time_difference / 29030400);

        $tanggal_asli = date("d-m-Y", strtotime($tanggal_waktu));
        $waktu = date("H:i", strtotime($tanggal_waktu));
        $pecah = explode("-", $tanggal_asli);
        $tanggal = $pecah[0];
        $bulan = $this->bulan($pecah[1]);
        $tahun = $pecah[2];

        $nama = date("l", strtotime($tanggal_waktu));

        $nama_hari = "";
        if ($nama == "Sunday") {
            $nama_hari = "Minggu";
        } else if ($nama == "Monday") {
            $nama_hari = "Senin";
        } else if ($nama == "Tuesday") {
            $nama_hari = "Selasa";
        } else if ($nama == "Wednesday") {
            $nama_hari = "Rabu";
        } else if ($nama == "Thursday") {
            $nama_hari = "Kamis";
        } else if ($nama == "Friday") {
            $nama_hari = "Jumat";
        } else if ($nama == "Saturday") {
            $nama_hari = "Sabtu";
        }

        return $nama_hari . ', ' . $tanggal . ' ' . $bulan . ' ' . $waktu . ' WIB';
    }

    function indo($tanggal_waktu) {
        $tanggal_asli = date("d-m-Y", strtotime($tanggal_waktu));
        $waktu = date("H:i", strtotime($tanggal_waktu));
        $pecah = explode("-", $tanggal_asli);
        $tanggal = $pecah[0];
        $bulan = $this->bulan($pecah[1]);
        $tahun = $pecah[2];

        $return = $tanggal . ' ' . $bulan . ' ' . $tahun . ' ' . $waktu;

        return $return;
    }

    function english($tanggal_waktu) {
        $tanggal_asli = date("d-m-Y", strtotime($tanggal_waktu));
        $waktu = date("H:i", strtotime($tanggal_waktu));
        $pecah = explode("-", $tanggal_asli);
        $tanggal = $pecah[0];
        $bulan = $this->month($pecah[1]);
        $tahun = $pecah[2];

        $return = $tanggal . ' ' . $bulan . ' ' . $tahun . ' ' . $waktu;

        return $return;
    }

    function mobile($tanggal_waktu) {
        $tanggal_asli = date("d-m-Y", strtotime($tanggal_waktu));
        $waktu = date("H:i", strtotime($tanggal_waktu));
        $pecah = explode("-", $tanggal_asli);
        $tanggal = $pecah[0];
        $bulan = $this->bulan($pecah[1]);
        $tahun = $pecah[2];

        $return = $tanggal . ' ' . $bulan . ' ' . $tahun . ' ' . $waktu;

        return $return;
    }

    function detail($tanggal_waktu) {
        $tanggal_asli = date("d-m-Y", strtotime($tanggal_waktu));
        $waktu = date("H:i", strtotime($tanggal_waktu));
        $pecah = explode("-", $tanggal_asli);
        $tanggal = $pecah[0];
        $bulan = $this->bulan($pecah[1]);
        $tahun = $pecah[2];

        $return = $tanggal . ' ' . $bulan . ' ' . $tahun . ' ' . $waktu;

        return $return;
    }

    function mobile_waktu($tanggal_waktu) {
        $tanggal_asli = date("d-m-Y", strtotime($tanggal_waktu));
        $waktu = date("H:i", strtotime($tanggal_waktu));
        $pecah = explode("-", $tanggal_asli);
        $tanggal = $pecah[0];
        $bulan = $this->bulan($pecah[1]);
        $tahun = $pecah[2];

        $return = $tanggal . ' ' . $bulan . ' ' . $tahun . ' ' . $waktu;
        return $return;
    }


}

?>
