<?php

//determine from where acessed
$domain = explode('/', $_SERVER['REQUEST_URI']);

if (!defined('ENVIRONTMENT')) {
	define("ENVIRONTMENT", 'development');
}

if ($domain[2] == 'm') {
	$viewport = "mobile";
}else {
	$viewport = "desktop";
}

if ($domain[3] == 'en') {
	$site_lang = "en";
}else {
	$site_lang = "id";
}

if (!defined('BRILIO_CURRENT_VIEWPORT')) {
    define("BRILIO_CURRENT_VIEWPORT", $viewport);
}

if (!defined('BRILIO_CURRENT_LANG')) {
	define("BRILIO_CURRENT_LANG", $site_lang);
}


/**
 *  -----------------------------------------------------------------------------------
 *
 *  Konfigurasi umum
 *
 *  -----------------------------------------------------------------------------------
 */
$config['sitename'] = "trigger.co.id";
$config['is_maintanance'] = false;
$config['debug_exec_time'] = true;
$config['eloquent_query_log'] = true;
$config['mysql_timezone'] = '+07:00';

/**
 *  ----------------------------------------------------------------------------
 *  Autoloading
 *  Daftar model, library atau helper yang diload secara otomatis oleh system.
 *  status = implemented
 *
 *  @todo membuat implementasi autoload - done
 *
 *  @var mixed $config['models']
 *  @var mixed $config['libraries']
 *  @var mixed $config['helpers']
 *
 *  ----------------------------------------------------------------------------
 */
$config['models'] = array();
$config['libraries'] = array();
$config['helpers'] = array("core", "layout");
// viewport
// $config['BRILIO_CURRENT_VIEWPORT '] = 'desktop';

// MongoDB
$config['mongo_host'] = '127.0.0.1';
$config['mongo_port'] = 27017;
$config['mongo_db'] = "develmerdeka";
if (BRILIO_CURRENT_VIEWPORT == 'mobile') {
	if(BRILIO_CURRENT_LANG == 'en'){
	  $config['mongo_prefix'] = "brilionet_en_";
	}else{
	  $config['mongo_prefix'] = "brilionet_";
	}
}else{
	if(BRILIO_CURRENT_LANG == 'en'){
	  $config['mongo_prefix'] = "brilionet_en_";
	}else{
	  $config['mongo_prefix'] = "brilionet_";
	}
}


// Required if Mongo is running in auth mode
$config['mongo_user'] = "develmerdeka";
$config['mongo_pass'] = "TicTax2!R_4";

if (BRILIO_CURRENT_VIEWPORT == 'mobile') {
	if(BRILIO_CURRENT_LANG == 'en'){
		$config['json_news_detail'] = TRUE;
	}else{
		$config['json_news_detail'] = TRUE;
	}
}else{
	if(BRILIO_CURRENT_LANG == 'en'){
		$config['json_news_detail'] = TRUE;
	}else{
		$config['json_news_detail'] = TRUE;
	}
}

/*
 * Defaults to FALSE. If FALSE, the program continues executing without waiting for a database response.
 * If TRUE, the program will wait for the database response and throw a MongoCursorException if the update did not succeed.
 */
$config['mongo_query_safety'] = TRUE;

//If running in auth mode and the user does not have global read/write then set this to true
$config['mongo_db_flag'] = TRUE;

/*DIMAS*/
if(BRILIO_CURRENT_LANG == 'en'){
	$config['domain_id'] = 10;
}else{
	$config['domain_id'] = 3;
}




/**
 *  -------------------------------------------------
 *
 *  Theme
 *
 *  -------------------------------------------------
 */
$config['theme'] = "default";

$GLOBALS['month_short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des');
$GLOBALS['month_long'] = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
$GLOBALS['day_short'] = array('Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Ming');
$GLOBALS['day_long'] = array('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu');
$GLOBALS['religion'] = array('islam' => 'islam', 'kristen katolik' => 'kristen katolik', 'kristen protestan' => 'kristen protestan', 'hindu' => 'hindu', 'buddha' => 'buddha');


// $config['tag_id_brand']   =
// [
// //FOR TEST ON LOCAL
// 	#YOUR CODE
// 	'13394' =>
//     [
//       'logo-uc.png',
//       ''
//     ],
// // END TEST
//   	'8424' =>
//   	[
//    		'logo-intel-desktop.png',
//    		'',
//   	],
//   	'11393' =>
//   	[
// 	   	'logo-intel-desktop.png',
// 	   	'',
//   	],
//   	'10448' =>
//   	[
//     	'logo-fabelio-fix.png',
//     	'',
//   	],
//   	'10503' =>
//   	[
//    		'logo-air-asia.png',
//    		''
//   	],
//   	'11657' =>
//   	[
//    		'logo-insto.jpg',
//    		''
//   	],
//   	'11738' =>
//   	[
//    		'logo-lenovo.jpg',
//    		''
//   	],
//   	'8495' =>
// 	[
// 	   	'logo-insto-new.jpg',
// 	   	''
// 	],
//   	'11802' =>
//         [
//           'logo-uc.png',
//           ''
//         ],
//         '11874' =>
//         [
//           'logo-brother.jpg',
//           ''
//         ],
//         '11887' =>
//         [
//           'logo-garnier.png',
//           ''
//         ],
//
// ];


$config['keyword_brand'] =
[
		// for brand url test please put array temp under this comment
				#your code here
		'kisah-sopir-bus-jadi-seleb-dadakan-karena-tolong-nenek-salut-1601294' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],
		'photo-page-151207e' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],
		'test-link-video-selebriti-151202r' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],
		// end of temp test array

		'10-tanda-ketika-bos-di-kantormu-menjengkelkan-bikin-kamu-malas-kerja-150831f' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],

		'pojok-beteng-keraton-yogyakarta-cuma-tinggal-3-yang-1-lagi-kemana-ya-151015f' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],

		'bukti-wisata-jogja-geser-bali-bandung-bahkan-destinasi-luar-negeri-151015o' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],

		'20-tempat-mengagumkan-ini-cukup-jadi-alasanmu-pergi-berlibur-ke-tokyo-1510154' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],

		'7-pesona-wisata-di-krabi-kamu-juga-bisa-temukan-karimunjawa-di-sana-151015w' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],

		'15-alasan-kenapa-orang-malaysia-sebut-langkawi-persis-dengan-bali-151015q' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],

		'dengan-rp-5-juta-kamu-bisa-kunjungi-16-tujuan-wisata-di-thailand-ini-1510150' =>
		[
				'ssbd_75.png',
				'margin-top: 14px;',
		],

		'25-alasan-mengapa-cewek-lebih-suka-cowok-bersih-tak-berjanggut-151218h' =>
		[
				'logo-philips.png',
				'margin-top: 28px;',
		],

		'yuk-intip-prediksi-peruntungan-kamu-di-tahun-monyet-api-160206v' =>
		[
				'logo-bearbrand-small.png',
				'margin-top: 14px;',
		],

];

// $config['allowed_sponsorship_tag'] = [9, 10, ];

if(BRILIO_CURRENT_LANG == 'en'){
	$config['video_sponsor'] =
		[
			'allowed_sponsorship_tag' => [],
			'url_code' => '37sNP59yjeQ',
			'video_title' => 'KEMERIAHAN REDCARPET GALA PREMIERE ADA APA DENGAN CINTA 2 DI YOGYAKARTA',
			'video_deskrip' => 'Kamu tidak sempat menyaksikan Gala Premiere AADC 2 karena sedang berada di luar kota atau ada kesibukan lain? Tidak perlu khawatir, kamu bisa tonton video brilio ini untuk tahu kemeriahan Premiere AADC 2.',
		];
}else{
	$config['video_sponsor'] =
	[
		'allowed_sponsorship_tag' => [9, 10],
		'url_code' => 'g1hnLAdrGkI',
		'video_title' => 'Jomblo Hunt Web Series - Episode 1',
		'video_deskrip' => 'Saksikan perjuangan para jombloers menghadapi tantangan demi menjadi jomblo yang berkualitas ! Jangan ketinggalan episode selanjutnya tiap Jumat !',
	];
}


// $config['article_sponsor'] = ['24771', '24776', '24772', '25531', '25527', '26042', '23238', '23245',
//                               '23240', '23247', '23243', '26532', '27010', '27387', '28674', '27789',
//                               '28029', '28131', '29347', '31288', '29298', '29305', '29598', '30003',
//                               '30009', '30012', '30019', '30470', '30797', '30843', '30846', '30847',
//                               '30850', '30854', '31372', '31947', '32041', '32231', '32744', '33224',
//                               '32141', '32160', '32276', '32277', '33194', '33198', '33230', '33228',
//                               '32326', '31305', '31652', '31852', '42587', '34162', '42829', '43730',
//                               '44094', '41486', '42797', '44101', '45711', '46978', '34740', '45038',
//                               '45195'];
?>
