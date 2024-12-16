<!DOCTYPE html>
<html lang="en">

<head>
	<title>Songs</title>
	<link rel="stylesheet" href="style.css">
	<meta charset="UTF-8">
</head>
<?php
	function parse_chordpro_line($line) {
		$line = rtrim($line);
		$array_of_chords = [];
		$array_of_lyrics = [];

		#$tokens = preg_split('/(?<=\s)?(\[[^\[\]]*\])/i', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		preg_match_all('/(\[[^\[\]]*\])|([^\[\]]+)/', $line, $tokens);
		$tokens = $tokens[0];
		#print_r($tokens);
		return $tokens;

		/*$resultant_array = [];
		$i = 0;
		if (!empty($tokens)) {
			array_push($resultant_array, array_shift($tokens));
			$i++;
		}

		foreach ($tokens as $token) {
			if ($token[0] != '[')
				array_push($resultant_array, $token);
			else {
				if (end($resultant_array)[0] == "[")
					array_push($resultant_array, array_pop($resultant_array) . $token);
				else
					array_push($resultant_array, $token);
			}
				
		}

		print_r($resultant_array);*/
	}

	function is_lyrics($token) {
		if ($token[0] == '[')
			return false;
		
		return true;
	}

	function print_chordpro_line($line) {
		$tokens = parse_chordpro_line($line);
		if (count($tokens) == 0) {
			echo "<br class=\"songs_text\">\n";
			return;
		}

		$chord_tokens = [];
		$lyrics_tokens = [];

		foreach ($tokens as $token) {
			if (is_lyrics($token)) {
				if(count($chord_tokens) <= count($lyrics_tokens)) 
					array_push($chord_tokens, "");

				array_push($lyrics_tokens, $token);
			}
			else {
				if(count($chord_tokens) > count($lyrics_tokens)) {
					array_push($lyrics_tokens, "");
					$chord_tokens[count($chord_tokens) - 1] .= " ";
				}

				array_push($chord_tokens, mb_substr($token, 1, strlen($token) - 2));
			}
		}

		#print_r($chord_tokens);
		#print_r($lyrics_tokens);

		while (count($chord_tokens) < count($lyrics_tokens))
			array_push($chord_tokens, "");
		while (count($lyrics_tokens) < count($chord_tokens)) {
			array_push($lyrics_tokens, "");
				
			if (count($chord_tokens) >= 2)
				$chord_tokens[count($chord_tokens) - 2] .= " ";
		}

		$result = "<table class=\"songs_text\">\n";
		$result .= "\t<tr class=\"chords\">\n";
		foreach ($chord_tokens as $token) {
			$result .= "\t\t<td>$token</td>\n";
		}
		$result .= "\t</tr>\n";
		$result .= "\t<tr class=\"lyrics\">\n";
		foreach ($lyrics_tokens as $token) {
			$result .= "\t\t<td>$token</td>\n";
		}
		$result .= "\t</tr>\n";
		$result .= "</table>\n";

		echo "$result";
		return $result;
	}

	function parse_chord($file_name, &$chord_output) {
		$data = file_get_contents("./songs/chords/".$file_name);
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $data, $values, $indicies);
		xml_parser_free($parser);
		echo "<br>";
		$name="no name";
		$symbol="no symbol";
		$singles=[];
		$barres=[];
		$mutes=[];
		foreach ($values as $value) {
			#echo "$value[tag] $value[tag][value] <br>";
			if ($value["tag"] == "name")
				$name=$value["value"];
			else if ($value["tag"] == "symbol")
				$symbol=$value["value"];
			else if ($value["tag"] == "single")
				array_push($singles, $value["attributes"]);
			else if ($value["tag"] == "barre") 
				array_push($barres, $value["attributes"]);
			else if ($value["tag"] == "mute")
				array_push($mutes, $value["attributes"]);
		}

		echo "$name ~ $symbol<br>";
		#print_r($barres);
		#echo "<br>";
		#print_r($singles);
		#echo "<br>";
		#print_r($mutes);

		$chord_output=["name"=>$name, "symbol"=>$symbol, "singles"=>$singles, "barres"=>$barres, "mutes"=>$mutes];
	}

	function guitar_string_to_number($string) {
		if ($string == 'E') return 6;
		else if ($string == 'A') return 5;
		else if ($string == 'D') return 4;
		else if ($string == 'G') return 3;
		else if ($string == 'H') return 2;
		else if ($string == 'e') return 1;
		return 0;
	}

	function print_chord($file_name, $width=150, $height=150) {
		$chord=[];
		parse_chord($file_name, $chord);
		
		$min_fret = 100;
		$max_fret = 0;
		foreach(array_merge($chord["singles"], $chord["barres"]) as $fingering) {
			if ($fingering["fret"] < $min_fret)
				$min_fret = $fingering["fret"];
			if ($fingering["fret"] > $max_fret)
				$max_fret = $fingering["fret"];
		}

		if ($max_fret <= 5) $min_fret = 0;
		if ($min_fret > 0) $min_fret -= 1;

		$inter_string_distance = $height / 7; // Seven because there must be some space for denoting the base fret number
		$number_of_frets = $max_fret - $min_fret;
		$inter_fret_distance = $width / $number_of_frets;
		echo "<svg width=\"$width\" height=\"$height\">";
		echo "
		<line 
			x1=\"1\" 
			y1=\"".($inter_string_distance / 2)."\" 
			x2=\"1\" 
			y2=\"".($height - 3 * $inter_string_distance / 2)."\" 
			stroke=\"black\" stroke-width=\"2\"2/>"; //Drawing the first fret
		if ($min_fret == 0) //Drawing the nut if the chord is open (or close enough to the nut)
			echo "<line 
				x1=\"4\" 
				y1=\"".($inter_string_distance / 2)."\" 
				x2=\"4\" 
				y2=\"".($height - 3 * $inter_string_distance / 2)."\" 
				stroke=\"black\" stroke-width=\"2\"2/>";
		else //If the nut isn't being drawn, print the number of the base fret
			echo "<text 
				x=\"".($inter_fret_distance / 2)."\" 
				y=\"".($height - $inter_string_distance * 2 / 3)."\" 
				font-size=\"".($inter_string_distance * 2 / 3)."\" 
				font-weight=\"bold\"
				text-anchor=\"middle\">
				".($min_fret + 1)."</text>";
		for ($i = 0; $i < 6; $i++) //Draw the strings
			echo "<line 
				x1=\"0\" 
				y1 =\"".($inter_string_distance / 2 + $i * $inter_string_distance)."\" 
				x2=\"".($width)."\" 
				y2 =\"".($inter_string_distance / 2 + $i * $inter_string_distance)."\" 
				stroke=\"black\" stroke-width=\"1\"/>";
		for ($i = 1; $i <= $number_of_frets; $i++) //Drawing frets
			echo "<line 
				x1=\"".($i * $inter_fret_distance)."\" 
				y1=\"".($inter_string_distance / 2)."\" 
				x2=\"".($i * $inter_fret_distance)."\" 
				y2=\"".($height - 3/2 * $inter_string_distance)."\" 
				stroke=\"black\" stroke-width\"1\"/>";

		$r = $inter_string_distance * 3 / 8;
		$circle_stroke_width = 1.5;
		foreach ($chord["singles"] as $single) {
			$x=($single["fret"] - 1 - $min_fret) * $inter_fret_distance + $inter_fret_distance / 2;
			$y=(guitar_string_to_number($single["string"]) - 1) * $inter_string_distance + $inter_string_distance / 2;
			echo "
			<circle 
				cx=\"$x\"
				cy=\"$y\"
				r=\"$r\"
				fill=\"white\"
				stroke=\"black\"
				stroke-width=\"$circle_stroke_width\"/>
			<text
				x=\"$x\"
				y=\"$y\"
				text-anchor=\"middle\"
				dominant-baseline=\"middle\"
				font-size=\"".($inter_string_distance /2)."\"
				font-weight=\"bold\"
				>$single[finger]</text>";
			}
		foreach ($chord["barres"] as $barre) {
			$x=($barre["fret"] - 1 - $min_fret) * $inter_fret_distance + $inter_fret_distance / 2;
			$y1=(guitar_string_to_number($barre["string_high"]) - 1) * $inter_string_distance + $inter_string_distance / 2;
			$y2=(guitar_string_to_number($barre["string_low"]) - 1) * $inter_string_distance + $inter_string_distance / 2;
			echo "
			<circle 
				cx=\"$x\"
				cy=\"$y1\"
				r=\"$r\"
				fill=\"white\"
				stroke=\"black\"
				stroke-width=\"$circle_stroke_width\"/>
			<text
				x=\"$x\"
				y=\"$y1\"
				text-anchor=\"middle\"
				dominant-baseline=\"middle\"
				font-size=\"".($inter_string_distance /2)."\"
				font-weight=\"bold\"
				>$single[finger]</text>
			<circle 
				cx=\"$x\"
				cy=\"$y2\"
				r=\"$r\"
				fill=\"white\"
				stroke=\"black\"
				stroke-width=\"$circle_stroke_width\"/>
			<text
				x=\"$x\"
				y=\"$y2\"
				text-anchor=\"middle\"
				dominant-baseline=\"middle\"
				font-size=\"".($inter_string_distance /2)."\"
				font-weight=\"bold\"
				>$single[finger]</text>
			<line
				x1=\"".($x - 1/3 * $r)."\"
				y1=\"".($y1 + $r)."\"
				x2=\"".($x - 1/3 * $r)."\"
				y2=\"".($y2 - $r)."\"
				stroke=\"black\"
				stroke-width=\"1.7\"
			/>
			<line
				x1=\"".($x + 1/3 * $r)."\"
				y1=\"".($y1 + $r)."\"
				x2=\"".($x + 1/3 * $r)."\"
				y2=\"".($y2 - $r)."\"
				stroke=\"black\"
				stroke-width=\"1.7\"
			/>";
			}
		foreach($chord["mutes"] as $mute) {
			$x = $inter_fret_distance / 5;
			$y = (guitar_string_to_number($mute["string"]) - 1) * $inter_string_distance + $inter_string_distance / 2;
			$a = $x / 2;
			$stroke_width = 1.5;
			echo "
			<line
				x1=\"".($x - $a)."\"
				y1=\"".($y - $a)."\"
				x2=\"".($x + $a)."\"
				y2=\"".($y + $a)."\"
				stroke=\"black\"
				stroke-width=\"$stroke_width\"
				/>
			<line
				x1=\"".($x + $a)."\"
				y1=\"".($y - $a)."\"
				x2=\"".($x - $a)."\"
				y2=\"".($y + $a)."\"
				stroke=\"black\"
				stroke-width=\"$stroke_width\"
				/>
			";
		}
		echo "</svg>";
	}
?>

<body>
	<?php
		$songs=array_diff(scandir('./songs'), ['.', '..', 'recordings', 'chords']);
		$desired_song = $_GET['song'];
		if (in_array($desired_song, $songs)) {
			echo "<h1>$desired_song</h1>\n";
			$song=file("./songs/$desired_song");
			echo "<h2>$song[0]</h2>\n";
			if (trim($song[1]) != "")
				echo "<em>$song[1]</em><br>\n";
			array_shift($song);
			array_shift($song);
			foreach ($song as $line) {
				print_chordpro_line($line);
			}

			$recordings = preg_grep("/$desired_song(.*)\..[^\.]*/", scandir("./songs/recordings"));
			echo "<br>";
			foreach ($recordings as $recording) {
				preg_match("/$desired_song(.*)\.[^\.]+/", $recording, $date);
				echo "<em>$date[1]:</em>
				<br>";
				echo "<audio controls>
					<source src=\"./songs/recordings/$recording\" type=\"audio/mpeg\">
				</audio>
				<br>";
			}
		}
		else {
			foreach ($songs as $song)
				echo "<a href=\"songs.php?song=$song\">$song</a><br>\n";

			print_chord("E.xml");
			print_chord("F.xml");
			print_chord("d̅m.xml");
		}
	?>
</body>
	
</html>
