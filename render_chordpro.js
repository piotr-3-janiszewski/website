function symbol_type(symbol) {
	if (symbol.length == 0)
		return 0;
	else if (symbol[0] == '[')
		return 1;
	else 
		return 2;
}

function render_line(line, type = "parallel") {
	let symbols = line.match(/\[[^\[\]]*\]|[^\[\]]+/g);
	console.log(symbols);

	let output = "";
	if (type == "parallel") {
		let last_symbol_type = -1; //-1 ~ no symbols yet, 0 ~ empty symbol, 1 ~ chords, 2 ~ lyrics
		for (let i = 0; i < symbols.length; i++) {
			if (last_symbol_type == 0)
				
		}
	}
	else if (type == "serial") {

	}
	else  {
		alert("Improper type");
	}

	if (output == "")
		output = "error";
	return output;
}

let raw_song_element = document.getElementById("raw_song");

let raw_song_content = raw_song_element.innerHTML;
let song = raw_song_content.split("\n\n");
for (let i = 0; i < song.length; i++) {
	song[i] = song[i].split("\n");
}

const url = new URLSearchParams(window.location.search);
const song_title = url.get('song');

let rendered = "";
rendered += "<div class=\"song\">";
rendered += "<div class=\"song_head\">";
rendered += "<div class=\"song_title\">"
rendered += song_title;
rendered += "</div>";
if (song[0].length == 2) {
	rendered += "<div class=\"song_comment\">";
	rendered += song[0][1];
	rendered += "</div>";
}
rendered += "</div>";
rendered += "<div class=\"song_body\">";
for (let i = 1; i < song.length; i++) {
	rendered += "<div class=\"verse\">";
	for (let ii = 0; ii < song[i].length; ii++) {
		rendered += "<div class=\"line\">";
		rendered += render_line(song[i][ii]);
		rendered += "</div>";
	}
	rendered += "</div>";
}
rendered += "</div>";
rendered += "</div>";

raw_song_element.remove();
document.write(rendered);
