<?php

if(isset($_REQUEST['pn'])){
	checkProcesses($_REQUEST['pn']);
}else{
	checkProcesses("all");
}
function checkProcesses($processName){
	exec("tasklist 2>NUL", $task_list);
	
	// if all the processes needs to be returned
	
	//if($processName==="all"){return $task_list;}
	
	// to check if the input process name is running in the server
	
	foreach ($task_list as $task){
		//echo $tasks."<br/>";
		if($task==$processName){
			echo $processName." is running in the server";
		}else{
			echo $processName." is NOT running in the server";
		}
	}
	
	
	//print_r($task_list);
}
?>