<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;


class UploadFileController extends Controller
{
	public function index()
	{
		$client = new Google_Client();
		$client->setAuthConfig(__DIR__ . '/client_secret.json');
		$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
		$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback';
		header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
		exit();
	}

	public function oauth2callback()
	{
		$client = new Google_Client();
		$client->setAuthConfigFile(__DIR__ . '/client_secret.json');
		$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback');
		$client->addScope(Google_Service_Drive::DRIVE);
		$client->setAccessType('offline');

		if (! isset($_GET['code'])) {
		  
		  $auth_url = $client->createAuthUrl();
		  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
		  exit();

		} else {
			
			$client->authenticate($_GET['code']);
			
			$service = new Google_Service_Drive($client);
			
			$folderName = "File Upload on User Google Drive";
			$files = $service->files->listFiles();

			$found = false;

			foreach ($files as $key=>$value) {
				if ($files[$key]['name'] == $folderName) {
					$found = true;
					$folderId = $files[$key]['id'];
					break;
				}
			}

			if ($found == false) {
				$fileMetadata = new Google_Service_Drive_DriveFile(array(
				'name' => $folderName,
				'mimeType' => 'application/vnd.google-apps.folder'));
				$folder = $service->files->create($fileMetadata, array(
				'fields' => 'id'));
				$folderId = $folder->id;
			}

			$fileMetadata = new Google_Service_Drive_DriveFile(array(
			'name' => 'photo.png',
			'parents' => array($folderId)));

			$content = file_get_contents(__DIR__ . '/Screenshot (3).png');
			$file = $service->files->create($fileMetadata, array(
			  'data' => $content,
			  'mimeType' => 'image/jpeg',
			  'uploadType' => 'multipart',
			  'fields' => 'id'));

			dd($file->id);
		}
	}
}
