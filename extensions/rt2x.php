<?php
	/****
	 * rt2x.php
	 * Author: @ConanChou (http://conanblog.me)
	 * Discription: RT2X Extension for Tweet Nest
	 * Licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License
	 * Version 0.2
	 * Release Date 25/10/2010
	****/

	class Extension_Rt2X {
		// Change accordingly
		private $cookie_files = array(
					"renren_cookie" => ""
					); // Full path to cookie file
		private $accounts = array(
					"renren_email" => "",
					"sina_email" => ""
					); // Your renren.com's login Email account
		private $passwords = array(
					"renren_password" => "",
					"sina_password" => ""
					); // Your renren.com's passcode
		// Stop editing
		
		public function rt2x($tweet){
						
			$item = str_replace("RT ","转自》",$tweet['text']);
			
			$start = stripos($item, "#2[")+3;
			$arr = str_split($item);
		
			for ($i=$start;$i<=strlen($item);$i++){
				if($arr[$i]==']'){
					$end = $i;
					break;
				}
			}
			$arr = array_slice($arr,$start,$end-$start);	
			$cmd_arr = array_unique($arr);
			$cmd_str = implode('',$arr);

			$item = preg_replace("/#2\[".$cmd_str."\]/i","",$item);


			foreach($cmd_arr as $cmd){
				$cmd = strtolower($cmd);
				switch($cmd){
					case "r": 
						$this->send2RenRen($item); 
						break;
					case "s":
						$this->send2Sina($item);
						break;
				}
			}
		}


		private function send2Sina($item) {
			$postdata = array('source=702420162','status='.urlencode($item));

			$ch = curl_init();	
			curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $postdata));
			curl_setopt($ch, CURLOPT_USERPWD, $this->accounts[sina_email].':'.$this->passwords[sina_password]);
			$url = 'http://api.t.sina.com.cn/statuses/update.json';
			curl_setopt($ch, CURLOPT_URL, $url);
			
			$ret = curl_exec($ch);
		}

		private function send2RenRen($item){
			$renren_login = "http://3g.renren.com/login.do?fx=0&autoLogin=true";
			$post = 'sour=home&status='.urlencode($item).'&update=发布';
			
			echo l("Prepare to log in...\n");
			try{
			    $ch = curl_init();
		    	curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookie_files[renren_cookie]);
	   		 	curl_setopt($ch,CURLOPT_URL,$renren_login);
			    curl_setopt($ch,CURLOPT_POST,TRUE);
			    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
			    curl_setopt($ch,CURLOPT_POSTFIELDS,'email='.$this->accounts[renren_email].'&password='.$this->passwords[renren_password].'&login=登录');
			    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
			    $str = curl_exec($ch);
			    curl_close($ch);
			    $pattern = '/action="([^"]*)"/';
			    preg_match($pattern,$str,$matches);
			    echo l("Login done.\nPrepare to post...\n");
		    } catch(Exception $e) {
		    	echo l($e->getMessage());
		    }
		    
			try{	
			    $ch = curl_init($matches[1]);
			    curl_setopt($ch,CURLOPT_POST,TRUE);
			    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
			    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
			    curl_setopt($ch,CURLOPT_COOKIEFILE,$this->cookie_files[renren_cookie]);
			    $ret = curl_exec($ch);
			    curl_close($ch);
			    echo l("Post done.\n");
		    } catch(Exception $e){
		    	echo l($e->getMessage());
		    }
		}
	}
	
	$o = new Extension_Rt2X();
