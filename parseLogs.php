<?php


$linesParsed=0;
$fileName="";
if(isset($_REQUEST['s'])){
	$linesParsed=$_REQUEST['s'];
}
if(isset($_REQUEST['f'])){
	$fileName=$_REQUEST['f'];
	//echo $fileName;
}else{
	echo "No files specified"; return;
}

echo parseRequestLog($linesParsed,$fileName);

function parseRequestLog($linesParsed,$fileName){
	$myfile = fopen($fileName, "r") or die("Unable to open file!");
// Output one line until end-of-file

$lines=array();
$num=0;

$startLine=$linesParsed;

//echo $endIndex; //return 0;

while(true) {
	$line=fgets($myfile);
	
	//skip the lines already parsed
	
	if($startLine>0){
		$startLine--;
		continue;
	}
	
	if(feof($myfile)){
		return "-1";
	}
	
	if($num===1000){  //$num is the number of lines to parse in one go
		return ($linesParsed+$num);
	}
	
	//echo $line;
	//Fetching the Log Id
	$startIndex=strpos($line,"[",0)+1;
	$length=strpos($line,"]",0)-$startIndex;
	$logId=substr($line,$startIndex,$length);
	
	//If found request insert in an array
	if(strpos($line,"->")){	
		$lines[$logId]=$line;
		//echo $line;
		$num++;
		continue;
	}
	
	//If response found - append with the request, insert into influx and delete from array
	if(strpos($line,'<-')){
		//if the key exists, append response with the request
		if(array_key_exists($logId,$lines)){
			$num++;
			$reqRespString=$lines[$logId].substr($line,strpos($line,'<-')+2);
			
			$reqRespString=str_replace("=","-",$reqRespString);
			
			//echo $reqRespString; //break;
			//20/Oct/2017:00:00:05 
			$dtime = @DateTime::createFromFormat("d/M/Y:G:i:s", substr($reqRespString,0,strpos($line,"+")-1));
			$timestamp = $dtime->getTimestamp();
			$timestamp=str_pad($timestamp,19,"0");
			
			$values=explode(" ",str_replace("; ",";",substr($reqRespString,strpos($reqRespString,"[")))); //some cleaning work with the logs

			$url=$values[3];
			if(strpos($url,"?")>0){$url=substr($url,0,strpos($url,"?"));} // stripping off the querystring part
			if(strpos($url,"clientlib")>0){$url=substr($url,0,strpos($url,"."))."...".substr($url,strpos($url,"etc"));} //stripping off the version number part
			
		/**/			
			$influxPostBody="requestLog,id=".$logId
							.",methodType=".$values[2];
			$influxPostBody.=",url=".$url
						   .",connectivity=".trim($values[4]);
			$influxPostBody.=",responseCode=".$values[5].",responseType=".trim($values[6]);
			$influxPostBody.=" responseTime=".trim(str_replace("ms","",$values[7]));
			$influxPostBody.=" ".$timestamp;
		/**/	
			//echo $influxPostBody;
			
			//writing data to influx
			__toInflux("POST",$influxPostBody);
		
			//delete the request from the array
			unset($lines[$logId]);
			unset($reqRespString);
			unset($timestamp);
			
			
		}
		
	}
}
fclose($myfile);
return $num;
}

function __toInflux($methodType,$influxPostBody){	
	
	//	echo $influxPostBody;
	
	// Create a stream
	$opts = array(
			'http'=>array(
					'method'=>$methodType,
					'header'  => "Content-type: application/x-www-form-urlencoded",
					'content' => $influxPostBody
			)
	);
	//print_r($opts);
	
	$context = stream_context_create($opts);
	
	//print_r($context);
	
	// Open the file using the HTTP headers set above
	try{
	$file = file_get_contents("http://localhost:8086/write?db=logs",false, $context);
	}catch(Exception $e){
		echo $e->getMessage();
		break;
	}
}

?>