<?php 

exec("tasklist 2>NUL", $task_list);

foreach($task_list as $tasks){
	echo $tasks."<br/>";
}

print_r($task_list);

?>