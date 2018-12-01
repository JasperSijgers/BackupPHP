<?php

/**
*	Backup Script v1.0 by Jasper Sijgers
*	
*	Protect the access to this file (DO NOT PUT IT IN A PUBLICLY ACCESSIBLE FOLDER)
*	Do NOT share the private key below
*
*	!! Please note that this script will NOT store empty directories in its created archives !!
*
*	Created: December 1, 2018
*/

// Set the private key required for running the backup script
$private_key = 'PLEASE_CHANGE_THIS';

// Set the directory to be backed up
$data_dir = '';

// Set a directory in which to store the backups
$backup_dir = '/backups';

// Number of days to keep backups
$backup_max_d = 10;

// Set the naming format for the backups
$name_format = 'backup-' . date('d-m-Y');

// ___________________________ DO NOT CHANGE CODE BELOW THIS LINE ___________________________

// Check if private key was used to execute file
if(!isset($_GET['private_key'])){
	echo('private key not provided... exiting!' . PHP_EOL);
	exit;
}

if($_GET['private_key'] !== $private_key){
	echo('private key invalid... exiting!' . PHP_EOL);
	exit;
}

// Check if the data directory as defined above exists
if(!file_exists($data_dir)){
	echo('directory ' . $data_dir . ' not found!' . PHP_EOL);
	exit;
}

// Create the path for the backup directory if it doesn't yet exist
if(!file_exists($backup_dir)){
	exec('mkdir -p ' . $backup_dir);
	echo('creating directory path ' . $backup_dir . PHP_EOL);
}

// Check for old backups to be deleted
echo('Checking for old backups to be deleted' . PHP_EOL);
$files = glob($backup_dir . '/*.tar.gz');
foreach($files as $file){
	if(time() - filemtime($file) > $backup_max_d * 24 * 60 * 60){
		echo('Deleting backup: ' . $file . PHP_EOL);
		unlink($file);
	}
}

// Create a backup of the directory
$backup_name = $backup_dir . '/' . $name_format;

if(file_exists($backup_name . '.tar.gz')){
	$backup_name .= ' (' . find_ind($backup_name, 1) . ')';
}

$archive_location = $backup_name . '.tar';

$archive = new PharData($archive_location);
$archive->buildFromDirectory($data_dir);
$archive->compress(Phar::GZ);
unlink($archive_location);

echo('Backup has been made: ' . $archive_location . '.gz' . PHP_EOL);

function find_ind($file, $i){
	if(!file_exists($file . ' (' . $i . ').tar.gz'))
		return $i;
	else {
		$i++;
		return find_ind($file, $i);
	}
}
