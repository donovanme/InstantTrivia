<?php

	// Load Google Libraries

	require 'c:/php/vendor/autoload.php';
	use Google\Cloud\TextToSpeech\V1\AudioConfig;
	use Google\Cloud\TextToSpeech\V1\AudioEncoding;
	use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
	use Google\Cloud\TextToSpeech\V1\SynthesisInput;
	use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
	use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
	
	
	// CONSTANTS
	$total_rounds = 5;
	$generate_intro_outro = true;
	
	// Path for Intro Music: https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3


	function update_episode_number ($episode_number) {
		$myfile = fopen("current_episode.txt", "w") or die("Unable to open file!");
		fwrite($myfile, $episode_number);
		fclose($myfile);		
	}

	function get_text_file_name ($episode_number) {
		return 'episodes\\' . $episode_number . '_instant_trivia.txt';
	}
	function get_html_file_name ($episode_number) {
		return 'episodes\\' . $episode_number . "_instant_trivia.html";
	}			
	
	function write_to_text ($text) {
		global $episode_number;
		//$text = strip_tags($text);
		$myfile = fopen (get_text_file_name($episode_number), 'a') or die ('Unable to open transcript file!');
		fwrite($myfile, $text . PHP_EOL);
		fclose($myfile);
	}
	function write_to_html ($text) {
		global $episode_number;
		$myfile = fopen (get_html_file_name($episode_number), 'a') or die ('Unable to open transcript file!');
		fwrite($myfile, $text . PHP_EOL);
		fclose($myfile);
	}
	
	function write_episode_name_to_file ($episode_name) {
		global $episode_number;
		$myfile = fopen ('episodes\\' . $episode_number . '_episode_name.txt', 'a') or die ('Unable to open transcript file!');
		fwrite($myfile, $episode_name . PHP_EOL);
		fclose($myfile);
	}
	
	
    function get_next_episode_number () {
		$myfile = fopen("current_episode.txt", "r") or die("Unable to open file!");
		$episode_number = fread($myfile,filesize("current_episode.txt"));
		fclose($myfile);
		return $episode_number + 1;
	}

    function get_file_name ($episode_number) {
		return 'episodes\\' . $episode_number . '_instant_trivia.mp3';
	}		
	
	
	function generate_introduction () {
		global $episode_number;
		global $generate_intro_outro;
		if ($generate_intro_outro) {
	        echo PHP_EOL;
			echo 'Generating Intro - ';
			write_to_html ('<p>Welcome to the Instant Trivia podcast episode ' . $episode_number . ', where we ask the best trivia on the Internet.</p>');
			write_to_mp3 ('<speak><par>
			<media fadeInDur="2s" fadeOutDur="5s" end="8s">
			  <audio
				src="https://www.dropbox.com/s/n5ndm3tfu5n3gtt/podcast-intro.mp3?dl=1"/>
			</media>
			<media xml:id="question" begin="5s">
			  <speak><s>Welcome to the Instant Trivia podcast</s><s>episode' . $episode_number . '</s>, where we ask the ' .add_emphasis('best') . ' trivia on the Internet.</speak>
			</media>
			</par></speak>');

			write_to_text ('Welcome to the Instant Trivia podcast episode ' . $episode_number . ', where we ask the best trivia on the Internet.');
		}
	}
	function generate_outro (){
		global $generate_intro_outro;
		if ($generate_intro_outro) {
			echo PHP_EOL;
			echo 'Generating Outro - ';			
			write_to_mp3 ('<speak><par>
			<media fadeInDur="2s" fadeOutDur="8s" end="10s">
			  <audio
				src="https://www.dropbox.com/s/b56nw0932fd6hrg/podcast-outro.mp3?dl=1"/>
			</media>
			<media xml:id="question" begin="5s">
			  <speak><s>Thanks for listening! Come back tomorrow for more exciting trivia!</s></speak>
			</media>
			</par></speak>');

			write_to_text ('Thanks for listening!  Come back tomorrow for more exciting trivia!');
			write_to_html ('<p>Thanks for listening!  Come back tomorrow for more exciting trivia!</p>');
			write_to_html ('<p>Special thanks to https://blog.feedspot.com/trivia_podcasts/ </p>');
		}
	}
	

	function print_voice_name ($voice) {
		if ($voice == 'en-US-Wavenet-A') {  // M 3/ 10
			$name = 'Aaron';			
		} elseif ($voice == 'en-US-Wavenet-B') { // MALE 7/10 -- Not bad
			$name = 'Ben';   		      
		} elseif ($voice == 'en-US-Wavenet-C') { // FEMALE 7 /10 -- Not bad
			$name = 'Chloe';
		} elseif ($voice == 'en-US-Wavenet-D') { // M 7 / 10
			$name = 'Dominic';			
		} elseif ($voice == 'en-US-Wavenet-E') { // F 6 / 10  -- Not bad either
			$name = 'Emma';
		} elseif ($voice == 'en-US-Wavenet-F') { // F 10 / 10
			$name = 'Felicity';		
		} elseif ($voice == 'en-US-Wavenet-G') { // F 2 / 10 -- Might skip her
			$name = 'Gabby';
		} elseif ($voice == 'en-US-Wavenet-H') { // F 8 / 10
			$name = 'Harriet';
		} elseif ($voice == 'en-GB-Wavenet-B') { // British Male
			$name = 'Barnaby';
		} elseif ($voice == 'en-GB-Wavenet-F') { // British Female
			$name = 'Fern';
		}
		return $name;
	}
    
	
	// ***** GENERATE THE MP3 FILE ***** //
	function write_to_mp3 ($text) {
		global $output_mode;
		global $file_name;
		
		// create client object
		$client = new TextToSpeechClient();

		
		//$voice = (new VoiceSelectionParams())
		//->setName('en-GB-Standard-B')
		//->setLanguageCode('en-US');
		//		->setName('en-US-Wavenet-' . $voice_letter );
		
		$best_voices = array("en-US-Wavenet-B", "en-US-Wavenet-C", "en-US-Wavenet-D", "en-US-Wavenet-E", "en-US-Wavenet-F", "en-US-Wavenet-H", "en-GB-Wavenet-B", "en-GB-Wavenet-F");
		$voice = $best_voices[array_rand($best_voices)]; 
		
		echo 'Reading in voice: ' . print_voice_name($voice) . PHP_EOL;
		
		if (str_contains($voice, "en-GB")) {
			$language_code = 'en-GB';
		} else {
			$language_code = 'en-US';
		}
		
		$voice = (new VoiceSelectionParams())
		->setLanguageCode($language_code)
		->setName($voice);

		$input_text = (new SynthesisInput())
		->setSsml($text);		

		$audioConfig = (new AudioConfig())
		->setAudioEncoding(AudioEncoding::MP3);

		$response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);
		$audioContent = $response->getAudioContent();
	
		file_put_contents($file_name, $audioContent, FILE_APPEND);
		$client->close(); 
	
	}
	
	// ***** Get a JSON question set from Internet ***** //
	function get_question_set ($categoryid) {
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, "https://jservice.io/api/clues?category=$categoryid");
		$result = curl_exec($ch);
		curl_close($ch);

		$questions = json_decode($result);
		return $questions;
	}
	function check_question ($question) {
		// Check if the question and answer are not empty
		$result = true; // Assume it is okay unless it is not
		
		if (empty($question->question)) {
			$result = false;
		} elseif (empty($question->answer)) {
			$result = false;
		} elseif (empty($question->category))		{
			$result = false;
		} elseif (str_contains($question->question, 'Clue Crew')) {
			$result = false;
	    } elseif (stripos($question->question, 'seen here') > 0) {
			$result = false;
	    } elseif (stripos($question->question, 'heard here') > 0) {
			$result = false;					
		}		
		
		return $result;
	}
	
	function format ($text) {
		// Formats that need to be done across all outputs
	    $text = preg_replace('/&/m', ' and ', $text);
		$text = str_ireplace('  ', ' ', $text);
        $text = preg_replace('/_{3,}/m', ' blank ', $text);	
		$text = str_replace("\'", "'", $text);


		return $text;
	}
	
	

	function format_ssml ($text) {
		$text = format ($text);	
		$text = str_ireplace('this', '<prosody rate="72%" volume="medium">this</prosody><break time=".05s"/>', $text); 
		$text = str_ireplace('these', '<prosody rate="72%" volume="medium">these</prosody><break time=".05s"/>', $text); 
		
		$text = str_ireplace('<i>', '<emphasis level="moderate">', $text);
		$text = str_ireplace('</i>', '</emphasis>', $text);	
		$text = str_ireplace('M*A*S*H', 'mash', $text);
		return $text;
	}
	
	
	function format_category_ssml ($text) {
		$text = format ($text);
		// If category has something in quotes ("GRAND" slam) then say "with grand in quotation marks".
		$text = mb_convert_case($text, MB_CASE_TITLE, "UTF-8"); // Puts first letter uppercase
		$text = str_replace('0S', '0s', $text); // Make lower case after dates eg. 1950S '50S to '50s for google to pronounce properly
		$regex = '/"(.*)"/m';		
		if(preg_match($regex, $text, $quote)) 
		{
			$text = str_replace('"', '', $text);	// Remove the quotes
			$seed = rand (1, 10);
			if ($seed > 5) {
				$text = $text . '. With ' . $quote[0] . ' in quotation marks';
			} else {
				$text = $text . '. With ' . $quote[0] . ' in quotes';			
			}
		}

		return $text;
	}
	function format_category_text ($text) {
		$text = format ($text);
		$text = mb_convert_case($text, MB_CASE_TITLE, "UTF-8"); // Puts first letter uppercase
		$text = str_replace('0S', '0s', $text); // Make lower case after dates eg. 1950S '50S to '50s for google to pronounce properly
		return $text;
	}
	
	function add_break ($seconds) {
			return '<break time="' . $seconds . 's"/>';
	}
	
	function add_emphasis($text) {
			return '<emphasis level="moderate">' . $text . '</emphasis>';
	}
	
	function vary_category_text () {
		$seed = rand (1, 10);
		if ($seed > 8) {
			$text = 'The Category?';
		} elseif ($seed > 6) {
			$text = 'The ' . add_emphasis('Category?' . '.');
		} elseif ($seed > 4) {
			$text = 'The Category ' . add_emphasis('is') . '.';
		} elseif ($seed > 3) {
			$text = 'Category?';			
		} else { 
			$text = 'The Category is.';
		}
		return $text;
	} 
	
	function vary_answer_text () {
		$seed = rand (1, 10);
		if ($seed > 8) {
			$text = 'The Answer?';
		} elseif ($seed > 6) {
			$text = 'The ' . add_emphasis('answer?');
		} elseif ($seed > 4) {
			$text = 'The ' . add_emphasis('answer') . 'is';
		} elseif ($seed > 2) {
			$text = 'Answer?';		
		} elseif ($seed > 1) {
			$text = 'Answer';					
		} else { 
			$text = 'The answer is.';
		}
		return $text;
	} 
	
	function vary_round_text ($round_number) {
		global $total_rounds;
		$seed = rand (1, 10);
		if ($seed > 8) {
			$text = '<say-as interpret-as="ordinal">' . $round_number . '</say-as> round';
	    } elseif ($seed > 7 and $round_number == $total_rounds) {
			$text = 'And now the last round';
	    } elseif ($seed > 5 and $round_number == $total_rounds) {
			$text = 'Last round';
	    } elseif ($seed > 4 and $round_number == $total_rounds) {
			$text = 'Final round';
		} else { 
			$text = 'Round ' . $round_number;			
		}
		return $text;
	} 

	function vary_question_text ($question_number) {
		$seed = rand (1, 10);
		if ($seed > 8) {
			$text = '<say-as interpret-as="ordinal">' . $question_number . '</say-as> question';
		} else { 
			$text = 'question ' . $question_number;			
		}
		return $text;
	} 
	
	function question_sort ($q1, $q2) {
		return ($q1->value > $q2->value) ? +1 : -1;
	}
	
	function filter_unique($array, $keep_key_assoc = false){
		$duplicate_keys = array();
		$tmp = array();       
		
		foreach ($array as $key => $val){
			// convert objects to arrays, in_array() does not support objects
			if (is_object($val))
				$val = (array)$val;

			if (!in_array($val, $tmp))
				$tmp[] = $val;
			else
				$duplicate_keys[] = $key;
		}

		foreach ($duplicate_keys as $key)
			unset($array[$key]);

		return $keep_key_assoc ? $array : array_values($array);
	}
	
	
	
	
	// ***** Generate a Category and questions ***** //	
	function generate_trivia_round ($round_number) {
		global $episode_name;
			
		$ssml = '<speak>'. PHP_EOL;
		$html = '<p>' . PHP_EOL;
		
		$question_count = 1;
		
		do {
			// Choose a random category
			$categoryid = rand (1, 11500);
			$questions = get_question_set ($categoryid);
			$questions = filter_unique($questions);
			shuffle($questions);
			$questions = array_filter($questions, "check_question");
		} while (count($questions) < 5);
		
		$questions = array_slice($questions, 0, 5);		
		usort($questions, "question_sort");
		
		$category_text = format_category_text($questions[0]->category->title);
		$category_ssml = format_category_ssml($questions[0]->category->title);

		echo '-' . PHP_EOL;;
		echo 'creating questions for round: ' . $round_number . ' (categoryid = ' . $categoryid . ') '  . PHP_EOL;
		echo 'category text: ' . $category_text . PHP_EOL;
	    echo 'category ssml: ' . $category_ssml . PHP_EOL;
		$ssml = $ssml ."\t" . '<s>' . vary_round_text($round_number) . '.</s><s> ' . vary_category_text() . ' </s>' . add_break(.1) . '<s>' . $category_ssml. '.</s>' . add_break(3) . PHP_EOL . PHP_EOL;
		$html = $html . 'Round ' . $round_number . '. Category: ' . $category_text .  PHP_EOL .'<ul>' . PHP_EOL;
		$episode_name = $episode_name . ' - ' . $category_text;



		foreach ($questions as $q) {
			if (!empty($q)) {
				// Display the question
				echo 'Value: ' . $q->value . '   --  ' . 'Air date: ' . substr($q->airdate, 0, 10) . PHP_EOL;
				
				$ssml = $ssml . "\t" . vary_question_text($question_count) . '.' . add_break(2) . format_ssml($q->question) . '.' . add_break(3) . PHP_EOL;
				$html = $html  . "\t" . '<li>' .$question_count . ': ' . format($q->question) . '.</li>' . PHP_EOL;
				// Display the answer
				$ssml = $ssml . "\t" . vary_answer_text() . ' ' . format_ssml($q->answer ). '. ' . add_break(3) . PHP_EOL . PHP_EOL;
				$html = $html . "\t" . '<li>' . format($q->answer) . '.</li>' . PHP_EOL . PHP_EOL;
				$question_count++;
			}
			if ($question_count === 6) {
				break;
			}
		}

		$ssml = $ssml . '</speak>' . PHP_EOL;
		$html = $html . '</ul>'. PHP_EOL . '</p>' . PHP_EOL;
		
	    return array($ssml, $html);
	}

	// ***** Main loop ***** //
	$episode_number = get_next_episode_number();
    $file_name = get_file_name($episode_number);
	$episode_name = 'Episode ' . $episode_number;
	
	echo 'Generating Episode ' . $episode_number . PHP_EOL;

	generate_introduction();	

	for ($round_number = 1 ;$round_number < ($total_rounds + 1); $round_number++){
		list($round_ssml, $round_html) = generate_trivia_round($round_number);		
		write_to_mp3 ($round_ssml);
		write_to_html ($round_html);
		write_to_text ($round_ssml);
	}

    generate_outro();			
	update_episode_number($episode_number);
	
	write_episode_name_to_file($episode_name);
	echo $episode_name . ' ... ';
	
?>