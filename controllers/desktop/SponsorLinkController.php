<?php
  /**
   *
   */
  class SponsorLinkController extends CController
  {

    function __construct()
    {
      parent::__construct();
      $this->library(array('SponsorLinkContent'));
    }

    function cronSponsorlink(){

      $news_type = ['news', 'photo', 'video'];

      foreach ($news_type as $key) {
        $this->mongoMassUpdate($key);
        // $this->SponsorLinkContent->massdeleteSingleKeyword('news', 'ipsum', 1000);
        sleep(5);
      }
    }

    function mongoMassUpdate($news_type = '', $limit){
      $mongo_prefix = $this->config['mongo_prefix'];
      $this->SponsorLinkContent->generateMassSponsorlink($mongo_prefix, $news_type, $limit);
      // $this->SponsorLinkContent->generateMassSponsorlink($news_type);

    }

    function mongoMassDeleteLink($news_type = '', $date = ''){
      // echo ucfirst($news_type) ." Sponsorlink deleted ";
      $this->SponsorLinkContent->generateMassDeleteSponsorlink($news_type, $date);
    }
  }


 ?>
