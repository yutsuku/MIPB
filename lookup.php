<?php
/**
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version 1.0
* @author moh <moh@yutsuku.net>
*/
class MailBlackList {
	public $sessionURI="http://multirbl.valli.org/lookup/%s.html";
	
	public $ip=false;
	public $token=false; // ash
	public $tokenData=false;
	public $databases=array();
	public $dns_ip=array();
	public $preparedQuery=array();
	public $html=false;
	
	/**
	* @access public
	* @param [string $ip]
	* @return true
	*/
	function __construct($ip=false) {
		if ( $ip ) { $this->ip=$ip; } else { $this->ip=$_SERVER["REMOTE_ADDR"]; }
		return true;
	}
	/**
	* @access public
	* @return string|false
	*/
	function getSessionToken() {
		$result = file_get_contents(sprintf($this->sessionURI, $this->ip)); // todo - validate ip address
		if ( !$result ) return false;
		$this->html = $result;
		preg_match("/\"asessionHash\":\s*\"([a-zA-Z0-9]{40})\",/", $result, $output); // sha1
		if ( !isset($output[1]) ) { 
			return false;
		} else {
			$this->tokenData=$result;
			$this->token=$output[1];
			return $this->token;
		}
	}
	/**
	* @access public
	* @return array|false
	*/
	function getDatabases() {
		if ( $this->tokenData == false ) return false;
		$doc = new DOMDocument();
		$doc->loadHTML($this->html);
		$dnsbl_data = $doc->getElementById('dnsbl_data');
		$rows = $doc->getElementsByTagName('tr');
		$accept = array(
			'DNSBLBlacklistTest',
			'DNSBLCombinedlistTest',
			'DNSBLWhitelistTest',
			'DNSBLInformationallistTest'
		);
		$databases=array();
		foreach ( $rows as $row ) {
			// reset values
			$R_ID=false;
			$L_ID=false;
			$link_href=false;
			$link_text=false;
			$DNZ_Zone=false;
			
			$name = $row->nodeName;
			$id    = $row->getAttribute("id");
			$value = $row->nodeValue;
			$id = explode("_", $id);
			if ( count($id) == 2 && in_array($id[0], $accept) ) {
				$R_ID=sprintf("%s_%d", $id[0], $id[1]);
				//echo "Attribute '$id[0]#$name' <br />";
				$row_cells = $row->getElementsByTagName("td");
				foreach ( $row_cells as $cell ) {
						$td_name = $cell->nodeName;
						$td_class    = $cell->getAttribute('class');
						$td_value = $cell->nodeValue;
						if ( $td_class == "l_id" && !empty($td_value) ) {
							$L_ID = $td_value;
						} elseif ( $td_class == "dns_zone" ) {
							$DNS_Zone = $td_value;
						} elseif ( $td_class == "" ) {
							$link = $cell->getElementsByTagName("a")->item(0);
							if ( $link ) {
								$link_name = $link->nodeName;
								$link_href  = $link->getAttribute('href');
								$link_text = $link->nodeValue;
								//echo "Attribute '$link_name.$link_text' :: '$link_href'<br />";
							}
						}
						//echo "Attribute '$td_name.$td_class' :: '$td_value'<br />";
				}
				if ( $R_ID && $L_ID && $link_href && $link_text && $DNS_Zone ) {
					//echo 'Got all importand data, continue...';
					array_push($databases, 
						array(
							"rid"		=> $R_ID,
							"lid"		=> $L_ID,
							"dns_zone"	=> $DNS_Zone,
							"url_text"	=> $link_text,
							"url_href"	=> $link_href
						)
					);
				}
				//echo "<br />";
			}
		}
		if ( !empty($databases) ) {
			$this->databases = $databases;
			return $this->databases;
		} else {
			return false;
		}
	} // => END getDatabases();
	/**
	* @access public
	* @return array|false
	*/
	function getDNSorIP() {
		$doc = new DOMDocument();
		$doc->loadHTML($this->html);
		$doc_main = $doc->getElementById("lo-main");
		$tables = $doc_main->getElementsByTagName("table")->item(1);
		if ( !$tables ) return false;
		$cells = $tables->getElementsByTagName("td");
		if ( $cells->length != 8 ) return false;
		$data=array();
		array_push($data, array(
			"rdns"=>$cells->item(1)->nodeValue, 
			"rdns_check"=>$cells->item(2)->nodeValue, 
			"ip"=>$cells->item(4)->nodeValue,
			"ip_check"=>$cells->item(5)->nodeValue,
			"summary"=>$cells->item(6)->nodeValue,
			"summary_check"=>$cells->item(7)->nodeValue
			)
		);
		if ( !empty($data) ) {
			$this->dns_ip=$data;
			return $this->dns_ip;
		} else {
			return false;
		}
	}
	/**
	* @access public
	* @return array|false
	*/
	function prepareQuery() {
		if ( empty($this->databases) ) return false;
		$format = "?rid=%s&lid=%s";
		for($i=count($this->databases)-1;$i>0;--$i) {
			//echo $this->databases[$i]['dns_zone'] . '<br />';
			array_push($this->preparedQuery, sprintf($format, $this->databases[$i]['rid'], $this->databases[$i]['lid']));
		}
		return $this->preparedQuery;
	}
	/**
	* @access public
	* @return string
	*/
	function RespondToAjaxRequest() {
		if ( $this->token != false && !empty($this->databases) ) {
			$status="true";
		} else {
			$status="false";
		}
		$data=array(
			"isOk"=>$status,
			"ash"=>$this->token,
			"dns_ip"=>$this->dns_ip,
			"databases"=>$this->databases,
			//"preparedQuery"=>$this->preparedQuery
		);
		return json_encode($data);
	}
}
?>