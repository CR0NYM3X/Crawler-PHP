<?php


#tiempo de ejecucions
set_time_limit(0);


class facilcurl 
{
	
	//- //cabeceras por defaul si no se cabian con la funcion  cabeceras()
	private $varCabeceras=array("Connection: keep-alive", /*"Connection: close"*/
								"Cache-control: no-cache",
								"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
								"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3"); 


	private $session; //donde se almacenA la session curl
	private $data_post=array(); // datos que se enviaran por post
	private $userAgents=array();
	private $type_proxy=array("CURLPROXY_HTTP","CURLPROXY_SOCKS4","CURLPROXY_SOCKS5","CURLPROXY_SOCKS4A","CURLPROXY_SOCKS5_HOSTNAME"); //tipos de proxys
	private $curlexitoso; // se guarda la pagina si es que fue exito y la retorna
	private $error_curl=null;


	// USER-AGENT variables de navegadores a utilizar - todos los user agent: http://www.useragentstring.com/pages/useragentstring.php
	private $navegadores = array(	array("Mozilla/5.0 (Windows NT 6.3; rv:54.0) Gecko/20100101 Firefox/54.0",
		                        "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:53.0) Gecko/20100101 Firefox/53.0", 
							 	"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0",
							 	"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0", 
							 	"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0", 
							 	"Mozilla/5.0 (Windows NT 6.1; rv:31.0) Gecko/20100101 Firefox/31.0",
							 	"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:26.0) Gecko/20100101 Firefox/26.0",
							 	"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0",
							 	"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0",
							 	"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0",
							 	"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0"),

										//chrome
						array( "Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36",
								"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36",
								"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.11 (KHTML like Gecko) Chrome/23.0.1271.95 Safari/537.11",
								"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36",
								"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/35.0.1916.153 Safari/537.36",
								"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/37.0.2062.120 Safari/537.36",
								"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/36.0.1985.143 Safari/537.36",
								"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML like Gecko) Chrome/31.0.1650.63 Safari/537.36",
								"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/36.0.1985.143 Safari/537.36",
								"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/35.0.1916.114 Safari/537.36"),
						


						//internet explorer 11
						array( "Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
								"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
								"Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0",
								"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)",
								"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
								"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)",
								"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
								"Mozilla/5.0 (compatible; MSIE 10.0; Macintosh; Intel Mac OS X 10_7_3; Trident/6.0)",
								"Mozilla/4.0 (Compatible; MSIE 8.0; Windows NT 5.2; Trident/6.0)",
								"Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko"),

						//operamini
						array( "Opera/9.80 (X11; Linux i686; Ubuntu/14.10) Presto/2.12.388 Version/12.16",
								"Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14",
								"Mozilla/5.0 (Windows NT 6.0; rv:2.0) Gecko/20100101 Firefox/4.0 Opera 12.14",
								"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0) Opera 12.14",
								"Opera/12.80 (Windows NT 5.1; U; en) Presto/2.10.289 Version/12.02",
								"Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00",
								"Opera/9.80 (Windows NT 5.1; U; zh-sg) Presto/2.9.181 Version/12.00",
								"Opera/12.0(Windows NT 5.2;U;en)Presto/22.9.168 Version/12.00",
								"Mozilla/5.0 (Windows NT 5.1) Gecko/20100101 Firefox/14.0 Opera/12.0",
								"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0) Opera 12.14"),
						
						
							//Safari browser
						array( "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9",
								"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A",
								"Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25",
								"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2",
								"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10",
								"Mozilla/5.0 (iPad; CPU OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko ) Version/5.1 Mobile/9B176 Safari/7534.48.3",
								"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1",
								"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1",
								"Mozilla/5.0 (Windows; U; Windows NT 6.1; tr-TR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
								"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; ja-jp) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27"));


	# MODIFICA LAS CABECERAS 
	public function cabeceras($cabecerasArray)
	{
		$this->varCabeceras=$cabecerasArray;
	}


	# AGREGAMOS UN TXT DE USERS AGENT POR DEFECTO USA LOS USER AGENTS QUE US SQLMAP
	public function put_userAgents($ruta="files/user-agents.txt")
	{

		if($ruta=="files/user-agents.txt")
		{
			if(file_exists($ruta))
			{
				$Data_useragent = fopen($ruta, "r"); // solo lectura
			}
			else
			{
				die("no existe el txt de [USER-AGENT]");
			}
		}
		else
		{
			if(file_exists($ruta))
			{
				$Data_useragent = fopen($ruta, "r"); // solo lectura
			}
			else
			{
				die("no existe el txt de [USER-AGENT] espesificado");
			}
		}


		while ($linea = fgets($Data_useragent))
		{
		    if($linea!="\r\n")
		    {   // es es igual aa 0x0d0a
		    	array_push($this->userAgents, preg_replace("[\s+]","",$linea));
	    	}
	    }



	    fclose($Data_useragent);



	
	}



	# SE PONE LA URL EN LA CURL
	public function url($url=NULL,&$error=null)
	{
		if (isset($url))
		 {
			curl_setopt($this->session, CURLOPT_URL,  $url);  //para identificar una url
		}
			else
		{
			echo "Introdusca una url";
		}
		
	}



	# AQUI PUEDES IMPORTAR UN ARCHIVO CON LA COOKIE
	public function cookie($var=1,$nombre="cookie",&$error=null)
	{
		if($var==1 or $var==true)
		{
		curl_setopt($this->session, CURLOPT_COOKIESESSION, true ); // TRUE para usarse como una nueva cookie de "sesión"
		curl_setopt($this->session, CURLOPT_COOKIEJAR,  getcwd() . "/".$nombre.".txt");  // Nombre del fichero donde guardar cookies internas cuando se cierra se cierra, por e.j. después de llamar a curl_close. 
 		curl_setopt($this->session, CURLOPT_COOKIEFILE,  getcwd() . "/".$nombre.".txt");  // Nombre del fichero que contiene datos de las cookies.
 		}
	}




	# PERMITE HABILITAR EL SSL PERO YA BIENE DESACTIVADO POR DEFECTO
	public function ssl($ssl=1,&$error=null)
	{
		if($ssl==1)
		{
			//certificados-ssl- https://curl.haxx.se/docs/caextract.html
			curl_setopt($this->session, CURLOPT_SSLVERSION, 0);  // por default para identificar las verciones de ssl
			curl_setopt($this->session, CURLOPT_SSL_VERIFYPEER,  true); // FALSE para que cURL no verifique el peer del certificado. 
			curl_setopt($this->session, CURLOPT_SSL_VERIFYHOST, 2);  //  2 (valor predeterminado). 
			//curl_setopt($this->session, CURLOPT_SSLCERT, file_get_contents(getcwd() . "\cacert2.pem")); //Nombre del fichero que contiene un certificado con formato PEM. 
			curl_setopt($this->session, CURLOPT_CAINFO,  getcwd() . "files/certificado.crt"); // para poner las credenciarles Nombre del fichero que contiene uno o más certificados para verificar el peer
		}
		elseif($ssl==0)
		{
			curl_setopt($this->session, CURLOPT_SSL_VERIFYPEER,  FALSE);
			
		}
		else
		{
			die("El valor que ingreso en SSL es incorrecto, [ 1 para true o 0 Para false ]");
		}

	}



	# MODIFICA EL TIPO DE PETICION

	public function peticiones($peticiones=0,&$error=null)
	{
		switch ($peticiones) 
		{
			case 0:
				curl_setopt($this->session, CURLOPT_CUSTOMREQUEST, "POST"); // Método de petición personalizado - GET", "POST", "CONNECT",Put, Delete		
				break;
			case 1:
				curl_setopt($this->session, CURLOPT_CUSTOMREQUEST, "GET");

				break;
			case 2:
				curl_setopt($this->session, CURLOPT_CUSTOMREQUEST, "PUT");
				break;
			case 3:
				curl_setopt($this->session, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;

			default:
				echo "peticiones No Reconocidas 0=POST, 1=GET, 2=PUT, 3=DELETE ";
				break;
		}

	}


	public function navegador($navegador=1,&$error=null)
	{
		
		switch ($navegador)
		 {
			case 0:
				$navegadorRand= rand(0,(count($this->navegadores)-1));
				$versionNavegador= rand(0,(count($this->navegadores[$navegadorRand])-1));

				curl_setopt($this->session, CURLOPT_USERAGENT,$this->navegadores[$navegadorRand][$versionNavegador]); // identificar el navegador ausar
				break;
			case 1:
				curl_setopt($this->session, CURLOPT_USERAGENT,$this->navegadores[0][0]);
				break;
			case 2:
				curl_setopt($this->session, CURLOPT_USERAGENT,$this->navegadores[1][0]);
				break;
			case 3:
				curl_setopt($this->session, CURLOPT_USERAGENT,$this->navegadores[2][0]);
				break;
			case 4:
				curl_setopt($this->session, CURLOPT_USERAGENT,$this->navegadores[3][0]);
				break;
			case 5:
				curl_setopt($this->session, CURLOPT_USERAGENT,$this->navegadores[4][0]);
				break;
			case 6:
				curl_setopt($this->session, CURLOPT_USERAGENT,$this->navegadores[5][0]);
				break;
			
			default:
				echo "No se encontro el numero de navegador [Favor de solicitar la informacion de navegadores]";
				break;
		}
		
	}


	public function puerto($puerto=80)
	{
		
		curl_setopt($this->session, CURLOPT_PORT, $puerto); //Puerto alternativo a conectarse 

	}


	function max_location($max_location=5)
	{
		curl_setopt($this->session, CURLOPT_MAXREDIRS , $max_location);// para establecer un maximo de redirecciones location
	}


	function tiempos($timeout=120,$timeconnect=0)
	{

		curl_setopt($this->session, CURLOPT_TIMEOUT, $timeout); // Número máximo de segundos permitido para ejectuar funciones cURL. 
		curl_setopt ($this->session, CURLOPT_CONNECTTIMEOUT , $timeconnect);// Número de segundos a esperar cuando se está intentado conectar. Use 0 para esperar indefinidamente.

	}


	public function proxy($ip,$puerto=null,$tipo_proxy=0,&$error=null,$usuario=null,$password=null)
	{

		curl_setopt($this->session, CURLOPT_PROXY, $ip); //El proxy HTTP para enviar peticiones a través de tunel. 	
		if(isset($puerto)){curl_setopt($this->session, CURLOPT_PROXYPORT, $puerto);}// para asignar el puerto del proxy
		
		curl_setopt($this->session, CURLOPT_HTTPPROXYTUNNEL, TRUE);//TRUE para usar un tunel a través de un proxy HTTP. 
		
		if(isset($usuario) and isset($password))
		{
			curl_setopt($this->session, CURLOPT_PROXYUSERPWD, "$usuario:$password");  //si el proxy nesesita contraseña
		}
		switch ($tipo_proxy) {
			case 0:
				curl_setopt($this->session, CURLOPT_PROXYTYPE , $this->type_proxy[0]);// Puede ser CURLPROXY_HTTP (por defecto), CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A o CURLPROXY_SOCKS5_HOSTNAME. 
				break;
			case 1:
				curl_setopt($this->session, CURLOPT_PROXYTYPE , $this->type_proxy[1]);
				break;
			case 2:
				curl_setopt($this->session, CURLOPT_PROXYTYPE , $this->type_proxy[2]);
				break;
			case 3:
				curl_setopt($this->session, CURLOPT_PROXYTYPE , $this->type_proxy[3]);
				break;
			case 4:
				curl_setopt($this->session, CURLOPT_PROXYTYPE , $this->type_proxy[4]);
				break;
			
			default:
				echo "No se encontro el tipo_proxy solo existen 0=CURLPROXY_HTTP ,1= CURLPROXY_SOCKS4	,2= CURLPROXY_SOCKS5 ,3= CURLPROXY_SOCKS4A ,4 CURLPROXY_SOCKS5_HOSTNAME. ";
				break;
		}
	}


	public function datos_post($variable,$contenido)
	{
		$this->data_post[$variable]=urldecode($contenido);
	}


	public function enviar_post()
	{
		curl_setopt($this->session, CURLOPT_POSTFIELDS,http_build_query($this->data_post));  //es la informacion la cuarl se enviar por post
	}


	public function datos_json($contenidoJSON)
	{
		$this->data_post=$contenidoJSON;
		$this->varCabeceras=array('Content-Type: application/json','Content-Length: ' . strlen($this->data_post), "Connection: close", "Cache-control: no-cache" );
		#curl_setopt($this->session, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($this->data_post), "Connection: close","Cache-control: no-cache" ));
	}


	public function enviar_json()
	{
		curl_setopt($this->session, CURLOPT_POSTFIELDS,$this->data_post);  //es la informacion la cuarl se enviar por post
	}



	// POSIBLE EVASION DE WAF - INFO : // https://www.ionos.es/digitalguide/hosting/cuestiones-tecnicas/cabecera-http/
	function evasion_waf()
	{
		// ESTAS OPCIONES LAS UTILIZA NMAP PARA LA EVASION DE POSIBLES WAFS
		$this->varCabeceras[].="x-originating-IP: 127.".rand(1,254).".".rand(1,254).".".rand(1,254);
		$this->varCabeceras[].="X-Client-IP: 127.".rand(1,254).".".rand(1,254).".".rand(1,254);
		$this->varCabeceras[].="Remote_Addr: 127.".rand(1,254).".".rand(1,254).".".rand(1,254);
		$this->varCabeceras[].="X-Server-IP: 127.".rand(1,254).".".rand(1,254).".".rand(1,254);
		$this->varCabeceras[].="X-ProxyUser-Ip: 127.".rand(1,254).".".rand(1,254).".".rand(1,254);
		$this->varCabeceras[].="X-Forwarded-For: 127.".rand(1,254).".".rand(1,254).".".rand(1,254);
		//$this->varCabeceras[].="Proxy-Authorization: Proxy-Authorization: Basic WjbU7D25zTAlV2tZ7=";
		//$this->varCabeceras[].="Proxy-Authenticate: Basic";
		
	}





	/*
	#esta opcion esta en procesos de actualizacion que permitira mandar post serializado
	public function datos_serializado($contenido)
	{
		serialize($contenido);
	}

	public function enviar_serializado()
	{
	
	}
	*/



	public function curl($url,$cookie=null,$ssl=0,$peticiones=null,$navegador=0,$puerto=null,$max_location=null,$timeout=null,$timeconnect=null)
	{

		if (isset($url))
		{
			$this->url($url); //mandando llamar metodo url

			if(is_array(isset($cookie)))
			{
				$this->cookie($cookie[0],$cookie[1]);//mandando llamar metodo cookie
			}
			elseif(isset($cookie))
			{
				$this->cookie($cookie);//mandando llamar metodo cookie
			}
			
			$this->ssl($ssl);  //mandando llamar metodo ssl

			if(isset($peticiones))
			{
				$this->peticiones($peticiones); //mandando llamar metodo peticiones
			}
			

			if( empty($this->userAgents) )
			{
				$this->navegador($navegador);//mandando llamar metodo navegador
			}
			else
			{
				curl_setopt($this->session, CURLOPT_USERAGENT,$this->userAgents[ rand( 1,(count($this->userAgents)-1) ) ]);
			}


			if(isset($puerto))
			{
				$this->puerto($puerto);//mandando llamar metodo puerto
			}
			
			if(isset($max_location))
			{
				$this->max_location($max_location); //mandando llamar metodo location
			}
			
			if(isset($timeout) and !isset($timeout))
			{
				$this->tiempos($timeout,$timeconnect); //mandando llamar metodo tiempos
			}
			
			
			curl_setopt($this->session, CURLOPT_HTTPHEADER, $this->varCabeceras); //enviar cabeseras
			curl_setopt($this->session, CURLOPT_FOLLOWLOCATION, true); // permitir los location: si nos manda a otra paginas
			curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true); // TRUE para devolver el resultado de la transferencia como string del valor de curl_exec() 
			curl_setopt($this->session, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE); // version por defaults, para identificar la verciones de http
			curl_setopt($this->session,  CURLOPT_ENCODING, "gzip,deflate,sdch" );//si se deja en "" maneja todas las codificaciones se pone basio para que soporte todas las codificaciones: es mejor -> [gzip,deflate,sdch]
			curl_setopt($this->session, CURLOPT_FAILONERROR, true); // si hay un mensaje de error mayor de 400 se ignorara el codigo
			curl_setopt($this->session, CURLINFO_HEADER_OUT, true);	 #habilitra para ver como esta mandando las cabeceras al servidor
			//$this->proxy("127.0.0.1:8080");
			

		}
		else
		{
			return false;
		}

	}




	#sirve para ver como esta mandando las cabeceras al servidor [NOTA]: se debe de poner despues exe_curl() y retorna la cabeceras
	public  function header_out()
	{
		return curl_getinfo($this->session,CURLINFO_HEADER_OUT);
	}



	#sirve para ver las header=cabeceras que contesta el servidor;
	/*Ejemplo 
	HTTP/1.1 200 OK
	Host: 127.0.0.1
	Connection: close
	X-Powered-By: PHP/7.0.21
	Content-type: text/html; charset=UTF-8*/

	//
	public function header_in()
	{
		curl_setopt($this->session, CURLOPT_HEADER, true); 
		curl_setopt($this->session, CURLOPT_NOBODY, true);
	}


	#desabilita esta opcion y ya no retornara nomas las cabeceras
	public function header_desability()
	{
		curl_setopt($this->session, CURLOPT_HEADER, false); 
		curl_setopt($this->session, CURLOPT_NOBODY, false);
	}




	# DONDE MANDA LLAMAR TODO
	public function exe_curl(&$info=null,&$error=null)
	{


		if(($this->curlexitoso=curl_exec($this->session)) === false)
		{
    		if(!isset($error))
    		{
    	   		$errror=array(
    	   						curl_error($this->session)/*Mostrara en pantalla todos los errores que se realizaron*/,
    	   						curl_getinfo($this->session)/*para obtener la info de la url*/
    	   					);
    	 	}
    		return false;    		
		}
		else
		{
			 
			 if(!isset($info))
			 {
			 	$info=curl_getinfo($this->session);
			 }

			 return $this->curlexitoso;
		}
	}






	//  metodo constructor el cual inicia siempre cuando la instancias.
	function __construct()
	{
		$this->session=curl_init();
	}



	//metodo de destructor si ya no mandan a hablar a ningun metodo se ejecuta el contenido de destructor
	function __destruct()
	{

		curl_close($this->session); //cerrando la session de curl
		
		// liberando memoria borrando las variables
	 	unset($this->session);
		unset($this->data_post);
		unset($this->type_proxy);
		unset($this->datos_post);
		unset($this->navegadores);
		unset($this->curlexitoso);

	}

}





/*
$mm= new facilcurl();

$mm->cabeceras(["Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3"]); // si no se ponen usara unas por default
$mm->evasion_waf(); // esta opcion solo funciona si se pone debajo del metodo de cabecera() o almenos que no se ponga
//$mm->proxy("127.0.0.1",8080); 


$mm->put_userAgents("files/user-agents.txt");  // POR DEFAUL UN TXT : files/user-agents.txt


$mm->curl("https://api.db-ip.com/v2/free/187.158.8.19"); 

if ( ($pag= $mm->exe_curl()) ) {

	print_r($mm->header_out()); // cabeceras
	print_r($pag); // contenido de la pagina 
	echo "\n La pagina se ejecuto con exito \n";
}
else
{
	echo "\n Erorr \n";
	print_r($pag);
}

*/




/*


 //mini ejemplo
$mm= new facilcurl();
curl($url,$cookie=null,$ssl=0,$peticiones=null,$navegador=1,$puerto=null,$max_location=null,$timeout=null,$timeconnect=null)

$mm->curl("https://www.cual-es-mi-ip.net/",0,0,1,1,443,5,120,0); //tambien se pude asi $mm->curl("https://www.cual-es-mi-ip.net/");
//$mm->proxy("52.163.63.65",3128); // tambien se puede asi : 52.163.63.65:3128

print_r($mm->exe_curl());

*/

#$mm= new facilcurl();
#$mm->curl("https://cse.google.com/cse?cx=partner-pub-6779091984393228%3A69q7uss5stz&ie=GB2312&q=php?id=#gsc.tab=0&gsc.q=php?id=&gsc.page=1");
#print_r($mm->exe_curl());

/*


################################# Utilizando el metodo curl #####################################
 metodo   curl($url,$cookie=null,$ssl=0,$peticiones=null,$navegador=1,$puerto=null,$max_location=null,$timeout=null,$timeconnect=null)
url 
	:Es donde se pondra la url

cookie
	array(Estado, $nombre_de_cookie)
	
	estados:
	0=false
	1=true
	:normalmente esta en false

ssl	
	0=false
	1=true
	:Normalmente esta en false

peticiones
	0=POST
	1=GET
	2=PUT
	3=DELETE
	:Son los tipos de peticiones que se pueden utilizar

navegador
	0=Navegador_RANDOM	
	1=FIREFOX
	2=CHROME
	3=INTERNET EXPLORER
	4=OPERAMINI
	5=SAFARI
	:Normalmente esta activado el firefox

PUERTO:
	:por defecto tiene el puerto 80 pero se puede cambiar
	:si no se pone su valor, no se cargan la funcion y no se carga a la sesion

max_location
	:por defecto estan 5 location pero se puede cambiar
	:si no se pone su valor, no se cargan la funcion y no se carga a la sesion

timeout
	:por defecto esta a 120 milisegundos pero se puede cambiar
	:si no se pone su valor, no se cargan la funcion y no se carga a la sesion

connection = 
	:por defecto esta en 0 para que tarde lo que quiera en cargar la pagina
	:si no se pone su valor, no se cargan la funcion y no se carga a la sesion
######################################################################

###################################### CABECERAS ################

cabeceras(array("Connection: keep-alive", "Cache-control: no-cache"));

##########################################################



################################ Ejecutar la curl ######################################

exe_curl(&$info=null,&$error=null)

######################################################################

######################### PARA UTILIZAR PROXY EN CURL#############################################
proxy proxy($ip,$puerto,$tipo_proxy=0,$usuario=null,$password=null)
	DEFAUL=httpproxy=0
	1= CURLPROXY_SOCKS4	
	2= CURLPROXY_SOCKS5
	3= CURLPROXY_SOCKS4A 
	4 CURLPROXY_SOCKS5_HOSTNAME. 
######################################################################




############################ DATOS POR MANDAR POR POST ##########################################
datos_post(variable,contenido);
enviar_post();
######################################################################


############################ DATOS POR MANDAR POR POST en JSON##########################################
datos_post(contenidoJSON);
 enviar_json();
######################################################################



############################ header_out() ##########################################

	#sirve para ver como esta mandando las cabeceras al servidor
	header_out();
######################################################################


############################## header_in() ########################################
	sirve para ver las header=cabeceras que contesta el servidor;
	/*Ejemplo 
	HTTP/1.1 200 OK
	Host: 127.0.0.1
	Connection: close
	X-Powered-By: PHP/7.0.21
	Content-type: text/html; charset=UTF-8
	
	header_in();
######################################################################




*/
?>