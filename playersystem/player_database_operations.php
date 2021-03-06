<?php 

include_once('player.php');

class PlayerDatabaseOperations {

	/* TABLE format is PID, name, games_played, games_won, win_percent, 
  		avg_assists, most_played_support, lolking, date_added 
  		Add information into the table. */

	function __construct() {
		$this->con = mysqli_connect("localhost", "admin", "candy12", "summoners");
		// Check connection
		if (!$this->con) {	
			//TODO: relocate to error page
			header("Location: /index.php");
  		}
	}

	public function add_player(&$player) {
		/** Takes a reference to a Player instance, extracts all relevant information
		from the Player, and then inputs the information into the database. 
		This creates a player in the database. */

		$id = $player->get_id();
		$name = $player->get_name();
		$region = $player->get_region();
		$games_played = $player->get_games_played();
		$games_won = $player->get_games_won();
		$win_percent = $player->get_win_percent();
		$avg_assists = $player->get_avg_assists();
		$most_played_support = $player->get_most_played_support();
		$lolking = $player->get_lolking();
		$mmr = $player->get_mmr();

		//One summoner added if INSERT runs without error.
		$sql = "INSERT INTO support (PID, name, region, games_played, games_won, 
			win_percent, avg_assists, most_played_support, lolking, mmr, date_added) 
			VALUES ('$id', '$name', '$region', '$games_played', '$games_won', '$win_percent','$avg_assists',
				'$most_played_support', '$lolking', '$mmr', UNIX_TIMESTAMP())";
		
		if (!mysqli_query($this->con, $sql)) {
			//TODO: make this error not so out there
			die('Error: ' . mysqli_error($this->con));
		}
	}

	public function update_player(&$player)  {
		/** Update a player within the database to contain
		the new stats. This function is only called if the previous 
		information for the player hasn't been updated by a specific X time. */

		$id = $player->get_id();
		$name = $player->get_name(); //In case of player name change.
		$games_played = $player->get_games_played();
		$games_won = $player->get_games_won();
		$win_percent = $player->get_win_percent();
		$avg_assists = $player->get_avg_assists();
		$most_played_support = $player->get_most_played_support();
		$mmr = $player->get_mmr();

		$sql = "UPDATE support SET 
			name = '$name', 
			games_played = '$games_played', 
			games_won = '$games_won',
			win_percent = '$win_percent', 
			avg_assists = '$avg_assists',
			most_played_support = '$most_played_support',
			mmr = '$mmr', 
			date_added = UNIX_TIMESTAMP()
			WHERE PID = $id";

		$query = mysqli_query($this->con, $sql);
		if (!mysqli_query($this->con, $sql)) {
			//TODO: make this error not so out there
			die('Error: ' . mysqli_error($this->con));
		}
    }

    public function get_player_information($id) {
    	/** Return an associate array with all of the Player instance's 
    	information from the database. */
    	$sql = "SELECT * from support where PID = '$id'";
    	$query = mysqli_query($this->con, $sql);

    	//Error occured with getting the necessary information.
    	if (!$query) {
    		die('Error: ' . mysqli_error($this->con));
    	}

    	//Info is the associative array with all relevant user information.
    	$info = $query->fetch_assoc();
    	return $info;
    }       

    public function get_other_players(&$player) {
    	/** Return an array of arrays of all the other players
    	who are near the mmr of the player specified.  */

    	$mmr = $player->get_mmr();
    	$name = $player->get_name();
    	$region = $player->get_region();
    	$mmr_max = $mmr + 20;
    	$mmr_min = $mmr - 20;

    	//Makes a bit of sense to have a limit of 0 for min.
    	if ($mmr_min < 0) {
    		$mmr_min = 0;
    	}

    	$sql = "SELECT * from support WHERE 
    			region = '$region' 
    			AND mmr BETWEEN $mmr_min AND $mmr_max 
    			AND win_percent >= 60 AND win_percent <= 100
    			AND NOT name = '$name'";
    	$query = mysqli_query($this->con, $sql);
    	
    	//Error occured with getting the necessary information.
    	if (!$query) {
    		die('Error: ' . mysqli_error($this->con));
    	}

    	//all_other_players contains array data of all the champions within the given mmr
    	$all_other_players = array(); 
    	while ($row = $query->fetch_assoc()) {
    		array_push($all_other_players, $row);
    	} 
    	return $all_other_players;	
    
    } 
             
	public function player_needs_update($id) {
		/** Predicate function which returns true if and only if 
		a player needs to be updated in the database. */
		$sql = "SELECT date_added from support WHERE PID = '$id'";
		$query = mysqli_query($this->con, $sql); 

		//Error occurred with getting the necessary information. 
		if (!$query) {
			die('Error: ' . mysqli_error($this->con));
		}

		//The values for date_added and now are put into variables.
		$row = $query->fetch_assoc();
		$date_added = $row['date_added'];
		$date_now = time();

		//If player hasn't been updated in 4 hours, the player needs an update.
		if ($date_now - $date_added > 14400) {
			return true;
		}  return false;		
	}	


	public function contains_ID($id) {
		/** Return true if an only if the database contains the PlayerID 
		passed as a parameter. */
		$query = mysqli_query($this->con, "SELECT PID from support WHERE PID = '$id'");
		if ($query->num_rows > 0) {
			return true;
		} 
		return false;
	}
	
	public function close_db() {
		/** Closes the database. Sometimes we do not want to close
		the database after a single operation. */
		mysqli_close($this->con);
	}

}

?>