<?php

include_once "class/DexEntry.php";
include_once "class/Sidebar.php";

$data = json_decode(file_get_contents("data/fusions.json"));

//Search by Fusion name
if ( isset($_GET["name"]) )
{
	foreach ( $data as $f )
	{
		if ( $f->Name == $_GET["name"] )
		{
			header("location:dex.php?no=$f->index");
			die;
		}
	}
	
	header("location:dex.php");
}

$areas = json_decode(file_get_contents("data/areas.json"), true);
$credits = json_decode(file_get_contents("data/credits.json"), true);

$evolution_names = array();
$evolutions = array();

//Default Fusion Dex entry
if ( empty($_GET["no"]) || !array_key_exists($_GET["no"] - 1, $data) )
{
	$_GET["no"] = 1;
}

$fusion = $data[$_GET["no"] - 1]; //Lookup specified Fusion by index number
$evolution_names = explode(",", $fusion->Evolutions); //Evolution names to look for and retrieve more data

//Search for evolutions of this Fusion
foreach ( $data as $f )
{
	if ( !empty($evolution_names) && in_array($f->InternalName, $evolution_names) )
	{
		$evolutions[$f->InternalName] = $f;
	}
}

//Attach data crediting the creator(s) of this Fusion if it exists to the object
if ( array_key_exists($fusion->InternalName, $credits) )
{
	$fusion->credits = $credits[$fusion->InternalName];
}

$entry = new DexEntry($fusion, $evolutions, $areas[$fusion->dexno]); //Create dex entry
echo $entry->render(new Sidebar($data, $fusion)); //Output
