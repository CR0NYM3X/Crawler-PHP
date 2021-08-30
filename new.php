<?php 
require_once("class_curl/facilcurl.php"); 



class Craw
{
										# EJEMPLOS	
	private static $url			= '';	# https://www.myDominio.com/index.php
	public static $ipUrl		= '';	# 185.168.100.12
	private static $urlDomain 	= '';	# www.myDominio.com
	private static $codeUrl 	= '';	# <html> code </html>
	private static $keywords 	= []; 	#son palabras claves que tienen los dominios www.rancho.vaca.mx   www.zo.vaca.mx la palabra clave es vaca 
	private static $trashDominos 	= [];
	public static $links		=  [	'linksMain' 		=> [], 
										'linksSubDomain' 	=> [],
										'subDomains' 		=> [],
										'links' 			=> [],
										'socialNetwork'		=> [],
										'linksImg' 			=> [],
										'linksDesign' 		=> [],
										'linksDoc' 			=> [],
										'linksCompress' 	=> [],
										'linksTrash' 		=> [],
										'mail' 				=> [] ];



	#https://es.wikipedia.org/wiki/Dominio_de_nivel_superior_gen%C3%A9rico
	private static $gtopLevelDomains	= [ 'gob', 'com', 'org', 'net', 'info', 'biz', 'name', 'pro', 'gov', 'edu', 'mil', 'int', 'aero', 'coop', 'museum', 'cat', 
											'jobs', 'mobi', 'tel', 'travel', 'asia', 'xxx', 'post', 'eus', 'email', 'gal', 'arpa', 'root', 'blogspot' ];
				

	#https://www.ionos.mx/digitalguide/dominios/extensiones-de-dominio/cctld-la-lista-completa-de-dominios-por-pais/
	#private static $domainsByCountry	=  [];

	private static $socialNetworks	= [ 'youtu.be', 'youtube.com', 'facebook.org', 'facebook.com', 'fb.com', 'twitch.org', 'twitter.com', 'twitter.org', 'maps.google',
										'tiktok.com', 'instagram.com', 'telegram.me', 'telegram.com', 'whatsapp.com', 'snapchat.com', 'gmail.com', 'github.com', 'linkedin.com', 'messenger.com' ];


	#son Expresiones que se utilizaran para clasificar las url
	private static $expDocuments	= [ '\.pdf', '\.txt', '\.docx', '\.docm', '\.dotx', '\.dotm', '\.xlsx', '\.xlsm', '\.xltx', '\.xltm', '\.xlsb', '\.xlam', '\.pptx', '\.pptm', '\.potx',
										 '\.potm', '\.ppam', '\.ppsx', '\.ppsm', '\.sldx', '\.sldm', '\.thmx' ];

	private static $expImg		= [ '\.ico', '\.png', '\.BMP', '\.TIFF', '\.jpg', '\.JPEG', '\.GIF', '\.PNG', '\.EPS', '\.SVG', '\.EPS', '\.WebP', '\.heif', '\.psd', '\.ai', '\.xcf', '\.indd' ];
	private static $expCompress	= [ '\.zip', '\.ZIPX', '\.TAR', '\.GZ', '\.RAR', '\.7z', '\.ACE' ];
	private static $expDesign	= [ '\.CSS', '\.JS', '\.eot', '\.woff', '\.ttf', 'fonts.google'];#, 'apis\.', '\.apis', 'api\.', '\.api' 

	

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
		self::putTrashDominos( [ 	'wikipedia.org',
									'netflix.com',
									'wiktionary.org',
									'yahoo.com',
									'office.com',
									'wikipedia.org' ] );


		#Se aplicaron filtros para eliminar links repetidos y dominios que no queremos etc..
		$linksWithFilter = self::filterLinks( $if_extractLinks );


		#se obtiene la keyword del dominio que estamos consultando
		self::putkeywords( self::extractKeyword( self::$urlDomain ) );


		#Se Clasifican los links separandolos por pdf, documentos, links de diseño
		self::saveLinks( self::classifyLinks( $linksWithFilter ) );


		#get matching subdomains
		#return self::$links;

		return self::$links;


	
	}



	# Verifica los diminos que coincidad con el dimino original de dns
	public static function checkDomainsIp( $Dom, $ip ){

		$AipMain = explode('.', $ip);
		$i = 0;

		$domFun = $Dom;

		$domGood 	= [];
		$domBad		= [];
		$domExp 	= [ '2-a8s8d' ];
		$k = [];

		while (  @$domFun[$i] ) 
		{


			#Verifica si un domino ya fue escaneado
			if ( !preg_match("#". self::prepareExp( $domExp ) ."#i", $domFun[$i]) ) { #if 1

				$ipUrl 	=   explode('.',@dns_get_record( $domFun[$i], DNS_A)[0]['ip'] );		

				if ( ($ipUrl[0] == $AipMain[0]) and ($ipUrl[1] == $AipMain[1]) )  #if #2
				{
					#echo "Goooood : ".$domFun[$i]."\n";
					
					$knormal = self::extractKeyword( $domFun[$i] );	
					$k =  array_merge( $k ,   $knormal);

					$f = self::applyKeywordToDomains( $domFun , $knormal );
					$domGood 	= array_merge( $domGood ,$f['good']);
					$domFun 	= $f['bad'];
					$domBad		= $f['bad'];

					$i = 0;
				
				} #if #2
				else
				{

		
					#echo "Baaaaad : ".$domFun[$i]."\n";
					$domExp[] .= $domFun[$i];
				 	$i++;


				} #if #2
			
			} #if #1
			else{

				#echo 'Ya escanee  :  '. $domFun[$i]."\n";
				$i++;

			}#if #1



		}#end while

		if ( empty( $domGood ) )
			return false;

		#echo "\n\n\ncantidad de vueltas realizadas : $i\n";
		return [ 'keywords' => $k, 'subDomains' => $domGood,'links' => $domBad ] ;


	}




	public static function getKeywords(  ){
		return self::$keywords;
	}


	public static function applyKeywordToDomains( $doms, $k ){
		$domGood	= [];
		$domBad		= [];

		
		foreach ($doms as $value) {

			#aqui es donde usa el poder de las keyword
			if ( preg_match("#". self::prepareExp( $k ) ."#i", $value) ) {

				$domGood[] 	.= $value;

			}else{

				$domBad[] .= $value;
			}
			

		}




		$z=  [ 'good' => $domGood,'bad' => $domBad ];
		return $z;
	}





	/*
	*	Se guarda todo  en la variable links
	*/

	public static function saveLinks( $parLinks ){

		self::$links[ 'linksMain' ]			=  array_values( array_unique(	array_merge( self::$links[ 'linksMain' ]		, $parLinks[ 'linksMain' 	])		, SORT_STRING ));
		self::$links[ 'linksSubDomain' ]	=  array_values( array_unique(	array_merge( self::$links[ 'linksSubDomain' ]	, $parLinks[ 'linksSubDomain'])		, SORT_STRING ));
		self::$links[ 'subDomains' ]		=  array_values( array_unique(	array_merge( self::$links[ 'subDomains' ]		, $parLinks[ 'subDomains' ])		, SORT_STRING ));
		self::$links[ 'links' ]				=  array_values( array_unique(	array_merge( self::$links[ 'links' ]			, $parLinks[ 'links' ])			 	, SORT_STRING ));
		self::$links[ 'socialNetwork' ]		=  array_values( array_unique(	array_merge( self::$links[ 'socialNetwork' ]	, $parLinks[ 'socialNetwork' ])		, SORT_STRING ));
		self::$links[ 'linksImg' ]			=  array_values( array_unique(	array_merge( self::$links[ 'linksImg' ]			, $parLinks[ 'linksImg' ])			, SORT_STRING ));
		self::$links[ 'linksDesign' ]		=  array_values( array_unique(	array_merge( self::$links[ 'linksDesign' ]		, $parLinks[ 'linksDesign'])		, SORT_STRING ));	
		self::$links[ 'linksDoc' ]			=  array_values( array_unique(	array_merge( self::$links[ 'linksDoc' ]			, $parLinks[ 'linksDoc' ])			, SORT_STRING ));
		self::$links[ 'linksCompress' ]		=  array_values( array_unique(	array_merge( self::$links[ 'linksCompress' ]	, $parLinks[ 'linksCompress'])		, SORT_STRING ));
		self::$links[ 'linksTrash' ]		=  array_values( array_unique(	array_merge( self::$links[ 'linksTrash' ]		, $parLinks[ 'linksTrash' ])		, SORT_STRING ));
		self::$links[ 'mail' ]				=  array_values( array_unique(	array_merge( self::$links[ 'mail' ]				, $parLinks[ 'mail' ])				, SORT_STRING ));
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
		$socialNet 		= [];



		foreach ($links as $value) {			




			if( preg_match("#". self::prepareExp( self::$socialNetworks ) ."#i", $value) ){

				$socialNet[] .= $value;


			}elseif ( preg_match("#". self::prepareExp( self::$expDesign ) ."#i", $value) ) {
				

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



				if ( preg_match("#^(http|https)#i", $value) ) {
					


					#aqui es donde usa el poder de las keyword
					if ( preg_match("#". self::prepareExp( self::$keywords ) ."#i", $value) ) {
						$linksSubDomain[] 	.= $value;
						$subDomains[]		.= self::extractDomain( $value );

					}else{
						$unknownLinks[] .= $value;#.'-----78';
					}



				}elseif( preg_match("/[a-zA-Z0-9_\-\.]{0,100}@[a-zA-Z0-9_\-\.]+/i", $value) ){

					$mails[] .=  str_replace( 'mailto:', '', $value ); 


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
					'socialNetwork'		=> $socialNet,
					'linksImg' 			=> $linksImg,
					'linksDesign' 		=> $linksDesing,
					'linksDoc' 			=> $linksDocuments,
					'linksCompress' 	=>  $linksCompress,
					'linksTrash' 		=> $linksTrash,
					'mail' 				=> $mails ];


	}



	public static function clearLinks(  ){
		self::$links = [	'linksMain' 		=> [], 
							'linksSubDomain' 	=> [],
							'subDomains' 		=> [],
							'links' 			=> [],
							'socialNetwork'		=> $socialNet,
							'linksImg' 			=> [],
							'linksDesign' 		=> [],
							'linksDoc' 			=> [],
							'linksCompress' 	=> [],
							'linksTrash' 		=> [],
							'mail' 				=> [] ];

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

				#tecmilenioenlinea  

			if ( !preg_match("#^(". self::prepareExp( self::$gtopLevelDomains ) .")$#i", $partDom[ (count($partDom)-2) ]) ) { 
				
				$keywordsDom[] .= $partDom[ (count($partDom)-2) ].'\.';
				$keywordsDom[] .= '\.'.$partDom[ (count($partDom)-2) ];

				#print_r($keywordsDom);	
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

		#QUITANDO LINK REPETIDOS  "PROBLEMA CUANDO EL ARRAY TIENE MAS DE 600 ELEMENTOS" remove repeated
		$linksFun = self::deleteRepeated( $linksFun );

		return $linksFun;

	}

	#Elimina elementos repetidos
	public static function deleteRepeated( $data ){
		return array_unique( $data , SORT_STRING );
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


#$data =  Craw::start( 'https://unam.mx/' );
#$data =  Craw::start( 'https://uvm.mx/' );
#$data =  Craw::start( 'https://une.sonora.gob.mx/' ); # no tiene
#$data =  Craw::start( 'https://tecmilenioenlinea.mx/' );




$data =  Craw::start( 'https://www.unison.mx/' );
#$data =  Craw::start( 'https://www.srm.pr.gov/' );
#$data =  Craw::start( 'https://tec.mx/' );

#hacen lo posible para extraer todo los subdominios de la url que consultamos
$Ds = array_values(  Craw::deleteRepeated( Craw::extractDomain( Craw::$links['links'] ) ) );
$l = Craw::checkDomainsIp( ( $Ds ) , Craw::$ipUrl ); 

Craw::$links[ 'subDomains' ] = array_merge(Craw::$links[ 'subDomains' ], $l[ 'subDomains' ]  );
Craw::$links[ 'links' ] = $l[ 'links' ];


print_r( Craw::$links );

Craw::putkeywords( $l[ 'keywords' ] );



print_r( Craw::getKeywords() );
#print_r( Craw::getKeywords() );
 





/*
$i = 0;
$notWorkPag = [];

echo "Links : ";
while ( true ) 
{


	if (  ( $l = @$data['linksMain'][ $i ] )  and $i <= 175 ) {
		echo ", $i";

		if( $d = Craw::start( $l ) )
		{
			$data = $d;
		}
		else{
			$notWorkPag[] .= $l;
		}

	}else{

		break;
	}

	$i++;


}


echo " FINALIZO ....\n\n";
print_r( $data );
echo "Links no funcionales\n\n";
print_r( $notWorkPag );
*/


#https://book.hacktricks.xyz/pentesting/pentesting-web/php-tricks-esp/php-useful-functions-disable_functions-open_basedir-bypass





?>