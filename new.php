<?php 


require_once("class_curl/facilcurl.php"); 




class Craw
{
										# EJEMPLOS	
	private static $url			= '';	# https://www.myDominio.com/index.php
	private static $ipUrl		= '';	# 185.168.100.12
	private static $urlDomain 	= '';	# www.myDominio.com
	private static $codeUrl 	= '';	# <html> code </html>
	private static $keywords 	= []; 	#son palabras claves que tienen los dominios www.rancho.vaca.mx   www.zo.vaca.mx la palabra clave es vaca 
	private static $trashDominos 	= [];
	private static $links		=  [	'linksMain' 		=> [], 
										'linksSubDomain' 	=> [],
										'subDomains' 		=> [],
										'links' 			=> [],
										'linksImg' 			=> [],
										'linksDesign' 		=> [],
										'linksDoc' 			=> [],
										'linksCompress' 	=> [],
										'linksTrash' 		=> [],
										'mail' 				=> [] ];




	#https://es.wikipedia.org/wiki/Dominio_de_nivel_superior_gen%C3%A9rico
	private static $gtopLevelDomains	= [ 'com', 'org', 'net', 'info', 'biz', 'name', 'pro', 'gov', 'edu', 'mil', 'int', 'aero', 'coop', 'museum', 'cat', 
											'jobs', 'mobi', 'tel', 'travel', 'asia', 'xxx', 'post', 'eus', 'email', 'gal', 'arpa', 'root', 'blogspot' ];


	#son Expresiones que se utilizaran para clasificar las url
	private static $expDocuments	= [ '\.pdf', '\.txt', '\.docx', '\.docm', '\.dotx', '\.dotm', '\.xlsx', '\.xlsm', '\.xltx', '\.xltm', '\.xlsb', '\.xlam', '\.pptx', '\.pptm', '\.potx',
										 '\.potm', '\.ppam', '\.ppsx', '\.ppsm', '\.sldx', '\.sldm', '\.thmx' ];

	private static $expImg		= [ '\.ico', '\.png', '\.BMP', '\.TIFF', '\.jpg', '\.JPEG', '\.GIF', '\.PNG', '\.EPS', '\.SVG', '\.EPS', '\.WebP', '\.heif', '\.psd', '\.ai', '\.xcf', '\.indd' ];
	private static $expCompress	= [ '\.zip', '\.ZIPX', '\.TAR', '\.GZ', '\.RAR', '\.7z', '\.ACE' ];
	private static $expDesign	= [ '\.CSS', '\.JS', '\.eot', '\.woff', '\.ttf' ];



	

	public static function start( $url )
	{

		#Verifica que la url cumpla con los parametros necesarios para poder trabajar con ella
		self::$url 					= self::checkUrl( $url );

		#Realiza la consulta para obtener el codigo html
		if( !(self::$codeUrl = self::consultUrl(  self::$url )) )
			return false;

		#Verfica que el codigo html tenga urls
		if( !( $if_extractLinks = self::extractLinks( self::$codeUrl )) )
			return false;


		#Preparando variables
		self::$urlDomain 			= self::extractDomain( self::$url ); 
		self::$ipUrl 				= self::getIpUrl( self::$urlDomain );


		#self::putSubDomain( [self::$urlDomain] ); // lo estamos ingresando en la lista de dominios


		#Eliminando Dominos que no queremos
		self::putTrashDominos( [ 'github.com',
								 'google.com',
								 'www.google',
								 'youtu.be',
								 'youtube.com',
								 'wikipedia.org',
								 'facebook.org',
								 'facebook.com',
								 'fb.com',
								 'wikipedia.org',
								 'twitch.org',
								 'twitter.com',
								 'twitter.org',
								 'tiktok.com',
								 'instagram.com',
								 'netflix.com',
								 'wiktionary.org',
								 'yahoo.com',
								 'office.com',
								 'telegram.me',
								 'telegram.com',
								 'whatsapp.com' ] );



		#Se aplicaron filtros para eliminar links repetidos y dominios que no queremos etc..
		$linksWithFilter = self::filterLinks( $if_extractLinks );


		#se obtiene la keyword del dominio que estamos consultando
		self::putkeywords( self::extractKeyword( self::$urlDomain ) );



		#Se Clasifican los links separandolos por pdf, documentos, links de diseño
		self::$links = self::classifyLinks( $linksWithFilter );



		print_r( self::$links );


		#buscar links mandandole liks con un array de dominos



		#print_r( self::prepareExp( self::$trashDominos ) );

		#print_r( self::$keywords );
		



	}



	/*
	*	Se busca una palabra en espesifico en la pagina y si la tiene la retorna
	*/

	public static function searchThePage( $word ){
	}



	/*
	*	Se clasifican las url por imagenes url de diseño por url de compresion etc..
	*/

	public static function classifyLinks( $links ){



		$linksDesing	= [];
		$linksImg		= [];
		$linksDocuments	= [];
		$linksCompress	= [];
		$linksDomain	= [];
		$unknownLinks	= [];
		$mails			= [];
		$linksTrash		= [];
		$linksSubDomain = [];
		$subDomains		= [];



		foreach ($links as $value) {			

			if ( preg_match("#". self::prepareExp( self::$expDesign ) ."#i", $value) ) {
				

				if ( preg_match("#".self::$urlDomain."#i", $value) ) {
					array_unshift($linksDesing, $value);
				}else{
					$linksDesing[] .=  $value;
				}


			}elseif ( preg_match("#". self::prepareExp( self::$expImg ) ."#i", $value) ) {
				
				if ( preg_match("#".self::$urlDomain."#i", $value) ) {
					array_unshift($linksImg, $value);
				}else{
					$linksImg[] .=  $value;
				}


			}elseif ( preg_match("#". self::prepareExp( self::$expDocuments ) ."#i", $value) ) {
				
				if ( preg_match("#".self::$urlDomain."#i", $value) ) {
					array_unshift($linksDocuments, $value);
				}else{
					$linksDocuments[] .=  $value;
				}



			}elseif ( preg_match("#". self::prepareExp( self::$expCompress ) ."#i", $value) ) {
				
				if ( preg_match("#".self::$urlDomain."#i", $value) ) {
					array_unshift($linksCompress, $value);
				}else{
					$linksCompress[] .=  $value;
				}


			}elseif ( preg_match("#[https|http]\:\/\/". self::$urlDomain  ."#i", $value) ) {

				if( ( self::deleteHttp( $value ) !=  self::$urlDomain  ) and ( self::deleteHttp( $value ) != self::$urlDomain.'/'  ) ) #
						$linksDomain[] .= $value;#. '------ 0 -';



			}else{

				#$unknownLinks[] .= $value;


				if ( preg_match("#^[http|https]#i", $value) ) {
					
					if ( preg_match("#". self::prepareExp( self::$keywords ) ."#i", $value) ) {
						$linksSubDomain[] 	.= $value;
						$subDomains[]		.= self::extractDomain( $value );

					}else{
						$unknownLinks[] .= $value;
					}

				}elseif( preg_match("/[a-zA-Z0-9_\-\.]{0,100}@[a-zA-Z0-9_\-\.]+/i", $value) ){

					$mails[] .= $value;
				}elseif ( preg_match("#^[a-z]#i", $value) ) { // [^http]|^[a-z]

					$linksDomain[] .= 'https://'.self::$urlDomain.'/'.$value;#. '------ 1 -'.self::$url;

				}elseif ( preg_match("#^[/]#i", $value) ) {
					
					if( $value != '/' )
						$linksDomain[] .= 'https://'.self::$urlDomain.$value;#. '------ 2 -'.self::$url;

				}elseif( preg_match("#^[\?]#i", $value) ){

					$linksDomain[] .= 'https://'.self::$urlDomain.'/'.$value;#. '------ 3 -'.self::$url;
				}else{
					$linksTrash[] .= $value;
				}




			}#end If

		}#end Foreach


		#eliminando linksmain repetidos haciendo 2 verificacion
		$linksDomain = array_values( array_unique( $linksDomain , SORT_STRING ) );

		#eliminando subdominos repetidos
		$subDomains = array_values( array_unique( $subDomains , SORT_STRING ) );


		return	[	'linksMain' 		=> $linksDomain, 
					'linksSubDomain' 	=> $linksSubDomain,
					'subDomains' 		=> $subDomains,
					'links' 			=> $unknownLinks,
					'linksImg' 			=> $linksImg,
					'linksDesign' 		=> $linksDesing,
					'linksDoc' 			=> $linksDocuments,
					'linksCompress' 	=>  $linksCompress,
					'linksTrash' 		=> $linksTrash,
					'mail' 				=> $mails ];


	}





	# Verifica los diminos que coincidad con el dimino original de dns
	public static function checkDomainsIp( $Dom, $ip ){


		$AipMain = explode('.', $ip);
		$domBad = [];
		$domGood = [];



		foreach ($Dom as $key => $value) {

			$ipUrl 	=   explode('.',@dns_get_record($value,DNS_A)[0]['ip'] );
			


			if ( ($ipUrl[0] == $AipMain[0]) and ($ipUrl[1] == $AipMain[1]) ) 
			{
				$domGood[] .=  $value;
			}
			else
			{
				$domBad[] .=  $value;
			}
			

		}

		return [ 'domGood' => $domGood, 'domBad' => $domBad];

	}






	/*
	*	Elimina al principio el http(s)
	*/

	public static function deleteHttp( $url ){
		return str_replace( ['http://','https://'], ['',''], $url );
	}


	/*
	*	Extrae la palabra clave de un domino para obtener subdominios
	*/

	public static function extractKeyword( $dom ){

		$domFun = [];
		$keywordsDom = [];

		if( !is_array( $dom ) ){
			$domFun = [ $dom ];
		}else{
			$domFun = $dom;
		}



		foreach ($domFun as $value) {

			$partDom = explode('.', $value);   #www.vaca.com #www.rancho.vaca.com.mx  extractKeyword( $dom )


			if ( !preg_match("#". self::prepareExp( self::$gtopLevelDomains ) ."#i", $partDom[ (count($partDom)-2) ]) ) { 
				
				$keywordsDom[] .= $partDom[ (count($partDom)-2) ].'\.';
				$keywordsDom[] .= '\.'.$partDom[ (count($partDom)-2) ];

			}
			else{
				$keywordsDom[] .= $partDom[ (count($partDom)-3) ].'\.';
				$keywordsDom[] .= '\.'.$partDom[ (count($partDom)-3) ];
			}


		}

		return $keywordsDom;

	}


	/*
	*	Guarda una palabra clave 
	*/

	public static function putkeywords( $words ){


		if( !is_array( $words ) )
		{
			self::$keywords[] .=   $words;
		}
		else
		{
		
			foreach($words as  $value) {
				self::$keywords[] .= $value;
			}


		}


	}

	



	/*
	*	Filtra los links quitandole caracteres innecesarios, elimina espacios etc..
	*/


	public static function filterLinks( $links ){

		$linksFun = $links;


		#Aqui se eliminan los dominos basura
		if( !empty( self::$trashDominos ) )
			$linksFun = preg_grep("#". self::prepareExp( self::$trashDominos ) ."#i", $links,PREG_GREP_INVERT);

		#eliminando caracteres inservibles
		$linksFun = str_replace( ["'"], [""],  $linksFun);

		#Quitando espacios en los links
		$linksFun = preg_replace("[\s+]", "", $linksFun);

		#Elimina elementos vacios o nulos del array
		$linksFun = array_filter($linksFun);

		#QUITANDO LINK REPETIDOS  "PROBLEMA CUANDO EL ARRAY TIENE MAS DE 600 ELEMENTOS"
		$linksFun = array_unique( $linksFun , SORT_STRING );

		return $linksFun;


	}



	/*
	*	Extrae todos los link que se puedan encontrar
	*/

	public static function extractLinks( $pag ){

		$linksTotal = [];

		preg_match_all('#src="(.*?)"#i', 		$pag , $linksSrc);
		preg_match_all('#href="(.*?)"#i', 		$pag , $linksHref);
		preg_match_all('#action="(.*?)"#i', 	$pag , $linksAction);
		preg_match_all("#url\(\'(.*?)\'\)#i",	$pag , $linksUrl); 
		preg_match_all("#url\((.*?)\)#i", 		$pag , $linksUrl2);


		#uniendo los array 
		$linksTotal = array_merge($linksTotal, $linksSrc[1]);
		$linksTotal = array_merge($linksTotal,  $linksHref[1]);
		$linksTotal = array_merge($linksTotal, $linksAction[1]);
		$linksTotal = array_merge($linksTotal, $linksUrl[1]);
		$linksTotal = array_merge($linksTotal, $linksUrl2[1]);


		if ( !isset( $linksTotal ) )
			return false;


		return $linksTotal;
	}








	public static function putTrashDominos( $dom  ){
		

		if( !is_array( $dom ) )
		{
			self::$trashDominos[] .= $dom;
		}
		else
		{
		
			foreach($dom as  $value) {
				self::$trashDominos[] .= $value;
			}#end ForEach

		}#end If

	}



	/*
	*	Agrega dominos asi mejora la busquedas
	*/

	public static function putSubDomain( $dom  ){

		$domFun = $dom;

		if( !is_array( $dom ) )
			$domFun = [ $dom ];

		self::$links['subDomains'] = array_merge( self::$links['subDomains'], $domFun );
		
	}



	/*
	*	Verifica y agrega a la url los parametros necesarios para trabajar con ella y si no se le agregan los faltante
	*/


	public static function checkUrl( $url ){
		
		$urlFun = $url;

		if ( !preg_match("#http#i", $urlFun) )
			$urlFun =  'http://'.$urlFun;

		if ( !preg_match("#(http|https|ftp|ftps)\://(.*?)/#i", $urlFun) )
			$urlFun =  $urlFun.'/';

	
		return $urlFun;

	}



	/*
	*	Consulta la url solicitada y retorna el codigo html	
	*/

	public static function consultUrl( $url ){

		$mm = new facilcurl();
		$mm->curl($url,null,0,null,1);


		if ( $pag = $mm->exe_curl() ) {
			return $pag;
		}
		else{
			return false;
		}


	}



	/*
	*	Prepara un array para que lo puedamos colocar a como expresion regular	
	*/

	public static function prepareExp( $exp ){
		return implode('|',  $exp );
	}



	/*
	*	retorna solamente el domino de una url especificada	
	*/

	public static function extractDomain( $url ){

		$urlFun = [];


		if( !is_array( $url ) ){
			
			preg_match_all('#(http|https|ftp|ftps)\://(.*?)/#i', $url."/" , $result);
			return $result[2][0];


		}else{
			
			foreach ($url as  $value) {
				preg_match_all('#(http|https|ftp|ftps)\://(.*?)/#i', $value."/" , $result);
				$urlFun[] .= $result[2][0];
				
			}


			return $urlFun;
		}


	}



	/*
	*	Se consula la ip del dns de la url especificada
	*/

	public static function getIpUrl( $dom ){
			return ( !empty( $r = @dns_get_record( $dom ,DNS_A )[0]['ip'] ) ) ? $r : false ;
	}


}



#Craw::start( 'https://tec.mx/' );


#Agregando keyword para facilitar el trabajo al buscar subdominios
#Craw::putkeywords( ['\.uson', 'uson\.'] );

#Craw::start( 'https://www.hackplayers.com/' );




#https://book.hacktricks.xyz/pentesting/pentesting-web/php-tricks-esp/php-useful-functions-disable_functions-open_basedir-bypass

/*
eval('function jose(){
	echo "esta es la funcion\n";
}');


jose();
*/




?>