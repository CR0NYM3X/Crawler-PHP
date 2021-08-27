<?php 
require_once("class_curl/facilcurl.php"); 

#sobre expresiones regulares: https://desarrollowp.com/blog/tutoriales/buscando-patron-con-expresiones-regulares/

function extUrl( $url ){

	$linksTotal = [];
	$mm = new facilcurl();
	$mm->curl($url,null,0,null,1);
	$dominio = dominioMain( $url, true );


	if ( $pag = $mm->exe_curl() ) {
		
		#Extraccion de links
		preg_match_all('#src="(.*?)"#i', $pag , $linksSrc);
		preg_match_all('#href="(.*?)"#i', $pag , $linksHref);
		preg_match_all('#action="(.*?)"#i', $pag , $linksAction);
		preg_match_all("#url\(\'(.*?)\'\)#i", $pag , $linksUrl); 
		preg_match_all("#url\((.*?)\)#i", $pag , $linksUrl2);
	#	preg_match_all("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/\S*)?/i",$pag , $links);


		#uniendo los array 
		$linksTotal = array_merge($linksTotal, $linksSrc[1]);
		$linksTotal = array_merge($linksTotal,  $linksHref[1]);
		$linksTotal = array_merge($linksTotal, $linksAction[1]);
		$linksTotal = array_merge($linksTotal, $linksUrl[1]);
		$linksTotal = array_merge($linksTotal, $linksUrl2[1]);


	#	$links = str_replace(array("\">","\""), array("",""), $links[0]);

		#fuciona 2 array
	#	$linksTotal = array_merge($linksTotal, $links);

	
		#Eliminando dominios conocidos
		$linksTotal = preg_grep("#github.com|google.com|www.google|youtu.be|youtube.com|wikipedia.org|facebook.org|facebook.com|fb.com|wikipedia.org|twitch.org|twitter.com|twitter.org|tiktok.com|instagram.com|netflix.com|wiktionary.org|yahoo.com#i",$linksTotal,PREG_GREP_INVERT);


		#eliminando caracteres inservibles
		$linksTotal = str_replace( ["'"], [""],  $linksTotal);


		#QUITANDO LINK REPETIDOS  "PROBLEMA CUANDO EL ARRAY TIENE MAS DE 600 ELEMENTOS"
		$linksTotal = array_unique( preg_replace("[\s+]", "", $linksTotal), SORT_STRING );


		#Elimina elementos vacios o nulos del array
		$linksTotal = array_filter($linksTotal);

	
	}
	else
	{
		echo "No se imprimio \n";

	}





	$l = clasificarDom( $linksTotal, dominioMain($url) );





	$p =  checkLinks( [dominioMain( $url )], $l['links'] );# aqui verifica si hay links que coincidad con la url main
	$l['domain'] = $p['domain'];
	$l['linkDomain'] = $p['linkGood2'];
	$l['links'] = $p['linkBad2'];



	$d = wantDomain( $l['links'] );  #obtiene los pueros dominios de links sin repetir dominios
	$domcheck = checkDomain( $d ,$dominio ); #checa los dominios y verifica que sean similar a la ipDns del dommain 
	$l['domain'] = array_merge($l['domain'], $domcheck['domGood']);



	print_r( $l   );





/*
	$statusDom = domOnLink( $l['links'] , $domcheck['domGood'] ); 
	$l['links'] = $statusDom['linkBad'];
	$l['linkDomain'] = $statusDom['linkGood'];




	$linksCheck =  checkLinks( $l['domain'], $l['links'] );


	$l['domain'] = array_merge($l['domain'], $linksCheck['domain']);
	$l['linkDomain'] = array_merge($l['linkDomain'], $linksCheck['linkGood2']);
	$l['links'] = $linksCheck['linkBad2'];

*/


}




function checkLinks( $dom, $links ){

	$exprDom = [];
	$linkGood2 =[];
	$linkBad2 =[];
	$domain 	=[];

	foreach ($dom as $value) {
		$partDom = explode('.', $value);
		$exprDom[] .=  $partDom[ (count($partDom)-2) ].'\.' ;

	}

	$exprDom = array_unique( $exprDom, SORT_STRING );
	$exprDom = implode('|',  $exprDom );


	 foreach ($links as $key => $value) {
	 	
	 	if ( preg_match("#".$exprDom."#i", $value) ) {
	 		$linkGood2[] .= $value;
	 	}
	 	else
	 	{
	 		$linkBad2[] .= $value;
	 	}


	 }




	  return [ 'linkGood2' => $linkGood2, 'linkBad2' => $linkBad2, 'domain' => wantDomain( $linkGood2 ) ] ;


}






function domOnLink( $links , $dom ){

	$expr = '';
	$linkGood	= [];
	$linkBad	= [];

	foreach ($dom as $value) {
		$expr .= '|'.$value;
	}

	$expr = substr($expr, 1);


	foreach ($links as  $value) {

		if ( preg_match("#".$expr."#i", $value) ) {
			$linkGood[] .= $value;
		}
		else
		{
			$linkBad[] .= $value;
		}

	}

	return [ 'linkGood' => $linkGood ,'linkBad' => $linkBad  ] ;

}








function clasificarDom( $Dom, $DomMain ){


	$img 		= [];
	$docFile 	= [];
	$designFile = [];
	$compresFile =[];
	$nada		=[];
	$linkMain	=[];
	$links 		=[];
	$trash		=[];
	$correos	=[];





	foreach ( $Dom as $key => $value ) {
		

		if ( preg_match("#\.CSS|\.JS|\.eot|\.woff|\.ttf#i", $value) ) { #diseno

			if ( preg_match("#".$DomMain."#i", $value) ) {
				array_unshift($designFile, $value);
			}else{
				$designFile[] .=  $value;
			}




		}elseif ( preg_match("#\.ico|\.png|\.BMP|\.TIFF|\.jpg|\.JPEG|\.GIF|\.PNG|\.EPS|\.SVG|\.EPS|\.WebP|\.heif|\.psd|\.ai|\.xcf|\.indd#i", $value) ) { # imagenes
			
			if ( preg_match("#".$DomMain."#i", $value) ) {
				array_unshift($img, $value);
			}else{
				$img[] .=  $value;
			}


		}elseif( preg_match("#\.pdf|\.txt|\.docx|\.docm|\.dotx|\.dotm|\.xlsx|\.xlsm|\.xltx|\.xltm|\.xlsb|\.xlam|\.pptx|\.pptm|\.potx|\.potm|\.ppam|\.ppsx|\.ppsm|\.sldx|\.sldm|\.thmx#i", $value) ){ #documentos
			
			if ( preg_match("#".$DomMain."#i", $value) ) {
				array_unshift($docFile, $value);
			}else{
				$docFile[] .=  $value;
			}


		}elseif( preg_match("#\.zip|\.ZIPX|\.TAR|\.GZ|\.RAR|\.7z|\.ACE#i", $value) ){ #file de compresion

			if ( preg_match("#".$DomMain."#i", $value) ) {
				array_unshift($compresFile, $value);
			}else{
				$compresFile[] .=  $value;
			}


		
		}else{ #nada
			$nada[] .=  $value;

		}
	}



	foreach ($nada as $key => $value) {

		#Varificando que sean links que empiecen con http
		if ( preg_match("#https|http#i", $value) ) {

			#verificando que el link sea del domino que estamos buscando
			

			if ( preg_match("#\/".$DomMain."#i", $value) ) {
				$linkMain[] .= $value;

			}
			else
			{
				$links[] .= $value;
			}
		

		}
		else
		{
			
			if ( !preg_match("#\#|\@#i", $value) ) {
				
				if ( preg_match("#^[a-z]#i", $value) ) {
					$linkMain[] .= 'https://'.$DomMain.'/'.$value;

				}elseif ( preg_match("#^[/]#i", $value) ) {
					
					$linkMain[] .= 'https://'.$DomMain.$value;

				}elseif( preg_match("#^[\?]#i", $value) ){
					$linkMain[] .= 'https://'.$DomMain.'/'.$value;
				}else{
					$trash[] .= $value;
				}



			}
			else
			{

				if( preg_match("/[a-zA-Z0-9_\-\.]{0,100}@[a-zA-Z0-9_\-\.]+/i", $value) ){
					$correos[] .= $value;
				}
				else{
					$trash[] .= $value;

				}
				
				
			}


		}

		
	}




		return [ 'linkMain' =>$linkMain, 'linkDomain' => [], 'domain' => [] ,'links' => $links , 'img' => $img, 'design' => $designFile, 'doc' => $docFile, 'compres' => $compresFile, 'trash' => $trash, 'mail' => $correos ];


}



function wantDomain( $doms ){

	$domOnly = []; 

	foreach ($doms as $key => $value) {
		$domOnly[] .= dominioMain( $value ); 
	}

	return array_unique( $domOnly, SORT_STRING );



}







# Verifica los diminos que coincidad con el dimino original de dns
function checkDomain( $Dom, $ip ){


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








function onlyDomain( $Dom ){
	$onlyDom = [];


	foreach ($Dom as $key => $value) {
		preg_match_all('#(http|https|ftp|ftps)\://(.*?)/#i', $value."/" , $result);
		$onlyDom[].= $result[2][0];
	}


	return array_unique( $onlyDom, SORT_STRING );
}





function dominioMain( $dom, $dns = false){
	
	preg_match_all('#(http|https|ftp|ftps)\://(.*?)/#i', $dom."/" , $result);

	if( $dns )
		return ( !empty( $r = @dns_get_record($result[2][0],DNS_A)[0]['ip'] ) ) ? $r : false ;
	

	return $result[2][0];

}






	print_r(  extUrl( "https://blog.segu-info.com.ar/"  ) );



	/*
mandar una lista de dominios en array para que pueda eliminarlos y no repetirlos
mandar todo el array completo

diferenciar links de mismo dominio


obtener rutas

	*/

#https://neobuy2u.com/wp-login.php?redirect_to=https%3A%2F%2Fneobuy2u.com%2Fwp-admin%2Fpost.php%3Faction%3Dedit%26post%3D3&reauth=1

?>



