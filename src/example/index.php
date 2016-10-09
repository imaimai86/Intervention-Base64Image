<?php

include_once '../autoload.php';

/**
 * Show basic usage of Base64ImageHandler
 * This class writes uploaded file to a temporary location in the server, validates it.
 * If you want to process the file, you can use it
 */

if (is_array($_POST['base64Images']))
{
	foreach ($_POST['base64Images'] as $img)
	{
		$imageHandler = new Base64ImageHandler($img);
		if ($imageHandler->isValidImage())
		{
			//Auto deleting the uploaded image on the destructor of class 
			$imageHandler->autoDelete(true);
			//Upload image to some other place
			$data['param_images'][] = uploadImageToCdn($imageHandler->tempFilePath());
		}
		else
		{
			throw new Exception('Invalid param - image: '. implode('.', $imageHandler->errors()));
		}
	}
}

/**
 * Cleanup example
 */
if (is_array($_POST['base64Images']))
{
	foreach ($_POST['base64Images'] as $img)
	{
		$imageHandler = new Base64ImageHandler($img);
		if ($imageHandler->isValidImage())
		{
			//Upload image to some other place
			$data['param_images'][] = uploadImageToCdn($imageHandler->tempFilePath());
		}
		else
		{
			throw new Exception('Invalid param - image: '. implode('.', $imageHandler->errors()));
		}
	}
}
Base64ImageHandler::cleanup($data['param_image']);


/**
 * Example of all methods implemented
 */
$imageHandler = new Base64ImageHandler($_POST['base64Image']);
if ($imageHandler->isValidImage())
{
	//Upload image to some other place
	echo '<br />isValidImage: '; print_r($imageHandler->isValidImage());
	echo '<br />error:'; print_r($imageHandler->errors());
	echo '<br />extension:'; print_r($imageHandler->extension());
	echo '<br />fileSize:'; print_r($imageHandler->fileSize());
	echo '<br />imageSourceEncoded:'; print_r($imageHandler->imageSourceEncoded());
	echo '<br />mimeType:'; print_r($imageHandler->mimeType());
	echo '<br />width:'; print_r($imageHandler->width());
	echo '<br />height:'; print_r($imageHandler->height());
	echo '<br />tempFilePath:'; print_r($imageHandler->tempFilePath());
	
	//custom validation
	echo '<br />'; print_r($imageHandler->validation($data));
}




//Implement your own uploader / move to directory
function uploadImageToCdn()
{
	//Your code here....
}