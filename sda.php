<html>
<center>
<h1>Saibateku Developer Application</h1>
<hr>
<a href="?task=1">Task 1</a><br />
<a href="?task=2">Task 2</a><br />
<a href="?task=3">Task 3</a><br />
<form action="" method="GET">Task 4: <input type="hidden" name="task" value="4"></input><input type="text" name="q"></input><input type="submit" value="Search"></input></form>
<a href="?task=5">Task 5 &amp; 6</a><br />
<a href="?task=7">Task 7 &amp; 8</a><br />
<a href="?task=9">Task 9</a><br />
<form action="" method="GET">Task 10: <input type="hidden" name="task" value="10"></input><input type="text" name="q"></input><input type="submit" value="Search"></input></form>
<hr>
</center>
</html>
<?php

//Saibateku Developer Application by Kamil

if(!isset($_GET['task'])) { exit; } // In case there is no input...
error_reporting(0);

//Required Configuration
$sqlhostname = "localhost";
$sqldatabase = "sda";
$sqluser = "sda";
$sqlpassword = "password123";

//Task 1
$bunchawords = array("lorem","ipsum","dolor","sit","amet","consectetuer","adipiscing","elit","sed","diam","nonummy","nibh","euismod","tincidunt","ut","laoreet","dolore","magna","aliquam","erat","volutpat","ut","wisi","enim","ad","minim","veniam","quis","nostrud","exerci","tation","ullamcorper","suscipit","lobortis","nisl","ut","aliquip","ex","ea","commodo","consequat");
file_put_contents("array.dat", serialize($bunchawords)); //Save array to disk

//Task 2
$array = unserialize(file_get_contents("array.dat")); //Retrieve array from disk and store it back in an array
sort($array);

switch ($_GET['task']) {
	case "1":
		echo "Task 1 was already completed during the initialization of this script. However, here is the array, not yet in alphabetical order.<br />\n<pre>";
		foreach($bunchawords as $word) {
			echo $word."\n";
		}
		echo "</pre>";
	break;
	case "2":
		echo "Task 2 was alrady completed during the initialization of this script. It was loaded into a variable named \"array\". However, go to task 3 and you will be able to view the contents of the array.";
	break;
	case "3":
		//Task 3
		foreach($array as $word) {
			echo $word."<br />\n";
		}
	break;
	case "4":
		//Task 4
		$query = (empty($_GET['q'])) ? die("Fail: You did not specify a query. Failing out.") : $_GET['q']; // ternary yes
		$search = array_search(strtolower($query), $array);
		$message = ($search === false ? 'Fail: Your search "' . $query. '" did not match any results.' : 'Success: '.$query.' found in key ' . $search);
		// Bug Fixed: I used the === operator due to a previous bug was where if the query was found in key 0, it would fail due to the 0 evaluating to FALSE.
		echo $message . "<br />";
	break;
	case "5":
		//Task 5 and 6
		foreach($array as $word) {
			file_put_contents("firstparts.txt", substr($word, 0, floor(strlen($word) / 2)) . "\n", LOCK_EX | FILE_APPEND);
			file_put_contents("secondparts.txt", substr($word, floor(strlen($word) / 2)) . "\n", LOCK_EX | FILE_APPEND);
		}
	break;
	case "6":
		echo "Task 6 is integrated with task 5.<br />";
	break;
	case "7":
		//Task 7 and 8
		$halves = array_merge(file('firstparts.txt'), file('secondparts.txt'));
		echo "Random word half: " . $halves[mt_rand(0, count($halves))] . "<br />";
		echo "Random word half inverted: " . strrev($halves[mt_rand(0, count($halves))]) . "<br />";
		echo "Storing word array and halves into SQL database...<br />";
		$sqlc = new mysqli($sqlhostname, $sqluser, $sqlpassword, $sqldatabase);
		if ($sqlc->connect_error) {
			die("<strong>Could not connect to the MySQL database:</strong> "  . $sqlc->connect_error . "<br />");
		} else {
		echo "Connected to the MySQL database.<br />";
		}
		$tbentered = array_merge($array, $halves);
		$sql = "INSERT INTO words (keyid, value) VALUES ";
		$it = new ArrayIterator($tbentered);
		$cit = new CachingIterator($it);
		foreach ($cit as $value) {
		$sql .= "('".$cit->key()."','" .$cit->current()."')";
			if( $cit->hasNext() ) {
				$sql .= ",";
			}
		}
		$result = $sqlc->query($sql);
		if ($result === false) {
			die("An error has occurred while doing the SQL transaction: " . $sqlc->error . "<br />");
		} else {
			echo "Successfully entered all the words and halves into the database.<br />";
		}
		$sqlc->close();
	break;
	case "8":
		echo "Task 8 is integrated with task 7.<br />";
	break;
	case "9":
		$sqlc = new mysqli($sqlhostname, $sqluser, $sqlpassword, $sqldatabase);
		if ($sqlc->connect_error) {
			die("<strong>Could not connect to the MySQL database:</strong> "  . $sqlc->connect_error . "<br />");
		} else {
		echo "Connected to the MySQL database.<br />";
		}
		$result = $sqlc->query("SELECT id, value FROM `words`");
		$words = array();
		echo '<form action="?task=9a" method="post">' . "\n";
		while($word = $result->fetch_assoc() ){
			echo '<p>' . $word["id"] . '. <input type="text" name="' . $word["id"] . '" value="' . str_replace("\n","",$word["value"]) . '"></input></p>' . "\n";
		}
		echo '<p><input type="submit" value="Submit"></input></p></form>';
	break;
	case "9a":
		// case 9a processes all the updates and forwards it to the MySQLi database
		$sqlc = new mysqli($sqlhostname, $sqluser, $sqlpassword, $sqldatabase);
		if ($sqlc->connect_error) {
			die("<strong>Could not connect to the MySQL database:</strong> "  . $sqlc->connect_error . "<br />");
		} else {
		echo "Connected to the MySQL database.<br />";
		}
		$sqlc->autocommit(0);
		foreach($_POST as $id => $value) {
			$sqlc->query('UPDATE words SET value="' . mysqli_escape_string($sqlc, $value) . '" WHERE id="' . mysqli_escape_string($sqlc, $id) . '"');
		}
		if (!$sqlc->commit()) {
    		die("<strong>Commit failed</strong>");
		} else {
			echo "Successfully updated.";
		}
	break;
	case "10":
		$sqlc = new mysqli($sqlhostname, $sqluser, $sqlpassword, $sqldatabase);
		if ($sqlc->connect_error) {
			die("<strong>Could not connect to the MySQL database:</strong> "  . $sqlc->connect_error . "<br />");
		} else {
		echo "Connected to the MySQL database.<br />";
		}
		// might as well pdo this later
		$query = (empty($_GET['q'])) ? die("Fail: You did not specify a query. Failing out.") : mysqli_escape_string($sqlc, $_GET['q']);
		$result = $sqlc->query("SELECT * FROM `words` WHERE value like \"%".$query."%\"");
		if($result->num_rows > 0) {
			echo $result->num_rows . " results found<br />";
			while($row = $result->fetch_assoc()){
				echo $row['id'] . ': ' . $row['value'] . '<br />';
			}
		} else {
			echo "No results were found.<br />";
		}
		$sqlc->close();
	break;
	default:
		echo "I don't recognize that task number.<br />";
	break;
}
?>