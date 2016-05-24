<?php

class SponsorLinkContent
{
  protected $configurations;

  function __construct()
  {
    if (is_file(CONFIG_PATH . "config.php"))
    {
        include(CONFIG_PATH ."config.php");
        $this->configurations = & $config;
    }
  }

  // public function generateMassSponsorlink($news_type = ''){
  public function generateMassSponsorlink($mongo_prefix ='', $news_type = '', $limit){

    $mongo = app_load_mongo();
    $row = $mongo->get($mongo_prefix. $news_type, $limit)->result_array();

    $total = 0;
    $tmp_result[] = '';
    foreach ($row as $rw => $r) {
      if ($news_type == 'photo')
        {$r['news_TE'] = 'Photo';}
      if ($news_type == 'video')
        {$r['news_TE'] = 'Video';}

      $result = $this->generateSingleNews($r, $r['news_TE'], $news_type);

      if ( !empty($result['total']) ) {
        $total = $total + $result['total'];
      }
      if ( !empty($result['news_data']) ) {
        $tmp_result[] = $result['news_data'];
        echo '<pre>';
        echo $result['news_data']['json_name'].'----->';
        echo '</pre>';
      }
    }
    echo 'Total news generated : '.$total.'<br>';//$total;
  }

  public function generateMassDeleteSponsorlink($news_type ='', $date =''){

    $mongo = app_load_mongo();
    $collections = $mongo->get($this->configurations['mongo_prefix']. $news_type, 1000)->result_array();
    $total = 0;

    foreach ($collections as $collection => $row) {
      $result = $this->deleteSingleNews($row, $news_type);
      if ( !empty($result) ) {
        $total ++;
      }
    }
  }

  public function generateSingleNews($news_data, $TE, $news_type =''){
    /*
      For every generateSingleNews execute it will receive a single collection of article.
    */
    $keywords = $this->keywords(); //for keyword list
    $flag = false;
    $data['total'] = 0 ;
    $data['news_data'] = '';

    if ( !empty($news_data['news_sponsorship']) ) {
      $news_data['news_content'] = $this->deleteSingleNews($news_data, $news_type);
      return $news_data;
    }
    else {
      $count = 0;
      $tmp_key = [];
      $existed_keyword = [];
      $total_changelink = 3;
      $news_data = $this->deleteSingleNews($news_data, $news_type);

      foreach ($keywords as $key => $val) {
        if (in_array($val['category'], $existed_keyword)){
          continue;
        }else {
          $existed_keyword[] = $val['category'];
        }
        if ($count == $total_changelink) {
          break;
        }

        $change_link = "<a href='http://".$val['url']."' class='content-sponsorlink' target='_blank' onclick=\"ga('send', 'event', 'TE', 'Sponsored link', '".$val['keyword']."')\" onload=\"ga('send', 'event', 'Impression link', '".$val['keyword']."', '".$val['url']."')\" >".$val['keyword']."</a>";
        $pattern = '/\b'.$val['keyword'].'?\b/';
        $pattern_linked = '/<a[^>]+>'.$val['keyword'].'<\/a>/';
        $check = preg_match($pattern, $news_data['news_content']); //found keyword
        $check_linked = preg_match($pattern_linked, html_entity_decode($news_data['news_content'])); //found keyword already changed into sponsorlink

        if ( !empty($check) )
        {
            if (empty($check_linked) && $count<=$total_changelink) {
              $news_data['news_content'] = preg_replace($pattern, $change_link, $news_data['news_content'] , 1);
            }
            $flag = true;
            $tmp_key[] = $val ;
            $count++;
            $data['total'] = 1;
            $data['news_data'] = $news_data;
        }
      }
      if ($flag == true)
        $this->saveToMongo($news_data, $tmp_key, $news_type);
    }
    return $data;
  }

  public function deleteSingleNews($news_data, $news_type = ''){

    /*
      this function for delete every single keyword sponsorlink in an article
      $news_data = full article data from mongo DB
      $news_type is article video, photo, or news
    */

    $keywords = $this->keywords(); //get keyword list
    $pattern_linked = "/<a href='[^']+' class='content-sponsorlink'[^>]+>(.*?)<\/a>/"; //to check link which is already change into A LINK
    preg_match_all($pattern_linked, html_entity_decode($news_data['news_content']), $check_linked); //found keyword already changed into sponsorlink

    if ( !empty($check_linked) ) {
      $no = 1;
      foreach ($check_linked[1] as $key => $val) {
        // if ($no > 3) {
          $link_selected = "/<a href='[^']+' class='content-sponsorlink'[^>]+>$val<\/a>/";
        //   $news_data['news_content'] = preg_replace($link_selected, $val, $news_data['news_content']);
        // }
        // echoPre($link_selected);
        $news_data['news_content'] = preg_replace($link_selected, $val, $news_data['news_content']);
        $no++;
      }
      //update mongo news
      updateDataMongo($this->configurations['mongo_prefix'].$news_type,  $news_data, [ 'json_name'=> $news_data['json_name'], 'news_url_with_base' => $news_data['news_url_with_base']]);
      //update log
      deleteDataMongo([ 'json_name'=> $news_data['json_name'], 'news_url_with_base' => $news_data['news_url_with_base']], $this->configurations['mongo_prefix'].'sponsorlinkcontent');
    }
    return $news_data;
  }

  public function massdeleteSingleKeyword($collection = '', $keyword = '', $limit){

    $mongo = app_load_mongo();
    $collections = $mongo->get($this->configurations['mongo_prefix']. $collection, $limit)->result_array();

    $total = 0;
    foreach ($collections as $row => $val) {
      if ( empty($val['news_sponsorship']) )
      {
        $del = $this->deleteSingleKeyword($val, $keyword);

        if ( !empty($del['total']) )
        {
          $total = $total + $del['total'] ;
          echo '<pre>';
          echo $del['news_data']['json_name'];
          echo '</pre>';
        }
      }
    }
    echo '<pre>';
    echo 'Total article with keyword \''.$keyword.'\' deleted : '.$total;
    echo '</pre>';
  }

  public function deleteSingleKeyword($news_data, $keyword){
    $data['total'] = 0;
    $data['news_data'] ='';

    $pattern_linked = "/<a href='[^']+' class='content-sponsorlink'[^>]+>\b$keyword\b<\/a>/";
    preg_match_all($pattern_linked, html_entity_decode($news_data['news_content']), $check_linked); //found keyword already changed into sponsorlink

    if ( !empty($check_linked[0]) ) {
        $news_data['news_content'] = preg_replace($pattern_linked, $keyword, $news_data['news_content']);
        $this->saveToMongo($news_data, '', "news");

        $data['total'] = 1 ;
        $data['news_data'] = $news_data;
    }
    return $data;
  }

  function saveToMongo($news_data, $keyword_scaned = '', $news_type){

    // mongoData use for log sponsorlink. DIFFERENT with mongo from news
    $mongoData = [
      'json_name' => $news_data['json_name'],
      'news_id' => $news_data['news_id'],
      'news_title' => $news_data['news_title'],
      'news_url_with_base' => $news_data['news_url_with_base'],
      'news_type' => $news_data['news_type'],
      'news_category_name' => $news_data['news_category_name'],
      'news_url' => $news_data['news_url_with_base'],
      'news_keyword_scanned' => $keyword_scaned,
      'news_sponsorlink_start' => strtotime(date("Y-m-d H:i:s")),
      'news_sponsorlink_end' => strtotime('+ 7 days', time()),
    ];

    // update mongo log sponsorlink content
    updateDataMongo($this->configurations['mongo_prefix'].'sponsorlinkcontent', $mongoData , [ 'json_name' => $mongoData['json_name']]);
    //update mongo news
    updateDataMongo($this->configurations['mongo_prefix'].$news_type,  $news_data, [ 'json_name'=> $mongoData['json_name'], 'news_url_with_base' => $mongoData['news_url']]);

    return true;

  }

  function keywords(){
    $keywords = [
      '1' => [
          'keyword' => 'ipsum',
          'url' => 'www.ipsum.com',
          'category' => 'abrd'
      ],
      '2' => [
          'keyword'=>'tebal',
          'url' => 'www.tebal.com',
          'category' => 'gadget'
      ],
      '3' => [
          'keyword'=>'Indonesia',
          'url' => 'www.indonesia.com',
          'category' => 'negara'
      ],
      '4' => [
          'keyword'=>'consectetuer',
          'url' => 'www.consectetuer.com',
          'category' => 'phone'
      ],
      '5' => [
          'keyword'=>'kaya',
          'url' => 'www.kaya.com',
          'category' => 'gadget'
      ],
      '6' => [
          'keyword'=>'lorem',
          'url' => 'www.lorem.com',
          'category' => 'logic'
      ],
      '7' => [
          'keyword'=>'ridiculus',
          'url' => 'www.ridiculus.com',
          'category' => 'logic'
      ],
      '8' => [
          'keyword'=>'Balikpapan',
          'url' => 'www.Balikpapan.com',
          'category' => 'kota'
      ],
      '9' => [
          'keyword'=>'hati-hati',
          'url' => 'www.hati-hati.com',
          'category' => 'sifat'
      ],
      '10' => [
          'keyword'=>'transportasi',
          'url' => 'www.transportasi.com',
          'category' => 'kota'
      ],
      '11' => [
          'keyword'=>'Game of Thrones',
          'url' => 'www.game-of-thrones.com',
          'category' => 'film'
      ],
      '12' => [
          'keyword'=> 'World Wildlife Fund',
          'url' => 'www.wwf.com',
          'category'=> 'organization'
      ],
    ];
    return $keywords;
  }

  function exclutionKeyword(){

    $exclutionKeyword =[
      '1' => [
        'keyword' => 'adidas',
        'url' => 'www.adidas.com',
        'kategori' => 'brand',
        // 'opponent' => 'nike',
      ],
    ];
  }

  // function _generateMassSponsorlink(){
  //     $keyword = [
  //       //format : 'keyword' => 'url keyword destination'
  //       'Balikpapan' => "www.balikpapan.com",
  //       'hati-hati' => "www.dota2.com",
  //       'tebal' => "www.tebal.com",
  //       'kaya' => "www.kaya.com",
  //       'Indonesia' => "wwww.indonesia.com",
  //       'akrab' => "www.akrab.com",
  //       'penghargaan' => "www.penghargaan.com",
  //       'transportasi' => "www.steam.com",
  //       'Game of Thrones' => "www.gameofthrones.com",
  //       'consequat' => "www.consequat.com",
  //       'cozy' => "www.cozy.com",
  //       'dolor' => "www.dolor.com",
  //       'parturient' => "www.parturient.com",
  //
  //     ];
  //
  //     $mongo = app_load_mongo();
  //     $row = $mongo->get($this->configurations['mongo_prefix']. 'news', 1000)->result_array();
  //
  //
  //     foreach ($row as $r => $v) {
  //       $TE = $v['news_TE'];
  //       $count = 0;
  //       $tmp_key = [];
  //       $total_changelink = 3;
  //       if ( !empty($v['news_sponsorship']) ) {
  //         continue;
  //       }
  //       else
  //       {
  //         $this->deleteSingleNews($v);
  //         foreach ($keyword as $key => $val)
  //         {
  //           if ($count >= $total_changelink) {
  //             continue;
  //           }
  //           $flag = false;
  //           $pattern = '/\b'.$key.'?\b/';
  //           $pattern_scaned = '/<a[^>]+>'.$key.'<\/a>/';
  //
  //           $check = preg_match($pattern, $v['news_content']); //found keyword
  //           $check_scaned = preg_match($pattern_scaned, $v['news_content']); //found keyword already changed into link
  //
  //           if ( !empty($check) && $count<3 )
  //           {
  //               $change_link = "<a href='http://$val' class='content-sponsorlink' target='_blank' onclick=\"ga('send', 'event', '$TE', 'Sponsored link', '$key')\" onload=\"ga('send', 'event', 'Impression link', '$key', '$val')\" >$key</a>";
  //               if ( empty($check_scaned) )
  //               {
  //                 $v['news_content'] = preg_replace($pattern, $change_link, $v['news_content'] , 1);
  //               }
  //               $count++;
  //               $flag = true;
  //               $tmp_key[] = $key ; //insert keyword detected into log
  //
  //               echo '<pre>';
  //               echo $v['json_name'].'-->'.$key;
  //               echo '</pre>';
  //           }
  //
  //           $mongoData = [
  //             'json_name' => $v['json_name'],
  //             'news_id' => $v['news_id'],
  //             'news_title' => $v['news_title'],
  //             'news_url' => $v['news_url'],
  //             'news_category_name' => $v['news_category_name'],
  //             'news_url' => $v['news_url_with_base'],
  //             'news_keyword_scanned' => $tmp_key,
  //             'news_sponsorlink_start' => strtotime(date("Y-m-d H:i:s")),
  //             'news_sponsorlink_end' => strtotime('+ 7 days', time()),
  //           ];
  //
  //           if ($flag == true)
  //           {
  //             $this->saveToMongo($v);
  //             //update log for news with sponsor link
  //             // updateDataMongo($this->configurations['mongo_prefix'].'sponsorlinkcontent', $mongoData , [ 'json_name' => $mongoData['json_name']]);
  //             //update mongo collection "news"
  //             // if ( empty($check_scaned) )
  //               // updateDataMongo($this->configurations['mongo_prefix'].'news', ['news_content' => $v['news_content'], 'news_sponsorlink_content' => true ], ['news_id' => $mongoData['news_id'], 'json_name' => $mongoData['json_name']]);
  //           }
  //         }//end of foreach keyword
  //       }//end of else
  //     }//end of foreach row
  //
  //     return true;
  // }

  // public function _deleteMassContentLink(){
  //   $keyword = [
  //     //format : 'keyword' => 'url keyword destination'
  //     'Balikpapan' => "www.balikpapan.com",
  //     'hati-hati' => "www.dota2.com",
  //     'tebal' => "www.tebal.com",
  //     'kaya' => "www.kaya.com",
  //     'Indonesia' => "wwww.indonesia.com",
  //     'akrab' => "www.akrab.com",
  //     'penghargaan' => "www.penghargaan.com",
  //     'transportasi' => "www.steam.com",
  //     'Game of Thrones' => "www.gameofthrones.com",
  //     'consequat' => "www.consequat.com",
  //     'cozy' => "www.cozy.com",
  //     'dolor' => "www.dolor.com",
  //     'parturient' => "www.parturient.com",
  //     'lorem'=> "www.lorem.com"
  //   ];
  //
  //   $mongo = app_load_mongo();
  //   $collection = $mongo->get($this->configurations['mongo_prefix']. 'news', 1000)->result_array();
  //
  //   foreach ($collection as $row => $r) {
  //       //to check link which is already change into A LINK
  //       $pattern_linked = "/<a href=.*? class='content-sponsorlink'[^>]+>(.*?)<\/a>/";
  //       preg_match_all($pattern_linked, html_entity_decode($r['news_content']), $check_linked); //found keyword already changed into sponsorlink
  //
  //       if ( !empty($check_linked) )
  //       {
  //         $flag= true;
  //         $no = 1;
  //         foreach ($check_linked[1] as $key => $val) {
  //           //for more than 3 in a content
  //           // if ($no > 3) {
  //           //   $link_selected = "/<a href=.*? class='content-sponsorlink'[^>]+>$val<\/a>/";
  //           //   $r['news_content'] = preg_replace($link_selected, $val, $r['news_content']);
  //           // }
  //           // $no++;
  //
  //           //for all sponsorlink in content
  //           $link_selected = "/<a href=.*? class='content-sponsorlink'[^>]+>$val<\/a>/";
  //           $r['news_content'] = preg_replace($link_selected, $val, $r['news_content']);
  //         echo '<pre>';
  //         echo $r['news_title'].'---->'.$val;
  //         echo '</pre>';
  //       }
  //         $mongoData = [
  //           'json_name' => $r['json_name'],
  //           'news_id' => $r['news_id'],
  //           'news_title' => $r['news_title'],
  //           'news_content' => $r['news_content'],
  //           'news_url' => $r['news_url'],
  //           'news_category_name' => $r['news_category_name'],
  //           'news_url' => $r['news_url_with_base'],
  //           'news_sponsorlink_start' => strtotime(date("Y-m-d H:i:s")),
  //           'news_sponsorlink_end' => strtotime('+ 7 days', time()),
  //         ];
  //
  //         if ($flag == true)
  //         {
  //           //update log for news with sponsor link
  //           deleteDataMongo(['json_name' => $mongoData['json_name'] ], $this->configurations['mongo_prefix'].'sponsorlinkcontent');
  //           //update mongo collection "news"
  //           if ( empty($check_scaned) )
  //             updateDataMongo($this->configurations['mongo_prefix'].'news', ['news_content' => $mongoData['news_content'], 'news_sponsorlink_content' => true ], ['news_id' => $mongoData['news_id'], 'json_name' => $mongoData['json_name']]);
  //
  //         }
  //       }
  //   }//end of foreach $collection
  //
  //   return $row;
  // }
  //'news_sponsorlink_start' => strtotime(date("Y-m-d H:i:s")),
  // 'news_sponsorlink_end' => strtotime('+ 7 days', time()),
  //


}

 ?>
