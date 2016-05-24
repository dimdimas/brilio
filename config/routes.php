	<?php

if (BRILIO_CURRENT_VIEWPORT == 'desktop') {
	# code...
	// $router->group('www.brilio.net', function(){
		$routes           														= [];
		//ROUTES DESKTOP EN
		if(BRILIO_CURRENT_LANG == 'en'){
			$routes['tag/([a-zA-Z\-_0-9.]+)/([index0-9]+).html']                    = 'desktop_en/TagController/index/$1/$2';
			$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)'] = 'desktop_en/TagNameController/index/$1/$2/$3';
			$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                    = 'desktop_en/TagNameController/index/$1/$2';
			$routes['brands/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                 = 'desktop_en/TagNameController/sponsor_brand/$1/$2';
			$routes['video/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'desktop_en/NewsVideoDetailController/index/$1/$2';
			$routes['devel/photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']            = 'desktop_en/DevelPhotoDetailController/index/$2'; // preview
			$routes['photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'desktop_en/PhotoDetailController/index/$1/$2';
			$routes['cronsponsorlink']                      												= 'SponsorLinkController/cronSponsorlink';
			$routes['deletesponsorlink/([photo]+|[news]+|[video]+)/([0-9\-])']      = 'desktop_en/SponsorLinkController/mongoMassDeleteLink/$1/$2';
			$routes['sponsorlink/([photo]+|[news]+|[video]+)/([0-9]+)']             = 'SponsorLinkController/mongoMassUpdate/$1/$2';
			$routes['cookies_close.html']                                           = 'desktop_en/collect_email/cookies_close';
			$routes['save_collect_email/']                                          = 'desktop_en/collect_email/save_collect_email';
			$routes['search-result.html']                                           = 'desktop_en/SearchController/Index';
			$routes['search-result/([a-zA-Z\-_0-9.]+)']                             = 'desktop_en/SearchController/data/$1';
			$routes['send_contact/([a-zA-Z\-_0-9.]+)']                              = 'desktop_en/static_page/send_contact';
			$routes['send_pengaduan/([a-zA-Z\-_0-9.]+)']                            = 'desktop_en/static_page/send_pengaduan';
			$routes['company/about']                                                = 'desktop_en/StaticPageController/about';
			$routes['company/redaksi']                                              = 'desktop_en/StaticPageController/redaksi';
			$routes['company/sitemap']                                              = 'desktop_en/StaticPageController/sitemap';
			$routes['company/contact-us']                                           = 'desktop_en/StaticPageController/contact_us';
			$routes['company/pengaduan']                                            = 'desktop_en/StaticPageController/pengaduan';
			$routes['company/disclaimer']                                           = 'desktop_en/StaticPageController/disclaimer';
			$routes['company/kode-etik']                                            = 'desktop_en/StaticPageController/kode_etik';
			$routes['company/privacy-policy']                                       = 'desktop_en/StaticPageController/privacy_policy';
			$routes['company/karir']                                                = 'desktop_en/StaticPageController/karir';
			$routes['plugins/feedback.php']                                         = 'desktop_en/StaticPageController/feedback';
			// $routes['search-result.html']                                        = 'SearchController/index/';
			$routes['tag/([a-zA-Z\-_0-9.]+)']                                       = 'desktop_en/TagController/index/$1/$2';
			$routes['([a-zA-Z\!-_0-9.]+)/index([0-9]+)?.html']                       = 'desktop_en/CategoryController/index/$1/$2';
			$routes['most-commented/([a-zA-Z\-_0-9.]+)']                            = 'desktop_en/MostCommentedController/index/$1/$2/$3/$4/$5';
			$routes['most-shared/([a-zA-Z\-_0-9.]+)']                               = 'desktop_en/MostSharedController/index/$1/$2/$3/$4/$5';
			$routes['most-liked/([a-zA-Z\-_0-9.]+)']                                = 'desktop_en/MostLikeController/index/$1/$2/$3/$4/$5';
			$routes['popular/([a-zA-Z\-_0-9.]+)']                                   = 'desktop_en/PopularController/index/$1/$2/$3/$4/$5';
			$routes['feed/fb']                                                      = 'desktop_en/FeedController/facebook';
			$routes['feed/kurio']                                                   = 'desktop_en/FeedController/kurio';
			$routes['([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                        = 'desktop_en/NewsDetailController/index/$1/$2'; //buat baca news
			$routes['popular']                                                      = 'desktop_en/PopularController/index/';
			$routes['most-liked']                                                   = 'desktop_en/MostLikeController/index/';
			$routes['most-commented']                                               = 'desktop_en/MostCommentedController/index/';
			$routes['most-shared']                                                  = 'desktop_en/MostSharedController/index/';
			$routes['tag']                                                          = 'desktop_en/TagController/index/';
			// $routes['tags']                                                         = 'desktop_en/TagsController/index/'; // ga dipake
			$routes['robots.txt']                                                   = 'desktop_en/static_page/robots';
			$routes['feed']                                                         = 'desktop_en/FeedController/index';
			$routes['([a-zA-Z\-_0-9.]+)']                                           = 'desktop_en/CategoryController/index/$1';
			$routes['/']                                                            = 'desktop_en/HomeController/index';
			//END OF ROUTES DESKTOP EN
		}else{
			//ROUTES DESKTOP ID
			$routes['tag/([a-zA-Z\-_0-9.]+)/([index0-9]+).html']                    = 'desktop/TagController/index/$1/$2';
			$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)'] = 'desktop/TagNameController/index/$1/$2/$3';
			$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                    = 'desktop/TagNameController/index/$1/$2';
			$routes['brands/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                 = 'desktop/TagNameController/sponsor_brand/$1/$2';
			$routes['video/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'desktop/NewsVideoDetailController/index/$1/$2';
			$routes['devel/photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']            = 'desktop/DevelPhotoDetailController/index/$2'; // preview
			$routes['photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'desktop/PhotoDetailController/index/$1/$2';
			$routes['cronsponsorlink']                      												= 'SponsorLinkController/cronSponsorlink';
			$routes['deletesponsorlink/([photo]+|[news]+|[video]+)/([0-9\-])']      = 'desktop/SponsorLinkController/mongoMassDeleteLink/$1/$2';
			$routes['sponsorlink/([photo]+|[news]+|[video]+)/([0-9]+)']             = 'SponsorLinkController/mongoMassUpdate/$1/$2';
			$routes['cookies_close.html']                                           = 'desktop/collect_email/cookies_close';
			$routes['save_collect_email/']                                          = 'desktop/collect_email/save_collect_email';
			$routes['search-result.html']                                           = 'desktop/SearchController/Index';
			$routes['search-result/([a-zA-Z\-_0-9.]+)']                             = 'desktop/SearchController/data/$1';
			$routes['send_contact/([a-zA-Z\-_0-9.]+)']                              = 'desktop/static_page/send_contact';
			$routes['send_pengaduan/([a-zA-Z\-_0-9.]+)']                            = 'desktop/static_page/send_pengaduan';
			$routes['company/about']                                                = 'desktop/StaticPageController/about';
			$routes['company/redaksi']                                              = 'desktop/StaticPageController/redaksi';
			$routes['company/sitemap']                                              = 'desktop/StaticPageController/sitemap';
			$routes['company/contact-us']                                           = 'desktop/StaticPageController/contact_us';
			$routes['company/pengaduan']                                            = 'desktop/StaticPageController/pengaduan';
			$routes['company/disclaimer']                                           = 'desktop/StaticPageController/disclaimer';
			$routes['company/kode-etik']                                            = 'desktop/StaticPageController/kode_etik';
			$routes['company/privacy-policy']                                       = 'desktop/StaticPageController/privacy_policy';
			$routes['company/karir']                                                = 'desktop/StaticPageController/karir';
			$routes['plugins/feedback.php']                                         = 'desktop/StaticPageController/feedback';
			// $routes['search-result.html']                                        = 'SearchController/index/';
			$routes['tag/([a-zA-Z\-_0-9.]+)']                                       = 'desktop/TagController/index/$1/$2';
			$routes['([a-zA-Z\!-_0-9.]+)/index([0-9]+)?.html']                      = 'desktop/CategoryController/index/$1/$2';
			$routes['most-commented/([a-zA-Z\-_0-9.]+)']                            = 'desktop/MostCommentedController/index/$1/$2/$3/$4/$5';
			$routes['most-shared/([a-zA-Z\-_0-9.]+)']                               = 'desktop/MostSharedController/index/$1/$2/$3/$4/$5';
			$routes['most-liked/([a-zA-Z\-_0-9.]+)']                                = 'desktop/MostLikeController/index/$1/$2/$3/$4/$5';
			$routes['popular/([a-zA-Z\-_0-9.]+)']                                   = 'desktop/PopularController/index/$1/$2/$3/$4/$5';
			$routes['feed/fb']                                                      = 'desktop/FeedController/facebook';
			$routes['feed/kurio']                                                   = 'desktop/FeedController/kurio';
			$routes['([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                        = 'desktop/NewsDetailController/index/$1/$2'; //buat baca news
			$routes['popular']                                                      = 'desktop/PopularController/index/';
			$routes['most-liked']                                                   = 'desktop/MostLikeController/index/';
			$routes['most-commented']                                               = 'desktop/MostCommentedController/index/';
			$routes['most-shared']                                                  = 'desktop/MostSharedController/index/';
			$routes['tag']                                                          = 'desktop/TagController/index/';
			// $routes['tags']                                                         = 'desktop/TagsController/index/'; // ga dipake
			$routes['robots.txt']                                                   = 'desktop/static_page/robots';
			$routes['feed']                                                         = 'desktop/FeedController/index';
			$routes['([a-zA-Z\-_0-9.]+)']                                           = 'desktop/CategoryController/index/$1';
			// $routes['/']                                                            = 'desktop/HomeController/index';
			$routes['/']                                                            = 'desktop/DevelHomeController/index';
		}//END OF ROUTES DESKTOP ID
	//   return $routes;
	// });
}
else{
// $router->group('m.brilio.net'), function(){
	$routes           														= [];
	//ROUTES MOBILE EN
	if(BRILIO_CURRENT_LANG == 'en'){
		$routes['search-result.html']                                           = 'mobile_en/SearchController/Index';
		$routes['search-result/([a-zA-Z\-_0-9.]+)']                             = 'mobile_en/SearchController/data/$1';
		$routes['tag/([a-zA-Z\-_0-9.]+)/([index0-9]+).html']                    = 'mobile_en/TagController/index/$1/$2';
		$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)'] = 'mobile_en/TagsController/index/$1/$2/$3'; // TagNameController
		$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                    = 'mobile_en/TagsController/index/$1/$2';
		$routes['brands/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                 = 'mobile_en/TagsController/sponsor_brand/$1/$2';
		$routes['amp/video/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']              = 'mobile_en/AmpNewsVideoDetailController/index/$1//$2';
		$routes['video/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'mobile_en/NewsVideoDetailController/index/$1//$2';
		$routes['amp/photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']              = 'mobile_en/AmpPhotoDetailController/index/$2';
		$routes['photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'mobile_en/PhotoDetailController/index/$1/$2';
		$routes['cookies_close.html']                                           = 'mobile_en/collect_email/cookies_close';
		$routes['save_collect_email/([a-zA-Z\-_0-9.]+)']                        = 'mobile_en/collect_email/save_collect_email';
		$routes['send_contact/([a-zA-Z\-_0-9.]+)']                              = 'mobile_en/static_page/send_contact';
		$routes['send_pengaduan/([a-zA-Z\-_0-9.]+)']                            = 'mobile_en/static_page/send_pengaduan';
		$routes['company/about']                                                = 'mobile_en/StaticPageController/about';
		$routes['company/redaksi']                                              = 'mobile_en/StaticPageController/redaksi';
		$routes['company/sitemap']                                              = 'mobile_en/StaticPageController/sitemap';
		$routes['company/contact-us']                                           = 'mobile_en/StaticPageController/contact_us';
		$routes['company/pengaduan']                                            = 'mobile_en/StaticPageController/pengaduan';
		$routes['company/disclaimer']                                           = 'mobile_en/StaticPageController/disclaimer';
		$routes['company/kode-etik']                                            = 'mobile_en/StaticPageController/kode_etik';
		$routes['company/privacy-policy']                                       = 'mobile_en/StaticPageController/privacy_policy';
		$routes['company/karir']                                                = 'mobile_en/StaticPageController/karir';
		$routes['plugins/feedback.php']                                         = 'mobile_en/StaticPageController/feedback';
		$routes['tag/([a-zA-Z\-_0-9.]+)']                                       = 'mobile_en/TagController/index/$1/$2';
		$routes['([a-zA-Z\!-_0-9.]+)/index([0-9]+)?.html']                       = 'mobile_en/CategoryController/index/$1/$2';
		$routes['most-commented/([a-zA-Z\-_0-9.]+)']                            = 'mobile_en/MostCommentedController/index/$1/$2/$3/$4/$5';
		$routes['most-shared/([a-zA-Z\-_0-9.]+)']                               = 'mobile_en/MostSharedController/index/$1/$2/$3/$4/$5';
		$routes['most-liked/([a-zA-Z\-_0-9.]+)']                                = 'mobile_en/MostLikedController/index/$1/$2/$3/$4/$5';
		$routes['popular/([a-zA-Z\-_0-9.]+)']                                   = 'mobile_en/PopularController/index/$1/$2/$3/$4/$5';
		$routes['amp/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                    = 'mobile_en/AmpNewsDetailController/index/$1/$2'; //buat baca news
		$routes['([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                        = 'mobile_en/NewsDetailController/index/$1/$2'; //buat baca news
		$routes['popular']                                                      = 'mobile_en/PopularController/index/';
		$routes['most-liked']                                                   = 'mobile_en/MostLikedController/index/';
		$routes['most-commented']                                               = 'mobile_en/MostCommentedController/index/';
		$routes['most-shared']                                                  = 'mobile_en/MostSharedController/index/';
		$routes['tag']                                                          = 'mobile_en/TagController/index/';
		$routes['tag/([a-zA-Z\-_0-9.]+)']                                       = 'mobile_en/TagController/index/$1/$2'; // tes
		$routes['robots.txt']                                                   = 'mobile_en/static_page/robots';
		$routes['feed']                                                         = 'mobile_en/feed/index';
		$routes['([a-zA-Z\-_0-9.]+)']                                           = 'mobile_en/CategoryController/index/$1';
		$routes['/']                                                            = 'mobile_en/IndexController/index';
		//END OF ROUTES MOBILE EN
	}else{
		//ROUTES MOBILE ID
		$routes['search-result.html']                                           = 'mobile/SearchController/Index';
		$routes['search-result/([a-zA-Z\-_0-9.]+)']                             = 'mobile/SearchController/data/$1';
		$routes['tag/([a-zA-Z\-_0-9.]+)/([index0-9]+).html']                    = 'mobile/TagController/index/$1/$2';
		$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)'] = 'mobile/TagsController/index/$1/$2/$3'; // TagNameController
		$routes['tag/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                    = 'mobile/TagsController/index/$1/$2';
		$routes['brands/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                 = 'mobile/TagsController/sponsor_brand/$1/$2';
		$routes['amp/video/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']              = 'mobile/AmpNewsVideoDetailController/index/$1//$2';
		$routes['video/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'mobile/NewsVideoDetailController/index/$1//$2';
		$routes['amp/photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']              = 'mobile/AmpPhotoDetailController/index/$2';
		$routes['photo/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                  = 'mobile/PhotoDetailController/index/$1/$2';
		$routes['cookies_close.html']                                           = 'mobile/collect_email/cookies_close';
		$routes['save_collect_email/([a-zA-Z\-_0-9.]+)']                        = 'mobile/collect_email/save_collect_email';
		$routes['send_contact/([a-zA-Z\-_0-9.]+)']                              = 'mobile/static_page/send_contact';
		$routes['send_pengaduan/([a-zA-Z\-_0-9.]+)']                            = 'mobile/static_page/send_pengaduan';
		$routes['company/about']                                                = 'mobile/StaticPageController/about';
		$routes['company/redaksi']                                              = 'mobile/StaticPageController/redaksi';
		$routes['company/sitemap']                                              = 'mobile/StaticPageController/sitemap';
		$routes['company/contact-us']                                           = 'mobile/StaticPageController/contact_us';
		$routes['company/pengaduan']                                            = 'mobile/StaticPageController/pengaduan';
		$routes['company/disclaimer']                                           = 'mobile/StaticPageController/disclaimer';
		$routes['company/kode-etik']                                            = 'mobile/StaticPageController/kode_etik';
		$routes['company/privacy-policy']                                       = 'mobile/StaticPageController/privacy_policy';
		$routes['company/karir']                                                = 'mobile/StaticPageController/karir';
		$routes['plugins/feedback.php']                                         = 'mobile/StaticPageController/feedback';
		$routes['tag/([a-zA-Z\-_0-9.]+)']                                       = 'mobile/TagController/index/$1/$2';
		$routes['([a-zA-Z\!-_0-9.]+)/index([0-9]+)?.html']                       = 'mobile/CategoryController/index/$1/$2';
		$routes['most-commented/([a-zA-Z\-_0-9.]+)']                            = 'mobile/MostCommentedController/index/$1/$2/$3/$4/$5';
		$routes['most-shared/([a-zA-Z\-_0-9.]+)']                               = 'mobile/MostSharedController/index/$1/$2/$3/$4/$5';
		$routes['most-liked/([a-zA-Z\-_0-9.]+)']                                = 'mobile/MostLikedController/index/$1/$2/$3/$4/$5';
		$routes['popular/([a-zA-Z\-_0-9.]+)']                                   = 'mobile/PopularController/index/$1/$2/$3/$4/$5';
		$routes['amp/([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                    = 'mobile/AmpNewsDetailController/index/$1/$2'; //buat baca news
		$routes['([a-zA-Z\-_0-9.]+)/([a-zA-Z\-_0-9.]+)']                        = 'mobile/NewsDetailController/index/$1/$2'; //buat baca news
		$routes['popular']                                                      = 'mobile/PopularController/index/';
		$routes['most-liked']                                                   = 'mobile/MostLikedController/index/';
		$routes['most-commented']                                               = 'mobile/MostCommentedController/index/';
		$routes['most-shared']                                                  = 'mobile/MostSharedController/index/';
		$routes['tag']                                                          = 'mobile/TagController/index/';
		$routes['tag/([a-zA-Z\-_0-9.]+)']                                       = 'mobile/TagController/index/$1/$2'; // tes
		$routes['robots.txt']                                                   = 'mobile/static_page/robots';
		$routes['feed']                                                         = 'mobile/feed/index';
		$routes['([a-zA-Z\-_0-9.]+)']                                           = 'mobile/CategoryController/index/$1';
		$routes['/']                                                            = 'mobile/IndexController/index';
		//END OF ROUTES MOBILE ID
	 }
//   return $routes;
// });
}



	?>
