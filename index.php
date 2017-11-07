<html>
<head>
<title>Importing Log files</title>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.min.js"></script>
</head>
<body>

<?php 
if(isset($_POST["submit"])) {
	
	
	$target_dir = "logs/";
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	//echo $target_file;
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded. <br/> Parsing ...";
	} else {
		echo "Sorry, there was an error uploading your file.";
		return;
	}
	
}else{ // else show file uploader

//check if the influxDB is running

?>

<script>
$(document).ready(function(){

	var processName="influxd.exe";
	$.get("checkProcess.php?pn="+processName,function(data,status){
		if(data.includes("false")){
			var msgtext=processName+" is currently NOT running in the server";
			console.log(msgtext);
			$("#msgDisplay").html(msgtext);
		}else{
			var processDetails=data.split(";");
			console.log("Name="+processDetails[0]+",pid"+processDetails[1]+" is running in the server.");
			$("#uploadLogForm").show();
			$("#msgDisplay").html(processName+" is running in the server");
		}
	});
	
});
</script>

<div id="msgDisplay"></div>
<div id="uploadLogForm" style="display:none;">

<form action="#" method="post" enctype="multipart/form-data">
    Select log to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Image" name="submit">
</form>
</div>
<?php 
}

 
if(isset($_POST["submit"])) {
?>

<div>Lines parsed - <b id ="parsedLine"></b></div>
<div id="logs">

</div>


<script>
$(document).ready(function(){


parsingLines(<?php if(isset($_REQUEST['s'])){ echo $_REQUEST['s'];}else{ echo "0";} ?>);

function parsingLines(linesParsed){	
	$.get("parseLogs.php?s="+linesParsed+"&f=<?php echo $target_file;?>", function(data, status){
			$("#parsedLine").html(data);

			console.log(data);
			
				if(data>0){ 
					parsingLines(data);
				}
	 });
}
	
});
</script>

<?php 

}

?>
</body>
</html>

