<?php
	/****
	 * rt2x.php
	 * Author: @ConanChou (http://conanblog.me)
	 * Discription: RT2X Extension for Tweet Nest
	 * Licensed under The MIT License 
	 * Version 0.3.3
	 * Release Date 23/02/2011
	****/

	class Extension_Rt2X {
		// Change accordingly
		private $cookies_path = '';
		private $cookie_files = array(
					"renren_cookie" => ".rt2renren.cookie",
					); // Full path to cookie file
		private $accounts = array(
					"renren_email" => "",
					"sina_email" => "",
					"renjian_email" => "",
					"fanfou_email" => ""
					); // Your renren.com's login Email account
		private $passwords = array(
					"renren_password" => "",
					"sina_password" => "",
					"renjian_password" => "",
					"fanfou_password" => ""
					); // Your renren.com's passcode
		// Stop editing
		private $uris = array(
					"renjian_uri" => 'http://api.renjian.com/statuses/update.xml',
					"sina_uri" => 'http://api.t.sina.com.cn/statuses/update.json',
					"fanfou_uri" => 'http://api.fanfou.com/statuses/update/update.xml'	
					);
		public function rt2x($tweet){
						
			$item = str_replace("RT ","转自▶",$tweet['text']);
			$all_arr = array('r','s','j','f');
			$exception=false;
			$ending = '';

			if (stripos($item, "#2all") !== false) {
				if (stripos($item, "#2all|") !== false) {
					$startStr = "#2all\|";
					$offset = 6;
					$exception=true;
				} else {
					$startStr = "#2all";
					$offset = 5;
				}
			} elseif (stripos($item, "#2") !== false) {
				$startStr = "#2";
				$offset = 2;
			}
			
			$start = stripos($item, "#2")+$offset;
			$arr = str_split($item);
			for ($i=$start;$i<=strlen($item);$i++){
				$end = $i;
				if($arr[$i]=='2'){
					$ending = '2';
					break;
				} elseif ($arr[$i]==' ') {
					$ending = ' ';
					break;
				} elseif ($arr[$i]=="\"") {
					break;
				}
			}

			if ($startStr != "#2all"){
				$arr = array_slice($arr,$start,$end-$start);
				$cmd_str = implode('',$arr);
				$arr_str = strtolower($cmd_str);
				$arr = str_split($arr_str);
				$cmd_arr = array_unique($arr);
				if($exception){
					$cmd_arr = array_diff($all_arr, $cmd_arr);
				}
			} else {
				$cmd_arr = $all_arr;
				$cmd_str = '';
			}
			$item = preg_replace("/$startStr".$cmd_str."$ending/i","",$item);

			$this->rt2xManage($item, $cmd_arr);
		}

		private function rt2xManage($item, $cmd_arr){
			
			$item = urlencode($item);
			if(!empty($item)){
				foreach($cmd_arr as $cmd){
					switch($cmd){
						case "r": 
							$this->send2RenRen($item);
							break;
						case "s":
							$this->send2Sina($item);
							break;
						case "j":
							$this->send2Renjian($item);
							break;
						case "f":
							$this->send2Fanfou($item);
							break;

					}
				}
			}

		}
		
		private function postManager($postdata,$hasSource, $user, $pwd, $url){
			if($hasSource){
				$postdata ['source'] = "RT2X";
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->createPostStr($postdata));
			curl_setopt($ch, CURLOPT_USERPWD, "$user:$pwd");
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$ret = curl_exec($ch);
			curl_close($ch);

		}

		private function createPostStr($data) {
			if (! is_array ( $data )) {
				return "content={$data}";
			}
			
			$string = '';
			foreach ( $data as $k => $v ) {
				$string .= "{$k}={$v}&";
			}
			
			return substr ( $string, 0, - 1 );
		}

		private function send2Fanfou($item) {
			$postdata['status'] = $item;
			$this->postManager($postdata, true, $this->accounts[fanfou_email], $this->passwords[fanfou_password], $this->uris[fanfou_uri]);

		}
		private function send2Renjian($item) {
			$postdata['text'] = $item;
			$this->postManager($postdata, true, $this->accounts[renjian_email], $this->passwords[renjian_password], $this->uris[renjian_uri]);
		}

		private function send2Sina($item) {
			$postdata['source']= '702420162';
			$postdata['status']= $item;
			$this->postManager($postdata, false, $this->accounts[sina_email], $this->passwords[sina_password], $this->uris[sina_uri]);
		}

		private function send2RenRen($item){
			$renren_login = "http://3g.renren.com/login.do?fx=0&autoLogin=true";
			$post = 'sour=home&status='.$item.'&update=发布';
			$cookie_file = tempnam($this->cookies_path,$this->cookie_files[renren_cookie]);

			try{
			    $ch = curl_init();
		    	curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);
	   		 	curl_setopt($ch,CURLOPT_URL,$renren_login);
			    curl_setopt($ch,CURLOPT_POST,TRUE);
			    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
			    curl_setopt($ch,CURLOPT_POSTFIELDS,'email='.$this->accounts[renren_email].'&password='.$this->passwords[renren_password].'&login=登录');
			    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
			    $str = curl_exec($ch);
			    curl_close($ch);
			    $pattern = '/action="([^"]*)"/';
			    preg_match($pattern,$str,$matches);
		    } catch(Exception $e) {
		    	echo l($e->getMessage());
		    }
		    
			try{	
			    $ch = curl_init($matches[1]);
			    curl_setopt($ch,CURLOPT_POST,TRUE);
			    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
			    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
			    curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_file);
			    $ret = curl_exec($ch);
			    curl_close($ch);
		    } catch(Exception $e){
		    	echo l($e->getMessage());
		    }
		}
	}
	
	$o = new Extension_Rt2X();
