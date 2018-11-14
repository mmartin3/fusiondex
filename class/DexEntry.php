<?php

include_once "class/Sidebar.php";

/**
 * Fusion Dex UI class
 */
class DexEntry {
	var $fusion;     //The Fusion described into the evolution
	var $evolutions; //The array of the next stage or stages of evolution
	var $areas;      //Where to encounter/obtain the Fusion
	
	function __construct($fusion, array $evolutions, array $areas)
	{
		$this->fusion = $fusion;
		$this->evolutions = $evolutions;
		$this->areas = $areas;
	}
	
	/**
	 * Return the name of the Fusion's creator.
	 */
	function get_creator()
	{
		if ( empty($this->fusion->credits) )
		{
			return "electrovert";
		}
		
		return $this->fusion->credits[0];
	}
	
	/**
	 * Return a cell with the Fusion's primary type.
	 */
	function get_primary_type()
	{
		$type = strtolower($this->fusion->Type1);
		
		//Exceptional cases
		switch ( $this->fusion->Name )
		{
			case "Stanbuck":
				$title = "Stanbuck's primary type changes to Ice during December.";
				return "<td id='primary-type' class='type-cell normal'><a href='#' data-toggle='tooltip' data-placement='top' title=\"$title\">Normal</a></td>";
		}
		
		return "<td id='primary-type' class='type-cell $type'>".ucwords($type)."</td>";
	}
	
	/**
	 * Return a cell with Fusion's secondary type if it has one.
	 */
	function get_secondary_type()
	{
		//Exceptional cases
		switch ( $this->fusion->Name )
		{
			case "Bayveon":
				return "<td id='secondary-type' class='type-cell ice'><a href='#' data-toggle='tooltip' data-placement='top' title='Ice Forme'>Ice</a></td>";
			case "Quiveon":
				return "<td id='secondary-type' class='type-cell psychic'><a href='#' data-toggle='tooltip' data-placement='top' title='Psy Forme'>Psychic</a></td>";
			case "Croveon":
				return "<td id='secondary-type' class='type-cell dark'><a href='#' data-toggle='tooltip' data-placement='top' title='Dark Forme'>Dark</a></td>";
			case "Flaaveon":
				return "<td id='secondary-type' class='type-cell fairy'><a href='#' data-toggle='tooltip' data-placement='top' title='Nymph Forme'>Fairy</a></td>";
			case "Starus":
				return "<td id='secondary-type' class='type-cell psychic'><a href='#' data-toggle='tooltip' data-placement='top' title='Me First Forme'>Psychic</a></td>";
		}
		
		if ( empty($this->fusion->Type2) )
		{
			return "<td class='type-cell ".strtolower($this->fusion->Type1)."'></td>";
		}
		
		$type = strtolower($this->fusion->Type2);
		
		//Fusion Gen I fairies
		if ( $this->fusion->index < 105 && $type == "fairy" )
		{
			return "<td id='secondary-type' class='type-cell fairy'><a href='#' data-toggle='tooltip' data-placement='top' title='Fusion Generation II only.'>Fairy</a></td>";
		}
		
		return "<td id='secondary-type' class='type-cell $type'>".ucwords($type)."</td>";
	}
	
	/**
	 * Return cell(s) with any possible abilities.
	 */
	function get_abilities()
	{
		$ab = explode(",", $this->fusion->Abilities);
		
		//Perfect Match
		if ( $ab[0] == "PERFECTMATCH" )
		{
			return "<td colspan=2><a href='#' data-toggle='tooltip' data-placement='top' title='Doubles Sp. Attack stat.'>Perfect Match</a></td>";
		}
		
		//Irreconcilable
		elseif ( $ab[0] == "IRRECONCILABLE" )
		{
			$title = "Separates if used for too many consecutive turns. No effect on opponent's Fusion.";
			
			return "<td colspan=2><a href='#' data-toggle='tooltip' data-placement='top' title=\"$title\">Irreconcilable</a></td>";
		}
		
		//Duplicated ability
		elseif ( count(array_unique($ab)) < count($ab) )
		{
			return "<td colspan=2>".ucwords(strtolower($ab[0]))."</td>";
		}
		
		//Dual abilities
		elseif ( strpos($this->fusion->Abilities, ",") !== false )
		{			
			return "<td>".ucwords(strtolower($ab[0]))."</td><td>".ucwords(strtolower($ab[1]))."</td>";
		}
		
		//Single ability
		return "<td colspan=2>".ucwords(strtolower($this->fusion->Abilities))."</td>";
	}
	
	/**
	 * Return human readable base stats.
	 */
	private function get_base_stats()
	{
		$total = 0;
		
		//Calculate base stat total
		foreach ( explode(",", $this->fusion->BaseStats) as $value )
		{
			$total += $value;
		}
		
		return "<td>".str_replace(",", "</td><td>", $this->fusion->BaseStats)."</td><td>$total</td>";
	}
	
	/**
	 * Table describing how to evolve to the next stage if the Fusion has one.
	 */
	private function evolution_table()
	{
		//Does not evolve
		if ( empty($this->evolutions) )
		{
			$text = "<tr><td>".$this->fusion->Name." is the final stage and does not evolve.</td></tr>";
		}
		
		else
		{
			$text = "";
			
			//Describe each possible evolution
			foreach ( array_chunk(explode(",", $this->fusion->Evolutions), 3) as $chunk )
			{
				$text .= "<tr><td>".$this->describe_evolution($chunk)."</td></tr>";
			}
			
			//Special starter forms
			switch ( $this->fusion->Name )
			{
				case "Chikovee":
					$text .= "<tr><td>Becomes Ice Bayveon if evolved in the Snowy Meadow Habitat.</td></tr>";
					break;
				case "Cyndavee":
					$text .= "<tr><td>Becomes Psy Quiveon if evolved in the Sunny Habitat.</td></tr>";
					break;
				case "Totovee":
					$text .= "<tr><td>Becomes Dark Croveon if evolved in the Dark Swamp Habitat.</td></tr>";
					break;
				case "Mareevee":
					$text .= "<tr><td>Becomes Nypmh Flaaveon if evolved in the Enchanted Rainforest Habitat.</td></tr>";
			}
		}
		
		return <<<HTML
			<table class="table table-striped table-hover table-bordered" id="evolution">
				<tbody>
					<tr>
						<th>Evolution</th>
					</tr>
					$text
				</tbody>
			</table>
HTML;
	}
	
	/**
	 * Hyperlink to other Fusions that appear in the given string
	 */
	private function linkify($desc)
	{
		if ( $desc )
		{
			$words = explode(" ", $desc);
			
			if ( count($words) > 1 && $words[0] == "Evolve" )
			{
				for ( $i = 1; $i < count($words); $i++ )
				{
					$prev_stage = trim(str_replace(",", "", $words[$i]));
					$icon = "icons/$prev_stage.png";
					
					if ( !file_exists($icon) )
					{
						continue;
					}
					
					$words[$i] = "<a href='dex.php?name=$prev_stage'>$prev_stage</a> <img src='$icon' class='ficon' />";
					$desc = implode(" ", $words);
					break;
				}
			}
		}
		
		return $desc;
	}
	
	/**
	 * Basic Fusion info table
	 */
	private function summary_table()
	{
		return <<<HTML
			<table class="table table-striped table-hover table-bordered" id="species">
				<tbody>
					<tr>
						<th>#</th>
						<th>Name</th>
						<th>Kind</th>
						<th>Creator</th>
					</tr>
					<tr>
						<td>{$this->fusion->dexno}</td>
						<td>{$this->fusion->Name}</td>
						<td>{$this->fusion->Kind} Fusion</td>
						<td>{$this->get_creator()}</td>
					</tr>
				</tbody>
			</table>
HTML;
	}
	
	/**
	 * Table showing where to find the Fusion in each game
	 */
	private function area_table()
	{
		$name = $this->fusion->Name;
		$areas = $this->areas;
		$pkmn = "<sup>P</sup><sub>K</sub><sup>M</sup><sub>N</sub>";
		$pfg_encounter = empty($areas[0]) ? "$name cannot be seen in $pkmn Fusion Generation." : $areas[0];
		$pfg_capture = empty($areas[1]) ? "$name cannot be caught in $pkmn Fusion Generation." : $this->linkify($areas[1]);
		$pfgii_encounter = empty($areas[2]) ? "$name is not seen in Fusion Generation II." : $areas[2];
		$pfgii_capture = $this->linkify($areas[3]);
		
		//Display more Fusion Generation II obtain details as a tooltip if available
		if ( count($areas) > 4 )
		{
			$pfgii_capture = <<<HTML
				<a href="#" data-toggle="tooltip" data-placement="top" title="$areas[4]">$pfgii_capture</a>
HTML;
		}
		
		return <<<HTML
			<table class="table table-striped table-hover table-bordered" id="nest">
				<tbody>
					<tr>
						<th></th><th>Encounter</th><th>Obtain</th>
					</tr>
					<tr>
						<th>$pkmn Fusion Generation</th><td>$pfg_encounter</td><td>$pfg_capture</td>
					</tr>
					<tr>
						<th>Fusion Generation II</th><td>$pfgii_encounter</td><td>$pfgii_capture</td>
					</tr>
				</tbody>
			</table>
HTML;
	}
	
	/**
	 * Translate evolution details into human-readable format
	 */
	private function describe_evolution($data)
	{
		$internalname = $data[0];
		$evolution = array_key_exists($internalname, $this->evolutions) ? $this->evolutions[$internalname] : $this->fusion;
		$realname = $evolution->Name;
		$realname_linked = "<a href='dex.php?no=$evolution->index'>$realname</a> <img src='icons/$evolution->index.png' class='ficon' />";
		$prefix = "Evolves into $realname_linked";
		$method = $data[1];
		$value = ucwords(strtolower($data[2]));
		$suffix = ($this->fusion->index < 105 && strpos($this->fusion->Name, "vee") === false) ? " (Fusion Generation II only)" : "";
		
		switch ( $method )
		{
			case "Level":
				return "$prefix at level $value.$suffix";
			case "Item":
				return "$prefix with $value.$suffix";
			case "Happiness":
				return "$prefix with maximum happiness.$suffix";
			case "HappinessDay":
				return "$prefix with happiness during the day.$suffix";
			case "HappinessNight":
				return "$prefix with happiness at night.$suffix";
			case "Trade":
				return "$prefix by trading (therefore it cannot be evolved legitimately in the beta).$suffix";
			case "TradeItem":
				return "$prefix when traded holding $value (cannot be evolved legitimately in the beta).$suffix";
			case "HasMove":
				return "$prefix when leveled up with move $value.$suffix";
			case "DayHoldItem":
				return "$prefix holding $value during the day.$suffix";
			case "ItemFemale":
				return "$prefix with $value (female only).$suffix";
			case "Custom1":
				if ( $this->fusion->Name == "Bayveon" ) {
					return "Ice Bayveon evolves into $realname_linked at level $value.";
				} elseif ( $this->fusion->Name == "Quiveon" ) {
					return "Psy Quiveon evolves into $realname_linked at level $value.";
				} elseif ( $this->fusion->Name == "Croveon" ) {
					return "Dark Croveon evolves into $realname_linked at level $value.";
				} elseif ( $this->fusion->Name == "Flaaveon" ) {
					return "Nymph Flaaveon evolves into $realname_linked at level $value.";
				} else {
					return "Evolves at level $value (alternate form).$suffix";
				}
			case "Custom4":
				return "$prefix at level $value with a Dark type in the party.";
			case "Custom6":
				return "$prefix at level $value holding Prism Scale.";
			case "Shedinja":
				return "Yields Fusion Forme Shedinja under same conditions as Nincada.";
		}
		
		if ( is_numeric($value) )
		{
			return "$prefix at level $value ($method).$suffix";
		}
		
		return "$prefix ($method, $value).$suffix";
	}
	
	/**
	 * Table of Fusion's ability or abilities
	 */
	private function ability_table()
	{
		return <<<HTML
			<table class="table table-striped table-hover table-bordered" id="ability">
				<tbody>
					<tr>
						<th colspan=2>Type</th>
						<th colspan=2>Ability</th>
					</tr>
					<tr>
						{$this->get_primary_type()}
						{$this->get_secondary_type()}
						{$this->get_abilities()}
					</tr>
				</tbody>
			</table> 
HTML;
	}
	
	/**
	 * Table containing Fusion's base stats
	 */
	private function base_stats_table()
	{
		return <<<HTML
			<table class="table table-striped table-hover table-bordered" id="stats">
				<tbody>
					<tr>
						<th>HP</th>
						<th>ATK</th>
						<th>DEF</th>
						<th>SPD</th>
						<th>SP ATK</th>
						<th>SP DEF</th>
						<th>Total</th>
					</tr>
					<tr>
						{$this->get_base_stats()}
					</tr>
				</tbody>
			</table>
HTML;
	}
	
	/**
	 * Table containing Fusion's Pokedex description
	 */
	private function dex_table()
	{
		return <<<HTML
			<table class="table table-striped table-hover table-bordered" id="stats">
				<tbody>
					<tr>
						<th>Dex Entry</th>
					</tr>
					<tr>
						<td>{$this->fusion->Pokedex}</td>
					</tr>
				</tbody>
			</table>
HTML;
	}
	
	/**
	 * List of moves learned by leveling up
	 */
	private function learnset_table()
	{
		$movelist = "";
		$moves = array_chunk(explode(",", $this->fusion->Moves), 2);
		
		foreach ( $moves as $chunk )
		{
			if ( $chunk[0] == "1" )
			{
				$chunk[0] = "-";
			}
			
			$move = "<tr><td>$chunk[0]</td><td>".ucfirst(strtolower($chunk[1]))."</td></tr>";
			
			//Dedupe
			if ( isset($prev) && $move == $prev )
			{
				continue;
			}
			
			$movelist .= $move;
			$prev = $move;
		}
		
		return <<<HTML
			<table class="table table-striped table-hover table-bordered" id="learnset">
				<tbody>
					<tr>
						<th>Level</th>
						<th>Move</th>
					</tr>
					$movelist
				</tbody>
			</table>
HTML;
	}
	
	/**
	 * Return HTML for Fusion Dex entry
	 */
	function render(Sidebar $sidebar)
	{		
		return <<<HTML
			<html>
				<head>
					<title>Fusion Dex / {$this->fusion->Name}</title>
					<link href="//stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
					<link href="css/sidebar.css" rel="stylesheet">
					<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
					<script src="//unpkg.com/popper.js"></script>
					<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
					<link rel='shortcut icon' href='icons/{$this->fusion->index}.png' type='image/x-icon' />
					<script>
					$(function () {
					  $("[data-toggle='tooltip']").tooltip();
					  var itemHeight = $(".sidebar li").outerHeight();
					  $(".sidebar")[0].scrollTop = itemHeight * {$this->fusion->index} - itemHeight * 2;
					});
					</script>
					<meta name="google" content="notranslate">
				</head>
				<body>
					<aside class="sidebar">
					  <nav>
						<ul>
							{$sidebar->render()}
						</ul>
					  </nav>
					</aside>
					<section class="main">
						<div class="container">
							{$this->summary_table()}
							{$this->ability_table()}         
							{$this->base_stats_table()}
							{$this->dex_table()}
							{$this->evolution_table()}
							{$this->area_table()}
							{$this->learnset_table()}
						</div>
					</section>
				</body>
			</html>
HTML;
	}
}
