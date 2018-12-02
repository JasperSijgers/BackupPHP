# BackupPHP
A simple script to back up a specified directory

## Configuration
Configure the script to work in your intended use case. Set the directory to back up, the output directory, number of days to keep the backups for, backup names and the key in order for the script to run. If run without the key, or with an invalid key, the script will exit. 

```php
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
```


## Running the script
It is recommended to set up a repeating task (cronjob) in order to run the script at specified intervals (for example, each day). 

<b>Web Browser</b><br>
The script can be run by browsing to its location using a web browser (if placed in a public directory), and providing its private key using <i>?private_key=[private-key]</i>. 

<b>Command Line</b><br>
To run the script from the command-line, run: <i>php-cgi [path-to-script] private_key=[private-key]</i>


## Code Explained
<b>Private Key</b><br>
The code first checks if the private key is provided, and if so, if it matches the key set in the configuration section. If the key is invalid or not provided, the script will terminate.

```php
if(!isset($_GET['private_key'])){
	echo('private key not provided... exiting!' . PHP_EOL);
	exit;
}

if($_GET['private_key'] !== $private_key){
	echo('private key invalid... exiting!' . PHP_EOL);
	exit;
}
```

<b>File Paths</b><br>
The code then checks if the data directory as defined exists. If the path doesn't exist, it will create the necessary directories. Note that, depending on the set path, this may require elevated privileges.
```php
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
```

<b>Old Backups</b><br>
The backup folder is scanned for any files last modified before the maximum age of the oldest backup as set in the configuration section. If those are found, the files are deleted. <u>This means that any archives (.tar.gz) placed in the directory that aren't backups are also likely to get deleted!</u>
```php
// Check for old backups to be deleted
echo('Checking for old backups to be deleted' . PHP_EOL);
$files = glob($backup_dir . '/*.tar.gz');
foreach($files as $file){
	if(time() - filemtime($file) > $backup_max_d * 24 * 60 * 60){
		echo('Deleting backup: ' . $file . PHP_EOL);
		unlink($file);
	}
}
```

<b>Backup Creation</b><br>
The script is set to create a backup of the specified folder, and place it in the output directory. To do this, it must find a suitable name. If a file already exists with the name you provided in the configuration section, it will automatically add (1),(2),(3),etc. until it finds a filename that hasn't yet been used. It is therefor recommended to use a name that will be unique when the script is run (for example, including the date, time, or both).
```php
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
```
