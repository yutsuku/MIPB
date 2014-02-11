<!doctype HTML>
<html>
<head>
<title>Mail server BlackList Check Tool</title>
<style type="text/css">
html,body { background: #f3f3f3; color: #666666; }
a { text-decoration: none; color: #278fdf; }
#table-data { background: #fcfcfc; border: 1px solid #eeeeee; padding: 10px; margin-top: 15px; }
#table-data td { padding: 5px; }
#table-data td.listed_info_label { border-bottom: 1px solid #eeeeee; border-right: 1px dashed #eeeeee; }
#table-data td.listed_info_data { border-bottom: 1px solid #eeeeee; border-left: 1px dashed #eeeeee; }
#table-data td div ul {
	list-style-type: none;
	padding: 0px;
	margin: 0px;
}
input[type="text"] { background: #ffffff; border: 1px solid #eeeeee; outline: none; padding: 7px; color: #777777; }
input[type="button"] {
	background: #f3f3f3;
	background: -moz-linear-gradient(top, #f3f3f3 0%, #d9d9d9 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f3f3f3), color-stop(100%,#d9d9d9));
	background: -webkit-linear-gradient(top, #f3f3f3 0%,#d9d9d9 100%);
	background: -o-linear-gradient(top, #f3f3f3 0%,#d9d9d9 100%);
	background: -ms-linear-gradient(top, #f3f3f3 0%,#d9d9d9 100%);
	background: linear-gradient(to bottom, #f3f3f3 0%,#d9d9d9 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f3f3f3', endColorstr='#d9d9d9',GradientType=0 );
	border: 1px solid #aaaaaa;
	border-radius: 3px;
	padding: 7px 10px;
	color: #777777;
	font-weight: bold;
	box-shadow: 3px 3px 5px rgba(0,0,0,0.1);
}
#statusbar {
	margin-top: 15px;
}
#progress {
	border: 1px solid #aaaaaa;
	padding: 1px;
	background: #ffffff;
	height: 14px;
	width: 500px;
}
#progress #progress-seek {
	width: 100%;
	height: 100%;
	background: #aaaaaa;
}
.row_default {
	background: #f9f9f9;
	border: 1px solid #eeeeee;
	border-bottom-width: 3px;
	color: #272727;
}
.row_brown td {
	border: 1px solid #eeeeee;
	border-bottom-width: 3px;
	border-left-width: 0px;
	border-right-width: 0px;
}
.row_black {
	background: #272727;
	border: 1px solid #525252;
	border-bottom-width: 3px;
	color: #cccccc;
}
.row_brown td {
	border: 1px solid #272727;
	border-bottom-width: 3px;
	border-left-width: 0px;
	border-right-width: 0px;
}
.row_brown {
	background: #3d2f2b;
	border: 1px solid #525252;
	border-bottom-width: 3px;
	color: #cccccc;
}
.row_brown td {
	border: 1px solid #525252;
	border-bottom-width: 3px;
	border-left-width: 0px;
	border-right-width: 0px;
}
.row_yellow {
	background: #e1d89e;
	border: 1px solid #525252;
	border-bottom-width: 3px;
	color: #272727;
}
.row_yellow td {
	border: 1px solid #525252;
	border-bottom-width: 3px;
	border-left-width: 0px;
	border-right-width: 0px;
}
.row_white {
	background: #f9f9f9;
	border: 1px solid #eeeeee;
	border-bottom-width: 3px;
	color: #272727;
}
.row_white td {
	border: 1px solid #eeeeee;
	border-bottom-width: 3px;
	border-left-width: 0px;
	border-right-width: 0px;
}
</style>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script type="text/javascript">
var lookup = {
	"table": "#table-data",
	"statusbar": "#statusbar",
	"statusdata": "#statusbar p",
	"progressbar": "#progress",
	"progressseek": "#progress-seek",
	"url": "proxy.php",
	
	"failed": 0,
	"timeout": 0,
	"data": {},
	"finished": false,
	"processing": 0,
	"precent": 0,
	"total": 0
};
function lookup_reset() {
	lookup = {
		"table": "#table-data",
		"statusbar": "#statusbar",
		"statusdata": "#statusbar p",
		"progressbar": "#progress",
		"progressseek": "#progress-seek",
		"url": "proxy.php",
		
		"failed": 0,
		"timeout": 0,
		"data": {},
		"finished": false,
		"processing": 0,
		"precent": 0,
		"total": 0
	}
	$(lookup.table).empty();
	$(lookup.statusdata).empty();
}
$( document ).ready(function() {
	console.log("IM READY");
	//$(lookup.statusbar).html("Status: Idle...");
	main();
});
function main() {
	$(lookup.progressseek).width(lookup.precent + "%");
	$(lookup.progressbar).hide();
	$("input#query").click(function( event ) {
		console.log("clicked!");
		lookup_reset();
		$(lookup.statusdata).html("Loading...");
		$.ajax({
			url: "ajax.php",
			data: {
				ip: document.getElementById("ip").value,
			},
			type: "GET",
			dataType : "json",
			success: function( json ) {
				$(lookup.progressbar).show();
				lookup.data=json;
				console.log(lookup.data);
				query(lookup.data);
			},
			error: function( xhr, status ) {
				$(lookup.statusdata).html("Sorry, there was a problem!");
			},
			complete: function( xhr, status ) {
				//alert( "The request is complete!" );
				//$(lookup.statusbar).html("Processing...");
			}
		});
	});
}
function query(data) {
	if ( data.isOk != "true" ) {
		//poperror
		return false;
	}
	var i=data.databases.length;
	lookup.total=data.databases.length;
	--lookup.total;
	for(i=--i;i>0;--i) {
		//$("#data").append("DNS: " + data.databases[i].dns_zone + ", id: " + data.databases[i].lid + "<br />");
		var reqdata = 'ash=' + data.ash + '&rid=' + data.databases[i].rid + '&lid=' + data.databases[i].lid + '&q=' + document.getElementById("ip").value;
		$.post(lookup.url, reqdata, lookup_callback, 'json');
	}
}
function lookup_callback(req) {
	lookup.processing++;
	lookup.precent = Math.round((lookup.processing/lookup.total)*100);
	if ( lookup.processing >= lookup.total ) {
		$(lookup.statusdata).html("Finished " + lookup.processing + "/" + lookup.total + ", failed: " + lookup.failed + ", time out: " + lookup.timeout);
		lookup.finished=true;
		$(lookup.progressseek).width(lookup.precent + "%");
	} else {
		$(lookup.statusdata).html("Checking " + lookup.processing + "/" + lookup.total + ", failed: " + lookup.failed + ", time out: " + lookup.timeout);
		console.log("Checking " + lookup.processing + "/" + lookup.total + " ...");
		$(lookup.progressseek).width(lookup.precent + "%");
	}
	//console.log(req);
	var state;
	var id=req.id;
	if ( req.failed ) {
		state="Failed";
		lookup.failed++;
	} else if ( req.data.timeout ) {
		state="Time out";
		lookup.timeout++;
	} else {
		state = (req.data.listed) ? 'Listed' : 'Not listed';
		$(lookup.table).append(get_tr_html(req.rid, req.id, req.qhost, req.url, req.name, req.dns_zone, state));
		switch (req.result_color) {
			case 'black':
				$("#" + req.rid).addClass("row_black");
				console.log("setting \".row_black\" to \"#" + req.rid + "\"");
				break;
			case 'brown':
				$("#" + req.rid).addClass("row_brown");
				console.log("setting \".row_brown\" to \"#" + req.rid + "\"");
				break;
			case 'yellow':
				$("#" + req.rid).addClass("row_yellow");
				console.log("setting \".row_yellow\" to \"#" + req.rid + "\"");
				break;
			case 'white':
				$("#" + req.rid).addClass("row_white");
				console.log("setting \".row_white\" to \"#" + req.rid + "\"");
				break;
			case 'neutral':
				$("#" + req.rid).addClass("row_neutral");
				console.log("setting \".row_neutral\" to \"#" + req.rid + "\"");
				break;
			default:
				$("#" + req.rid).addClass("row_default");
				console.log("setting \".row_default\" to \"#" + req.rid + "\"");
		}
	}
	if ( typeof(req.data) == "object") {
		if ( req.data.listed ) {
			if (req.data.comments_if_listed) {
				for (var comment in req.data.comments_if_listed) {
					$(lookup.table).append(get_itr_html(req.rid + "_cli_" + id, 'Comment:', req.data.comments_if_listed[comment]));
				}
			}
			$(lookup.table).append(get_itr_html(req.rid + "_q_" + id, 'Query:', req.data.host));
			if (req.data.a) {
				for (var aKey in req.data.a) {
					$(lookup.table).append(get_itr_html(req.rid + "_a_" + aKey, 'A Record:', req.data.a[aKey].ip_short));
					$(lookup.table).append(get_itr_html(req.rid + "_ttl_" + aKey, 'TTL:', req.data.a[aKey].ttl));
					if (req.data.a[aKey].db_rc) {
						var str = '<ul>';
						for (var rKey in req.data.a[aKey].db_rc) {
							str += '<li>' + req.data.a[aKey].db_rc[rKey].description + '</li>';
						}
						str += '</ul>';
						$(lookup.table).append(get_itr_html(req.rid + "_db_rci_" + aKey, 'DB_rc:', str));
					}
				}
			}
			if (req.data.txt) {
				var txt_str = '<ul>';
				for (var txtKey in req.data.txt) {
					txt_str += '<li>' + req.data.txt[txtKey] + '</li>';
				}
				txt_str += '</ul>';
				$(lookup.table).append(get_itr_html(req.rid + "_txt_" + id, 'TXT:', txt_str));;
			}
		} else {
			console.log("no object \"req.data.listed\" found... dumping data");
			console.log(req);
		}
	} else {
		// failed: true
		console.log("no object \"req.data\" found... dumping data");
		console.log(req);
	}
}
function get_itr_html(id, label, data) {
	var itr_html_base = "\n" + '<tr id="' + id + '">';
	itr_html_base += "\n" + '  <td>&nbsp;</td>';
	itr_html_base += "\n" + '  <td class="listed_info_label">' + label + '</td>';
	itr_html_base += "\n" + '  <td colspan="3" class="listed_info_data"><div>' + data + '</div></td>';
	itr_html_base += "\n" + '</tr>';
	return itr_html_base;
}
function get_tr_html(id, l_id, host, url, url_text, dns_zone, result) {
	var itr_html_base = "\n" + '<tr id="' + id + '">';
	itr_html_base += "\n" + '  <td class="l_id">' + l_id + '</td>';
	itr_html_base += "\n" + '  <td class="l_qhost">' + host + '</td>';
	itr_html_base += "\n" + '  <td class="url"><a href="' + url + '">' + url_text + '</a></td>';
	itr_html_base += "\n" + '  <td class="dns_zone">' + dns_zone + '</td>';
	itr_html_base += "\n" + '  <td class="result">' + result + '</td>';
	itr_html_base += "\n" + '</tr>';
	return itr_html_base;
}
</script>
</head>
<body>
<form>
	<input type="text" name="ip" id="ip" value="<?php echo $_SERVER["REMOTE_ADDR"]; ?>">
	<input type="button" id="query" value="Check">
</form>
<div id="data">
	<div id="statusbar">
		<div id="progress">
			<div id="progress-seek"></div>
		</div>
		<p>Idle</p>
	</div>
	<table id="table-data" cellspacing="0" cellpadding="0"></table>
</div>
</body>
</html>