<?php
require_once('wfAPI.php');
require_once('wfArray.php');
class wordfenceURLHoover {
	private $debug = false;
	public $errorMsg = false;
	private $hostsToAdd = false;
	private $table = '';
	private $apiKey = false;
	private $wordpressVersion = false;
	private $useDB = true;
	private $hostKeys = array();
	private $hostList = array();
	public $currentHooverID = false;
	private $_foundSome = false;
	private $dRegex = 'AAA|AARP|ABB|ABBOTT|ABBVIE|ABOGADO|ABUDHABI|AC|ACADEMY|ACCENTURE|ACCOUNTANT|ACCOUNTANTS|ACO|ACTIVE|ACTOR|AD|ADAC|ADS|ADULT|AE|AEG|AERO|AETNA|AF|AFL|AG|AGAKHAN|AGENCY|AI|AIG|AIRFORCE|AIRTEL|AKDN|AL|ALIBABA|ALIPAY|ALLFINANZ|ALLY|ALSACE|AM|AMICA|AMSTERDAM|ANALYTICS|ANDROID|ANQUAN|AO|APARTMENTS|APP|APPLE|AQ|AQUARELLE|AR|ARAMCO|ARCHI|ARMY|ARPA|ARTE|AS|ASIA|ASSOCIATES|AT|ATTORNEY|AU|AUCTION|AUDI|AUDIO|AUTHOR|AUTO|AUTOS|AVIANCA|AW|AWS|AX|AXA|AZ|AZURE|BA|BABY|BAIDU|BAND|BANK|BAR|BARCELONA|BARCLAYCARD|BARCLAYS|BAREFOOT|BARGAINS|BAUHAUS|BAYERN|BB|BBC|BBVA|BCG|BCN|BD|BE|BEATS|BEER|BENTLEY|BERLIN|BEST|BET|BF|BG|BH|BHARTI|BI|BIBLE|BID|BIKE|BING|BINGO|BIO|BIZ|BJ|BLACK|BLACKFRIDAY|BLOG|BLOOMBERG|BLUE|BM|BMS|BMW|BN|BNL|BNPPARIBAS|BO|BOATS|BOEHRINGER|BOM|BOND|BOO|BOOK|BOOTS|BOSCH|BOSTIK|BOT|BOUTIQUE|BR|BRADESCO|BRIDGESTONE|BROADWAY|BROKER|BROTHER|BRUSSELS|BS|BT|BUDAPEST|BUGATTI|BUILD|BUILDERS|BUSINESS|BUY|BUZZ|BV|BW|BY|BZ|BZH|CA|CAB|CAFE|CAL|CALL|CAMERA|CAMP|CANCERRESEARCH|CANON|CAPETOWN|CAPITAL|CAR|CARAVAN|CARDS|CARE|CAREER|CAREERS|CARS|CARTIER|CASA|CASH|CASINO|CAT|CATERING|CBA|CBN|CC|CD|CEB|CENTER|CEO|CERN|CF|CFA|CFD|CG|CH|CHANEL|CHANNEL|CHASE|CHAT|CHEAP|CHLOE|CHRISTMAS|CHROME|CHURCH|CI|CIPRIANI|CIRCLE|CISCO|CITIC|CITY|CITYEATS|CK|CL|CLAIMS|CLEANING|CLICK|CLINIC|CLINIQUE|CLOTHING|CLOUD|CLUB|CLUBMED|CM|CN|CO|COACH|CODES|COFFEE|COLLEGE|COLOGNE|COM|COMMBANK|COMMUNITY|COMPANY|COMPARE|COMPUTER|COMSEC|CONDOS|CONSTRUCTION|CONSULTING|CONTACT|CONTRACTORS|COOKING|COOL|COOP|CORSICA|COUNTRY|COUPON|COUPONS|COURSES|CR|CREDIT|CREDITCARD|CREDITUNION|CRICKET|CROWN|CRS|CRUISES|CSC|CU|CUISINELLA|CV|CW|CX|CY|CYMRU|CYOU|CZ|DABUR|DAD|DANCE|DATE|DATING|DATSUN|DAY|DCLK|DDS|DE|DEALER|DEALS|DEGREE|DELIVERY|DELL|DELOITTE|DELTA|DEMOCRAT|DENTAL|DENTIST|DESI|DESIGN|DEV|DHL|DIAMONDS|DIET|DIGITAL|DIRECT|DIRECTORY|DISCOUNT|DJ|DK|DM|DNP|DO|DOCS|DOG|DOHA|DOMAINS|DOT|DOWNLOAD|DRIVE|DTV|DUBAI|DURBAN|DVAG|DZ|EARTH|EAT|EC|EDEKA|EDU|EDUCATION|EE|EG|EMAIL|EMERCK|ENERGY|ENGINEER|ENGINEERING|ENTERPRISES|EPSON|EQUIPMENT|ER|ERNI|ES|ESQ|ESTATE|ET|EU|EUROVISION|EUS|EVENTS|EVERBANK|EXCHANGE|EXPERT|EXPOSED|EXPRESS|EXTRASPACE|FAGE|FAIL|FAIRWINDS|FAITH|FAMILY|FAN|FANS|FARM|FASHION|FAST|FEEDBACK|FERRERO|FI|FILM|FINAL|FINANCE|FINANCIAL|FIRESTONE|FIRMDALE|FISH|FISHING|FIT|FITNESS|FJ|FK|FLICKR|FLIGHTS|FLIR|FLORIST|FLOWERS|FLSMIDTH|FLY|FM|FO|FOO|FOOTBALL|FORD|FOREX|FORSALE|FORUM|FOUNDATION|FOX|FR|FRESENIUS|FRL|FROGANS|FRONTIER|FTR|FUND|FURNITURE|FUTBOL|FYI|GA|GAL|GALLERY|GALLO|GALLUP|GAME|GAMES|GARDEN|GB|GBIZ|GD|GDN|GE|GEA|GENT|GENTING|GF|GG|GGEE|GH|GI|GIFT|GIFTS|GIVES|GIVING|GL|GLASS|GLE|GLOBAL|GLOBO|GM|GMAIL|GMBH|GMO|GMX|GN|GOLD|GOLDPOINT|GOLF|GOO|GOOG|GOOGLE|GOP|GOT|GOV|GP|GQ|GR|GRAINGER|GRAPHICS|GRATIS|GREEN|GRIPE|GROUP|GS|GT|GU|GUARDIAN|GUCCI|GUGE|GUIDE|GUITARS|GURU|GW|GY|HAMBURG|HANGOUT|HAUS|HDFCBANK|HEALTH|HEALTHCARE|HELP|HELSINKI|HERE|HERMES|HIPHOP|HISAMITSU|HITACHI|HIV|HK|HKT|HM|HN|HOCKEY|HOLDINGS|HOLIDAY|HOMEDEPOT|HOMES|HONDA|HORSE|HOST|HOSTING|HOTELES|HOTMAIL|HOUSE|HOW|HR|HSBC|HT|HTC|HU|HYUNDAI|IBM|ICBC|ICE|ICU|ID|IE|IFM|IINET|IL|IM|IMAMAT|IMMO|IMMOBILIEN|IN|INDUSTRIES|INFINITI|INFO|ING|INK|INSTITUTE|INSURANCE|INSURE|INT|INTERNATIONAL|INVESTMENTS|IO|IPIRANGA|IQ|IR|IRISH|IS|ISELECT|ISMAILI|IST|ISTANBUL|IT|ITAU|IWC|JAGUAR|JAVA|JCB|JCP|JE|JETZT|JEWELRY|JLC|JLL|JM|JMP|JNJ|JO|JOBS|JOBURG|JOT|JOY|JP|JPMORGAN|JPRS|JUEGOS|KAUFEN|KDDI|KE|KERRYHOTELS|KERRYLOGISTICS|KERRYPROPERTIES|KFH|KG|KH|KI|KIA|KIM|KINDER|KITCHEN|KIWI|KM|KN|KOELN|KOMATSU|KP|KPMG|KPN|KR|KRD|KRED|KUOKGROUP|KW|KY|KYOTO|KZ|LA|LACAIXA|LAMBORGHINI|LAMER|LANCASTER|LAND|LANDROVER|LANXESS|LASALLE|LAT|LATROBE|LAW|LAWYER|LB|LC|LDS|LEASE|LECLERC|LEGAL|LEXUS|LGBT|LI|LIAISON|LIDL|LIFE|LIFEINSURANCE|LIFESTYLE|LIGHTING|LIKE|LIMITED|LIMO|LINCOLN|LINDE|LINK|LIPSY|LIVE|LIVING|LIXIL|LK|LOAN|LOANS|LOCKER|LOCUS|LOL|LONDON|LOTTE|LOTTO|LOVE|LR|LS|LT|LTD|LTDA|LU|LUPIN|LUXE|LUXURY|LV|LY|MA|MADRID|MAIF|MAISON|MAKEUP|MAN|MANAGEMENT|MANGO|MARKET|MARKETING|MARKETS|MARRIOTT|MATTEL|MBA|MC|MD|ME|MED|MEDIA|MEET|MELBOURNE|MEME|MEMORIAL|MEN|MENU|MEO|METLIFE|MG|MH|MIAMI|MICROSOFT|MIL|MINI|MK|ML|MLB|MLS|MM|MMA|MN|MO|MOBI|MOBILY|MODA|MOE|MOI|MOM|MONASH|MONEY|MONTBLANC|MORMON|MORTGAGE|MOSCOW|MOTORCYCLES|MOV|MOVIE|MOVISTAR|MP|MQ|MR|MS|MT|MTN|MTPC|MTR|MU|MUSEUM|MUTUAL|MUTUELLE|MV|MW|MX|MY|MZ|NA|NADEX|NAGOYA|NAME|NATURA|NAVY|NC|NE|NEC|NET|NETBANK|NETFLIX|NETWORK|NEUSTAR|NEW|NEWS|NEXT|NEXTDIRECT|NEXUS|NF|NG|NGO|NHK|NI|NICO|NIKON|NINJA|NISSAN|NISSAY|NL|NO|NOKIA|NORTHWESTERNMUTUAL|NORTON|NOWRUZ|NOWTV|NP|NR|NRA|NRW|NTT|NU|NYC|NZ|OBI|OFFICE|OKINAWA|OLAYAN|OLAYANGROUP|OLLO|OM|OMEGA|ONE|ONG|ONL|ONLINE|OOO|ORACLE|ORANGE|ORG|ORGANIC|ORIGINS|OSAKA|OTSUKA|OTT|OVH|PA|PAGE|PAMPEREDCHEF|PANERAI|PARIS|PARS|PARTNERS|PARTS|PARTY|PASSAGENS|PCCW|PE|PET|PF|PG|PH|PHARMACY|PHILIPS|PHOTO|PHOTOGRAPHY|PHOTOS|PHYSIO|PIAGET|PICS|PICTET|PICTURES|PID|PIN|PING|PINK|PIONEER|PIZZA|PK|PL|PLACE|PLAY|PLAYSTATION|PLUMBING|PLUS|PM|PN|POHL|POKER|PORN|POST|PR|PRAXI|PRESS|PRO|PROD|PRODUCTIONS|PROF|PROGRESSIVE|PROMO|PROPERTIES|PROPERTY|PROTECTION|PS|PT|PUB|PW|PWC|PY|QA|QPON|QUEBEC|QUEST|RACING|RE|READ|REALESTATE|REALTOR|REALTY|RECIPES|RED|REDSTONE|REDUMBRELLA|REHAB|REISE|REISEN|REIT|REN|RENT|RENTALS|REPAIR|REPORT|REPUBLICAN|REST|RESTAURANT|REVIEW|REVIEWS|REXROTH|RICH|RICHARDLI|RICOH|RIO|RIP|RO|ROCHER|ROCKS|RODEO|ROOM|RS|RSVP|RU|RUHR|RUN|RW|RWE|RYUKYU|SA|SAARLAND|SAFE|SAFETY|SAKURA|SALE|SALON|SAMSUNG|SANDVIK|SANDVIKCOROMANT|SANOFI|SAP|SAPO|SARL|SAS|SAXO|SB|SBI|SBS|SC|SCA|SCB|SCHAEFFLER|SCHMIDT|SCHOLARSHIPS|SCHOOL|SCHULE|SCHWARZ|SCIENCE|SCOR|SCOT|SD|SE|SEAT|SECURITY|SEEK|SELECT|SENER|SERVICES|SEVEN|SEW|SEX|SEXY|SFR|SG|SH|SHARP|SHAW|SHELL|SHIA|SHIKSHA|SHOES|SHOP|SHOUJI|SHOW|SHRIRAM|SI|SINA|SINGLES|SITE|SJ|SK|SKI|SKIN|SKY|SKYPE|SL|SM|SMILE|SN|SNCF|SO|SOCCER|SOCIAL|SOFTBANK|SOFTWARE|SOHU|SOLAR|SOLUTIONS|SONG|SONY|SOY|SPACE|SPIEGEL|SPOT|SPREADBETTING|SR|SRL|ST|STADA|STAR|STARHUB|STATEBANK|STATEFARM|STATOIL|STC|STCGROUP|STOCKHOLM|STORAGE|STORE|STREAM|STUDIO|STUDY|STYLE|SU|SUCKS|SUPPLIES|SUPPLY|SUPPORT|SURF|SURGERY|SUZUKI|SV|SWATCH|SWISS|SX|SY|SYDNEY|SYMANTEC|SYSTEMS|SZ|TAB|TAIPEI|TALK|TAOBAO|TATAMOTORS|TATAR|TATTOO|TAX|TAXI|TC|TCI|TD|TEAM|TECH|TECHNOLOGY|TEL|TELECITY|TELEFONICA|TEMASEK|TENNIS|TEST|TEVA|TF|TG|TH|THD|THEATER|THEATRE|TICKETS|TIENDA|TIFFANY|TIPS|TIRES|TIROL|TJ|TK|TL|TM|TMALL|TN|TO|TODAY|TOKYO|TOOLS|TOP|TORAY|TOSHIBA|TOTAL|TOURS|TOWN|TOYOTA|TOYS|TR|TRADE|TRADING|TRAINING|TRAVEL|TRAVELERS|TRAVELERSINSURANCE|TRUST|TRV|TT|TUBE|TUI|TUNES|TUSHU|TV|TVS|TW|TZ|UA|UBS|UG|UK|UNICOM|UNIVERSITY|UNO|UOL|UPS|US|UY|UZ|VA|VACATIONS|VANA|VC|VE|VEGAS|VENTURES|VERISIGN|VERSICHERUNG|VET|VG|VI|VIAJES|VIDEO|VIG|VIKING|VILLAS|VIN|VIP|VIRGIN|VISION|VISTA|VISTAPRINT|VIVA|VLAANDEREN|VN|VODKA|VOLKSWAGEN|VOTE|VOTING|VOTO|VOYAGE|VU|VUELOS|WALES|WALTER|WANG|WANGGOU|WARMAN|WATCH|WATCHES|WEATHER|WEATHERCHANNEL|WEBCAM|WEBER|WEBSITE|WED|WEDDING|WEIBO|WEIR|WF|WHOSWHO|WIEN|WIKI|WILLIAMHILL|WIN|WINDOWS|WINE|WME|WOLTERSKLUWER|WORK|WORKS|WORLD|WS|WTC|WTF|XBOX|XEROX|XIHUAN|XIN|XN--11B4C3D|XN--1CK2E1B|XN--1QQW23A|XN--30RR7Y|XN--3BST00M|XN--3DS443G|XN--3E0B707E|XN--3PXU8K|XN--42C2D9A|XN--45BRJ9C|XN--45Q11C|XN--4GBRIM|XN--55QW42G|XN--55QX5D|XN--5TZM5G|XN--6FRZ82G|XN--6QQ986B3XL|XN--80ADXHKS|XN--80AO21A|XN--80ASEHDB|XN--80ASWG|XN--8Y0A063A|XN--90A3AC|XN--90AIS|XN--9DBQ2A|XN--9ET52U|XN--9KRT00A|XN--B4W605FERD|XN--BCK1B9A5DRE4C|XN--C1AVG|XN--C2BR7G|XN--CCK2B3B|XN--CG4BKI|XN--CLCHC0EA0B2G2A9GCD|XN--CZR694B|XN--CZRS0T|XN--CZRU2D|XN--D1ACJ3B|XN--D1ALF|XN--E1A4C|XN--ECKVDTC9D|XN--EFVY88H|XN--ESTV75G|XN--FCT429K|XN--FHBEI|XN--FIQ228C5HS|XN--FIQ64B|XN--FIQS8S|XN--FIQZ9S|XN--FJQ720A|XN--FLW351E|XN--FPCRJ9C3D|XN--FZC2C9E2C|XN--FZYS8D69UVGM|XN--G2XX48C|XN--GCKR3F0F|XN--GECRJ9C|XN--H2BRJ9C|XN--HXT814E|XN--I1B6B1A6A2E|XN--IMR513N|XN--IO0A7I|XN--J1AEF|XN--J1AMH|XN--J6W193G|XN--JLQ61U9W7B|XN--JVR189M|XN--KCRX77D1X4A|XN--KPRW13D|XN--KPRY57D|XN--KPU716F|XN--KPUT3I|XN--L1ACC|XN--LGBBAT1AD8J|XN--MGB9AWBF|XN--MGBA3A3EJT|XN--MGBA3A4F16A|XN--MGBA7C0BBN0A|XN--MGBAAM7A8H|XN--MGBAB2BD|XN--MGBAYH7GPA|XN--MGBB9FBPOB|XN--MGBBH1A71E|XN--MGBC0A9AZCG|XN--MGBCA7DZDO|XN--MGBERP4A5D4AR|XN--MGBPL2FH|XN--MGBT3DHD|XN--MGBTX2B|XN--MGBX4CD0AB|XN--MIX891F|XN--MK1BU44C|XN--MXTQ1M|XN--NGBC5AZD|XN--NGBE9E0A|XN--NODE|XN--NQV7F|XN--NQV7FS00EMA|XN--NYQY26A|XN--O3CW4H|XN--OGBPF8FL|XN--P1ACF|XN--P1AI|XN--PBT977C|XN--PGBS0DH|XN--PSSY2U|XN--Q9JYB4C|XN--QCKA1PMC|XN--QXAM|XN--RHQV96G|XN--ROVU88B|XN--S9BRJ9C|XN--SES554G|XN--T60B56A|XN--TCKWE|XN--UNUP4Y|XN--VERMGENSBERATER-CTB|XN--VERMGENSBERATUNG-PWB|XN--VHQUV|XN--VUQ861B|XN--W4R85EL8FHU5DNRA|XN--W4RS40L|XN--WGBH1C|XN--WGBL6A|XN--XHQ521B|XN--XKC2AL3HYE2A|XN--XKC2DL3A5EE0H|XN--Y9A3AQ|XN--YFRO4I67O|XN--YGBI2AMMX|XN--ZFR164B|XPERIA|XXX|XYZ|YACHTS|YAHOO|YAMAXUN|YANDEX|YE|YODOBASHI|YOGA|YOKOHAMA|YOU|YOUTUBE|YT|YUN|ZA|ZAPPOS|ZARA|ZERO|ZIP|ZM|ZONE|ZUERICH|ZW';
	private $api = false;
	private $db = false;
	public function __sleep(){
		$this->writeHosts();	
		return array('debug', 'errorMsg', 'table', 'apiKey', 'wordpressVersion', 'dRegex');
	}
	public function __wakeup(){
		$this->hostsToAdd = new wfArray(array('owner', 'host', 'path', 'hostKey'));
		$this->api = new wfAPI($this->apiKey, $this->wordpressVersion);
		$this->db = new wfDB();
	}	
	public function __construct($apiKey, $wordpressVersion, $db = false){
		$this->hostsToAdd = new wfArray(array('owner', 'host', 'path', 'hostKey'));
		$this->apiKey = $apiKey;
		$this->wordpressVersion = $wordpressVersion;
		$this->api = new wfAPI($apiKey, $wordpressVersion);
		if($db){
			$this->db = $db;
		} else {
			$this->db = new wfDB();
		}
		global $wpdb;
		if(isset($wpdb)){
			$this->table = $wpdb->base_prefix . 'wfHoover';
		} else {
			$this->table = 'wp_wfHoover';
		}
		$this->db->truncate($this->table);
	}
	public function cleanup(){
		$this->db->truncate($this->table);
	}
	public function hoover($id, $data){
		if(strpos($data, '.') === false){
			return false;
		}
		$this->currentHooverID = $id;
		$this->_foundSome = false;
		try {
			@preg_replace_callback("/(?<=^|[^a-zA-Z0-9\-])(?:[a-z][a-z0-9\-\+\.]*\:)?\/\/((?:[a-zA-Z0-9\-]+\.)+)(" . $this->dRegex . ")($|[\r\n\s\t]|[\/\?][^\r\n\s\t\"\'\$\{\}<>]*)/i", array($this, 'addHost'), $data);
			//((?:$|[^a-zA-Z0-9\-\.\'\"])[^\r\n\s\t\"\'\$\{\}<>]*)
			//"\$this->" . "addHost(\$id, '$1$2', '$3')", $data);
		} catch(Exception $e){ 
			//error_log("Regex error 1: $e"); 
		}
		@preg_replace_callback("/(?<=[^\d]|^)(\d{8,10}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})($|[\r\n\s\t]|\/[^\r\n\s\t\"\'\$\{\}<>]*)/", array($this, 'addIP'), $data);
		//([^\d\'\"][^\r\n\s\t\"\'\$\{\}<>]*)
		//"\$this->" . "addIP(\$id, \"$1\",\"$2\")", $data);
		$this->writeHosts();
		return $this->_foundSome;
	}
	private function dbg($msg){ 
		if($this->debug){
			wordfence::status(4, 'info', $msg);
			//error_log("DEBUG: $msg\n"); 
		} 
	}
	public function addHost($matches){
		$id = $this->currentHooverID;
		$host = $matches[1] . $matches[2];
		$path = $matches[3];
		if(strpos($path, '/') !== 0){
			$path = '/';
		} else {
			$path = preg_replace_callback('/([^A-Za-z0-9\-\.\_\~:\/\?\#\[\]\@\!\$\&\'\(\)\*\+\,;\=]+)/', 'wordfenceURLHoover::urlenc', $path);
		}
		$host = strtolower($host);
		$hostParts = explode('.', $host);
		if(sizeof($hostParts) == 2){
			$hostKey = substr(hash('sha256', $hostParts[0] . '.' . $hostParts[1] . '/', true), 0, 4);
			$this->hostsToAdd->push(array('owner' => $id, 'host' => $host, 'path' => $path, 'hostKey' => $hostKey));
		} else if(sizeof($hostParts) > 2){
			$hostKeyThreeParts = substr(hash('sha256',$hostParts[sizeof($hostParts) - 3] . '.' . $hostParts[sizeof($hostParts) - 2] . '.' . $hostParts[sizeof($hostParts) - 1] . '/', true), 0, 4);
			$hostKeyTwoParts = substr(hash('sha256', $hostParts[sizeof($hostParts) - 2] . '.' . $hostParts[sizeof($hostParts) - 1] . '/', true), 0, 4);
			$this->hostsToAdd->push(array('owner' => $id, 'host' => $host, 'path' => $path, 'hostKey' => $hostKeyThreeParts));
			$this->hostsToAdd->push(array('owner' => $id, 'host' => $host, 'path' => $path, 'hostKey' => $hostKeyTwoParts));
		}
		if($this->hostsToAdd->size() > 1000){ $this->writeHosts(); }	
	}
	public function addIP($matches){
		$id = $this->currentHooverID;
		$ipdata = $matches[1];
		$path = $matches[2];
		$this->dbg("Add IP called with $ipdata $path");
		if(strstr($ipdata, '.') === false){
			if($ipdata >= 16777216 && $ipdata <= 4026531840){
				$ipdata = long2ip($ipdata);
			} else {
				return; //Is int but invalid address.
			}
		} 
		$parts = explode('.', $ipdata);
		foreach($parts as $part){
			if($part < 0 || $part > 255){
				return;
			}
		}
		if(wfUtils::isPrivateAddress($ipdata) ){
			return;
		}
		if(strlen($path) == 1){
			$path = '/'; //Because it's either a whitespace char or a / anyway. 
		} else if(strlen($path) > 1){
			$path = preg_replace_callback('/([^A-Za-z0-9\-\.\_\~:\/\?\#\[\]\@\!\$\&\'\(\)\*\+\,;\=]+)/', 'wordfenceURLHoover::urlenc', $path);
		}
		$hostKey = substr(hash('sha256', $ipdata . '/', true), 0, 4);
		$this->hostsToAdd->push(array('owner' => $id, 'host' => $ipdata, 'path' => $path, 'hostKey' => $hostKey));
		if($this->hostsToAdd->size() > 1000){ $this->writeHosts(); }	
	}
	public static function urlenc($m){
		return urlencode($m[1]);
	}
	private function writeHosts(){
		if($this->hostsToAdd->size() < 1){ return; }
		if($this->useDB){
			$sql = "insert into " . $this->table . " (owner, host, path, hostKey) values ";
			while($elem = $this->hostsToAdd->shift()){
				//This may be an issue for hyperDB or other abstraction layers, but leaving it for now.
				$sql .= sprintf("('%s', '%s', '%s', '%s'),", 
						$this->db->realEscape($elem['owner']),
						$this->db->realEscape($elem['host']),
						$this->db->realEscape($elem['path']),
						$this->db->realEscape($elem['hostKey'])
								);
			}
			$sql = rtrim($sql, ',');
			$this->db->queryWrite($sql);
		} else {
			while($elem = $this->hostsToAdd->shift()){
				$this->hostKeys[] = $elem['hostKey'];
				$this->hostList[] = array(
					'owner' => $elem['owner'],
					'host' => $elem['host'],
					'path' => $elem['path'],
					'hostKey' => $elem['hostKey']
					);
			}
		}
		
		$this->_foundSome = true;
	}
	public function getBaddies(){
		$allHostKeys = array();
		if($this->useDB){
			$q1 = $this->db->querySelect("select distinct hostKey as hostKey from $this->table");
			foreach($q1 as $hRec){
				$allHostKeys[] = $hRec['hostKey'];
			}
		} else {
			$allHostKeys = $this->hostKeys;
		}
		//Now call API and check if any hostkeys are bad. 
		//This is a shortcut, because if no hostkeys are bad it saves us having to check URLs
		if(sizeof($allHostKeys) > 0){ //If we don't have any hostkeys, then we won't have any URL's to check either.
			//Hostkeys are 4 byte sha256 prefixes
			//Returned value is 2 byte shorts which are array indexes for bad keys that were passed in the original list
			$this->dbg("Checking " . sizeof($allHostKeys) . " hostkeys");
			if($this->debug){
				foreach($allHostKeys as $key){
					$this->dbg("Checking hostkey: " . bin2hex($key));
				}
			}
			wordfence::status(2, 'info', "Checking " . sizeof($allHostKeys) . " host keys against Wordfence scanning servers.");
			$resp = $this->api->binCall('check_host_keys', implode('', $allHostKeys));
			wordfence::status(2, 'info', "Done host key check.");
			$this->dbg("Done host key check");

			$badHostKeys = array();
			if($resp['code'] == 200){
				$this->dbg("Host key response: " . bin2hex($resp['data']));
				$dataLen = strlen($resp['data']);
				if($dataLen > 0){
					if($dataLen % 2 != 0){
						$this->errorMsg = "Invalid data length received from Wordfence server: " . $dataLen;
						$this->dbg($this->errorMsg);
						return false;
					}
					$this->dbg("Checking response indexes");
					for($i = 0; $i < $dataLen; $i += 2){
						$idxArr = unpack('n', substr($resp['data'], $i, 2));
						$idx = $idxArr[1];
						$this->dbg("Checking index {$idx}");
						if(isset($allHostKeys[$idx]) ){
							$badHostKeys[] = $allHostKeys[$idx];
							$this->dbg("Got bad hostkey for record: " . bin2hex($allHostKeys[$idx]));
						} else {
							$this->dbg("Bad allHostKeys index: $idx");
							$this->errorMsg = "Bad allHostKeys index: $idx";
							return false;
						}
					}
				}
				else {
					$this->dbg("Empty host key response");
				}
			} else {
				$this->errorMsg = "Wordfence server responded with an error. HTTP code " . $resp['code'] . " and data: " . $resp['data'];
				return false;
			}
			if(sizeof($badHostKeys) > 0){
				$urlsToCheck = array();
				$totalURLs = 0;
				//need to figure out which id's have bad hostkeys
				//need to feed in all URL's from those id's where the hostkey matches a URL
				foreach($badHostKeys as $badHostKey){
					if($this->useDB){
						//Putting a 10000 limit in here for sites that have a huge number of items with the same URL that repeats.
						// This is an edge case. But if the URLs are malicious then presumably the admin will fix the malicious URLs
						// and on subsequent scans the items (owners) that are above the 10000 limit will appear.
						$q1 = $this->db->querySelect("select owner, host, path from $this->table where hostKey='%s' limit 10000", $badHostKey);
						foreach($q1 as $rec){
							$url = 'http://' . $rec['host'] . $rec['path'];
							if(! isset($urlsToCheck[$rec['owner']])){
								$urlsToCheck[$rec['owner']] = array();
							}
							if(! in_array($url, $urlsToCheck[$rec['owner']])){
								$urlsToCheck[$rec['owner']][] = $url;
								$totalURLs++;
							}
						}
					} else {
						foreach($this->hostList as $rec){
							if($rec['hostKey'] == $badHostKey){
								$url = 'http://' . $rec['host'] . $rec['path'];
								if(! isset($urlsToCheck[$rec['owner']])){
									$urlsToCheck[$rec['owner']] = array();
								}
								if(! in_array($url, $urlsToCheck[$rec['owner']])){
									$urlsToCheck[$rec['owner']][] = $url;
									$totalURLs++;
								}
							}
						}
					}
				}

				if(sizeof($urlsToCheck) > 0){
					wordfence::status(2, 'info', "Checking " . $totalURLs . " URLs from " . sizeof($urlsToCheck) . " sources.");
					$badURLs = $this->api->call('check_bad_urls', array(), array( 'toCheck' => json_encode($urlsToCheck)) );
					wordfence::status(2, 'info', "Done URL check.");
					$this->dbg("Done URL check");
					if(is_array($badURLs) && sizeof($badURLs) > 0){
						$finalResults = array();
						foreach($badURLs as $file => $badSiteList){
							if(! isset($finalResults[$file])){
								$finalResults[$file] = array();
							}
							foreach($badSiteList as $badSite){
								$finalResults[$file][] = array(
									'URL' => $badSite[0],
									'badList' => $badSite[1]
									);
							}
						}
						$this->dbg("Confirmed " . count($badURLs) . " bad URLs");
						return $finalResults;
					}
				}
			}
		}
		
		return array();
	}
}
?>
