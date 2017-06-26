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
			// $_SESSION['access_token'] = $client->getAccessToken();
			// $client->getAccessToken();
			// dd($token);
			// $client->setAccessToken($token);
			$drive = new Google_Service_Drive($client);
			// dd ($drive);
			$service = new Google_Service_Drive($client);

			$fileMetadata = new Google_Service_Drive_DriveFile(array(
			'name' => 'photo.png'));

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
