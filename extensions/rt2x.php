<?php
	/****
	 * rt2x.php
	 * Author: @ConanChou (http://conanblog.me)
	 * Discription: RT2X Extension for Tweet Nest
	 * Licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License
	 * Version 0.1
	 * Release Date 25/10/2010
	****/

	class Extension_Rt2X {
		public function rt2x($tweet){
			// Change accordingly
			$cookie_file = ''; // Full path to cookie file
			$renren_email = ''; // Your renren.com's login Email account
			$renren_password = ''; // Your renren.com's passcode
			// Stop editing
			
			$renren_login = "http://3g.renren.com/login.do?fx=0&autoLogin=true";
			$item = str_replace("RT ","转自》",$tweet['text']);
			$item = preg_replace("/#2r/i","",$item);
			$post = 'sour=home&status='.urlencode($item).'&update=发布';
			
			echo l("Prepare to log in...\n");
			try{
			    $ch = curl_init();
		    	curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);
	   		 	curl_setopt($ch,CURLOPT_URL,$renren_login);
			    curl_setopt($ch,CURLOPT_POST,TRUE);
			    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
			    curl_setopt($ch,CURLOPT_POSTFIELDS,'email='.$renren_email.'&password='.$renren_password.'&login=登录');
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
			    curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_file);
			    $ret = curl_exec($ch);
			    curl_close($ch);
			    echo l("Post done.\n");
		    } catch(Exception $e){
		    	echo l($e->getMessage());
		    }
		}
	}
	
	$o = new Extension_Rt2RenRen();
